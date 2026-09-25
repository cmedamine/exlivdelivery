<?php
session_start();
include("config.php");

if(isset($_POST['username'])){
	$back = $bdd->query("SELECT id FROM users WHERE email='".sanitize_vars($_POST['username'])."' AND password='".sanitize_vars($_POST['password'])."'");
	if($back->rowCount() == 1){
		$back = $bdd->query("SELECT * FROM users WHERE email='".sanitize_vars($_POST['username'])."' AND password='".sanitize_vars($_POST['password'])."' AND active='on' AND trash='1'");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$_SESSION['id'] = $row['id'];
			$_SESSION['fullname'] = $row['fullname'];
			$_SESSION['picture'] = $row['picture'];
			$_SESSION['phone'] = $row['phone'];
			$_SESSION['email'] = $row['email'];
			$_SESSION['roles'] = $row['roles'];
			$_SESSION['type'] = $row['type'];
			$_SESSION['upuser'] = $row['upuser'];
			
			session_regenerate_id(true);
			
			if($_SESSION['type'] != "moderator"){
				header('location: commands.php');
			}
			else{
				header('location: index.php');
			}
		}
		else{
			$error = 'Votre compte n\'est pas active';
		}
	}
	else{
		$error = 'E-mail ou mot de passe est incorrect';
	}
}

function sanitize_vars($var){
	if(preg_match("#script|select|update|delete|concat|create|table|union|length|show_table|mysql_list_tables|mysql_list_fields|mysql_list_dbs#i",$var)){
		$var = "";
	}
	return htmlspecialchars(addslashes(trim($var)));
}

if(file_exists("configdb.data")){
	unlink("configdb.data");
}
if(file_exists("installer.php")){
	unlink("installer.php");
}

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
		<link href="https://use.fontawesome.com/releases/v5.0.7/css/all.css" rel="stylesheet">
		<!-- Fav Icon -->
		<link rel="shortcut icon" href="../favicon.ico">
		<?php include("onesignal.php");?>
	</head>
	<body>

		<!-- Wrapper -->
		<div class="lx-wrapper">
			<!-- Main -->
			<div class="lx-main">
				<div class="lx-left-bg">
					<div class="lx-login">
						<div class="lx-login-content">
							<?php
							if($settings['logo'] != ""){
								?>
							<img src="uploads/<?php echo $settings['logo'];?>" />
								<?php
							}
							else{
								?>
							<h1><?php echo $settings['appname'];?></h1>
								<?php								
							}
							?>
							<h2>Se connecter à votre compte</h2>
							<form action="login.php" method="post">
								<?php
								if(isset($error)){
									?>
								<p class="lx-login-error"><?php echo $error;?></p>
									<?php
								}
								?>
								<div class="lx-textfield">
									<label><input type="text" name="username" placeholder="Adresse E-mail" /></label>
								</div>
								<div class="lx-textfield">
									<label><input type="password" name="password" placeholder="Mot de passe" /><i class="fa fa-eye-slash"></i></label>
								</div>
								<div class="lx-textfield">
									<label style="float:left;"><input type="checkbox" name="rememberme" value="yes" <?php echo isset($_COOKIE['rememberme'])?"checked":"";?> /> Se souvenir de moi<del class="checkmark"></del></label>
									<a href="password.php" class="lx-password-forgotten">Mot de passe oublier?</a>
									<div class="lx-clear-fix"></div>
								</div>
								<div class="lx-submit">
									<a href="javascript:;">Se connecter</a>
								</div>
							</form>
						</div>
					</div>				
				</div>
				<div class="lx-right-bg">
					<div class="lx-right-bg-shadow">

					</div>
				</div>
			</div>
		</div>
		<?php ?>
		<!-- JQuery -->
		<script src="js/jquery-1.12.4.min.js"></script>
		<!-- Main Script -->
		<script src="js/script.js"></script>
	</body>
</html>