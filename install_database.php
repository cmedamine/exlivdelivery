<?php
/**
 * EXLIV Delivery - Database Installation Script
 * Ce script installe automatiquement toutes les tables de la base de données
 */

session_start();
include("config.php");

// Vérifier si l'utilisateur est connecté et est modérateur
if(!isset($_SESSION['id']) || $_SESSION['type'] != "moderator"){
    die("Accès non autorisé. Vous devez être connecté en tant que modérateur.");
}

// Lire le fichier SQL
$sqlFile = __DIR__ . '/database_schema.sql';

if (!file_exists($sqlFile)) {
    die("Erreur: Le fichier database_schema.sql n'existe pas.");
}

$sql = file_get_contents($sqlFile);

if ($sql === false) {
    die("Erreur: Impossible de lire le fichier database_schema.sql.");
}

// Diviser le SQL en requêtes individuelles
$queries = array_filter(array_map('trim', explode(';', $sql)));

// Exécuter chaque requête
$errors = [];
$successCount = 0;

foreach ($queries as $query) {
    // Ignorer les commentaires et les lignes vides
    if (empty($query) || strpos($query, '--') === 0 || strpos($query, 'SET') === 0) {
        continue;
    }
    
    try {
        $bdd->exec($query);
        $successCount++;
    } catch (PDOException $e) {
        // Ignorer les erreurs de "table already exists"
        if (strpos($e->getMessage(), 'already exists') === false) {
            $errors[] = "Erreur: " . $e->getMessage() . "<br>Requête: " . substr($query, 0, 100) . "...";
        }
    }
}

// Afficher le résultat
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Base de Données - EXLIV Delivery</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #667eea;
            font-size: 28px;
            font-weight: bold;
        }
        .success {
            background: #efe;
            color: #3c3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .info {
            background: #eef;
            color: #36c;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #36c;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-right: 10px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #666;
        }
        .stats {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .stats-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .stats-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .stats-label {
            font-weight: 600;
            color: #333;
        }
        .stats-value {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>📦 EXLIV Delivery</h1>
            <p>Installation Base de Données</p>
        </div>
        
        <?php if (empty($errors)): ?>
            <div class="success">
                <strong>✅ Installation réussie!</strong>
                <p>La base de données a été installée avec succès.</p>
            </div>
        <?php else: ?>
            <div class="error">
                <strong>⚠️ Erreurs rencontrées:</strong>
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stats-item">
                <span class="stats-label">Requêtes exécutées:</span>
                <span class="stats-value"><?php echo $successCount; ?></span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Erreurs:</span>
                <span class="stats-value"><?php echo count($errors); ?></span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Tables créées:</span>
                <span class="stats-value">22</span>
            </div>
        </div>
        
        <div class="info">
            <strong>📝 Tables installées:</strong>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>users (utilisateurs)</li>
                <li>settings (paramètres globaux)</li>
                <li>parametres (paramètres utilisateur)</li>
                <li>cities (villes)</li>
                <li>shippingfees (frais livraison)</li>
                <li>clientfees (frais clients)</li>
                <li>gshippingfees (frais globaux)</li>
                <li>trackingstates (états de suivi)</li>
                <li>commands (commandes)</li>
                <li>commandshistory (historique)</li>
                <li>stocks (stocks clients)</li>
                <li>stockdlms (stocks livreurs)</li>
                <li>shipments (envois)</li>
                <li>packaging (emballages)</li>
                <li>products (produits)</li>
                <li>stores (magasins)</li>
                <li>subdlm (sous-livreurs)</li>
                <li>notices (annonces)</li>
                <li>expenses (dépenses)</li>
                <li>smsdevices (appareils SMS)</li>
                <li>smsmodels (modèles SMS)</li>
                <li>reclamations (réclamations)</li>
                <li>spreadsheets (Google Sheets)</li>
                <li>bls (bons de livraison)</li>
                <li>factures (factures)</li>
                <li>audit_log (journal d'audit)</li>
                <li>csrf_tokens (tokens CSRF)</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn">Retour au tableau de bord</a>
            <a href="install_database.php" class="btn btn-secondary">Réinstaller</a>
        </div>
    </div>
</body>
</html>
