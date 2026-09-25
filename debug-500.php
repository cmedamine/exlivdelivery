<?php
/**
 * EXLIV Delivery - HTTP 500 Error Diagnostic Script
 * Script pour diagnostiquer les erreurs HTTP 500 sur Hostinger
 */

// Activer l'affichage des erreurs pour le diagnostic
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Erreur 500 - EXLIV Delivery</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
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
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        .step { background: #f8f9fa; padding: 15px; margin-bottom: 15px; border-left: 4px solid #667eea; border-radius: 5px; }
        .step h3 { color: #667eea; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic Erreur HTTP 500</h1>
        
        <div class="section error">
            <h2>❌ Erreur HTTP 500 Détectée</h2>
            <p>Une erreur interne du serveur s'est produite. Ce script va identifier la cause.</p>
        </div>
        
        <?php
        $issues = [];
        $warnings = [];
        
        // 1. Vérifier PHP
        echo '<div class="section info">';
        echo '<h2>1. Configuration PHP</h2>';
        echo '<p><strong>Version PHP:</strong> ' . phpversion() . '</p>';
        echo '<p><strong>Memory Limit:</strong> ' . ini_get('memory_limit') . '</p>';
        echo '<p><strong>Max Execution Time:</strong> ' . ini_get('max_execution_time') . 's</p>';
        echo '<p><strong>Display Errors:</strong> ' . (ini_get('display_errors') ? 'ON' : 'OFF') . '</p>';
        echo '<p><strong>Error Reporting:</strong> ' . error_reporting() . '</p>';
        echo '</div>';
        
        // 2. Vérifier les fichiers de configuration
        echo '<div class="section info">';
        echo '<h2>2. Fichiers de Configuration</h2>';
        
        if (file_exists('.env')) {
            echo '<p class="status ok">✓ Fichier .env existe</p>';
            if (is_readable('.env')) {
                echo '<p class="status ok">✓ Fichier .env est lisible</p>';
                $env_content = file_get_contents('.env');
                if (strpos($env_content, 'DB_HOST') !== false) {
                    echo '<p class="status ok">✓ DB_HOST configuré</p>';
                } else {
                    echo '<p class="status fail">✗ DB_HOST manquant dans .env</p>';
                    $issues[] = 'DB_HOST manquant dans .env';
                }
                if (strpos($env_content, 'DB_NAME') !== false) {
                    echo '<p class="status ok">✓ DB_NAME configuré</p>';
                } else {
                    echo '<p class="status fail">✗ DB_NAME manquant dans .env</p>';
                    $issues[] = 'DB_NAME manquant dans .env';
                }
            } else {
                echo '<p class="status fail">✗ Fichier .env n\'est pas lisible (permissions)</p>';
                $issues[] = '.env non lisible - vérifiez les permissions';
            }
        } else {
            echo '<p class="status fail">✗ Fichier .env n\'existe pas</p>';
            $issues[] = 'Fichier .env manquant - l\'installation n\'est peut-être pas terminée';
        }
        
        if (file_exists('config.php')) {
            echo '<p class="status ok">✓ Fichier config.php existe</p>';
            if (is_readable('config.php')) {
                echo '<p class="status ok">✓ Fichier config.php est lisible</p>';
            } else {
                echo '<p class="status fail">✗ Fichier config.php n\'est pas lisible</p>';
                $issues[] = 'config.php non lisible';
            }
        } else {
            echo '<p class="status fail">✗ Fichier config.php n\'existe pas</p>';
            $issues[] = 'Fichier config.php manquant';
        }
        
        echo '</div>';
        
        // 3. Tester la connexion à la base de données
        echo '<div class="section info">';
        echo '<h2>3. Test de Connexion MySQL</h2>';
        
        if (file_exists('.env')) {
            $env_lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $env_vars = [];
            foreach ($env_lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $env_vars[trim($key)] = trim($value);
                }
            }
            
            if (isset($env_vars['DB_HOST']) && isset($env_vars['DB_USER']) && isset($env_vars['DB_NAME'])) {
                try {
                    $dsn = "mysql:host=" . $env_vars['DB_HOST'] . ";dbname=" . $env_vars['DB_NAME'] . ";charset=utf8";
                    $pdo = new PDO($dsn, $env_vars['DB_USER'], $env_vars['DB_PASSWORD'] ?? '', [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 10
                    ]);
                    echo '<p class="status ok">✓ Connexion MySQL réussie</p>';
                    echo '<p><strong>Base de données:</strong> ' . htmlspecialchars($env_vars['DB_NAME']) . '</p>';
                } catch (PDOException $e) {
                    echo '<p class="status fail">✗ Erreur de connexion MySQL: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    $issues[] = 'Erreur de connexion MySQL: ' . $e->getMessage();
                }
            } else {
                echo '<p class="status warn">⚠ Variables .env incomplètes</p>';
                $warnings[] = 'Variables .env incomplètes';
            }
        } else {
            echo '<p class="status fail">✗ Impossible de tester - .env manquant</p>';
        }
        echo '</div>';
        
        // 4. Vérifier les extensions PHP
        echo '<div class="section info">';
        echo '<h2>4. Extensions PHP Requises</h2>';
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
        
        // 5. Vérifier les permissions
        echo '<div class="section info">';
        echo '<h2>5. Permissions des Fichiers</h2>';
        $importantFiles = ['.env', 'config.php', 'index.php', 'functions.php'];
        foreach ($importantFiles as $file) {
            if (file_exists($file)) {
                $perms = substr(sprintf('%o', fileperms($file)), -4);
                echo '<p>' . $file . ': ' . $perms . '</p>';
                if ($perms < '644') {
                    echo '<p class="status warn">⚠ Permission basse - devrait être 644</p>';
                    $warnings[] = $file . ' a une permission basse';
                }
            }
        }
        echo '</div>';
        
        // 6. Vérifier le fichier error_log
        echo '<div class="section info">';
        echo '<h2>6. Logs d\'Erreurs</h2>';
        if (file_exists('error_log')) {
            echo '<p class="status warn">⚠ Fichier error_log existe</p>';
            $log_content = file_get_contents('error_log');
            $log_lines = array_slice(explode("\n", $log_content), -20); // Dernières 20 lignes
            echo '<pre>';
            foreach ($log_lines as $line) {
                echo htmlspecialchars($line) . "\n";
            }
            echo '</pre>';
        } else {
            echo '<p class="status ok">✓ Pas de fichier error_log</p>';
        }
        echo '</div>';
        
        // 7. Tester le chargement de config.php
        echo '<div class="section info">';
        echo '<h2>7. Test de Chargement config.php</h2>';
        if (file_exists('config.php')) {
            try {
                // Tenter d'inclure config.php
                ob_start();
                include_once 'config.php';
                $output = ob_get_clean();
                
                if ($output) {
                    echo '<p class="status fail">✗ Erreur lors du chargement de config.php</p>';
                    echo '<pre>' . htmlspecialchars($output) . '</pre>';
                    $issues[] = 'Erreur lors du chargement de config.php';
                } else {
                    echo '<p class="status ok">✓ config.php chargé sans erreur</p>';
                }
            } catch (Throwable $e) {
                echo '<p class="status fail">✗ Exception lors du chargement de config.php</p>';
                echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                $issues[] = 'Exception dans config.php: ' . $e->getMessage();
            }
        }
        echo '</div>';
        
        // 8. Résumé
        echo '<div class="section ';
        if (empty($issues)) {
            echo 'success">';
            echo '<h2>✅ Diagnostic Terminé - Pas d\'Erreurs Critiques</h2>';
            echo '<p>Si vous avez toujours une erreur 500, le problème peut être:</p>';
            echo '<ul>';
            echo '<li>Fichier .htaccess mal configuré</li>';
            echo '<li>Memory limit trop basse</li>';
            echo '<li>Timeout dépassé</li>';
            echo '<li>Erreur dans un autre fichier PHP</li>';
            echo '</ul>';
        } else {
            echo 'error">';
            echo '<h2>❌ Problèmes Détectés</h2>';
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
        
        // 9. Solutions
        echo '<div class="section info">';
        echo '<h2>🔧 Solutions Courantes pour Erreur 500</h2>';
        
        echo '<div class="step">';
        echo '<h3>Solution 1: Recréer le fichier .env</h3>';
        echo '<p>Si .env est corrompu ou incomplet:</p>';
        echo '<ol>';
        echo '<li>Supprimez le fichier .env</li>';
        echo '<li>Supprimez le fichier config_installed.php</li>';
        echo '<li>Relancez install.php avec les bons identifiants</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '<div class="step">';
        echo '<h3>Solution 2: Vérifier les permissions</h3>';
        echo '<p>Dans hPanel > Fichiers > Gestionnaire de fichiers:</p>';
        echo '<ul>';
        echo '<li>Sélectionnez tous les fichiers</li>';
        echo '<li>Cliquez sur Permissions</li>';
        echo '<li>Dossiers: 755</li>';
        echo '<li>Fichiers PHP: 644</li>';
        echo '<li>Dossier uploads: 777</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="step">';
        echo '<h3>Solution 3: Augmenter la memory limit</h3>';
        echo '<p>Dans hPanel > PHP > Options:</p>';
        echo '<ul>';
        echo '<li>Memory Limit: 256M ou 512M</li>';
        echo '<li>Max Execution Time: 300</li>';
        echo '<li>Max Input Time: 300</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="step">';
        echo '<h3>Solution 4: Vérifier .htaccess</h3>';
        echo '<p>Si .htaccess contient des erreurs:</p>';
        echo '<ol>';
        echo '<li>Renommez .htaccess en .htaccess.bak</li>';
        echo '<li>Testez si le site fonctionne</li>';
        echo '<li>Si oui, le problème est dans .htaccess</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '</div>';
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">→ Tester index.php</a>
            <a href="install.php" style="display: inline-block; padding: 15px 30px; background: #764ba2; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">→ Réinstaller</a>
        </div>
    </div>
</body>
</html>
