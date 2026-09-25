<?php
/**
 * EXLIV Delivery - E-commerce Integrations Page for Clients
 * Page de gestion des intégrations e-commerce (Shopify, Youcan, Google Sheets)
 */

session_start();
include("config.php");

if(!isset($_SESSION['id'])){
    header('location: login.php');
    exit;
}

if($_SESSION['type'] != "client"){
    header('location: 404.php');
    exit;
}

$error = '';
$success = '';

// Traitement du formulaire d'ajout d'intégration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'add_integration') {
        $platform = $_POST['platform'] ?? '';
        $api_key = sanitize_vars($_POST['api_key'] ?? '');
        $api_secret = sanitize_vars($_POST['api_secret'] ?? '');
        $store_url = sanitize_vars($_POST['store_url'] ?? '');
        $sheet_id = sanitize_vars($_POST['sheet_id'] ?? '');
        $sheet_email = sanitize_vars($_POST['sheet_email'] ?? '');
        
        if (empty($platform)) {
            $error = 'Veuillez sélectionner une plateforme.';
        } else {
            try {
                $req = $bdd->prepare("INSERT INTO ecommerce_integrations(id,client_id,platform,api_key,api_secret,store_url,sheet_id,sheet_email,dateadd,trash) 
                VALUES ('0',?,?,?,?,?,?,?,?,'1')");
                $req->execute([
                    $_SESSION['id'],
                    $platform,
                    $api_key,
                    $api_secret,
                    $store_url,
                    $sheet_id,
                    $sheet_email,
                    time()
                ]);
                
                add_audit_log($_SESSION['id'], 'create', 'ecommerce_integrations', null, null, ['platform' => $platform]);
                $success = 'Intégration ajoutée avec succès!';
                
            } catch (PDOException $e) {
                $error = 'Erreur lors de l\'ajout: ' . $e->getMessage();
            }
        }
    }
    
    elseif ($action == 'toggle_integration') {
        $integration_id = $_POST['integration_id'] ?? 0;
        $is_active = $_POST['is_active'] ?? 'off';
        
        try {
            $req = $bdd->prepare("UPDATE ecommerce_integrations SET is_active = ?, last_sync = ? WHERE id = ? AND client_id = ?");
            $req->execute([$is_active, time(), $integration_id, $_SESSION['id']]);
            
            add_audit_log($_SESSION['id'], 'update', 'ecommerce_integrations', $integration_id, ['is_active' => $is_active]);
            $success = 'Intégration mise à jour avec succès!';
            
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour: ' . $e->getMessage();
        }
    }
    
    elseif ($action == 'delete_integration') {
        $integration_id = $_POST['integration_id'] ?? 0;
        
        try {
            $req = $bdd->prepare("UPDATE ecommerce_integrations SET trash = '0' WHERE id = ? AND client_id = ?");
            $req->execute([$integration_id, $_SESSION['id']]);
            
            add_audit_log($_SESSION['id'], 'delete', 'ecommerce_integrations', $integration_id);
            $success = 'Intégration supprimée avec succès!';
            
        } catch (PDOException $e) {
            $error = 'Erreur lors de la suppression: ' . $e->getMessage();
        }
    }
}

// Récupérer les intégrations du client
$integrations = [];
try {
    $req = $bdd->prepare("SELECT * FROM ecommerce_integrations WHERE client_id = ? AND trash = '1' ORDER BY dateadd DESC");
    $req->execute([$_SESSION['id']]);
    $integrations = $req->fetchAll();
} catch (PDOException $e) {
    // Ignorer si la table n'existe pas
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intégrations E-commerce - <?php echo $settings['appname'] ?? 'EXLIV Delivery'; ?></title>
    <link rel="stylesheet" href="css/general_style.css">
    <link rel="stylesheet" href="css/main_style.php">
    <link rel="stylesheet" href="css/reset_style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .page-header h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .integrations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .integration-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
        }
        .integration-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .integration-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .integration-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
        }
        .icon-shopify { background: #95bf47; color: white; }
        .icon-youcan { background: #ff6b35; color: white; }
        .icon-google { background: #4285f4; color: white; }
        .integration-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        .integration-desc {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        .integration-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-inactive { background: #ffebee; color: #c62828; }
        .integration-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }
        .btn {
            flex: 1;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #f44336;
            color: white;
        }
        .btn-danger:hover {
            background: #d32f2f;
        }
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        .add-integration {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 2px dashed rgba(255,255,255,0.5);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .add-integration:hover {
            border-color: white;
            transform: translateY(-2px);
        }
        .add-integration i {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .success {
            background: #efe;
            color: #3c3;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
        .webhook-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .webhook-info h4 {
            color: #1976d2;
            margin-bottom: 10px;
        }
        .webhook-info code {
            background: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-family: monospace;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="page-header">
            <h1>🔗 Intégrations E-commerce</h1>
            <p>Connectez votre boutique en ligne pour automatiser la gestion des commandes</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div class="webhook-info">
            <h4>📡 Webhook URL</h4>
            <p>Utilisez cette URL pour configurer vos webhooks:</p>
            <code><?php echo $websiteurl; ?>/rcshopify.php</code> (Shopify)
            <br>
            <code><?php echo $websiteurl; ?>/rcyoucan.php</code> (Youcan)
        </div>
        
        <div class="integrations-grid">
            <div class="add-integration" onclick="openModal()">
                <i class="fa fa-plus"></i>
                <div>Ajouter une intégration</div>
            </div>
            
            <?php foreach ($integrations as $integration): ?>
                <div class="integration-card">
                    <div class="integration-header">
                        <div class="integration-icon icon-<?php echo $integration['platform']; ?>">
                            <?php
                            $icon = 'fa-shopping-cart';
                            if ($integration['platform'] == 'shopify') $icon = 'fa-shopping-bag';
                            elseif ($integration['platform'] == 'youcan') $icon = 'fa-store';
                            elseif ($integration['platform'] == 'google_sheets') $icon = 'fa-table';
                            ?>
                            <i class="fa <?php echo $icon; ?>"></i>
                        </div>
                        <div>
                            <div class="integration-title"><?php echo ucfirst($integration['platform']); ?></div>
                            <span class="integration-status <?php echo $integration['is_active'] == 'on' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $integration['is_active'] == 'on' ? 'Actif' : 'Inactif'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="integration-desc">
                        <?php
                        if ($integration['platform'] == 'shopify') echo 'Intégration Shopify pour synchroniser les commandes automatiquement.';
                        elseif ($integration['platform'] == 'youcan') echo 'Intégration Youcan pour synchroniser les commandes automatiquement.';
                        elseif ($integration['platform'] == 'google_sheets') echo 'Importation des colis depuis Google Sheets.';
                        ?>
                    </div>
                    <div class="integration-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_integration">
                            <input type="hidden" name="integration_id" value="<?php echo $integration['id']; ?>">
                            <input type="hidden" name="is_active" value="<?php echo $integration['is_active'] == 'on' ? 'off' : 'on'; ?>">
                            <button type="submit" class="btn btn-secondary">
                                <?php echo $integration['is_active'] == 'on' ? 'Désactiver' : 'Activer'; ?>
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete_integration">
                            <input type="hidden" name="integration_id" value="<?php echo $integration['id']; ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette intégration?');">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="color: #667eea; text-decoration: none; font-weight: 600;">← Retour au tableau de bord</a>
        </div>
    </div>
    
    <div class="modal" id="integrationModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Ajouter une intégration</div>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_integration">
                
                <div class="form-group">
                    <label>Plateforme *</label>
                    <select name="platform" id="platformSelect" required onchange="toggleFields()">
                        <option value="">Sélectionner une plateforme</option>
                        <option value="shopify">Shopify</option>
                        <option value="youcan">Youcan</option>
                        <option value="google_sheets">Google Sheets</option>
                    </select>
                </div>
                
                <div class="form-group" id="apiKeyField">
                    <label>API Key</label>
                    <input type="text" name="api_key" placeholder="Votre API Key">
                </div>
                
                <div class="form-group" id="apiSecretField">
                    <label>API Secret</label>
                    <input type="text" name="api_secret" placeholder="Votre API Secret">
                </div>
                
                <div class="form-group" id="storeUrlField">
                    <label>URL de la boutique</label>
                    <input type="url" name="store_url" placeholder="https://votre-boutique.com">
                </div>
                
                <div class="form-group" id="sheetIdField" style="display: none;">
                    <label>ID du Google Sheet</label>
                    <input type="text" name="sheet_id" placeholder="1BxiM...">
                </div>
                
                <div class="form-group" id="sheetEmailField" style="display: none;">
                    <label>Email du compte de service</label>
                    <input type="email" name="sheet_email" placeholder="service-account@project.iam.gserviceaccount.com">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Ajouter l'intégration</button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('integrationModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('integrationModal').classList.remove('active');
        }
        
        function toggleFields() {
            const platform = document.getElementById('platformSelect').value;
            const apiKeyField = document.getElementById('apiKeyField');
            const apiSecretField = document.getElementById('apiSecretField');
            const storeUrlField = document.getElementById('storeUrlField');
            const sheetIdField = document.getElementById('sheetIdField');
            const sheetEmailField = document.getElementById('sheetEmailField');
            
            if (platform === 'google_sheets') {
                apiKeyField.style.display = 'none';
                apiSecretField.style.display = 'none';
                storeUrlField.style.display = 'none';
                sheetIdField.style.display = 'block';
                sheetEmailField.style.display = 'block';
            } else {
                apiKeyField.style.display = 'block';
                apiSecretField.style.display = 'block';
                storeUrlField.style.display = 'block';
                sheetIdField.style.display = 'none';
                sheetEmailField.style.display = 'none';
            }
        }
        
        // Fermer la modal en cliquant à l'extérieur
        document.getElementById('integrationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
