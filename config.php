<?php
// Charger les variables d'environnement depuis .env si le fichier existe
$envFile = __DIR__ . '/.env';
$environment = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $environment[trim($name)] = trim($value);
        $_ENV[trim($name)] = trim($value);
    }
}

try{
	$dbHost = $environment['DB_HOST'] ?? $_ENV['DB_HOST'] ?? 'localhost';
	$dbName = $environment['DB_NAME'] ?? $_ENV['DB_NAME'] ?? 'exliv_delivery';
	$dbUser = $environment['DB_USER'] ?? $_ENV['DB_USER'] ?? 'exliv_delivery';
	$dbPass = $environment['DB_PASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? '';
	
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
	// The initial setup account is stored with the special `all` role. Convert
	// it to the named permissions used by the existing page guards and menu.
	// Without this, the first administrator can only see the dashboard.
	$allModeratorRoles = 'Modérateurs,Agents de confirmation,Annonces,Villes,Livreurs,Frais de livraison,Ramassage Agences,Clients,Envois,Stocks,Etats,Emballages,Confirmation,Google Sheets,Ramassage,Commandes,Modification état commandes,BLS,Factures clients,Factures livreurs,Dépences,Réclamations';
	$_SESSION['roles'] = $row['roles'] === 'all' ? $allModeratorRoles : $row['roles'];
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
	$parametres = $back->fetch() ?: ['nbrows' => ''];
}

$back = $bdd->query("SELECT * FROM settings");
$settings = $back->fetch();
if($settings['cmdprefix'] == ""){
	$settings['cmdprefix'] = "CMD";
}
?>
