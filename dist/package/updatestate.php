<?php
include("config.php");

if(isset($_GET['code'])){
	$back = $bdd->query("SELECT * FROM commands WHERE code='".$_GET['code']."'");
	$command = $back->fetch();
	$back = $bdd->query("SELECT fullname FROM users WHERE id='".$command['dlm']."'");
	$user = $back->fetch();		
	$req = $bdd->prepare("UPDATE commands SET state='".sanitize_vars($_GET['state'])."',datereported='".sanitize_vars(strtotime(str_replace("/","-",$_GET['datereported'])))."',note=CONCAT(note,'".$_GET['note']."'),dateupdate='".time()."' WHERE id='".$command['id']."'");
	$req->execute();
	$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".sanitize_vars($_GET['state'])."','".$user['fullname']."','".time()."')");
	$req->execute();
	if($_GET['state'] == "Livré" AND $command['state'] != "Livré"){
		// Factures Clients
		$back = $bdd->query("SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0' AND validated='off' AND trash='1'");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$back = $bdd->query("SELECT deliveredfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
			if($back->rowCount() == 0){
				$back = $bdd->query("SELECT deliveredfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
			}
			$city = $back->fetch();	
			$back = $bdd->query("SELECT price FROM packaging WHERE id='".$command['package']."' AND trash='1'");
			$package = $back->fetch();
			$price = $command['price'] - $city['deliveredfees'] - intval($package['price']);
			$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$command['id']."')");
			$req->execute();
			$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands+1),price=(price+".$price.") WHERE code='".$row['code']."'");
			$req->execute();
		}
		else{
			$back = $bdd->query("SELECT deliveredfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
			if($back->rowCount() == 0){
				$back = $bdd->query("SELECT deliveredfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
			}
			$city = $back->fetch();
			$back = $bdd->query("SELECT price FROM packaging WHERE id='".$command['package']."' AND trash='1'");
			$package = $back->fetch();
			$price = $command['price'] - $city['deliveredfees'] - intval($package['price']);						
			$rand = '';
			do{
				$rand = 'FCT-'.gmdate('dmY').'-'.random();
				$back2 = $bdd->query("SELECT id FROM factures WHERE code='".$rand."'");
			}
			while($back2->rowCount() != 0);
			$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','0','".$command['client']."','1','".$price."','','','off','off','".time()."','','1')");
			$req->execute();
			$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$command['id']."')");
			$req->execute();
		}
		
		// Factures DML
		$back = $bdd->query("SELECT code FROM factures WHERE dlm='".$command['dlm']."' AND client='0' AND validated='off' AND trash='1'");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$back = $bdd->query("SELECT deliveredfees FROM shippingfees WHERE city='".$command['city']."' AND dlm='".$command['dlm']."' AND trash='1'");
			$city = $back->fetch();	
			$price = $command['price'] - $command['extrafees'] - $city['deliveredfees'];
			$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$command['id']."')");
			$req->execute();
			$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands+1),price=(price+".$price.") WHERE code='".$row['code']."'");
			$req->execute();
		}
		else{
			$back = $bdd->query("SELECT deliveredfees FROM shippingfees WHERE city='".$command['city']."' AND dlm='".$command['dlm']."' AND trash='1'");
			$city = $back->fetch();
			$price = $command['price'] - $command['extrafees'] - $city['deliveredfees'];						
			$rand = '';
			do{
				$rand = 'FCT-'.gmdate('dmY').'-'.random();
				$back2 = $bdd->query("SELECT id FROM factures WHERE code='".$rand."'");
			}
			while($back2->rowCount() != 0);
			$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','".$command['dlm']."','0','1','".$price."','','','off','off','".time()."','','1')");
			$req->execute();
			$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$command['id']."')");
			$req->execute();
		}
	}
	elseif($_GET['state'] != "Livré" AND $command['state'] == "Livré"){
		// Factures Clients
		$back = $bdd->query("SELECT deliveredfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
		if($back->rowCount() == 0){
			$back = $bdd->query("SELECT deliveredfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
		}
		$city = $back->fetch();
		$back = $bdd->query("SELECT price FROM packaging WHERE id='".$command['package']."' AND trash='1'");
		$package = $back->fetch();
		$price = $command['price'] - $city['deliveredfees'] - intval($package['price']);						
		$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$command['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
		$row = $back->fetch();
		$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$command['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
		$req->execute();
		$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands-1),price=(price-".$price.") WHERE code='".$row['facture']."'");
		$req->execute();
		
		// Factures DLM
		$back = $bdd->query("SELECT deliveredfees FROM shippingfees WHERE city='".$command['city']."' AND dlm='".$command['dlm']."' AND trash='1'");
		$city = $back->fetch();
		$price = $command['price'] - $city['deliveredfees'];					
		$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$command['id']."' AND facture IN(SELECT code FROM factures WHERE client='0' AND dlm='".$command['dlm']."')");
		$row = $back->fetch();
		$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$command['id']."' AND facture IN(SELECT code FROM factures WHERE client='0' AND dlm='".$command['dlm']."')");
		$req->execute();
		$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands-1),price=(price-".$price.") WHERE code='".$row['facture']."'");
		$req->execute();
	}
	if($_GET['state'] == "Refusé" AND $command['state'] != "Refusé"){
		// Factures DLM
		$back = $bdd->query("SELECT code FROM factures WHERE client='0' AND dlm='".$command['dlm']."' AND validated='off' AND trash='1'");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$back = $bdd->query("SELECT refusedfees FROM shippingfees WHERE city='".$command['city']."' AND dlm='".$command['dlm']."' AND trash='1'");
			$city = $back->fetch();
			if($city['refusedfees'] != 0){
				$price = 0 - $command['extrafees'] - $city['refusedfees'];
				$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$command['id']."')");
				$req->execute();
				$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands+1),price=(price+".$price.") WHERE code='".$row['code']."'");
				$req->execute();
			}
		}
		else{
			$back = $bdd->query("SELECT refusedfees FROM shippingfees WHERE city='".$command['city']."' AND dlm='".$command['dlm']."' AND trash='1'");
			$city = $back->fetch();
			if($city['refusedfees'] != 0){
				$price = 0 - $command['extrafees'] - $city['refusedfees'];
				$rand = '';
				do{
					$rand = 'FCT-'.gmdate('dmY').'-'.random();
					$back2 = $bdd->query("SELECT id FROM factures WHERE code='".$rand."'");
				}
				while($back2->rowCount() != 0);
				$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','".$command['dlm']."','0','1','".$price."','','','off','off','".time()."','','1')");
				$req->execute();
				$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$command['id']."')");
				$req->execute();
			}
		}
	}
	elseif($_GET['state'] != "Refusé" AND $command['state'] == "Refusé"){
		// Factures DLM
		$back = $bdd->query("SELECT refusedfees FROM shippingfees WHERE city='".$command['city']."' AND dlm='".$command['dlm']."' AND trash='1'");
		$city = $back->fetch();
		if($city['refusedfees'] != 0){
			$price = 0 - $command['extrafees'] - $city['refusedfees'];
			$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$command['id']."' AND facture IN(SELECT code FROM factures WHERE client='0' AND dlm='".$command['dlm']."')");
			$row = $back->fetch();
			$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$command['id']."' AND facture IN(SELECT code FROM factures WHERE client='0' AND dlm='".$command['dlm']."')");
			$req->execute();
			$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands-1),price=(price-".$price.") WHERE code='".$row['facture']."'");
			$req->execute();
		}
	}
	if($_GET['state'] == "Refusé" AND $command['state'] != "Refusé"){
		// Factures Clients
		$back = $bdd->query("SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0' AND validated='off' AND trash='1'");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$back = $bdd->query("SELECT refusedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
			if($back->rowCount() == 0){
				$back = $bdd->query("SELECT refusedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
			}
			$city = $back->fetch();
			if($city['refusedfees'] != 0){
				$price = 0 - $city['refusedfees'];
				$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$command['id']."')");
				$req->execute();
				$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands+1),price=(price+".$price.") WHERE code='".$row['code']."'");
				$req->execute();
			}
		}
		else{
			$back = $bdd->query("SELECT refusedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
			if($back->rowCount() == 0){
				$back = $bdd->query("SELECT refusedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
			}
			$city = $back->fetch();
			if($city['refusedfees'] != 0){
				$price = 0 - $city['refusedfees'];
				$rand = '';
				do{
					$rand = 'FCT-'.gmdate('dmY').'-'.random();
					$back2 = $bdd->query("SELECT id FROM factures WHERE code='".$rand."'");
				}
				while($back2->rowCount() != 0);
				$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','0','".$command['client']."','1','".$price."','','','off','off','".time()."','','1')");
				$req->execute();
				$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$command['id']."')");
				$req->execute();
			}
		}
	}
	elseif($_GET['state'] != "Refusé" AND $command['state'] == "Refusé"){
		// Factures Clients
		$back = $bdd->query("SELECT refusedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
		if($back->rowCount() == 0){
			$back = $bdd->query("SELECT refusedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
		}
		$city = $back->fetch();
		if($city['refusedfees'] != 0){
			$price = 0 - $city['refusedfees'];
			$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$command['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
			$row = $back->fetch();
			$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$command['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
			$req->execute();
			$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands-1),price=(price-".$price.") WHERE code='".$row['facture']."'");
			$req->execute();
		}
	}
	if($_GET['state'] == "Retour reçu agence casablanca" AND $command['state'] != "Retour reçu agence casablanca"){
		// Factures Clients
		$back = $bdd->query("SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0' AND validated='off' AND trash='1'");
		if($back->rowCount() > 0){
			$row = $back->fetch();
			$back = $bdd->query("SELECT returnedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
			if($back->rowCount() == 0){
				$back = $bdd->query("SELECT returnedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
			}
			$city = $back->fetch();
			if($city['returnedfees'] != 0){
				$price = 0 - $city['returnedfees'];
				$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$command['id']."')");
				$req->execute();
				$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands+1),price=(price+".$price.") WHERE code='".$row['code']."'");
				$req->execute();
			}
		}
		else{
			$back = $bdd->query("SELECT returnedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
			if($back->rowCount() == 0){
				$back = $bdd->query("SELECT returnedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
			}
			$city = $back->fetch();
			if($city['returnedfees'] != 0){
				$price = 0 - $city['returnedfees'];
				$rand = '';
				do{
					$rand = 'FCT-'.gmdate('dmY').'-'.random();
					$back2 = $bdd->query("SELECT id FROM factures WHERE code='".$rand."'");
				}
				while($back2->rowCount() != 0);
				$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','0','".$command['client']."','1','".$price."','0','','off','off','".time()."','','1')");
				$req->execute();
				$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$command['id']."')");
				$req->execute();
			}
		}
	}
	elseif($_GET['state'] != "Retour reçu agence casablanca" AND $command['state'] == "Retour reçu agence casablanca"){
		// Factures Clients
		$back = $bdd->query("SELECT returnedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
		if($back->rowCount() == 0){
			$back = $bdd->query("SELECT returnedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
		}
		$city = $back->fetch();
		if($city['returnedfees'] != 0){
			$price = 0 - $city['returnedfees'];
			$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$command['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
			$row = $back->fetch();
			$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$command['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
			$req->execute();
			$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands-1),price=(price-".$price.") WHERE code='".$row['facture']."'");
			$req->execute();
		}
	}
	
	if($_GET['state'] == "Livré" AND $command['state'] != "Livré" AND $command['state'] != "Annulé" AND $command['state'] != "Refusé"){
		$back = $bdd->query("SELECT id FROM statistics WHERE dlm='".$command['dlm']."' AND city='".$command['city']."' AND product='".$command['product']."' AND client='".$command['client']."' AND (dateadd BETWEEN ".strtotime(gmdate("d-m-Y"))." AND ".(strtotime(gmdate("d-m-Y")) + (60*60*24) - 1).")");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$req = $bdd->prepare("UPDATE statistics SET delivered=(delivered+1) WHERE id='".$row['id']."'");
			$req->execute();
		}
		else{
			$req = $bdd->prepare("INSERT INTO statistics VALUES ('0','".$command['dlm']."','".$command['city']."','".$command['product']."','".$command['client']."','1','0','".(strtotime(gmdate("d-m-Y"))+1)."')");
			$req->execute();
		}					
	}
	elseif($_GET['state'] == "Livré" AND ($command['state'] == "Annulé" OR $command['state'] == "Refusé")){
		$back = $bdd->query("SELECT id FROM statistics WHERE dlm='".$command['dlm']."' AND city='".$command['city']."' AND product='".$command['product']."' AND client='".$command['client']."' AND (dateadd BETWEEN ".strtotime(gmdate("d-m-Y"))." AND ".(strtotime(gmdate("d-m-Y")) + (60*60*24) - 1).")");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$req = $bdd->prepare("UPDATE statistics SET delivered=(delivered+1),canceled=(canceled-1) WHERE id='".$row['id']."'");
			$req->execute();
		}				
	}
	elseif(($_GET['state'] == "Annulé" OR $_GET['state'] == "Refusé") AND $command['state'] != "Livré" AND $command['state'] != "Annulé" AND $command['state'] != "Refusé"){
		$back = $bdd->query("SELECT id FROM statistics WHERE dlm='".$command['dlm']."' AND city='".$command['city']."' AND product='".$command['product']."' AND client='".$command['client']."' AND (dateadd BETWEEN ".strtotime(gmdate("d-m-Y"))." AND ".(strtotime(gmdate("d-m-Y")) + (60*60*24) - 1).")");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$req = $bdd->prepare("UPDATE statistics SET canceled=(canceled+1) WHERE id='".$row['id']."'");
			$req->execute();
		}
		else{
			$req = $bdd->prepare("INSERT INTO statistics VALUES ('0','".$command['dlm']."','".$command['city']."','".$command['product']."','".$command['client']."','0','1','".(strtotime(gmdate("d-m-Y"))+1)."')");
			$req->execute();
		}						
	}
	elseif(($_GET['state'] == "Annulé" OR $_GET['state'] == "Refusé") AND $command['state'] == "Livré"){
		$back = $bdd->query("SELECT id FROM statistics WHERE dlm='".$command['dlm']."' AND city='".$command['city']."' AND product='".$command['product']."' AND client='".$command['client']."' AND (dateadd BETWEEN ".strtotime(gmdate("d-m-Y"))." AND ".(strtotime(gmdate("d-m-Y")) + (60*60*24) - 1).")");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$req = $bdd->prepare("UPDATE statistics SET delivered=(delivered-1),canceled=(canceled+1) WHERE id='".$row['id']."'");
			$req->execute();
		}						
	}
	elseif(($_GET['state'] != "Livré" AND $_GET['state'] != "Annulé" AND $_GET['state'] != "Refusé") AND $command['state'] == "Livré"){
		$back = $bdd->query("SELECT id FROM statistics WHERE dlm='".$command['dlm']."' AND city='".$command['city']."' AND product='".$command['product']."' AND client='".$command['client']."' AND (dateadd BETWEEN ".strtotime(gmdate("d-m-Y"))." AND ".(strtotime(gmdate("d-m-Y")) + (60*60*24) - 1).")");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$req = $bdd->prepare("UPDATE statistics SET delivered=(delivered-1) WHERE id='".$row['id']."'");
			$req->execute();
		}						
	}
	elseif(($_GET['state'] != "Livré" AND $_GET['state'] != "Annulé" AND $_GET['state'] != "Refusé") AND ($command['state'] == "Annulé" OR $command['state'] == "Refusé")){
		$back = $bdd->query("SELECT id FROM statistics WHERE dlm='".$command['dlm']."' AND city='".$command['city']."' AND product='".$command['product']."' AND client='".$command['client']."' AND (dateadd BETWEEN ".strtotime(gmdate("d-m-Y"))." AND ".(strtotime(gmdate("d-m-Y")) + (60*60*24) - 1).")");
		if($back->rowCount() == 1){
			$row = $back->fetch();
			$req = $bdd->prepare("UPDATE statistics SET canceled=(canceled-1) WHERE id='".$row['id']."'");
			$req->execute();
		}						
	}
	
	if($_GET['state'] != $command['state']){
		$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$user['fullname']." a changé l\'état du commande N° ".$command['code']." de [".$command['state']."] à [".$_GET['state']."]','".time()."')");
		$req->execute();
	}
}

function sanitize_vars($var){
	if(preg_match("#script|select|update|delete|concat|create|table|union|length|show_table|mysql_list_tables|mysql_list_fields|mysql_list_dbs#i",$var)){
		$var = "";
	}
	return htmlspecialchars(addslashes(trim($var)));
}

function random(){
	$alphabet = "0123456789";
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($j = 0; $j < 5; $j++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass);
}

function saveLog($action){
	$data = date("d/m/Y H:i")." - ".$action;
	$data .= "\r\n";
	file_put_contents("logs/log-".date("d-m-Y").".txt",$data,FILE_APPEND);
}
?>