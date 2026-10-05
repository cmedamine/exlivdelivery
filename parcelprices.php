<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['id'])) { header('Location: login.php'); exit; }
if (!preg_match('#Frais de livraison#', $_SESSION['roles'] ?? '')) { header('Location: 404.php'); exit; }

$types = [
    'particulier' => 'Colis passager',
    'rapide' => 'Colis express',
    'normal' => 'Colis normal',
];
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prices = $_POST['prices'] ?? [];
    try {
        $bdd->beginTransaction();
        $save = $bdd->prepare('INSERT INTO package_type_prices (package_type, price, active) VALUES (?, ?, \'1\') ON DUPLICATE KEY UPDATE price=VALUES(price), active=\'1\'');
        foreach ($types as $type => $label) {
            $value = $prices[$type] ?? '';
            if (!is_numeric($value) || (float)$value < 0 || (float)$value > 99999999.99) {
                throw new InvalidArgumentException('Veuillez saisir un tarif valide pour chaque type de colis.');
            }
            $save->execute([$type, number_format((float)$value, 2, '.', '')]);
        }
        $bdd->commit();
        $message = 'Les tarifs ont été enregistrés.';
    } catch (Throwable $e) {
        if ($bdd->inTransaction()) $bdd->rollBack();
        $message = $e instanceof InvalidArgumentException ? $e->getMessage() : 'Table de tarifs indisponible. Exécutez la migration 20261005_package_type_prices.php.';
    }
}
$rows = [];
try { $rows = $bdd->query('SELECT package_type, price FROM package_type_prices')->fetchAll(PDO::FETCH_KEY_PAIR); } catch (PDOException $e) {}
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tarifs des colis</title><link rel="stylesheet" href="css/general_style.css"><link rel="stylesheet" href="css/main_style.php"><link rel="stylesheet" href="css/reset_style.css"></head><body>
<div class="lx-wrapper"><div class="lx-header"><?php include 'header.php'; ?></div><div class="lx-main"><div class="lx-main-leftside"><?php include 'mainmenu.php'; ?></div><main class="lx-main-content"><div class="lx-page-header"><h2>Tarifs des colis</h2></div><div class="lx-page-content"><div class="lx-g1"><p>Ces tarifs s’ajoutent aux frais de livraison dans le calcul de la facture client.</p><?php if ($message): ?><p role="status"><?php echo htmlspecialchars($message); ?></p><?php endif; ?><form method="post"><div class="lx-form"><div class="lx-add-form">
<?php foreach ($types as $type => $label): ?><div class="lx-textfield lx-g1"><label><span><?php echo htmlspecialchars($label); ?> (<?php echo htmlspecialchars($settings['currency'] ?? 'MAD'); ?>)</span><input type="number" name="prices[<?php echo $type; ?>]" min="0" max="99999999.99" step="0.01" required value="<?php echo htmlspecialchars((string)($rows[$type] ?? '0.00')); ?>"></label></div><?php endforeach; ?>
<div class="lx-submit lx-g1"><button type="submit">Enregistrer les tarifs</button></div></div></div></form></div></div></main></div></div></body></html>
