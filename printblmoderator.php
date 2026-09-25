<?php
session_start();
include("config.php");
require_once __DIR__ . '/vendor/autoload.php';

ob_start();
$ids = explode(",",$_GET['tid']);
$back = $bdd->query("SELECT code,dateadd FROM bls WHERE dlm='".$_GET['dlm']."' AND cmds='".$_GET['tid']."'");
if($back->rowCount() == 0){
	$date = date('d/m/Y');
	$bl = 'BL-'.date('dmY').'-'.date('His');
	$req = $bdd->prepare("INSERT INTO bls(id,dlm,client,code,cmds,done,dateadd,trash) VALUES('0','".$_GET['dlm']."','0','".$bl."','".$_GET['tid']."','off','".time()."','1')");
	$req->execute();
	$req = $bdd->prepare("UPDATE commands SET dlm='".$_GET['dlm']."' WHERE id IN(".$_GET['tid'].")");
	$req->execute();
}
else{
	$row = $back->fetch();
	$bl = $row['code'];
	$date = date('d/m/Y',$row['dateadd']);
}
$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a créer le bon de livraison N° ".$bl."','".time()."')");
$req->execute();
$back1 = $bdd->query("SELECT fullname,stockout,emailstockout FROM users WHERE id='".$_GET['dlm']."' AND type='dlm'");
$user = $back1->fetch();
?>
<!DOCTYPE html>
<html lang="zxx">
	<head>
		<meta charset="utf-8">
		<style>
			@page {
				margin:25px;
			}
		</style>
	</head>
	<body>
		<div style="padding:20px;font-family:'Arial';border:2px solid #000000;">
			<table style="width:100%;font-family:'Arial';">
				<tr>
					<td style="width:50%;">
						<?php
						$logo = "images/logo.png";
						if($settings['logo'] != ""){
							$logo = "uploads/".$settings['logo'];
						}
						?>
						<img src="<?php echo $logo;?>" width="200">
					</td>
					<td style="width:50%;font-weight:bold;">
						<p>Livreur: <?php echo $user['fullname'];?></p>
						<p>Nb. commandes: <?php echo (count($ids) - 1);?></p>
						<p>Date: <?php echo $date;?></p>
					</td>
				</tr>
			</table>
			<h1 style="margin:40px 0px 20px 0px;font-size:24px;text-align:center;"><?php echo $bl;?></h1>
			<table style="width:100%;font-size:12px;font-family:'Arial';text-align:center;border:1px solid #000000;">
				<tr>
					<th style="width:35px;padding:4px;border:1px solid #000000;">N&ordm;</th>
					<th style="width:180px;padding:4px;border:1px solid #000000;">Code d'envoi</th>
					<th style="padding:4px;border:1px solid #000000;">Expéditeur</th>
					<th style="padding:4px;border:1px solid #000000;">Client</th>
					<th style="padding:4px;border:1px solid #000000;">Produit</th>
					<th style="padding:4px;border:1px solid #000000;">Adresse</th>
					<th style="padding:4px;border:1px solid #000000;">Ville</th>
					<th style="width:60px;padding:4px;border:1px solid #000000;">CRBT</th>
					<th style="width:60px;padding:4px;border:1px solid #000000;">Etat</th>
				</tr>
				<?php
				$j = 1;
				$back = $bdd->query("SELECT client,code,fullname,phone,address,city,price,product,qty,note FROM commands WHERE id IN(".$_GET['tid'].")");
				while($fd = $back->fetch()){
					?>
				<tr>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $j;?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['code'];?></td>
					<?php
					$back1 = $bdd->query("SELECT store,phone FROM users WHERE id='".$fd['client']."'");
					$row1 = $back1->fetch();
					?>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $row1['store']."<br />".$row1['phone'];?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['fullname']."<br />".$fd['phone'];?></td>
					<td style="padding:4px;border:1px solid #000000;">
						<?php
						$product = "";
						if(preg_match("#^[0-9]+(,[0-9]+)*$#",$fd['product'])){
							$i = 0;
							$qtys = explode(",",$fd['qty']);
							$back1 = $bdd->query("SELECT title FROM stocks WHERE id IN(".$fd['product'].") ORDER BY FIELD(id,".$fd['product'].")");
							while($row1 = $back1->fetch()){
								echo $row1['title']." x ".$qtys[$i];
								$product .= " - " . $row1['title'] . " x " . $qtys[$i];
								$i++;
							}
							if($fd['product'] != "0"){
								echo "<br />Depuis Stock";
							}
						}
						else{
							echo $fd['product'];
							$product .= $fd['product'];
						}
						?>
					</td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['address'];?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['city'];?></td>
					<td style="padding:4px;border:1px solid #000000;"><?php echo $fd['price']." ".$settings['currency'];?></td>				
					<td style="padding:4px;border:1px solid #000000;"></td>				
				</tr>
					<?php
					if(preg_match("#azedcolis#",$user['stockout'])){
						sendToAZ($user['stockout'],$user['emailstockout'],$fd['code'],$product,"1",$fd['fullname'],$fd['phone'],$fd['address'],$fd['city'],$fd['price'],$fd['note']);
					}
					elseif(preg_match("#ameex#",$user['stockout'])){
						sentAmeexOrder($user['stockout'],$user['emailstockout'],$fd['code'],$fd['fullname'],$fd['phone'],$fd['city'],$fd['address'],$product,$fd['note'],$fd['price'],$bdd);
					}					
					else{
						sendToStockOUTDLM($user['stockout'],$user['emailstockout'],$fd['code'],$product,"1",$fd['fullname'],$fd['phone'],$fd['address'],$fd['city'],$fd['price'],$fd['note']);
					}
					/*if($settings['onesignal'] != ""){
						$back1 = $bdd->query("SELECT idplayer FROM users WHERE id='".$_GET['dlm']."' AND idplayer<>''");
						if($back1->rowCount() > 0){
							$dlm = $back1->fetch();
							sendMessage($dlm['idplayer'],$fd['code'],"Nouveau commande à traiter","https://servicehl.ma/is-admin",$settings['onesignal']);
						}
					}*/
					$j += 1;
				}
				?>
			</table>
		</div>
	</body>
</html>
<?php
$content = ob_get_clean();
$mpdf = new \mPDF();
$mpdf->autoScriptToLang = true;
$mpdf->autoLangToFont = true;
$mpdf->WriteHTML($content);
$mpdf->setFooter("TRANSPORT DES MARCHANDISES POUR LE COMPTE D'AUTRUL, PATENTE N°:49603489");
$mpdf->Output($bl.'.pdf','D');

function sendToStockOUTDLM($url,$email,$code,$product,$qty,$fullname,$phone,$address,$city,$price,$note){
	$url = $url."/rcorderstockout.php?email=".urlencode($email)."&code=".urlencode($code)."&product=".urlencode($product)."&qty=".$qty."&fullname=".urlencode($fullname)."&phone=".urlencode($phone)."&city=".urlencode($city)."&address=".urlencode($address)."&price=".$price."&note=".urlencode($note);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$result = curl_exec($ch);
	curl_close($ch);
}

function sentAmeexOrder($id,$key,$code,$fullname,$phone,$city,$address,$product,$note,$price,$bdd){
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://cdn.ameex.ma/app/api/customer/Parcels/AddParcel",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "
		{
			\"ORDER_NUM\" : \"".$code."\",
			\"RECEIVER\" : \"".$fullname."\",
			\"PHONE\" : \"".$phone."\",
			\"CITY\" : \"".getAmeexCityID($city)."\",
			\"ADDRESS\" : \"".$address."\",
			\"COD\" : \"".$price."\",
			\"COMMENT\" : \"".$note."\",
			\"NATURE_PRODUCT\" : \"".$product."\"
		}
		",
		CURLOPT_HTTPHEADER => array(
			"X-AUTH-ID: 3452",
			"X-AUTH-KEY: 9435a2-921aa4-67fc55-ced90c-1bafbc",
			"content-type: application/json"
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);
	$data = json_decode($response,true);
	$req = $bdd->prepare("UPDATE commands SET code='".$data['ADD-PARCEL']['NEW-PARCEL']['TRACKING-NUMBER']."' WHERE code='".$code."'");
	$req->execute();
	// echo $response; // debug
}

function getAmeexCityID($city){
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://cdn.ameex.ma/app/api/customer/Cities",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_HTTPHEADER => array(
			"X-AUTH-ID: 3452",
			"X-AUTH-KEY: 9435a2-921aa4-67fc55-ced90c-1bafbc",
			"content-type: application/json"
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);
	$data = json_decode($response,true);
	$id = "0";
	foreach($data['Cities'] AS $value){
		if(preg_match("#^(".$city.")$#i",$value['NAME'])){
			$id = $value['NAME'];
		}
	}
	return $id;
}

/*function sendToAZ($url,$email,$code,$product,$qty,$fullname,$phone,$address,$city,$price,$note){
	$url = "https://azedcolis.com/restApi?do=create";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
	   "Content-Type: application/json",
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$data = <<<DATA
	{
	 "code":"$code",
	 "email":"$email",
	 "product":"$product",
	 "name":"$fullname",
	 "price":"$price",
	 "phone":"$phone",
	 "shipped":"$address",
	 "city":"$city" 
	}	
	DATA;

	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

	//for debug only!
	curl_setopt($curl, CULOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	$resp = curl_exec($curl);
	curl_close($curl);
	// debug: var_dump($resp);
}*/

/*function sendMessage($players,$header,$content,$url,$app_id){
	$players = explode(",",$players);
	$heading = array(
		"en" => $header
	);	
	$content = array(
		"en" => $content
	);
	$fields = array(
		'app_id' => $app_id,
		'include_player_ids' => $players,
		'data' => array("foo" => "bar"),
		'headings' => $heading,
		'contents' => $content,
		'url' => $url
	);
	
	$fields = json_encode($fields);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$response = curl_exec($ch);
	print_r($response);
	curl_close($ch);
	//return $response;
}*/