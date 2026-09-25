<?php
session_start();
include("config.php");
require_once __DIR__ . '/vendor/autoload.php';

ob_start();
$back = $bdd->query("SELECT fullname,phone FROM users WHERE id='".$_GET['dlm']."'");
$dlm = $back->fetch();
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

					</td>
				</tr>
			</table>
			<h1 style="margin:40px 0px 20px 0px;font-size:24px;text-align:center;">Bon d'envoi N&ordm;: <?php echo date("dmY");?></h1>
			<p>Date: le <?php echo date("d/m/Y");?></p>
			<p style="text-align:center;font-size:20px;font-weight:bold;">EXPEDITEUR</p>
			<br />
			<br />
			<p><strong style="display:inline-block;width:300px">Portable</strong>: 07 08 71 00 00</p>
			<p><strong style="display:inline-block;width:40%;">Fixe</strong>: 05 29 10 40 87</p>
			<p><strong style="display:inline-block;width:40%;">Ville</strong>: Safi</p>
			<p><strong style="display:inline-block;width:40%;">Nb colis</strong>: <?php echo count(explode(",",$_GET['tid'])) - 1;?></p>
			<p><strong style="display:inline-block;width:40%;">Email</strong>: benjermoungroup@gmail.com</p>
			<p>.....................................................................................................................................................................</p>
			<p>.....................................................................................................................................................................</p>
			<p style="text-align:center;font-size:20px;font-weight:bold;">DESTINATAIRE</p>
			<br />
			<br />
			<p><strong style="display:inline-block;width:40%;">Nom</strong>: <?php echo $dlm['fullname'];?></p>
			<p><strong style="display:inline-block;width:40%;">Téléphone</strong>: <?php echo $dlm['phone'];?></p>
			<br />
			<br />
			<br />
			<br />
			<br />
			<br />
			<br />
			<br />
			<p style="text-align:right;font-size:20px;font-weight:bold;">CEO & COFOUNDER</p>
			<p style="text-align:center;font-weight:bold;">BENJERMOUN GROUP</p>
			<p style="text-align:center;">Hub Principal Safi</p>
		</div>
	</body>
</html>
<?php
$content = ob_get_clean();
$mpdf = new \mPDF();
$mpdf->autoScriptToLang = true;
$mpdf->autoLangToFont = true;
$mpdf->WriteHTML($content);
$mpdf->Output('ENVOI-'.date("dmY").'.pdf','D');
?>