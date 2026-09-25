<?php
session_start();
header("Content-Type: application/vnd.ms-excel; charset=utf-8"); 
header('Content-Disposition: attachment;filename="Commandes-'.date('d/m/Y').'.xls"'); 
include('config.php')
?>
<html>
	<head>
		<meta charset='UTF-8'>
		<style>
			.text{
			  mso-number-format:"\@";/*force text*/
			}
		</style>
	</head>
	<body>
<table cellpadding="0" cellspacing="0" border="1">
	<tr align="center" valign="top">
		<td style="width:160px;font-weight:bold;background:#39add1;">Code</td>
		<?php
		if($_SESSION['type'] == "moderator"){
			?>
		<td style="width:160px;font-weight:bold;background:#39add1;">Client</td>
			<?php
		}
		?>		
		<td style="width:200px;font-weight:bold;background:#39add1;">Destinataire</td>
		<td style="width:100px;font-weight:bold;background:#39add1;">Telephone</td>
		<td style="width:120px;font-weight:bold;background:#39add1;">Ville</td>
		<td style="width:300px;font-weight:bold;background:#39add1;">Adresse</td>
		<td style="width:200px;font-weight:bold;background:#39add1;">Produit</td>
		<td style="width:60px;font-weight:bold;background:#39add1;">Prix</td>
		<td style="width:100px;font-weight:bold;background:#39add1;">Etat</td>
	</tr>
	<?php
	$tid = 0;
	if($_GET['tid'] != ""){
		$tid = $_GET['tid'];
	}
	$req = "SELECT code,client,product,qty,fullname,phone,city,address,price,state FROM commands WHERE id IN(".$tid.")";
	$back = $bdd->query($req);
	while($row = $back->fetch()){
		?>
	<tr align="center" valign="top">
		<td><?php echo $row['code'];?></td>
		<?php
		if($_SESSION['type'] == "moderator"){
			$back1 = $bdd->query("SELECT fullname,store FROM users WHERE id='".$row['client']."'");
			$row1 = $back1->fetch();
			?>
		<td><?php echo $row1['store']." (".$row1['fullname'].")";?></td>
			<?php
		}
		?>
		<td><?php echo $row['fullname'];?></td>
		<td class="text"><?php echo $row['phone'];?></td>
		<td><?php echo $row['city'];?></td>		
		<td><?php echo $row['address'];?></td>		
		<td>
			<?php
			if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])){
				$i = 0;
				$qtys = explode(",",$row['qty']);
				$back1 = $bdd->query("SELECT title FROM stocks WHERE id IN(".$row['product'].") ORDER BY FIELD(id,".$row['product'].")");
				while($row1 = $back1->fetch()){
					echo $row1['title']." x ".$qtys[$i];
					$i++;
				}
				if($row['product'] != "0"){
					echo "Depuis Stock";
				}
			}
			else{
				echo $row['product'];
			}
			?>
		</td>
		<td><?php echo $row['price'];?></td>
		<td><?php echo $row['state'];?></td>		
	</tr>				
		<?php
	}
	?>
</table>
	</body>
</html>