<?php
/**
 * EXLIV Delivery - Add Package Form for Clients
 * Formulaire d'ajout de colis pour les clients (particulier, rapide, normal)
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
$fromStock = false;
$stockId = '';

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_type = $_POST['package_type'] ?? 'normal';
    $fullname = sanitize_vars($_POST['fullname'] ?? '');
    $phone = sanitize_vars($_POST['phone'] ?? '');
    $address = sanitize_vars($_POST['address'] ?? '');
    $city = sanitize_vars($_POST['city'] ?? '');
    $product = sanitize_vars($_POST['product'] ?? '');
    $qty = sanitize_vars($_POST['qty'] ?? '1');
    $price = sanitize_vars($_POST['price'] ?? '0');
    $note = sanitize_vars($_POST['note'] ?? '');
    $fromStock = ($_POST['from_stock'] ?? '') === '1';
    $stockId = (string) ($_POST['stock_id'] ?? '');
    
    // Validation
    if (empty($fullname) || empty($phone) || empty($address) || empty($city) || (!$fromStock && empty($product))) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!validate_phone($phone)) {
        $error = 'Numéro de téléphone invalide.';
    } elseif (!is_numeric($price) || !ctype_digit((string) $qty) || (int) $qty < 1) {
        $error = 'Le prix et la quantité doivent être des nombres valides.';
    } elseif ($fromStock && !ctype_digit($stockId)) {
        $error = 'Choisissez un produit de votre stock.';
    } else {
        try {
            if ($fromStock) {
                // Commands linked to stock store its numeric stock ID in `product`.
                // Lock the selected row while checking availability to avoid overselling.
                $bdd->beginTransaction();
                $stockRequest = $bdd->prepare(
                    "SELECT id, qty FROM stocks WHERE id = ? AND client = ? AND received = 'on' AND trash = '1' FOR UPDATE"
                );
                $stockRequest->execute([(int) $stockId, $_SESSION['id']]);
                $stock = $stockRequest->fetch();
                if (!$stock) {
                    throw new RuntimeException('Ce produit de stock est introuvable ou n’est pas encore validé.');
                }

                $usedRequest = $bdd->prepare(
                    "SELECT product, qty FROM commands
                     WHERE client = ? AND trash = '1' AND code NOT LIKE 'CHANGE-%'
                     AND state NOT IN ('Ajouté', 'Retour client reçu')
                     AND (product = ? OR product LIKE ? OR product LIKE ? OR product LIKE ?)"
                );
                $stockProduct = (string) $stock['id'];
                $usedRequest->execute([
                    $_SESSION['id'],
                    $stockProduct,
                    $stockProduct . ',%',
                    '%,' . $stockProduct . ',%',
                    '%,' . $stockProduct,
                ]);
                $usedQuantity = 0;
                while ($usedCommand = $usedRequest->fetch()) {
                    $products = explode(',', (string) $usedCommand['product']);
                    $quantities = explode(',', (string) $usedCommand['qty']);
                    foreach ($products as $index => $commandProduct) {
                        if ($commandProduct === $stockProduct) {
                            $usedQuantity += (int) ($quantities[$index] ?? 0);
                        }
                    }
                }

                if ((int) $qty > ((int) $stock['qty'] - $usedQuantity)) {
                    throw new RuntimeException('La quantité demandée dépasse le stock disponible.');
                }
                $product = $stockProduct;
            }

            // Générer le code de commande
            $back = $bdd->query("SELECT id FROM commands WHERE trash='1'");
            $code = 'CMD-' . date('dmY') . '-' . sprintf("%05d", ($back->rowCount() + 1));
            
            // Générer le code de tracking unique
            $tracking_code = 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 10));
            
            // Insérer la commande
            $req = $bdd->prepare("INSERT INTO commands(id,code,product,qty,dlm,subdlm,client,worker,store,source,package_type,fullname,phone,address,city,price,fees,phase,state,datereported,note,workers,invoiced,tracking_code,dateadd,dateupdate,trash)
            VALUES ('0',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $req->execute([
                $code,
                $product,
                $qty,
                null,
                null,
                $_SESSION['id'],
                null,
                $_SESSION['id'],
                'Client',
                $package_type,
                $fullname,
                $phone,
                $address,
                $city,
                $price,
                '0',
                'confirmation',
                'Nouveau',
                null,
                $note,
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
            
            // Logger l'action
            add_audit_log($_SESSION['id'], 'create', 'commands', null, null, ['code' => $code, 'package_type' => $package_type]);

            if ($fromStock) {
                $bdd->commit();
            }
            
            $success = 'Colis ajouté avec succès! Code: ' . $code . ' | Tracking: ' . $tracking_code;
            
        } catch (Throwable $e) {
            if ($fromStock && $bdd->inTransaction()) {
                $bdd->rollBack();
            }
            $error = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'Erreur lors de l\'ajout: ' . $e->getMessage();
        }
    }
}

// Récupérer les villes
$cities = [];
try {
    $back = $bdd->query("SELECT city FROM cities WHERE trash='1' ORDER BY city");
    $cities = $back->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Ignorer si la table n'existe pas
}

// Only approved inventory can be selected.  The remaining quantity mirrors the
// existing stock page calculation, but excludes deleted commands.
$stockItems = [];
try {
    $stockRequest = $bdd->prepare("SELECT id, title, ref, qty FROM stocks WHERE client = ? AND received = 'on' AND trash = '1' ORDER BY title");
    $stockRequest->execute([$_SESSION['id']]);
    while ($stock = $stockRequest->fetch()) {
        $stockId = (string) $stock['id'];
        $usedRequest = $bdd->prepare(
            "SELECT product, qty FROM commands
             WHERE client = ? AND trash = '1' AND code NOT LIKE 'CHANGE-%'
             AND state NOT IN ('Ajouté', 'Retour client reçu')
             AND (product = ? OR product LIKE ? OR product LIKE ? OR product LIKE ?)"
        );
        $usedRequest->execute([
            $_SESSION['id'],
            $stockId,
            $stockId . ',%',
            '%,' . $stockId . ',%',
            '%,' . $stockId,
        ]);
        $usedQuantity = 0;
        while ($usedCommand = $usedRequest->fetch()) {
            $products = explode(',', (string) $usedCommand['product']);
            $quantities = explode(',', (string) $usedCommand['qty']);
            foreach ($products as $index => $commandProduct) {
                if ($commandProduct === $stockId) {
                    $usedQuantity += (int) ($quantities[$index] ?? 0);
                }
            }
        }
        $stock['remaining'] = max(0, (int) $stock['qty'] - $usedQuantity);
        $stockItems[] = $stock;
    }
} catch (PDOException $e) {
    // The manual product form remains available if stock data cannot be loaded.
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Colis - <?php echo $settings['appname'] ?? 'EXLIV Delivery'; ?></title>
    <link rel="stylesheet" href="css/general_style.css">
    <link rel="stylesheet" href="css/main_style.php">
    <link rel="stylesheet" href="css/reset_style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-header h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .package-types {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        .package-type {
            flex: 1;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        .package-type:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        .package-type.active {
            border-color: #667eea;
            background: #e3f2fd;
        }
        .package-type input {
            display: none;
        }
        .package-type-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .package-type-title {
            font-weight: 600;
            color: #333;
        }
        .package-type-desc {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
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
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
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
        .info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .info-box h4 {
            color: #1976d2;
            margin-bottom: 10px;
        }
        .info-box ul {
            margin-left: 20px;
            color: #666;
        }
        .info-box li {
            margin-bottom: 5px;
        }
        .product-source {
            background: #f8fafc;
            border: 1px solid #dbe4f0;
            border-radius: 12px;
            padding: 16px 18px;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .product-source.is-active {
            background: #f0fdf4;
            border-color: #22c55e;
            box-shadow: 0 0 0 3px #dcfce7;
        }
        .stock-choice-label {
            align-items: center;
            color: #14532d !important;
            cursor: pointer;
            display: flex !important;
            font-size: 15px;
            gap: 10px;
            margin: 0 !important;
        }
        .stock-choice-label input[type="checkbox"] {
            accent-color: #16a34a;
            height: 18px;
            margin: 0;
            padding: 0;
            width: 18px;
        }
        .stock-choice-label i {
            color: #16a34a;
            font-size: 17px;
        }
        .source-help, .stock-empty-note {
            color: #64748b;
            display: block;
            font-size: 13px;
            line-height: 1.45;
            margin: 7px 0 0 28px;
        }
        #stock-product-group {
            animation: stock-choice-in .2s ease-out;
            background: #f8fffb;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 16px;
        }
        #stock-product-group label::before {
            color: #16a34a;
            content: '✓';
            margin-right: 7px;
        }
        #stock-id {
            background-color: #fff;
        }
        @keyframes stock-choice-in {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 600px) {
            .product-source, #stock-product-group { padding: 14px; }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div class="form-header">
            <h1>📦 Ajouter un Colis</h1>
            <p>Remplissez le formulaire ci-dessous pour ajouter un nouveau colis</p>
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
        
        <div class="info-box">
            <h4>📋 Types de colis disponibles</h4>
            <ul>
                <li><strong>Particulier:</strong> Pour les envois personnels, livraison en 24-48h</li>
                <li><strong>Rapide:</strong> Livraison express en 12-24h</li>
                <li><strong>Normal:</strong> Livraison standard en 48-72h</li>
            </ul>
        </div>
        
        <form method="POST">
            <div class="package-types">
                <label class="package-type <?php echo ($_POST['package_type'] ?? 'normal') == 'particulier' ? 'active' : ''; ?>">
                    <input type="radio" name="package_type" value="particulier" <?php echo ($_POST['package_type'] ?? 'normal') == 'particulier' ? 'checked' : ''; ?>>
                    <div class="package-type-icon">👤</div>
                    <div class="package-type-title">Particulier</div>
                    <div class="package-type-desc">24-48h</div>
                </label>
                <label class="package-type <?php echo ($_POST['package_type'] ?? 'normal') == 'rapide' ? 'active' : ''; ?>">
                    <input type="radio" name="package_type" value="rapide" <?php echo ($_POST['package_type'] ?? 'normal') == 'rapide' ? 'checked' : ''; ?>>
                    <div class="package-type-icon">⚡</div>
                    <div class="package-type-title">Rapide</div>
                    <div class="package-type-desc">12-24h</div>
                </label>
                <label class="package-type <?php echo ($_POST['package_type'] ?? 'normal') == 'normal' ? 'active' : ''; ?>">
                    <input type="radio" name="package_type" value="normal" <?php echo ($_POST['package_type'] ?? 'normal') == 'normal' ? 'checked' : ''; ?>>
                    <div class="package-type-icon">📦</div>
                    <div class="package-type-title">Normal</div>
                    <div class="package-type-desc">48-72h</div>
                </label>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Nom du destinataire *</label>
                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Téléphone *</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required placeholder="06XXXXXXXX">
                </div>
                
                <div class="form-group">
                    <label>Ville *</label>
                    <select name="city" required>
                        <option value="">Sélectionner une ville</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>" <?php echo ($_POST['city'] ?? '') == $city ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Prix (<?php echo $settings['currency'] ?? 'MAD'; ?>) *</label>
                    <input type="number" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required step="0.01" min="0">
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Adresse *</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group product-source <?php echo $fromStock ? 'is-active' : ''; ?>" id="product-source-card" style="grid-column: 1 / -1;">
                    <label class="stock-choice-label">
                        <input type="checkbox" id="from-stock" name="from_stock" value="1" <?php echo $fromStock ? 'checked' : ''; ?>>
                        <i class="fa fa-boxes"></i>
                        Utiliser mon stock disponible
                    </label>
                    <small class="source-help">Le produit et la quantité seront déduits de votre stock validé.</small>
                </div>

                <div class="form-group" id="stock-product-group" style="display:<?php echo $fromStock ? 'block' : 'none'; ?>;">
                    <label>Produit en stock *</label>
                    <select name="stock_id" id="stock-id">
                        <option value="">Sélectionner un produit</option>
                        <?php foreach ($stockItems as $stock): ?>
                            <option value="<?php echo (int) $stock['id']; ?>" <?php echo $stockId === (string) $stock['id'] ? 'selected' : ''; ?> <?php echo $stock['remaining'] < 1 ? 'disabled' : ''; ?>>
                                <?php echo htmlspecialchars($stock['title']); ?> — <?php echo htmlspecialchars($stock['ref']); ?> (reste: <?php echo (int) $stock['remaining']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (count($stockItems) === 0): ?>
                        <small class="stock-empty-note">Aucun produit validé n’est disponible dans votre stock.</small>
                    <?php endif; ?>
                </div>

                <div class="form-group" id="manual-product-group" style="display:<?php echo $fromStock ? 'none' : 'block'; ?>;">
                    <label>Produit *</label>
                    <input type="text" id="manual-product" name="product" value="<?php echo htmlspecialchars($_POST['product'] ?? ''); ?>" <?php echo $fromStock ? '' : 'required'; ?>>
                </div>
                
                <div class="form-group">
                    <label>Quantité</label>
                    <input type="number" name="qty" value="<?php echo htmlspecialchars($_POST['qty'] ?? '1'); ?>" min="1" required>
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Note (optionnel)</label>
                    <textarea name="note"><?php echo htmlspecialchars($_POST['note'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">✅ Ajouter le Colis</button>
        </form>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="color: #667eea; text-decoration: none; font-weight: 600;">← Retour au tableau de bord</a>
        </div>
    </div>
    
    <script>
        // Gestion de la sélection du type de colis
        document.querySelectorAll('.package-type').forEach(function(type) {
            type.addEventListener('click', function() {
                document.querySelectorAll('.package-type').forEach(function(t) {
                    t.classList.remove('active');
                });
                this.classList.add('active');
                this.querySelector('input').checked = true;
            });
        });

        var fromStock = document.getElementById('from-stock');
        var stockProductGroup = document.getElementById('stock-product-group');
        var manualProductGroup = document.getElementById('manual-product-group');
        var manualProduct = document.getElementById('manual-product');
        var productSourceCard = document.getElementById('product-source-card');

        fromStock.addEventListener('change', function() {
            var useStock = this.checked;
            stockProductGroup.style.display = useStock ? 'block' : 'none';
            manualProductGroup.style.display = useStock ? 'none' : 'block';
            manualProduct.required = !useStock;
            productSourceCard.classList.toggle('is-active', useStock);
        });
    </script>
</body>
</html>
