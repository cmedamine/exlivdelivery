<?php
// Charger les variables d'environnement depuis .env si le fichier existe
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

try{
	$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
	$dbName = $_ENV['DB_NAME'] ?? 'exliv_delivery';
	$dbUser = $_ENV['DB_USER'] ?? 'exliv_delivery';
	$dbPass = $_ENV['DB_PASSWORD'] ?? '';
	
	$bdd = new PDO('mysql:host='.$dbHost.';dbname='.$dbName.';charset=utf8', $dbUser, $dbPass, array(PDO::ATTR_PERSISTENT => true));
	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(Exception $e){
	die('Erreur : '.$e->getMessage());
}

// Inclure les fonctions utilitaires
include_once __DIR__ . '/functions.php';

if(isset($_COOKIE['id'])){
	setcookie('id', $_SESSION['id'], time() - 3600);
	setcookie('fullname', $_SESSION['fullname'], time() - 3600);
	setcookie('picture', $_SESSION['picture'], time() - 3600);
	setcookie('phone', $_SESSION['phone'], time() - 3600);
	setcookie('email', $_SESSION['email'], time() - 3600);
	setcookie('roles', $_SESSION['roles'], time() - 3600);
	setcookie('type', $_SESSION['type'], time() - 3600);	
}

$websiteurl = 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){ 
	$websiteurl = 'https://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
}

$userwhere = "";
$userid = 0;
if(isset($_SESSION['type'])){
	$back = $bdd->query("SELECT * FROM users WHERE id='".$_SESSION['id']."'");
	$row = $back->fetch();	

	$_SESSION['id'] = $row['id'];
	$_SESSION['fullname'] = $row['fullname'];
	$_SESSION['picture'] = $row['picture'];
	$_SESSION['phone'] = $row['phone'];
	$_SESSION['email'] = $row['email'];
	$_SESSION['roles'] = $row['roles'];
	$_SESSION['type'] = $row['type'];	
	
	if($_SESSION['type'] == "client"){
		$userwhere = " AND client='".$_SESSION['id']."'";
		$userid = $_SESSION['id'];
	}
	if($_SESSION['type'] == "dlm"){
		$userwhere = " AND dlm='".$_SESSION['id']."'";
		$userid = $_SESSION['id'];
	}
	if($_SESSION['type'] == "subdlm"){
		$userwhere = " AND subdlm='".$_SESSION['id']."'";
		$userid = $_SESSION['id'];
	}
	if($_SESSION['type'] == "worker"){
		$userwhere = " AND worker='".$_SESSION['id']."'";
		$userid = $_SESSION['id'];
	}
	$back = $bdd->query("SELECT * FROM parametres WHERE user='".$_SESSION['id']."'");
	$parametres = $back->fetch();	
}

$back = $bdd->query("SELECT * FROM settings");
$settings = $back->fetch();
if($settings['cmdprefix'] == ""){
	$settings['cmdprefix'] = "CMD";
}
?>