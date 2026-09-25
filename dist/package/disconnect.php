<?php
session_start();
session_destroy();
if(isset($_COOKIE['id'])){
	setcookie('id', $_SESSION['id'], time() - 3600);
	setcookie('fullname', $_SESSION['fullname'], time() - 3600);
	setcookie('picture', $_SESSION['picture'], time() - 3600);
	setcookie('phone', $_SESSION['phone'], time() - 3600);
	setcookie('email', $_SESSION['email'], time() - 3600);
	setcookie('roles', $_SESSION['roles'], time() - 3600);
	setcookie('type', $_SESSION['type'], time() - 3600);	
	setcookie('upuser', $_SESSION['upuser'], time() - 3600);	
}
header('Location: login.php');
?>