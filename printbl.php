<?php
session_start();
include("config.php");
require_once __DIR__ . '/vendor/autoload.php';

ob_start();
$ids = explode(",",$_GET['tid']);
$back = $bdd->query("SELECT code,dateadd FROM bls WHERE dlm='".$_GET['dlm']."' AND subdlm='".$_GET['subdlm']."' AND cmds='".$_GET['tid']."'");
$date = date('d/m/Y');
if($back->rowCount() == 0){
	$bl = 'BL-'.date('dmY').'-'.date('His');
	$req = $bdd->prepare("UPDATE commands SET subdlm='".$_GET['subdlm']."' WHERE id IN(".$_GET['tid'].")");
	$req->execute();
	$req = $bdd->prepare("INSERT INTO bls(id,dlm,subdlm,code,cmds,dateadd,trash) VALUES('0','".$_GET['dlm']."','".$_GET['subdlm']."','".$bl."','".$_GET['tid']."','".time()."','1')");
	$req->execute();
}
else{
	$row = $back->fetch();
	$bl = $row['code'];
	$date = date('d/m/Y',$row['dateadd']);
}
$back = $bdd->query("SELECT fullname FROM users WHERE id='".$_GET['subdlm']."'");
if($back->rowCount() == 0){
	$back = $bdd->query("SELECT fullname FROM users WHERE id='".$_GET['dlm']."'");
}
$row = $back->fetch();
?>
<!DOCTYPE html>
<html lang="zxx">
	<head>
		<meta charset="utf-8">
		<style>
			@page {
				margin:25px;
			}
		</style>
	</head>
	<body>
		<div style="padding:20px;font-family:'Arial';border:2px solid #000000;">
			<table style="width:100%;font-family:'Arial';">
				<tr>
					<td style="width:50%;">
						<?php
						$logo = "images/logo.png";
						if($settings['logo'] != ""){
							$logo = "uploads/".$settings['logo'];
						}
						?>
						<img src="<?php echo $logo;?>" width="200">
					</td>
					<td style="width:50%;font-weight:bold;">
						<p>Livreur: <?php echo $row['fullname'];?></p>
						<p>Nb. commandes: <?php echo (count($ids) - 1);?></p>
						<p>Date: <?php echo $date;?></p>
					</td>
				</tr>
			</table>
			<h1 style="margin:40px 0px 20px 0px;font-size:24px;text-align:center;"><?php echo $bl;?></h1>
			<table style="width:100%;font-size:12px;font-family:'Arial';text-align:center;border:1px solid #000000;">
				<tr>
					<th style="width:35px;padding:4px;border:1px solid #000000;">N&ordm;</th>
					<th style="width:180px;padding:4px;border:1px solid #000000;">Code d'envoi</th>
					<th style="padding:4px;border:1px solid #000000;">Client</th>
					<th style="padding:4px;border:1px solid #000000;">Adresse</th>
					<th style="padding:4px;border:1px solid #000000;">Produit</th>
					<th style="width:60px;padding:4px;border:1px solid #000000;">CRBT</th>
					<th style="padding:4px;border:1px solid #000000;">Etat</th>
				</tr>
				<?php
				$i = 1;
				$back = $bdd->query("SELECT code,product,qty,fullname,phone,address,city,price FROM commands WHERE id IN(".$_GET['tid'].")");
				while($fd = $back->fetch()){
					?>
				<tr>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $i;?></td>
					<td style="padding:4px;border:1px solid #000000;">
					<?php
					$generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
					echo '<img src="data:image/jpeg;base64,' . base64_encode($generator->getBarcode($fd['code'], $generator::TYPE_CODE_128)) . '" style="width:160px;height:40px;"><br />';
					echo $fd['code'];
					?>
					</td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['fullname']."<br />".$fd['phone'];?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['address']." ".$fd['city'];?></td>
					<td style="padding:4px;border:1px solid #000000;">
						<?php
						if(preg_match("#^[0-9]+(,[0-9]+)*$#",$fd['product'])){
							$i = 0;
							$qtys = explode(",",$fd['qty']);
							$back1 = $bdd->query("SELECT product FROM stocks WHERE id IN(".$fd['product'].") ORDER BY FIELD(id,".$fd['product'].")");
							while($row1 = $back1->fetch()){
								$back2 = $bdd->query("SELECT title FROM products WHERE id='".$row1['product']."'");
								$row2 = $back2->fetch()
								?>
						<span><?php echo $row2['title']." x ".$qtys[$i];?></span>
								<?php
								$i++;
							}
						}
						else{
							$products = explode(",",$fd['product']);
							$qtys = explode(",",$fd['qty']);
							for($i=0;$i<count($products);$i++){
								?>
						<span><?php echo $products[$i]." x ".$qtys[$i];?></span>
								<?php
							}							
						}
						?>
					</td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['price']." ".$settings['currency'];?></td>				
					<td style="padding:4px;border:1px solid #000000;"></td>
				</tr>
					<?php
					$i += 1;
				}
				?>
			</table>
		</div>
	</body>
</html>
<?php
$content = ob_get_clean();
$mpdf = new \mPDF();
$mpdf->autoScriptToLang = true;
$mpdf->autoLangToFont = true;
$mpdf->WriteHTML($content);
$mpdf->Output($bl.'.pdf','D');
?>