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
					<div class="lx-page-header lx-pb-5">
						<h2>Page non trouvé</h2>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-cleaner">
						<h3>Désolé, mais il semble que vous n'avez pas l'autorisation pour voir cette page</h3>
					</div>
					<div class="lx-clear-fix"></div>
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