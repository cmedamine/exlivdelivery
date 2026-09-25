<?php
session_start();
include("config.php");
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/phpqrcode/qrlib.php';

if($_GET['tid'] != ""){
	$mpdf = new \mPDF('utf-8', array(103,100));
	$mpdf->autoScriptToLang = true;
	$mpdf->autoLangToFont = true;
	$back = $bdd->query("SELECT * FROM commands WHERE id IN(".$_GET['tid'].")");
	while($row = $back->fetch()){
		ob_start();
		?>
<!DOCTYPE html>
<html lang="zxx">
	<head>
		<meta charset="utf-8">
		<style>
			@page {
				margin:15px;
			}
		</style>
	</head>
	<body>
		<div>
			<div style="float:left;width:375px;margin:5px;">
				<div style="border:2px solid #242424;border-bottom:0px;">
					<table style="width:100%;height:40px;">
						<tr>
							<td style="width:50%;padding:5px 10px;font-family:'Arial';font-size:16px;font-weight:bold;text-align:left;">
								<h1><?php echo $settings['appname'];?></h1>
							</td>
							<td style="width:50%;padding:5px 10px;font-family:'Arial';font-size:16px;font-weight:bold;text-align:right;">
								<?php
								if($settings['logo'] != ""){
									?>
								<img src="uploads/<?php echo $settings['logo'];?>" height="30px" />
									<?php
								}
								?>							
							</td>
						</tr>
					</table>
				</div>
				<div style="border:2px solid #242424;border-bottom:0px;">
					<table style="border-spacing:0px;border-collapse:collapse;">
						<tr>
							<td style="width:70%;padding:10px;font-family:'Arial';font-size:10px;border-right:2px solid #242424;">
								<div style="padding:5px;font-family:'Arial';font-size:10px;">
									<b>Nom complet:</b> <?php echo $row['fullname'];?>
									<br />
									<b>Téléphone:</b> <?php echo $row['phone'];?>
									<br />
									<b>Adresse:</b> <?php echo substr($row['address'],0,100);?>
									<br />
									<b>Ville:</b> <?php echo $row['city'];?>
									<br />
									<b>Date d'envoi</b>: <?php echo date("d/m/Y");?>*
									<table style="margin-top:10px;font-family:'Arial';font-size:11px;border-spacing:0px;border-collapse:collapse;">
										<tr>
											<td style="width:16px;height:16px;text-align:center;border:1px solid #242424;"><?php echo ($row['openpackage'] == "1")?"&#10003;":"";?></td>
											<td style="padding:0px 20px 0px 5px;">Ouvrir le colis</td>
											<td style="width:16px;height:16px;text-align:center;border:1px solid #242424;"><?php echo ($row['echange'] == "1")?"&#10003;":"";?></td>
											<td style="padding:0px 0px 0px 5px;">Change</td>
										</tr>
									</table>
								</div>
							</td>
							<td style="width:30%;paddin-top:10px;font-family:'Arial';font-size:9px;text-align:center;">
								<?php
									$text = $row['code'];
									$folder = "images/";
									$file_name = $row['code'].".png";
									$file_name=$folder.$file_name;
									QRcode::png($text,$file_name);
									echo"<img src='".$file_name."' width='100' height='100'>";
								?>							
							</td>
						</tr>
					</table>					
				</div>
				<div style="border:2px solid #242424;border-bottom:0px;">
					<div style="padding:5px;font-family:'Arial';font-size:12px;">
						<strong>Remarque:</strong>
						<br />
						<?php
						if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])){
							$products = explode(",",$row['product']);
							$qtys = explode(",",$row['qty']);
							for($i=0;$i<count($products);$i++){
								$back1 = $bdd->query("SELECT * FROM stocks WHERE id='".$products[$i]."'");
								$row1 = $back1->fetch();
								echo $row1['title'] . " - " . $row1['ref'] . " x " . $qtys[$i] . " (Stock)<br />";
							}
						}
						else{
							echo $row['product']!="null"?$row['product']:"";
						}
						?>
					</div>			
				</div>
				<div style="border:2px solid #242424;border-bottom:0px;">
					<div style="padding:5px;font-family:'Arial';font-size:14px;font-weight:bold;text-align:center;">
						CRBT: <?php echo $row['price'];?> <?php echo $settings['currency'];?>
					</div>
				</div>
				<div style="padding:5px;font-family:'Arial';font-size:9px;text-align:center;border:2px solid #242424;border-bottom:0px;">
					<?php
					$back1 = $bdd->query("SELECT store,phone FROM users WHERE type='client' AND id='".$row['client']."'");
					$row1 = $back1->fetch();
					?>
					<b><?php echo $row1['store'];?> Nous vous remercions pour votre confiance Pour plus d'information veuillez nous appeler sur le numéro de service aprés vente: <?php echo $row1['phone'];?></b>
				</div>
				<div style="font-family:'Arial';font-size:10px;text-align:center;border:2px solid #242424;">
					<?php
					$code = "NOCODEBAR";
					if($row['code'] != ""){
						$code = $row['code'];
					}
					$generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
					echo '<img src="data:image/jpeg;base64,' . base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128)) . '" style="width:190px;height:30px;margin:5px auto;"><br />';
					echo $code;
					?>			
				</div>	
			</div>
		</div>
	</body>
</html>
		<?php				
		$content = ob_get_clean();
		$content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
		$mpdf->WriteHTML($content);
		$mpdf->AddPage();
	}
}
$mpdf->output(date("d-m-Y").'-TICKETS.pdf','D');