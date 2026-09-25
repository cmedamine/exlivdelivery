<?php
session_start();

// Vérifier si déjà installé
if (file_exists('.env') && file_exists('config_installed.php')) {
    header('location: index.php');
    exit;
}

$error = '';
$success = '';

// Traitement du formulaire d'installation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = trim($_POST['db_pass'] ?? '');
    
    // Validation
    if (empty($db_host) || empty($db_name) || empty($db_user)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        // Test de connexion à la base de données avec plusieurs hôtes possibles
        // Sur Hostinger, utilisez TOUJOURS 'localhost' pour les connexions internes
        // 'mysql.hostinger.com' est uniquement pour les connexions externes
        $hosts_to_try = [$db_host];
        if ($db_host === 'localhost') {
            $hosts_to_try = ['localhost', '127.0.0.1'];
        }
        
        $pdo = null;
        $connected_host = '';
        $last_error = '';
        
        foreach ($hosts_to_try as $host) {
            try {
                $dsn = "mysql:host=$host;charset=utf8";
                $pdo = new PDO($dsn, $db_user, $db_pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 10
                ]);
                $connected_host = $host;
                break;
            } catch (PDOException $e) {
                $last_error = $e->getMessage();
                continue;
            }
        }
        
        if (!$pdo) {
            $error = 'Erreur de connexion à la base de données. Hôtes testés: ' . implode(', ', $hosts_to_try) . '. Erreur: ' . $last_error;
            $error .= '<br><br><strong>Suggestions:</strong><br>';
            $error .= '- Vérifiez que l\'utilisateur MySQL existe et a les bons droits<br>';
            $error .= '- Essayez "mysql.hostinger.com" au lieu de "localhost"<br>';
            $error .= '- Vérifiez le mot de passe MySQL dans votre panneau Hostinger';
        } else {
            try {
                // Créer la base de données si elle n'existe pas
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Tester la connexion à la base de données
                $pdo = new PDO("mysql:host=$connected_host;dbname=$db_name;charset=utf8", $db_user, $db_pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 10
                ]);
                
                // Créer le fichier .env
                $env_content = "# Configuration de la base de données
DB_HOST=$connected_host
DB_NAME=$db_name
DB_USER=$db_user
DB_PASSWORD=$db_pass

# Configuration de l'application
APP_ENV=production
APP_DEBUG=false

# Configuration Shopify
SHOPIFY_APP_SECRET=your_shopify_app_secret_here
SHOPIFY_STORE_URL=your_shopify_store_url_here

# Configuration Youcan
YOUCAN_API_SECRET=your_youcan_api_secret_here

# Configuration Google Sheets
GOOGLE_SHEET_EMAIL=istore-sheet-auth@istore-sheet-327221.iam.gserviceaccount.com
GOOGLE_API_KEY=your_google_api_key_here

# Configuration OneSignal
ONESIGNAL_APP_ID=your_onesignal_app_id_here";
                
                $env_written = @file_put_contents('.env', $env_content);
                if ($env_written === false) {
                    throw new Exception('Impossible de créer le fichier .env. Vérifiez les permissions du dossier.');
                }
                
                // Créer le fichier de confirmation d'installation
                $install_content = "<?php
// Fichier de confirmation d'installation
// Ce fichier indique que l'installation a été effectuée avec succès
define('INSTALLED', true);
define('INSTALL_DATE', '" . date('Y-m-d H:i:s') . "');
";
                
                $install_written = @file_put_contents('config_installed.php', $install_content);
                if ($install_written === false) {
                    throw new Exception('Impossible de créer le fichier config_installed.php. Vérifiez les permissions du dossier.');
                }
                
                // Créer le fichier .htaccess pour la sécurité
                $htaccess_content = "# Empêcher l'accès direct aux fichiers de configuration
<FilesMatch \"^\.env$\">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch \"^config_installed\.php$\">
    Order allow,deny
    Deny from all
</FilesMatch>

# Réécriture d'URL
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]";
                
                $htaccess_written = @file_put_contents('.htaccess', $htaccess_content);
                if ($htaccess_written === false) {
                    // .htaccess non critique, continuer quand même
                    $error = 'Attention: Impossible de créer le fichier .htaccess. L\'installation continue mais vérifiez les permissions.';
                }
                
                $success = 'Installation réussie ! Redirection vers la page de configuration de la base de données...';
                $success .= '<br><br>Hôte connecté: <strong>' . htmlspecialchars($connected_host) . '</strong>';
                
                // Redirection vers install_database.php après 3 secondes
                header('refresh:3;url=install_database.php');
                
            } catch (Exception $e) {
                $error = 'Erreur lors de l\'installation: ' . $e->getMessage();
                $error .= '<br><br><strong>Solutions:</strong><br>';
                $error .= '- Vérifiez que le dossier du projet est accessible en écriture (chmod 755)<br>';
                $error .= '- Sur Hostinger, utilisez le Gestionnaire de fichiers pour vérifier les permissions<br>';
                $error .= '- Contactez le support Hostinger si les permissions ne peuvent pas être modifiées';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - EXLIV Delivery</title>
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
            max-width: 500px;
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
        .logo p {
            color: #666;
            margin-top: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #888;
            font-size: 12px;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .success {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
        .requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .requirements h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .requirements ul {
            list-style: none;
            padding: 0;
        }
        .requirements li {
            color: #666;
            font-size: 13px;
            margin-bottom: 5px;
            padding-left: 20px;
            position: relative;
        }
        .requirements li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #3c3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>📦 EXLIV Delivery</h1>
            <p>Assistant d'Installation</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <div class="requirements">
                <h3>Prérequis Serveur</h3>
                <ul>
                    <li>PHP 7.4 ou supérieur</li>
                    <li>Extension MySQL/PDO</li>
                    <li>Extension GD (images)</li>
                    <li>Extension mbstring</li>
                    <li>Extension curl</li>
                    <li>Extension zip</li>
                </ul>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="db_host">Hôte de la base de données *</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                    <small>Sur Hostinger, utilisez <strong>localhost</strong> pour les connexions internes</small>
                </div>
                
                <div class="form-group">
                    <label for="db_name">Nom de la base de données *</label>
                    <input type="text" id="db_name" name="db_name" placeholder="exliv_delivery" required>
                    <small>La base de données sera créée automatiquement si elle n'existe pas</small>
                </div>
                
                <div class="form-group">
                    <label for="db_user">Nom d'utilisateur MySQL *</label>
                    <input type="text" id="db_user" name="db_user" required>
                    <small>Votre nom d'utilisateur MySQL</small>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">Mot de passe MySQL</label>
                    <input type="password" id="db_pass" name="db_pass">
                    <small>Laissez vide si aucun mot de passe</small>
                </div>
                
                <button type="submit" class="btn">Installer la Plateforme</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
