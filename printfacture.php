<?php
session_start();
include("config.php");
require_once __DIR__ . '/vendor/autoload.php';

ob_start();
$back = $bdd->query("SELECT code,dlm,client,charges FROM factures WHERE code='".$_GET['f']."'");
$facture = $back->fetch();
$back = $bdd->query("SELECT fullname,rib FROM users WHERE id='".$facture['client']."'");
$client = $back->fetch();
$back = $bdd->query("SELECT fullname FROM users WHERE id='".$facture['dlm']."'");
$dlm = $back->fetch();
$back = $bdd->query("SELECT DISTINCT code,dlm,client,product,qty,phone,city,price,extrafees,state,dateupdate,(SELECT price FROM packaging WHERE id=c.package) AS packageprice FROM commands c,facturesdetails fd WHERE fd.command=c.id AND facture='".$facture['code']."' ORDER BY c.code");
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
						<?php
						if($facture['client'] != "0"){
							?>
						<p>Client: <?php echo $client['fullname'];?></p>
						<p>RIB: <?php echo $client['rib'];?></p>
							<?php
						}
						if($facture['dlm'] != "0"){
							?>
						<p>Livreur: <?php echo $dlm['fullname'];?></p>
							<?php
						}
						?>
						<p>Nb. commandes: <?php echo $back->rowCount();$nbcommands = $back->rowCount();?></p>
						<p>Date: <?php echo date('d/m/Y');?></p>
					</td>
				</tr>
			</table>
			<h1 style="margin:40px 0px 20px 0px;font-size:24px;text-align:center;">Facture N&ordm;: <?php echo $_GET['f'];?></h1>
			<table style="width:100%;font-size:12px;font-family:'Arial';text-align:center;border:1px solid #000000;">
				<tr>
					<th style="padding:4px;border:1px solid #000000;">N&ordm;</th>
					<th style="padding:4px;border:1px solid #000000;">Code d'envoi</th>
					<th style="padding:4px;border:1px solid #000000;">Date livraison</th>
					<th style="padding:4px;border:1px solid #000000;">Téléphone</th>
					<th style="padding:4px;border:1px solid #000000;">Ville</th>
					<?php
					if($facture['client'] != "0"){
						?>
					<th style="padding:4px;border:1px solid #000000;">Produit</th>
						<?php
					}
					?>
					<th style="padding:4px;border:1px solid #000000;">Etat</th>
					<th style="padding:4px;border:1px solid #000000;">CRBT</th>
					<th style="padding:4px;border:1px solid #000000;">Frais</th>
				</tr>
				<?php
				$j = 1;
				$price = 0;
				$fees = 0;
				$package = 0;
				while($fd = $back->fetch()){
					?>
				<tr>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $j;?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['code'];?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo date('d/m/Y',$fd['dateupdate']);?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['phone'];?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['city'];?></td>
					<?php
					if($facture['client'] != "0"){
						?>
					<td style="padding:4px;border:1px solid #000000;">
					<?php
						if(preg_match("#^[0-9]+(,[0-9]+)*$#",$fd['product'])){
							$i = 0;
							$qtys = explode(",",$fd['qty']);
							$back1 = $bdd->query("SELECT title FROM stocks WHERE id IN(".$fd['product'].") ORDER BY FIELD(id,".$fd['product'].")");
							while($row1 = $back1->fetch()){
								echo $row1['title']." x ".$qtys[$i];
								$i++;
							}
						}
						else{
							$qtys = explode(",",$fd['qty']);
							$products = explode(",",$fd['product']);
							for($i=0;$i<count($products);$i++){
								echo $products[$i];
							}
						}
					?>
					</td>
						<?php
					}	
					?>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['state'];?></td>
					<?php
					if($fd['state'] == "Livré"){
						?>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['price']." ".$settings['currency'];?></td>				
						<?php
					}
					else{
						?>
					<td style="padding:4px;border:1px solid #000000;"><?php echo "0 ".$settings['currency'];?></td>				
						<?php
					}
					if($facture['client'] != "0"){
						$back1 = $bdd->query("SELECT deliveredfees,refusedfees,returnedfees FROM clientfees WHERE city='".addslashes($fd['city'])."' AND client='".$fd['client']."' AND trash='1'");
						if($back1->rowCount() == 0){
							$back1 = $bdd->query("SELECT deliveredfees,refusedfees,returnedfees FROM gshippingfees WHERE city='".addslashes($fd['city'])."'");
						}
					}
					if($facture['dlm'] != "0"){
						$back1 = $bdd->query("SELECT deliveredfees,refusedfees,'0' AS returnedfees FROM shippingfees WHERE city='".addslashes($fd['city'])."' AND dlm='".$fd['dlm']."' AND trash='1'");
					}
					$row1 = $back1->fetch();
					?>
					<td style="padding:4px;border:1px solid #000000;">
						<?php
						if($fd['state'] == "Livré"){
							echo $row1['deliveredfees']+$fd['extrafees'];
						}
						elseif($fd['state'] == "Refusé"){
							echo $row1['refusedfees']+$fd['extrafees'];					
						}
						else{
							echo $row1['returnedfees']+$fd['extrafees'];						
						}						
						echo $settings['currency'];
						?>
					</td>							
				</tr>
					<?php
					if($fd['state'] == "Livré"){
						$price += $fd['price'];
						$fees += $row1['deliveredfees']+$fd['extrafees'];
						if($facture['client'] != "0"){
							$package += intval($fd['packageprice']);
						}
					}
					elseif($fd['state'] == "Refusé"){
						$fees += $row1['refusedfees']+$fd['extrafees'];						
					}
					else{
						$fees += $row1['returnedfees']+$fd['extrafees'];						
					}
					$j += 1;
				}
				?>
			</table>
			<table style="margin:20px 0px 0px 445px;font-family:'Arial';font-weight:bold;text-align:center;border:1px solid #000000;">
				<tr>
					<td style="width:150px;padding:4px;border:1px solid #000000;">Total brut</td>
					<td style="width:105px;padding:4px;border:1px solid #000000;"><?php echo $price;?> <?php echo $settings['currency'];?></td>
				</tr>
				<tr>
					<td style="padding:4px;border:1px solid #000000;">Frais</td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fees;?> <?php echo $settings['currency'];?></td>
				</tr>
				<?php
				if($facture['client'] != "0"){
					?>
				<tr>
					<td style="padding:4px;border:1px solid #000000;">Emballage</td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $package;?> <?php echo $settings['currency'];?></td>
				</tr>
					<?php
				}
				?>
				<?php
				if($facture['charges'] != "0"){
					?>
				<tr>
					<td style="padding:4px;border:1px solid #000000;">Charges sup.</td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $facture['charges'];?> <?php echo $settings['currency'];?></td>
				</tr>
					<?php
				}
				?>
				<tr>
					<td style="padding:4px;border:1px solid #000000;">Total net</td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $price-$fees-$package-$facture['charges'];?> <?php echo $settings['currency'];?></td>
				</tr>
				<?php
				$req = $bdd->query("UPDATE factures SET price='".($price-$fees-$package-$facture['charges'])."',nbcommands='".$nbcommands."' WHERE code='".$facture['code']."'");
				$req->execute();
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
$mpdf->Output($_GET['f'].'.pdf','D');
?>