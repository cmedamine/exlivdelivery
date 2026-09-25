<?php
session_start();
include("config.php");
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/phpqrcode/qrlib.php';

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
			<?php
			if($_GET['tid'] != "" AND $_GET['model'] != ""){
				$back = $bdd->query("SELECT * FROM commands WHERE id IN(".$_GET['tid'].")");
				while($row = $back->fetch()){
					if($_GET['model'] == 1){
						?>
			<div style="float:left;width:355px;height:300px;margin:0px 10px;margin-bottom:10px;border:2px solid #242424;">
				<table style="width:100%;border-bottom:2px solid #242424;border-spacing:0px;border-collapse:collapse;">
					<tr>
						<td style="width:50%;padding:5px 10px;font-family:'Arial';font-size:9px;text-align:left;">
							<h1><?php echo $settings['appname'];?></h1>
						</td>
						<td style="width:50%;padding:5px 10px;font-family:'Arial';font-size:9px;text-align:right;">
							<?php
							if($settings['logo'] != ""){
								?>
							<img src="uploads/<?php echo $settings['logo'];?>" height="40px" />
								<?php
							}
							?>
						</td>
					</tr>
				</table>
				<table style="border-spacing:0px;border-collapse:collapse;">
					<tr>
						<td style="width:65%;padding:10px;font-family:'Arial';font-size:10px;border-right:2px solid #242424;border-bottom:2px solid #242424;">
							<p style="margin:0px;padding:0px;font-weight:bold;"><?php echo $row['code'];?></p>
							<p style="margin:0px;padding:0px;"><?php echo $row['fullname'];?></p>
							<p style="margin:0px;padding:0px;"><?php echo $row['phone'];?></p>
							<p style="margin:0px;padding:0px;"><?php echo substr($row['address'],0,100);?></p>
							<p style="margin:0px;padding:0px;font-weight:bold;"><?php echo $row['city'];?></p>
							<p style="margin:0px;padding:0px;"><b>Date d'envoi: </b><?php echo date("d/m/Y");?></p>
							<table style="margin-top:10px;font-family:'Arial';font-size:11px;border-spacing:0px;border-collapse:collapse;">
								<tr>
									<td style="width:16px;height:16px;text-align:center;border:1px solid #242424;"><?php echo ($row['openpackage'] == "1")?"&#10003;":"";?></td>
									<td style="padding:0px 20px 0px 5px;">Ouvrir le colis</td>
									<td style="width:16px;height:16px;text-align:center;border:1px solid #242424;"><?php echo ($row['echange'] == "1")?"&#10003;":"";?></td>
									<td style="padding:0px 0px 0px 5px;">Change</td>
								</tr>
							</table>
						</td>
						<td style="width:35%;paddin-top:10px;font-family:'Arial';font-size:9px;text-align:center;border-bottom:2px solid #242424;">
							<?php
								$text = $row['code'];
								$folder = "images/";
								$file_name = $row['code'].".png";
								$file_name=$folder.$file_name;
								QRcode::png($text,$file_name);
								echo"<img src='".$file_name."' width='120' height='120'>";
							?>							
						</td>
					</tr>
				</table>
				<table style="width:100%;border-spacing:0px;border-collapse:collapse;">
					<tr valign="top">
						<td style="width:50%;padding:5px 10px;border-right:2px solid #242424;" valign="top">
							<table style="width:100%;font-family:'Arial';font-size:11px;border-spacing:0px;border-collapse:collapse;">
								<tr>
									<td style="height:15px;padding:0px 3px;font-weight:bold;text-align:center;border:1px solid #242424;">Produit</td>
									<td style="width:30px;height:15px;padding:0px 3px;font-weight:bold;text-align:center;border:1px solid #242424;">Qté</td>
								</tr>
								<tr>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;">
										<?php
										$products = explode(",",$row['product']);
										if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])){
											$qtys = explode(",",$row['qty']);
											for($i=0;$i<count($products);$i++){
												$back1 = $bdd->query("SELECT * FROM stocks WHERE id='".$products[$i]."'");
												$row1 = $back1->fetch();
												echo $row1['title']."<br />";
											}
										}
										else{
											echo $row['product'];
										}
										?>									
									</td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;">
										<?php
										if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])){
											$qtys = explode(",",$row['qty']);
											for($i=0;$i<count($products);$i++){
												echo $qtys[$i]."<br />";
											}
										}
										?>									
									</td>
								</tr>
								<tr>
									<td colspan="2" style="padding-top:10px;font-size:14px;font-weight:bold;text-align:right;">CRBT: <?php echo $row['price'];?>DH</td>
								</tr>
							</table>
						</td>
						<td style="width:50%;padding:5px 10px;">
							<table style="width:100%;font-family:'Arial';font-size:11px;border-spacing:0px;border-collapse:collapse;">
								<tr>
									<td style="height:15px;padding:0px 3px;font-weight:bold;text-align:center;border:1px solid #242424;">PR</td>
									<td style="height:15px;padding:0px 3px;font-weight:bold;text-align:center;border:1px solid #242424;">IN</td>
									<td style="height:15px;padding:0px 3px;font-weight:bold;text-align:center;border:1px solid #242424;">RP</td>
									<td style="height:15px;padding:0px 3px;font-weight:bold;text-align:center;border:1px solid #242424;">AN</td>
									<td style="height:15px;padding:0px 3px;font-weight:bold;text-align:center;border:1px solid #242424;">RF</td>
								</tr>
								<tr>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
								</tr>
								<tr>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
								</tr>
								<tr>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
								</tr>
								<tr>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
								</tr>
								<tr>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
									<td style="height:15px;padding:0px 3px;border:1px solid #242424;"></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<table style="width:100%;font-family:'Arial';font-size:9px;border-spacing:0px;border-collapse:collapse;">
					<tr>
						<td style="padding:3px 10px;border-top:2px solid #242424;">
							<?php
							$back1 = $bdd->query("SELECT store,phone FROM users WHERE type='client' AND id='".$row['client']."'");
							$row1 = $back1->fetch();
							?>
							<b><?php echo ucfirst(strtoupper($row1['store']));?></b> vous remercie pour votre confiance, Pour plus d'information veuillez nous appeler sur le numéro de service aprés vente: <strong><?php echo $row1['phone'];?></strong>
						</td>
					</tr>
				</table>
			</div>		
						<?php
					}
					elseif($_GET['model'] == 2){
						?>
			<div style="height:100px;border:2px solid #242424;border-bottom:0px;">
				<table style="width:100%;height:100px;">
					<tr>
						<td style="width:50%;padding:5px 10px;font-family:'Arial';font-size:40px;font-weight:bold;text-align:left;">
							<h1><?php echo $settings['appname'];?></h1>
						</td>
						<td style="width:50%;padding:5px 10px;font-family:'Arial';font-size:40px;font-weight:bold;text-align:right;">
							<?php
							if($settings['logo'] != ""){
								?>
							<img src="uploads/<?php echo $settings['logo'];?>" height="60px" />
								<?php
							}
							?>						
						</td>
					</tr>
				</table>
			</div>
			<table style="width:100%;border:2px solid #242424;border-bottom:0px;">
				<tr>
					<td style="width:50%;height:190px;padding:20px;font-family:'Arial';font-size:14px;border-right:2px solid #242424;">
						<strong>Destinataire:</strong>
						<br /><br />
						<b>Nom complet:</b> <?php echo $row['fullname'];?>
						<br />
						<b>Téléphone:</b> <?php echo $row['phone'];?>
						<br />
						<b>Adresse:</b> <?php echo $row['address'];?>
						<br />
						<b>Ville:</b> <?php echo $row['city'];?>
						<br />
						<b>Date d'envoi</b>: <?php echo date("d/m/Y");?>
					</td>
					<td style="width:50%;padding:20px;font-family:'Arial';font-size:14px;">
						<strong>Remarque:</strong>
						<br /><br />
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
							echo $row['product'];
						}
						?>
					</td>
				</tr>
			</table>
			<table style="width:100%;height:100px;border:2px solid #242424;border-bottom:0px;">
				<tr>
					<td style="width:30%;padding:10px;font-family:'Arial';font-size:30px;font-weight:bold;text-align:center;border-right:2px solid #242424;">
						CRBT: <?php echo $row['price'];?> <?php echo $settings['currency'];?>
					</td>
					<td style="width:50%;padding:5px 10px;font-family:'Arial';text-align:center;border-right:2px solid #242424;">
						<?php
						$code = "NOCODEBAR";
						if($row['code'] != ""){
							$code = $row['code'];
						}
						$generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
						echo '<img src="data:image/jpeg;base64,' . base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128)) . '" style="width:260px;height:60px;margin:10px auto 5px;"><br />';
						echo $code;
						?>			
					</td>
					<td style="width:20%;padding:10px;font-family:'Arial';text-align:center;">
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
			<div style="height:100px;margin-bottom:10px;padding:20px 0px 0px;font-family:'Arial';text-align:center;border:2px solid #242424;">
				<?php
				$back1 = $bdd->query("SELECT store,phone FROM users WHERE type='client' AND id='".$row['client']."'");
				$row1 = $back1->fetch();
				?>
				<b><?php echo $row1['store'];?> Nous vous remercions pour votre confiance<br />Pour plus d'information veuillez nous appeler sur le numéro de service aprés vente:<br />
				<strong style="font-size:30px;"><?php echo $row1['phone'];?></strong>
			</div>
						<?php
					}
					elseif($_GET['model'] == 3){
						?>
			<div style="float:left;width:375px;margin:5px;">
				<div style="height:50px;border:2px solid #242424;border-bottom:0px;">
					<table style="width:100%;height:50px;">
						<tr>
							<td style="width:50%;padding:5px 10px;font-family:'Arial';font-size:20px;font-weight:bold;text-align:left;">
								<h1><?php echo $settings['appname'];?></h1>
							</td>
							<td style="width:50%;padding:5px 10px;font-family:'Arial';font-size:20px;font-weight:bold;text-align:right;">
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
				<div style="height:160px;border:2px solid #242424;border-bottom:0px;">
					<div style="padding:10px;font-family:'Arial';font-size:14px;">
						<b>Nom complet:</b> <?php echo $row['fullname'];?>
						<br />
						<b>Téléphone:</b> <?php echo $row['phone'];?>
						<br />
						<b>Adresse:</b> <?php echo $row['address'];?>
						<br />
						<b>Ville:</b> <?php echo $row['city'];?>
						<br />
						<b>Date d'envoi</b>: <?php echo date("d/m/Y");?>
					</div>
				</div>
				<div style="height:90px;border:2px solid #242424;border-bottom:0px;">
					<div style="padding:10px;font-family:'Arial';font-size:14px;">
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
				<div style="height:50px;border:2px solid #242424;border-bottom:0px;">
					<div style="padding:10px;font-family:'Arial';font-size:24px;font-weight:bold;text-align:center;">
						CRBT: <?php echo $row['price'];?> <?php echo $settings['currency'];?>
					</div>
				</div>
				<div style="height:70px;padding:10px 5px 0px;font-family:'Arial';font-size:12px;text-align:center;border:2px solid #242424;border-bottom:0px;">
					<?php
					$back1 = $bdd->query("SELECT store,phone FROM users WHERE type='client' AND id='".$row['client']."'");
					$row1 = $back1->fetch();
					?>
					<b><?php echo $row1['store'];?> Nous vous remercions pour votre confiance Pour plus d'information veuillez nous appeler sur le numéro de service aprés vente:<br />
					<strong style="font-size:30px;"><?php echo $row1['phone'];?></strong>
				</div>
				<div style="height:90px;font-family:'Arial';font-size:12px;text-align:center;border:2px solid #242424;">
					<table style="width:100%;height:180px;font-family:'Arial';text-align:center;">
						<tr>
							<td style="width:80%;padding:10px;border-right:2px solid #242424;">
								<?php
								$code = "NOCODEBAR";
								if($row['code'] != ""){
									$code = $row['code'];
								}
								$generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
								echo '<img src="data:image/jpeg;base64,' . base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128)) . '" style="width:200px;height:50px;"><br />';
								echo $code;
								?>							
							</td>
							<td style="width:20%;padding:10px;">
								<?php
								$text = $row['code'];
								$folder = "images/";
								$file_name = $row['code'].".png";
								$file_name=$folder.$file_name;
								QRcode::png($text,$file_name);
								echo"<img src='".$file_name."' width='90' height='90'>";
								?>							
							</td>
						</tr>
					</table>		
				</div>	
			</div>
					<?php
					}
					elseif($_GET['model'] == 4){
						?>
			<div style="float:left;width:375px;margin:5px;">
				<div style="height:30px;border:2px solid #242424;border-bottom:0px;">
					<table style="width:100%;height:30px;">
						<tr>
							<td style="width:50%;padding:5px;font-family:'Arial';font-size:14px;font-weight:bold;text-align:left;">
								<h1><?php echo $settings['appname'];?></h1>
							</td>
							<td style="width:50%;padding:5px;font-family:'Arial';font-size:14px;font-weight:bold;text-align:right;">
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
				<div style="height:105px;border:2px solid #242424;border-bottom:0px;">
					<div style="padding:5px 10px;font-family:'Arial';font-size:12px;line-height:16px;">
						<?php
						if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])){
							$good = "";
							$goods = "";
							if($row['product'] != 0){
								$goods = " - ";
							}
							$products = explode(",",$row['product']);
							$qtys = explode(",",$row['qty']);
							for($i=0;$i<count($products);$i++){
								$back1 = $bdd->query("SELECT * FROM stocks WHERE id='".$products[$i]."'");
								$row1 = $back1->fetch();
								if($qtys[$i] > 1){
									$good .= " + " . $qtys[$i] . " x " . $row1['ref'];
								}
								else{
									$good .= " + " . $row1['ref'];
								}
							}
							$goods .= substr($good,3)." (Stock)";
						}
						else{
							$goods = " - ".$row['product'];
						}
						?>
						<b>Nom complet:</b> <?php echo $row['fullname'].$goods;?>
						<br />
						<b>Téléphone:</b> <?php echo $row['phone'];?>
						<br />
						<b>Adresse:</b> <?php echo $row['address'];?>
						<br />
						<b>Ville:</b> <?php echo $row['city'];?>
						<br />
						<b>Date d'envoi</b>: <?php echo date("d/m/Y");?>
					</div>
				</div>
				<div style="height:30px;border:2px solid #242424;border-bottom:0px;">
					<div style="padding:5px;font-family:'Arial';font-size:16px;font-weight:bold;text-align:center;">
						CRBT: <?php echo $row['price'];?> <?php echo $settings['currency'];?>
					</div>
				</div>
				<div style="height:25px;padding:5px 5px 0px;font-family:'Arial';font-size:12px;text-align:center;border:2px solid #242424;border-bottom:0px;">
					<?php
					$back1 = $bdd->query("SELECT store,phone FROM users WHERE type='client' AND id='".$row['client']."'");
					$row1 = $back1->fetch();
					?>
					<strong style="font-size:14px;"><?php echo $row1['store'];?> Service après vente: <?php echo $row1['phone'];?></strong>
				</div>
				<div style="height:60px;font-family:'Arial';font-size:12px;text-align:center;border:2px solid #242424;">
					<table style="width:100%;height:180px;font-family:'Arial';text-align:center;">
						<tr>
							<td style="width:80%;padding:0px;border-right:2px solid #242424;">
								<?php
								$code = "NOCODEBAR";
								if($row['code'] != ""){
									$code = $row['code'];
								}
								$generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
								echo '<img src="data:image/jpeg;base64,' . base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128)) . '" style="width:200px;height:35px;"><br />';
								echo $code;
								?>							
							</td>
							<td style="width:20%;padding:0px;">
								<?php
								$text = $row['code'];
								$folder = "images/";
								$file_name = $row['code'].".png";
								$file_name=$folder.$file_name;
								QRcode::png($text,$file_name);
								echo"<img src='".$file_name."' width='50' height='50'>";
								?>							
							</td>
						</tr>
					</table>		
				</div>	
			</div>
					<?php						
					}
				}
			}
			?>
		</div>
	</body>
</html>
<?php	
$content = ob_get_clean();
$content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
$mpdf = new \mPDF();
$mpdf->autoScriptToLang = true;
$mpdf->autoLangToFont = true;
$mpdf->WriteHTML($content);
$mpdf->output(date("d-m-Y").'-TICKETS.pdf','D');
?>