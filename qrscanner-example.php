<?php
/**
 * EXLIV Delivery - QR Scanner Example
 * Exemple d'intégration du scanner QR dans une page
 * Ce fichier montre comment intégrer le scanner QR dans n'importe quelle section
 */

session_start();
include("config.php");

if(!isset($_SESSION['id'])){
    header('location: login.php');
    exit;
}

$scannedCode = '';

// Traitement du code scanné
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scanned_code'])) {
    $scannedCode = sanitize_vars($_POST['scanned_code']);
    
    // Ici, vous pouvez traiter le code scanné selon vos besoins
    // Par exemple: rechercher une commande, un produit, etc.
    
    try {
        // Exemple: rechercher une commande par code
        $req = $bdd->prepare("SELECT * FROM commands WHERE code = ? OR tracking_code = ? AND trash = '1'");
        $req->execute([$scannedCode, $scannedCode]);
        $command = $req->fetch();
        
        if ($command) {
            // Rediriger vers la page de détails de la commande
            header('location: commands.php?code=' . urlencode($scannedCode));
            exit;
        } else {
            $error = 'Aucune commande trouvée avec ce code.';
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la recherche: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner QR - <?php echo $settings['appname'] ?? 'EXLIV Delivery'; ?></title>
    <link rel="stylesheet" href="css/general_style.css">
    <link rel="stylesheet" href="css/main_style.php">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <h1 style="text-align: center; color: #667eea; margin-bottom: 30px;">📱 Scanner QR Code</h1>
        
        <?php if (isset($error)): ?>
            <div style="background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c33;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Conteneur du scanner QR -->
        <div id="qr-scanner-container"></div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="color: #667eea; text-decoration: none; font-weight: 600;">← Retour au tableau de bord</a>
        </div>
    </div>
    
    <!-- Charger le script du scanner QR -->
    <script src="js/qrcode-scanner.js"></script>
    
    <!-- Initialiser le scanner QR -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser le scanner QR avec un callback
            QRScanner.init('qr-scanner-container', function(scannedCode) {
                // console.log('Code scanné:', scannedCode);
                
                // Envoyer le code scanné au serveur
                const formData = new FormData();
                formData.append('scanned_code', scannedCode);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    // Recharger la page pour voir le résultat
                    location.reload();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du traitement du code scanné');
                });
            });
        });
    </script>
</body>
</html>

<!--
    =====================================================
    INSTRUCTIONS D'INTÉGRATION DU SCANNER QR
    =====================================================
    
    Pour intégrer le scanner QR dans n'importe quelle page:
    
    1. Ajouter le conteneur du scanner dans votre HTML:
       <div id="qr-scanner-container"></div>
    
    2. Charger le script du scanner:
       <script src="js/qrcode-scanner.js"></script>
    
    3. Initialiser le scanner avec un callback:
       <script>
       document.addEventListener('DOMContentLoaded', function() {
           QRScanner.init('qr-scanner-container', function(scannedCode) {
               // Traitement du code scanné
               console.log('Code scanné:', scannedCode);
               
               // Exemple: rediriger vers une page
               window.location.href = 'commands.php?code=' + scannedCode;
               
               // Ou envoyer au serveur via AJAX
               // fetch('traitement.php', { method: 'POST', body: JSON.stringify({code: scannedCode}) });
           });
       });
       </script>
    
    =====================================================
    EXEMPLES D'UTILISATION
    =====================================================
    
    // Dans commands.php - Scanner pour rechercher une commande
    QRScanner.init('qr-scanner-container', function(code) {
        window.location.href = 'commands.php?code=' + code;
    });
    
    // Dans stocks.php - Scanner pour gérer un stock
    QRScanner.init('qr-scanner-container', function(code) {
        window.location.href = 'stocks.php?ref=' + code;
    });
    
    // Dans confirmation.php - Scanner pour confirmer une commande
    QRScanner.init('qr-scanner-container', function(code) {
        // Envoyer via AJAX pour confirmer
        fetch('ajax.php', {
            method: 'POST',
            body: JSON.stringify({action: 'confirm', code: code})
        });
    });
    
    =====================================================
-->
