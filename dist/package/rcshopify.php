<?php
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

// Shopify Data
$shopify_secret = $_ENV['SHOPIFY_APP_SECRET'] ?? '';

if (empty($shopify_secret)) {
    die('Erreur: SHOPIFY_APP_SECRET non configuré dans .env');
}

function verify_webhook($data, $hmac_header, $secret)
{
  $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
  return hash_equals($hmac_header, $calculated_hmac);
}

$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
$data = file_get_contents('php://input');

if (empty($hmac_header)) {
    die('Erreur: HMAC header manquant');
}

$verified = verify_webhook($data, $hmac_header, $shopify_secret);

if (!$verified) {
    log_error('Shopify webhook verification failed', ['hmac' => $hmac_header]);
    die('Erreur: Vérification webhook échouée');
}

// Retrieve Order Data
$data = json_decode($data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    log_error('Shopify webhook JSON decode error', ['error' => json_last_error_msg()]);
    die('Erreur: Données JSON invalides');
}

try {
    $fullname = ($data['customer']['first_name'] ?? '') . " " . ($data['customer']['last_name'] ?? '');
    $phone = $data['customer']['phone'] ?? '';
    $address = ($data['customer']['default_address']['address1'] ?? '') . " " . ($data['customer']['default_address']['address2'] ?? '');
    $city = $data['customer']['default_address']['city'] ?? '';
    $products = "";
    $qtys = "";
    
    if (isset($data['line_items']) && is_array($data['line_items'])) {
        for($i = 0; $i < count($data['line_items']); $i++) {
            $products .= "," . ($data['line_items'][$i]['title'] ?? '') . " - " . ($data['line_items'][$i]['variant_title'] ?? '');
            $qtys .= "," . ($data['line_items'][$i]['quantity'] ?? 0);
        }
        $products = substr($products, 1);
        $qtys = substr($qtys, 1);
    }
    
    $price = $data['total_price_set']['shop_money']['amount'] ?? 0;

    // Saving Data
    include("config.php");

    $back = $bdd->query("SELECT id FROM commands WHERE trash='1'");
    $rand = 'CMD-' . date('dmY') . '-' . sprintf("%05d", ($back->rowCount() + 1));
    
    $req = $bdd->prepare("INSERT INTO commands(id,code,product,qty,dlm,subdlm,worker,store,source,fullname,phone,address,city,price,fees,phase,state,datereported,note,workers,invoiced,dateadd,dateupdate,trash) 
    VALUES ('0',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $req->execute([
        $rand,
        sanitize_vars($products),
        sanitize_vars($qtys),
        '0',
        '',
        '0',
        '0',
        'Site',
        sanitize_vars($fullname),
        sanitize_vars($phone),
        sanitize_vars($address),
        sanitize_vars($city),
        sanitize_vars($price),
        '0',
        'confirmation',
        'Nouveau',
        '',
        '',
        '0',
        'off',
        time(),
        time(),
        '1'
    ]);
    
    $req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,dateadd) VALUES (?,?,?,?)");
    $req->execute(['0', $rand, 'Nouveau', time()]);
    
    log_error('Shopify webhook processed successfully', ['command' => $rand]);
    
} catch (Exception $e) {
    log_error('Shopify webhook processing error', ['error' => $e->getMessage()]);
    die('Erreur: ' . $e->getMessage());
}