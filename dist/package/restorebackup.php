<?php
/**
 * EXLIV Delivery - Database Restore Script
 * Ce script permet de restaurer une base de données depuis un fichier SQL
 */

session_start();
include("config.php");

// Vérifier si l'utilisateur est connecté et est modérateur
if(!isset($_SESSION['id']) || $_SESSION['type'] != "moderator"){
    die("Accès non autorisé. Vous devez être connecté en tant que modérateur.");
}

// Traitement de l'upload et de la restauration
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
    $file = $_FILES['backup_file'];
    
    // Vérifications
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Erreur lors de l'upload du fichier.";
    } elseif ($file['type'] !== 'text/plain' && pathinfo($file['name'], PATHINFO_EXTENSION) !== 'sql') {
        $error = "Le fichier doit être un fichier SQL (.sql).";
    } elseif ($file['size'] > 50 * 1024 * 1024) { // 50MB max
        $error = "Le fichier est trop volumineux (max 50MB).";
    } else {
        // Lire le fichier SQL
        $sql = file_get_contents($file['tmp_name']);
        
        if ($sql === false) {
            $error = "Impossible de lire le fichier.";
        } else {
            // Diviser le SQL en requêtes individuelles
            $queries = array_filter(array_map('trim', explode(';', $sql)));
            
            // Désactiver les contraintes étrangères temporairement
            try {
                $bdd->exec("SET FOREIGN_KEY_CHECKS = 0");
                
                $successCount = 0;
                $errors = [];
                
                foreach ($queries as $query) {
                    // Ignorer les commentaires et les lignes vides
                    if (empty($query) || strpos($query, '--') === 0 || strpos($query, 'SET') === 0) {
                        continue;
                    }
                    
                    try {
                        $bdd->exec($query);
                        $successCount++;
                    } catch (PDOException $e) {
                        // Ignorer les erreurs de "table already exists" et "duplicate entry"
                        if (strpos($e->getMessage(), 'already exists') === false && 
                            strpos($e->getMessage(), 'Duplicate entry') === false) {
                            $errors[] = $e->getMessage();
                        }
                    }
                }
                
                // Réactiver les contraintes étrangères
                $bdd->exec("SET FOREIGN_KEY_CHECKS = 1");
                
                if (empty($errors)) {
                    $success = "Restauration réussie! $successCount requêtes exécutées.";
                } else {
                    $success = "Restauration terminée avec $successCount requêtes exécutées. Certains avertissements: " . implode(', ', array_slice($errors, 0, 3));
                }
                
            } catch (PDOException $e) {
                $error = "Erreur lors de la restauration: " . $e->getMessage();
                try {
                    $bdd->exec("SET FOREIGN_KEY_CHECKS = 1");
                } catch (PDOException $e2) {
                    // Ignorer
                }
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
    <title>Restauration Base de Données - EXLIV Delivery</title>
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
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group input[type="file"]:focus {
            outline: none;
            border-color: #667eea;
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
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #666;
        }
        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-box h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-box ul {
            margin-left: 20px;
            color: #666;
        }
        .info-box li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>📦 EXLIV Delivery</h1>
            <p>Restauration Base de Données</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                <strong>⚠️ Erreur:</strong>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <strong>✅ Succès:</strong>
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="warning">
            <strong>⚠️ Attention:</strong>
            <p>La restauration va écraser les données existantes. Assurez-vous d'avoir une sauvegarde avant de continuer.</p>
        </div>
        
        <div class="info-box">
            <h3>📋 Instructions:</h3>
            <ul>
                <li>Sélectionnez un fichier de sauvegarde (.sql)</li>
                <li>Le fichier doit avoir été créé avec la fonction de sauvegarde</li>
                <li>La taille maximale du fichier est de 50MB</li>
                <li>La restauration peut prendre plusieurs secondes</li>
            </ul>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="backup_file">Fichier de sauvegarde (.sql):</label>
                <input type="file" id="backup_file" name="backup_file" accept=".sql,text/plain" required>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn btn-danger">🔄 Restaurer</button>
                <a href="settings.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="downloadbackup.php" class="btn">📥 Télécharger une sauvegarde</a>
            <a href="index.php" class="btn btn-secondary">Retour au tableau de bord</a>
        </div>
    </div>
</body>
</html>
