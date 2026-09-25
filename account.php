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
						<h2>Mon Compte</h2>
						<p>Vous pouvez modifier vos informations personnelles ici</p>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-page-content">
						<div class="lx-g2 lx-pb-0">
							<h3>Changer informations personnelles</h3><br />
							<div class="lx-add-form">
								<form action="#" method="post" id="accountform" autocomplete="off">
									<?php
									$back = $bdd->query("SELECT id,fullname,phone,email,picture FROM users WHERE id='".$_SESSION['id']."'");
									$row = $back->fetch();
									?>
									<div class="lx-textfield">
										<input type="file" name="medias" id="medias" accept="image/x-png,image/jpeg" />
										<input type="hidden" name="picture" value="<?php echo $row['picture'];?>" />
										<a href="javascript:;" class="lx-upload-picture">Changer photo de profile</a>
									</div>
									<div class="lx-medias-item">
										<img src="uploads/cropped_<?php echo $row['picture'];?>" />
									</div>
									<div class="lx-textfield">
										<label><span>Email: </span><input type="text" name="email" value="<?php echo $row['email'];?>" readonly /></label>
									</div>
									<div class="lx-textfield">
										<label><span>Nom complet: </span><input type="text" name="fullname" value="<?php echo $row['fullname'];?>" data-isnotempty="" data-message="Saisissez un nom" /></label>
									</div>
									<div class="lx-textfield">
										<label><span>Téléphone: </span><input type="text" name="phone" value="<?php echo $row['phone'];?>" data-isphone="" data-message="Ex: 06xxxxxxxx, 07xxxxxxxx ..." /></label>
									</div>
									<div class="lx-submit">
										<input type="hidden" name="id" value="<?php echo $row['id'];?>" />
										<a href="javascript:;">Enregistrer</a>
									</div>
								</form>
							</div>
						</div>
						<div class="lx-g2 lx-pb-0">
							<h3>Changer mot de passe</h3><br />
							<div class="lx-add-form">
								<form action="#" method="post" id="passwordform" autocomplete="off">
									<div class="lx-textfield">
										<label><span>Ancien mot de pass: </span><input type="password" name="oldpassword" /></label>
									</div>
									<div class="lx-textfield">
										<label><span>Nouveau mot de pass: </span><input type="password" name="newpassword1" /></label>
									</div>
									<div class="lx-textfield">
										<label><span>Confirmer nouveau mot de pass: </span><input type="password" name="newpassword2" /></label>
									</div>
									<div class="lx-submit">
										<input type="hidden" name="id" value="<?php echo $row['id'];?>" />
										<a href="javascript:;">Enregistrer</a>
									</div>
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