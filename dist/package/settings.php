<?php
session_start();
include("config.php");

if(!isset($_SESSION['id'])){
	header('location: login.php');
}

if(isset($_SESSION['id']) AND isset($_SESSION['fullname'])){
?>
<!DOCTYPE html>
<html lang="zxx">
	<head>
		<meta charset="utf-8">
		<title><?php echo $settings['appname'];?> - CPanel</title>
		<meta name="description" content="<?php echo $settings['appname'];?> - CPanel">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex,nofollow" />
		<!-- General CSS Settings -->
		<link rel="stylesheet" href="css/general_style.css">
		<!-- Main Style of the template -->
		<link rel="stylesheet" href="css/main_style.php">
		<!-- Landing Page Style -->
		<link rel="stylesheet" href="css/reset_style.css">
		<!-- Awesomefont -->
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
		<!-- DateRangePicker -->
		<link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
		<!-- Fav Icon -->
		<link rel="shortcut icon" href="../favicon.ico">
		<?php include("onesignal.php");?>
	</head>
	<body>

		<!-- Wrapper -->
		<div class="lx-wrapper">
			<!-- Header -->
			<div class="lx-header">
				<?php include('header.php');?>
			</div>
			<!-- Main -->
			<div class="lx-main">
				<div class="lx-main-leftside">
					<?php include('mainmenu.php');?>
				</div>
				<!-- Main Content -->
				<div class="lx-main-content">
					<div class="lx-page-header">
						<h2>Préferences</h2>
						<p>Vous pouvez modifier vos paramétres d'affichage ici</p>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-page-content">
						<div class="lx-g2 lx-pb-0">
							<h3>Personnaliser votre experience d'affichage</h3><br />
							<div class="lx-add-form">
								<form action="#" method="post" id="settingsform" autocomplete="off">
									<?php
									$back = $bdd->query("SELECT * FROM parametres WHERE user='".$_SESSION['id']."'");
									$parametres = $back->fetch();										
									?>
									<div class="lx-textfield" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
										<input type="file" name="medias" id="medias" accept="image/x-png,image/jpeg" />
										<input type="hidden" name="picture" value="<?php echo $settings['logo'];?>" />
										<a href="javascript:;" class="lx-upload-picture">Ajouter logo</a>
									</div>
									<div class="lx-medias-item" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
										<?php
										$logo = "logo.png";
										if($settings['logo'] != ""){
											$logo = $settings['logo'];
										}
										?>
										<img src="uploads/cropped_<?php echo $logo;?>" />
									</div>
									<div class="lx-clear-fix"></div>
									<div class="lx-textfield lx-g1-f" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
										<label><span>Nom App: </span><input type="text" name="appname" value="<?php echo $settings['appname'];?>" /></label>
									</div>
									<div class="lx-clear-fix"></div>
									<div class="lx-textfield lx-g1-f" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
										<label><span>Commande prefix: </span><input type="text" name="cmdprefix" value="<?php echo $settings['cmdprefix'];?>" /></label>
									</div>
									<div class="lx-clear-fix"></div>
									<div class="lx-textfield lx-g1-f" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
										<label><span>Devise: </span><input type="text" name="currency" value="<?php echo $settings['currency'];?>" /></label>
									</div>
									<div class="lx-clear-fix"></div>
									<div class="lx-textfield lx-g1-f" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
										<label><span>Frais de confirmation: </span><input type="text" name="confirmationfees" value="<?php echo $settings['confirmationfees'];?>" /></label>
									</div>
									<div class="lx-clear-fix"></div>
									<div class="lx-textfield lx-g1-f" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
										<label><input type="checkbox" name="sepdelivered" <?php if($settings['sepdelivered'] == "1"){echo "checked";}?> /> Séparer les commandes (En cours, livrées, Echouées)<del class="checkmark"></del></label>
									</div>
									<div class="lx-clear-fix"></div>
									<div class="lx-textfield lx-g1-f" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
										<label><input type="checkbox" name="simplestats" <?php if($settings['simplestats'] == "1"){echo "checked";}?> /> Statistiques simple<del class="checkmark"></del></label>
									</div>
									<div class="lx-clear-fix"></div>
									<div class="lx-textfield lx-g1-f" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
										<label><input type="checkbox" name="requirednote" <?php if($settings['requirednote'] == "1"){echo "checked";}?> /> Commentaire obligatoire pour (Reporté, Annulé, Refusé, Hors zone)<del class="checkmark"></del></label>
									</div>
									<div class="lx-clear-fix"></div>
									<div class="lx-textfield lx-g1-f" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
										<label><input type="checkbox" name="reseller" <?php if($settings['reseller'] == "1"){echo "checked";}?> /> Ajoutrer le nom et le téléphone du vendeur<del class="checkmark"></del></label>
									</div>
									<div class="lx-clear-fix"></div>
									<div class="lx-textfield lx-g1-f">
										<label><input type="checkbox" name="rowcolor" <?php if($parametres['rowcolor'] == "1"){echo "checked";}?> /> Colorer la ligne entier de la commande<del class="checkmark"></del></label>
									</div>
									<div class="lx-clear-fix"></div>
									<?php
									$nb = 20;
									if($parametres['nbrows'] != "" AND $parametres['nbrows'] != "0"){
										$nb = $parametres['nbrows'];
									}	
									?>
									<div class="lx-textfield lx-g1-f">
										<label><span>Nombre de ligne d'affichage sur les tableaux: </span>
											<select name="nbrows">
												<option value="20" <?php if($nb==20){echo "selected";}?>>20</option>
												<option value="50" <?php if($nb==50){echo "selected";}?>>50</option>
												<option value="100" <?php if($nb==100){echo "selected";}?>>100</option>
												<option value="250" <?php if($nb==250){echo "selected";}?>>250</option>
												<option value="500" <?php if($nb==500){echo "selected";}?>>500</option>
												<option value="1000" <?php if($nb==500){echo "selected";}?>>1000</option>
											</select>
										</label>
									</div>
									<div class="lx-clear-fix"></div>
									<div class="lx-submit">
										<input type="hidden" name="id" value="<?php echo $settings['id'];?>" />
										<a href="javascript:;">Enregistrer</a>
									</div>
								</form>
							</div>
						</div>
						<div class="lx-g2 lx-pb-0">
							<div class="lx-add-form">
								<form action="#" method="post" id="backupform" autocomplete="off">
									<?php
									if($_SESSION['type'] == "moderator"){
									?>
									<div class="lx-submit">
										<a href="downloadbackup.php">Télécharger backup</a>
									</div>
									<div class="lx-submit">
										<a href="restorebackup.php">Restaurer backup</a>
									</div>
									<div class="lx-submit">
										<a href="install_database.php">Réinstaller base de données</a>
									</div>
									<?php
									}
									?>
								</form>
							</div>
						</div>
						<div class="lx-clear-fix"></div>
					</div>
				</div>
			</div>
		</div>

		<!-- JQuery -->
		<script src="js/jquery-1.12.4.min.js"></script>
		<!-- Main Script -->
		<script src="js/script.js"></script>
	</body>
</html>
<?php

}
?>