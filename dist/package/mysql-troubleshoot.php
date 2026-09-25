<?php
/**
 * EXLIV Delivery - MySQL Troubleshooting Script for Hostinger
 * Script de dépannage pour résoudre les erreurs de connexion MySQL
 */

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dépannage MySQL - EXLIV Delivery</title>
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
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input { width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 5px; }
        .btn { padding: 12px 25px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .btn:hover { background: #5568d3; }
        .step { background: #f8f9fa; padding: 15px; margin-bottom: 15px; border-left: 4px solid #667eea; border-radius: 5px; }
        .step h3 { color: #667eea; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Dépannage MySQL - Hostinger</h1>
        
        <div class="section error">
            <h2>❌ Erreur Détectée</h2>
            <p><strong>Erreur 1045: Access denied for user</strong></p>
            <p>Cette erreur signifie que l'utilisateur MySQL n'existe pas, le mot de passe est incorrect, ou l'utilisateur n'a pas les droits sur la base de données.</p>
        </div>
        
        <div class="section info">
            <h2>📋 Étapes de Résolution</h2>
            
            <div class="step">
                <h3>Étape 1: Vérifier les identifiants dans hPanel</h3>
                <ol>
                    <li>Connectez-vous à votre panneau Hostinger (hPanel)</li>
                    <li>Allez dans <strong>Bases de données</strong> > <strong>MySQL</strong></li>
                    <li>Vous verrez une liste de vos bases de données</li>
                    <li>Cliquez sur <strong>Gérer</strong> ou <strong>Modifier</strong> pour voir les détails</li>
                    <li>Notez précisément:
                        <ul>
                            <li><strong>Nom de la base de données</strong> (ex: u123456789_exliv)</li>
                            <li><strong>Nom d'utilisateur</strong> (ex: u123456789_admin)</li>
                            <li><strong>Mot de passe</strong> (cliquez sur "Afficher" ou "Régénérer")</li>
                            <li><strong>Hôte</strong> (généralement <code>localhost</code>)</li>
                        </ul>
                    </li>
                </ol>
            </div>
            
            <div class="step">
                <h3>Étape 2: Recréer l'utilisateur MySQL (si nécessaire)</h3>
                <ol>
                    <li>Dans hPanel, allez dans <strong>Bases de données</strong> > <strong>MySQL</strong></li>
                    <li>Si l'utilisateur n'existe pas, cliquez sur <strong>Créer un utilisateur</strong></li>
                    <li>Entrez un nom d'utilisateur (ex: <code>exliv_admin</code>)</li>
                    <li>Générez un mot de passe fort et notez-le</li>
                    <li>Cliquez sur <strong>Créer</strong></li>
                </ol>
            </div>
            
            <div class="step">
                <h3>Étape 3: Attribuer les droits à l'utilisateur</h3>
                <ol>
                    <li>Dans hPanel, allez dans <strong>Bases de données</strong> > <strong>MySQL</strong></li>
                    <li>Cliquez sur <strong>Gérer les utilisateurs</strong> ou <strong>Modifier</strong></li>
                    <li>Sélectionnez votre utilisateur</li>
                    <li>Cochez <strong>Tous les droits</strong> ou sélectionnez:
                        <ul>
                            <li>SELECT</li>
                            <li>INSERT</li>
                            <li>UPDATE</li>
                            <li>DELETE</li>
                            <li>CREATE</li>
                            <li>DROP</li>
                            <li>ALTER</li>
                            <li>INDEX</li>
                        </ul>
                    </li>
                    <li>Cliquez sur <strong>Enregistrer</strong></li>
                </ol>
            </div>
            
            <div class="step">
                <h3>Étape 4: Tester la connexion</h3>
                <p>Utilisez le formulaire ci-dessous pour tester différentes combinaisons:</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Hôte MySQL:</label>
                        <input type="text" name="test_host" value="localhost" placeholder="localhost">
                    </div>
                    <div class="form-group">
                        <label>Nom d'utilisateur MySQL:</label>
                        <input type="text" name="test_user" placeholder="u123456789_exliv">
                    </div>
                    <div class="form-group">
                        <label>Mot de passe:</label>
                        <input type="password" name="test_pass" placeholder="Votre mot de passe">
                    </div>
                    <div class="form-group">
                        <label>Nom de la base de données (optionnel):</label>
                        <input type="text" name="test_db" placeholder="u123456789_exliv_delivery">
                    </div>
                    <button type="submit" class="btn">Tester la Connexion</button>
                </form>
                
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_host'])) {
                    $test_host = trim($_POST['test_host']);
                    $test_user = trim($_POST['test_user']);
                    $test_pass = trim($_POST['test_pass']);
                    $test_db = trim($_POST['test_db'] ?? '');
                    
                    echo '<div class="section info">';
                    echo '<h2>🔍 Résultat du Test</h2>';
                    
                    // Test 1: Connexion sans base de données
                    try {
                        $dsn = "mysql:host=$test_host;charset=utf8";
                        $pdo = new PDO($dsn, $test_user, $test_pass, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_TIMEOUT => 10
                        ]);
                        echo '<p class="status ok">✓ Connexion au serveur MySQL réussie!</p>';
                        echo '<p><strong>Hôte:</strong> ' . htmlspecialchars($test_host) . '</p>';
                        echo '<p><strong>Utilisateur:</strong> ' . htmlspecialchars($test_user) . '</p>';
                        
                        // Test 2: Liste des bases de données accessibles
                        $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
                        echo '<p><strong>Bases de données accessibles:</strong></p>';
                        echo '<ul>';
                        foreach ($databases as $db) {
                            if ($db !== 'information_schema' && $db !== 'mysql' && $db !== 'performance_schema') {
                                echo '<li>' . htmlspecialchars($db) . '</li>';
                            }
                        }
                        echo '</ul>';
                        
                        // Test 3: Connexion à la base de données spécifique
                        if (!empty($test_db)) {
                            try {
                                $pdo->exec("USE `$test_db`");
                                echo '<p class="status ok">✓ Accès à la base de données "' . htmlspecialchars($test_db) . '" réussi!</p>';
                                
                                // Test 4: Création de table test
                                $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INT AUTO_INCREMENT PRIMARY KEY, test_col VARCHAR(255))");
                                echo '<p class="status ok">✓ Création de table réussie (droits CREATE)</p>';
                                
                                // Test 5: Insertion
                                $pdo->exec("INSERT INTO test_table (test_col) VALUES ('test')");
                                echo '<p class="status ok">✓ Insertion réussie (droits INSERT)</p>';
                                
                                // Nettoyage
                                $pdo->exec("DROP TABLE IF EXISTS test_table");
                                echo '<p class="status ok">✓ Suppression de table réussie (droits DROP)</p>';
                                
                                echo '<div class="success">';
                                echo '<h3>✅ Tous les tests sont passés!</h3>';
                                echo '<p>Vous pouvez maintenant utiliser ces identifiants dans install.php:</p>';
                                echo '<pre>';
                                echo 'Hôte: ' . htmlspecialchars($test_host) . "\n";
                                echo 'Utilisateur: ' . htmlspecialchars($test_user) . "\n";
                                echo 'Mot de passe: ' . htmlspecialchars($test_pass) . "\n";
                                echo 'Base de données: ' . htmlspecialchars($test_db);
                                echo '</pre>';
                                echo '</div>';
                                
                            } catch (PDOException $e) {
                                echo '<p class="status fail">✗ Erreur d\'accès à la base de données: ' . htmlspecialchars($e->getMessage()) . '</p>';
                                echo '<p><strong>Solution:</strong> L\'utilisateur n\'a pas les droits sur cette base de données. Attribuez les droits dans hPanel.</p>';
                            }
                        }
                        
                    } catch (PDOException $e) {
                        echo '<p class="status fail">✗ Erreur de connexion: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        echo '<div class="warning">';
                        echo '<h3>Solutions possibles:</h3>';
                        echo '<ul>';
                        echo '<li>Vérifiez que le nom d\'utilisateur est correct (attention au préfixe uXXXXXX_)</li>';
                        echo '<li>Vérifiez que le mot de passe est correct (attention aux caractères spéciaux)</li>';
                        echo '<li>Essayez un autre hôte: <code>127.0.0.1</code> ou <code>mysql.hostinger.com</code></li>';
                        echo '<li>Recréez l\'utilisateur MySQL dans hPanel</li>';
                        echo '<li>Attribuez tous les droits à l\'utilisateur</li>';
                        echo '</ul>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="section warning">
            <h2>⚠️ Erreurs Communes Hostinger</h2>
            
            <h3>1. Préfixe de l'utilisateur</h3>
            <p>Sur Hostinger, les utilisateurs MySQL ont généralement un préfixe comme <code>u123456789_</code></p>
            <p><strong>Exemple:</strong> Au lieu de <code>exliv_admin</code>, utilisez <code>u123456789_exliv_admin</code></p>
            
            <h3>2. Préfixe de la base de données</h3>
            <p>Les bases de données ont aussi un préfixe</p>
            <p><strong>Exemple:</strong> Au lieu de <code>exliv_delivery</code>, utilisez <code>u123456789_exliv_delivery</code></p>
            
            <h3>3. Hôte incorrect</h3>
            <p>Sur Hostinger, utilisez TOUJOURS <code>localhost</code> pour les connexions internes</p>
            <p><code>mysql.hostinger.com</code> est uniquement pour les connexions externes (site hébergé ailleurs)</p>
            <p>Si localhost ne fonctionne pas, essayez <code>127.0.0.1</code></p>
            
            <h3>4. Droits insuffisants</h3>
            <p>L'utilisateur doit avoir les droits CREATE, DROP, ALTER sur la base de données</p>
            <p>Attribuez "Tous les droits" dans hPanel pour éviter les problèmes</p>
        </div>
        
        <div class="section info">
            <h2>📞 Support Hostinger</h2>
            <p>Si vous ne parvenez toujours pas à vous connecter:</p>
            <ul>
                <li>Contactez le support Hostinger via le Live Chat (24/7)</li>
                <li>Base de connaissances: <a href="https://support.hostinger.com" target="_blank">support.hostinger.com</a></li>
                <li>Recherchez "MySQL connection error" dans les tutoriels Hostinger</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="install.php" style="display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">→ Retour à l'Installation</a>
        </div>
    </div>
</body>
</html>
