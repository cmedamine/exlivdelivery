<?php
include("config.php");

if(isset($_GET['fullname'])){
	$storeid = 0;
	$back = $bdd->query("SELECT id FROM stores WHERE name LIKE '%".sanitize_vars($_GET['store'])."%'");
	if($back->rowCount()){
		$row = $back->fetch();
		$storeid = $row['id'];
	}
	$back = $bdd->query("SELECT id FROM commands WHERE trash='1'");
	$rand = 'CMD-'.date('dmY').'-'.sprintf("%05d",($back->rowCount()+1));
	$req = $bdd->prepare("INSERT INTO commands(id,code,product,qty,dlm,subdlm,worker,store,source,fullname,phone,address,city,price,fees,phase,state,datereported,note,workers,invoiced,dateadd,dateupdate,trash) 
	VALUES ('0','".$rand."','".sanitize_vars($_GET['product'])."','".sanitize_vars($_GET['qty'])."','0','','0','".$storeid."','Site','".sanitize_vars($_GET['fullname'])."','".sanitize_vars($_GET['phone'])."','".sanitize_vars($_GET['address'])."','".sanitize_vars($_GET['city'])."','".sanitize_vars($_GET['price'])."','0','confirmation','Nouveau','','','0','off','".time()."','".time()."','1')");
	$req->execute();
	$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,dateadd) VALUES ('0','".$rand."','Nouveau','".time()."')");
	$req->execute();	
}

function sanitize_vars($var){
	return htmlspecialchars(addslashes(trim($var)));
}
?>