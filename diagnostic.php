<?php
/**
 * EXLIV Delivery - Diagnostic Script for Hostinger
 * Script de diagnostic pour identifier les problèmes d'installation
 */

// Désactiver l'affichage des erreurs pour un résultat propre
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic - EXLIV Delivery</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; margin-bottom: 20px; }
        .section { margin-bottom: 30px; padding: 20px; border-radius: 8px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; }
        h2 { margin-bottom: 15px; font-size: 18px; }
        ul { margin-left: 20px; }
        li { margin-bottom: 8px; }
        .status { font-weight: bold; }
        .status.ok { color: #155724; }
        .status.fail { color: #721c24; }
        .status.warn { color: #856404; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic EXLIV Delivery - Hostinger</h1>
        
        <?php
        $issues = [];
        $warnings = [];
        
        // 1. Vérifier PHP
        echo '<div class="section info">';
        echo '<h2>1. Version PHP</h2>';
        $phpVersion = phpversion();
        echo '<p>Version PHP: <code>' . $phpVersion . '</code></p>';
        if (version_compare($phpVersion, '7.4', '>=')) {
            echo '<p class="status ok">✓ Version PHP compatible (7.4+ requis)</p>';
        } else {
            echo '<p class="status fail">✗ Version PHP incompatible. PHP 7.4+ requis</p>';
            $issues[] = 'PHP version trop ancienne';
        }
        echo '</div>';
        
        // 2. Vérifier les extensions PHP
        echo '<div class="section info">';
        echo '<h2>2. Extensions PHP Requises</h2>';
        $requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'curl', 'zip'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                echo '<p class="status ok">✓ Extension ' . $ext . ' installée</p>';
            } else {
                echo '<p class="status fail">✗ Extension ' . $ext . ' manquante</p>';
                $issues[] = 'Extension PHP ' . $ext . ' manquante';
            }
        }
        echo '</div>';
        
        // 3. Vérifier les permissions d'écriture
        echo '<div class="section info">';
        echo "<h2>3. Permissions d'Écriture</h2>";
        $writableFiles = ['.env', 'config_installed.php', '.htaccess', 'uploads/'];
        foreach ($writableFiles as $file) {
            if (file_exists($file)) {
                if (is_writable($file)) {
                    echo '<p class="status ok">✓ ' . $file . ' est accessible en écriture</p>';
                } else {
                    echo '<p class="status fail">✗ ' . $file . ' n\'est pas accessible en écriture</p>';
                    $issues[] = $file . ' non accessible en écriture';
                }
            } else {
                $dir = dirname($file);
                if ($dir == '.') $dir = __DIR__;
                if (is_writable($dir)) {
                    echo '<p class="status ok">✓ Le dossier permet de créer ' . $file . '</p>';
                } else {
                    echo '<p class="status fail">✗ Le dossier ne permet pas de créer ' . $file . '</p>';
                    $issues[] = 'Dossier non accessible en écriture pour ' . $file;
                }
            }
        }
        echo '</div>';
        
        // 4. Vérifier si déjà installé
        echo '<div class="section info">';
        echo '<h2>4. État de l\'Installation</h2>';
        if (file_exists('.env')) {
            echo '<p class="status warn">⚠ Le fichier .env existe déjà</p>';
            $warnings[] = 'Installation déjà partiellement effectuée';
        } else {
            echo '<p class="status ok">✓ Fichier .env non présent (prêt pour installation)</p>';
        }
        if (file_exists('config_installed.php')) {
            echo '<p class="status warn">⚠ Le fichier config_installed.php existe déjà</p>';
        } else {
            echo '<p class="status ok">✓ Fichier config_installed.php non présent</p>';
        }
        echo '</div>';
        
        // 5. Vérifier la configuration Hostinger
        echo '<div class="section info">';
        echo '<h2>5. Configuration Hostinger</h2>';
        echo '<p><strong>Informations Hostinger typiques:</strong></p>';
        echo '<ul>';
        echo '<li>Hôte MySQL: <code>localhost</code> ou <code>mysql.hostinger.com</code></li>';
        echo '<li>Port MySQL: <code>3306</code></li>';
        echo '<li>Chemin absolu: <code>' . __DIR__ . '</code></li>';
        echo '</ul>';
        echo '</div>';
        
        // 6. Tester la connexion PDO
        echo '<div class="section info">';
        echo '<h2>6. Test de Connexion PDO</h2>';
        echo '<p><em>Ce test nécessite les identifiants de votre base de données Hostinger</em></p>';
        echo '<form method="POST">';
        echo '<label>Hôte MySQL: <input type="text" name="test_host" value="localhost" style="padding: 5px; margin: 5px;"></label><br>';
        echo '<label>Utilisateur MySQL: <input type="text" name="test_user" style="padding: 5px; margin: 5px;"></label><br>';
        echo '<label>Mot de passe: <input type="password" name="test_pass" style="padding: 5px; margin: 5px;"></label><br>';
        echo '<label>Nom BD: <input type="text" name="test_db" style="padding: 5px; margin: 5px;"></label><br>';
        echo '<button type="submit" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">Tester</button>';
        echo '</form>';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_host'])) {
            try {
                $dsn = "mysql:host=" . $_POST['test_host'] . ";charset=utf8";
                $pdo = new PDO($dsn, $_POST['test_user'], $_POST['test_pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo '<p class="status ok">✓ Connexion PDO réussie!</p>';
                
                if (!empty($_POST['test_db'])) {
                    $pdo->exec("USE `" . $_POST['test_db'] . "`");
                    echo '<p class="status ok">✓ Base de données accessible!</p>';
                }
            } catch (PDOException $e) {
                echo '<p class="status fail">✗ Erreur de connexion: ' . htmlspecialchars($e->getMessage()) . '</p>';
                $issues[] = 'Connexion MySQL échouée: ' . $e->getMessage();
            }
        }
        echo '</div>';
        
        // 7. Résumé
        echo '<div class="section ';
        if (empty($issues)) {
            echo 'success">';
            echo '<h2>✓ Diagnostic Réussi</h2>';
            echo '<p>Tous les tests sont passés. Vous pouvez procéder à l\'installation.</p>';
        } else {
            echo 'error">';
            echo '<h2>✗ Problèmes Détectés</h2>';
            echo '<ul>';
            foreach ($issues as $issue) {
                echo '<li>' . htmlspecialchars($issue) . '</li>';
            }
            echo '</ul>';
        }
        
        if (!empty($warnings)) {
            echo '<h3>⚠ Avertissements</h3>';
            echo '<ul>';
            foreach ($warnings as $warning) {
                echo '<li>' . htmlspecialchars($warning) . '</li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        
        // 8. Instructions Hostinger
        echo '<div class="section info">';
        echo '<h2>📋 Instructions pour Hostinger</h2>';
        echo '<ol>';
        echo '<li>Créez une base de données MySQL dans le panneau Hostinger</li>';
        echo '<li>Notez les identifiants: hôte, utilisateur, mot de passe, nom de la BD</li>';
        echo '<li>Utilisez <code>localhost</code> comme hôte (ou <code>mysql.hostinger.com</code> si localhost ne fonctionne pas)</li>';
        echo '<li>Assurez-vous que le dossier du projet a les permissions 755</li>';
        echo '<li>Exécutez <code>install.php</code> avec les identifiants corrects</li>';
        echo '</ol>';
        echo '</div>';
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="install.php" style="display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">→ Procéder à l'Installation</a>
        </div>
    </div>
</body>
</html>
