<?php
/**
 * EXLIV Delivery - Public Tracking Page
 * Landing page pour le suivi des colis par les clients
 */

session_start();
include("config.php");

$tracking_result = null;
$error = '';

// Traitement du formulaire de tracking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tracking_code'])) {
    $tracking_code = sanitize_vars($_POST['tracking_code']);
    
    if (empty($tracking_code)) {
        $error = 'Veuillez entrer un numéro de suivi.';
    } else {
        try {
            // Rechercher par code de tracking ou code de commande
            $req = $bdd->prepare("SELECT c.*, 
                (	SELECT state FROM commandshistory 
                    WHERE command = c.code 
                    ORDER BY dateadd DESC LIMIT 1
                ) as current_state,
                (	SELECT dateadd FROM commandshistory 
                    WHERE command = c.code 
                    ORDER BY dateadd DESC LIMIT 1
                ) as last_update
                FROM commands c 
                WHERE (c.tracking_code = ? OR c.code = ?) 
                AND c.trash = '1'
                LIMIT 1");
            $req->execute([$tracking_code, $tracking_code]);
            $tracking_result = $req->fetch();
            
            if (!$tracking_result) {
                $error = 'Aucun colis trouvé avec ce numéro de suivi.';
            } else {
                // Récupérer l'historique complet
                $req = $bdd->prepare("SELECT * FROM commandshistory WHERE command = ? ORDER BY dateadd ASC");
                $req->execute([$tracking_result['code']]);
                $tracking_result['history'] = $req->fetchAll();
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la recherche: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi de Colis - <?php echo $settings['appname'] ?? 'EXLIV Delivery'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Barlow', Arial, sans-serif;
            background: #f6f8fc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 0;
            border: 1px solid #e4e9f1;
            box-shadow: 0 8px 24px rgba(17,39,78,0.07);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            color: #263a61;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 16px;
        }
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        .search-box input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 3px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .search-box input:focus {
            outline: none;
            border-color: #e88a28;
        }
        .search-box button {
            padding: 15px 30px;
            background: #e88a28;
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .search-box button:hover {
            transform: translateY(-2px);
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #c33;
        }
        .tracking-result {
            background: #f6f8fc;
            border-radius: 0;
            border: 1px solid #e4e9f1;
            padding: 30px;
        }
        .tracking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e4e9f1;
        }
        .tracking-code {
            font-size: 24px;
            font-weight: bold;
            color: #263a61;
        }
        .tracking-status {
            padding: 8px 20px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-nouveau { background: #e3f2fd; color: #1976d2; }
        .status-confirmé { background: #e8f5e9; color: #388e3c; }
        .status-ramassé { background: #fff3e0; color: #f57c00; }
        .status-livré { background: #e8f5e9; color: #2e7d32; }
        .status-retour { background: #ffebee; color: #c62828; }
        .status-annulé { background: #f5f5f5; color: #757575; }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            background: white;
            padding: 15px;
            border-radius: 3px;
            border: 1px solid #e4e9f1;
        }
        .info-label {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .timeline {
            margin-top: 30px;
        }
        .timeline-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .timeline-item {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 30px;
            bottom: -20px;
            width: 2px;
            background: #e0e0e0;
        }
        .timeline-item:last-child::before {
            display: none;
        }
        .timeline-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e88a28;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
        }
        .timeline-content {
            flex: 1;
            background: white;
            padding: 15px;
            border-radius: 3px;
            border: 1px solid #e4e9f1;
        }
        .timeline-state {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .timeline-date {
            font-size: 12px;
            color: #999;
        }
        .package-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .type-particulier { background: #fce4ec; color: #c2185b; }
        .type-rapide { background: #fff3e0; color: #e65100; }
        .type-normal { background: #e3f2fd; color: #1976d2; }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .footer a {
            color: #e88a28;
            text-decoration: none;
            font-weight: 600;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            .search-box {
                flex-direction: column;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Suivi de Colis</h1>
            <p>Entrez votre numéro de suivi pour suivre votre colis</p>
        </div>
        
        <form method="POST" class="search-box">
            <input type="text" name="tracking_code" placeholder="Numéro de suivi (ex: CMD-12082026-00001)" value="<?php echo htmlspecialchars($_POST['tracking_code'] ?? ''); ?>" required>
            <button type="submit">🔍 Rechercher</button>
        </form>
        
        <?php if ($error): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($tracking_result): ?>
            <div class="tracking-result">
                <div class="tracking-header">
                    <div>
                        <div class="tracking-code">
                            <?php echo htmlspecialchars($tracking_result['code']); ?>
                            <span class="package-type type-<?php echo $tracking_result['package_type']; ?>">
                                <?php echo ucfirst($tracking_result['package_type']); ?>
                            </span>
                        </div>
                        <div style="font-size: 14px; color: #666; margin-top: 5px;">
                            <?php if ($tracking_result['tracking_code']): ?>
                                Tracking: <?php echo htmlspecialchars($tracking_result['tracking_code']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="tracking-status status-<?php echo strtolower($tracking_result['current_state'] ?? 'nouveau'); ?>">
                        <?php echo htmlspecialchars($tracking_result['current_state'] ?? 'Nouveau'); ?>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Destinataire</div>
                        <div class="info-value"><?php echo htmlspecialchars($tracking_result['fullname']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value"><?php echo htmlspecialchars($tracking_result['phone']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ville</div>
                        <div class="info-value"><?php echo htmlspecialchars($tracking_result['city']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Montant</div>
                        <div class="info-value"><?php echo format_price($tracking_result['price'], $settings['currency'] ?? 'MAD'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Produit</div>
                        <div class="info-value"><?php echo htmlspecialchars(substr($tracking_result['product'], 0, 50)); ?>...</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date de commande</div>
                        <div class="info-value"><?php echo format_date($tracking_result['dateadd']); ?></div>
                    </div>
                </div>
                
                <?php if (!empty($tracking_result['history'])): ?>
                    <div class="timeline">
                        <div class="timeline-title">📋 Historique de suivi</div>
                        <?php foreach ($tracking_result['history'] as $item): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot">✓</div>
                                <div class="timeline-content">
                                    <div class="timeline-state"><?php echo htmlspecialchars($item['state']); ?></div>
                                    <div class="timeline-date"><?php echo format_date($item['dateadd']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>Powered by <a href="index.php"><?php echo $settings['appname'] ?? 'EXLIV Delivery'; ?></a></p>
        </div>
    </div>
</body>
</html>
