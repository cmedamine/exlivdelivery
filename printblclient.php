<?php
session_start();
include("config.php");
require_once __DIR__ . '/vendor/autoload.php';

ob_start();
$clientid = $_SESSION['id'];
$clientfn = $_SESSION['fullname'];
if($_GET['client'] != "0"){
	$back = $bdd->query("SELECT id,fullname FROM users WHERE id='".$_GET['client']."' AND type='client'");
	$row = $back->fetch();
	$clientid = $row['id'];
	$clientfn = $row['fullname'];
}
$ids = explode(",",$_GET['tid']);
$back = $bdd->query("SELECT code,dateadd FROM bls WHERE client='".$clientid."' AND cmds='".$_GET['tid']."'");
if($back->rowCount() == 0){
	$date = date('d/m/Y');
	$bl = 'BL-'.date('dmY').'-'.date('His');
	$req = $bdd->prepare("INSERT INTO bls(id,dlm,client,code,cmds,done,dateadd,trash) VALUES('0','0','".$clientid."','".$bl."','".$_GET['tid']."','off','".time()."','1')");
	$req->execute();
}
else{
	$row = $back->fetch();
	$bl = $row['code'];
	$date = date('d/m/Y',$row['dateadd']);
}
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
						<p>Client: <?php echo $clientfn;?></p>
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
					<th style="padding:4px;border:1px solid #000000;">Ville</th>
					<th style="width:60px;padding:4px;border:1px solid #000000;">CRBT</th>
				</tr>
				<?php
				$i = 1;
				$back = $bdd->query("SELECT code,fullname,phone,address,city,price FROM commands WHERE id IN(".$_GET['tid'].")");
				while($fd = $back->fetch()){
					?>
				<tr>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $i;?></td>
					<td style="padding:4px;border:1px solid #000000;">
					<?php
					//$generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
					//echo '<img src="data:image/jpeg;base64,' . base64_encode($generator->getBarcode($fd['code'], $generator::TYPE_CODE_128)) . '" style="width:160px;height:40px;"><br />';
					echo $fd['code'];
					?>
					</td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['fullname']."<br />".$fd['phone'];?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['address'];?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['city'];?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['price']." ".$settings['currency'];?></td>				
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