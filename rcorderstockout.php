<?php
include("config.php");

if(isset($_GET['fullname'])){
	$back = $bdd->query("SELECT id FROM commands WHERE code='".$_GET['code']."'");
	if($back->rowCount() == 0){
		$back = $bdd->query("SELECT id,fullname FROM users WHERE email='".$_GET['email']."'");
		$client = $back->fetch();
		$note = "";
		if(isset($_GET['note'])){
			$note = $_GET['note'];
		}
		$req = $bdd->prepare("INSERT INTO commands(id,code,product,qty,dlm,client,fullname,phone,address,city,price,state,datereported,note,invoiced,collected,dateadd,dateupdate,trash) 
		VALUES ('0','".sanitize_vars($_GET['code'])."','".$_GET['product']."','".sanitize_vars($_GET['qty'])."','0','".$client['id']."','".sanitize_vars($_GET['fullname'])."','".sanitize_vars($_GET['phone'])."','".sanitize_vars($_GET['address'])."','".sanitize_vars($_GET['city'])."','".sanitize_vars($_GET['price'])."','Ajouté','','".sanitize_vars($note)."','off','off','".time()."','".time()."','1')");
		$req->execute();
		$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".sanitize_vars($_GET['code'])."','Ajouté','".$client['fullname']."','".time()."')");
		$req->execute();
	}	
}

function sanitize_vars($var){
	return htmlspecialchars(addslashes(trim($var)));
}
?>