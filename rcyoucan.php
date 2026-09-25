<?php
/**
 * EXLIV Delivery - Youcan Webhook Integration
 * Webhook pour recevoir les commandes depuis la plateforme Youcan
 */

// Charger les variables d'environnement
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Youcan API Secret (optionnel pour vérification)
$youcan_secret = $_ENV['YOUCAN_API_SECRET'] ?? '';

// Récupérer les données du webhook
$data = file_get_contents('php://input');

if (empty($data)) {
    die('Erreur: Aucune donnée reçue');
}

// Décoder les données JSON
$data = json_decode($data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    log_error('Youcan webhook JSON decode error', ['error' => json_last_error_msg()]);
    die('Erreur: Données JSON invalides');
}

try {
    // Extraire les informations de la commande Youcan
    // Format typique de l'API Youcan (adapter selon documentation réelle)
    $order_id = $data['id'] ?? '';
    $order_reference = $data['reference'] ?? '';
    $customer_name = ($data['customer']['first_name'] ?? '') . " " . ($data['customer']['last_name'] ?? '');
    $customer_phone = $data['customer']['phone'] ?? '';
    $customer_address = ($data['shipping_address']['address1'] ?? '') . " " . ($data['shipping_address']['address2'] ?? '');
    $customer_city = $data['shipping_address']['city'] ?? '';
    
    // Produits
    $products = "";
    $qtys = "";
    
    if (isset($data['items']) && is_array($data['items'])) {
        for($i = 0; $i < count($data['items']); $i++) {
            $products .= "," . ($data['items'][$i]['name'] ?? '') . " - " . ($data['items'][$i]['variant'] ?? '');
            $qtys .= "," . ($data['items'][$i]['quantity'] ?? 0);
        }
        $products = substr($products, 1);
        $qtys = substr($qtys, 1);
    }
    
    $total_price = $data['total'] ?? 0;
    
    // Inclure config et sauvegarder
    include("config.php");

    // Vérifier si la commande existe déjà
    $req = $bdd->prepare("SELECT id FROM commands WHERE code = ? OR tracking_code = ?");
    $req->execute([$order_reference, $order_id]);
    
    if ($req->rowCount() > 0) {
        // Commande déjà existante, mettre à jour si nécessaire
        log_error('Youcan webhook: Commande déjà existante', ['reference' => $order_reference]);
        echo json_encode(['status' => 'success', 'message' => 'Commande déjà existante']);
        exit;
    }
    
    // Générer le code de commande
    $back = $bdd->query("SELECT id FROM commands WHERE trash='1'");
    $code = 'CMD-' . date('dmY') . '-' . sprintf("%05d", ($back->rowCount() + 1));
    
    // Générer le code de tracking
    $tracking_code = 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 10));
    
    // Insérer la commande
    $req = $bdd->prepare("INSERT INTO commands(id,code,product,qty,dlm,subdlm,worker,store,source,package_type,fullname,phone,address,city,price,fees,phase,state,datereported,note,workers,invoiced,tracking_code,dateadd,dateupdate,trash) 
    VALUES ('0',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $req->execute([
        $code,
        sanitize_vars($products),
        sanitize_vars($qtys),
        '0',
        '',
        '0',
        '0',
        'Youcan',
        'normal',
        sanitize_vars($customer_name),
        sanitize_vars($customer_phone),
        sanitize_vars($customer_address),
        sanitize_vars($customer_city),
        sanitize_vars($total_price),
        '0',
        'confirmation',
        'Nouveau',
        '',
        '',
        '',
        'off',
        $tracking_code,
        time(),
        time(),
        '1'
    ]);
    
    // Ajouter à l'historique
    $req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,dateadd) VALUES (?,?,?,?)");
    $req->execute(['0', $code, 'Nouveau', time()]);
    
    // Logger
    log_error('Youcan webhook processed successfully', ['command' => $code, 'youcan_id' => $order_id]);
    
    echo json_encode(['status' => 'success', 'command_code' => $code, 'tracking_code' => $tracking_code]);
    
} catch (Exception $e) {
    log_error('Youcan webhook processing error', ['error' => $e->getMessage()]);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
