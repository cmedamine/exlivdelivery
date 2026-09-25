<?php
header("Content-Type: application/vnd.ms-excel; charset=utf-8"); 
header('Content-Disposition: attachment;filename="Commandes-'.date('d/m/Y').'.xls"'); 
include('config.php');

$columns = explode(",",$_GET['columns']);
$titles = explode(",",$_GET['titles']);
$order = explode(",",$_GET['order']);
for($i=0;$i<count($order);$i++){
	for($j=0;$j<count($order);$j++){
		if($order[$i] < $order[$j]){
			$k = $order[$i];
			$order[$i] = $order[$j];
			$order[$j] = $k;
			// ------------
			$k = $columns[$i];
			$columns[$i] = $columns[$j];
			$columns[$j] = $k;
			// ------------
			$k = $titles[$i];
			$titles[$i] = $titles[$j];
			$titles[$j] = $k;
		}
	}
}

?>
<style>
	.text{
	  mso-number-format:"\@";/*force text*/
	}
</style>
<table cellpadding="0" cellspacing="0" border="1">
	<tr align="center" valign="top">
		<?php
		for($i=0;$i<count($titles);$i++){
			?>
		<td style="width:160px;font-weight:bold;background:#39add1;"><?php echo $titles[$i]?></td>
			<?php
		}
		?>
	</tr>
	<?php
	$tid = 0;
	if($_GET['tid'] != ""){
		$tid = $_GET['tid'];
	}
	$req = "SELECT * FROM commands WHERE id IN(".$tid.")";
	$back = $bdd->query($req);
	while($row = $back->fetch()){
		?>
	<tr align="center" valign="top">
		<?php
		for($i=0;$i<count($columns);$i++){
			if($columns[$i] == "fullname"){
				$goods = "";
				if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])){
					$good = "";
					$goods = " - ";
					$products = explode(",",$row['product']);
					$qtys = explode(",",$row['qty']);
					for($j=0;$j<count($products);$j++){
						$back1 = $bdd->query("SELECT * FROM stocks WHERE id='".$products[$j]."'");
						$row1 = $back1->fetch();
						$back1 = $bdd->query("SELECT * FROM products WHERE id='".$row1['product']."'");
						$row1 = $back1->fetch();
						if($qtys[$j] > 1){
							$good .= " + " . $qtys[$j] . " x " . $row1['ref'];
						}
						else{
							$good .= " + " . $row1['ref'];
						}
					}
					$goods .= substr($good,3);
				}
				?>
		<td class="text"><?php echo $row[$columns[$i]].$goods;?></td>
				<?php
				
			}
			elseif($columns[$i] == "product"){
				?>
		<td class="text">
				<?php
				if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])){
					$j = 0;
					$qtys = explode(",",$row['qty']);
					$back1 = $bdd->query("SELECT * FROM stocks WHERE id IN(".$row['product'].") ORDER BY FIELD(id,".$row['product'].")");
					while($row1 = $back1->fetch()){
						$back2 = $bdd->query("SELECT * FROM products WHERE id='".$row1['product']."'");
						$row2 = $back2->fetch();
						echo $row2['title']." x ".$qtys[$j];
						$j++;
					}
				}
				else{
					$products = explode(",",$row['product']);
					$qtys = explode(",",$row['qty']);
					for($j=0;$j<count($products);$j++){
						echo $products[$j]." x ".$qtys[$j];
					}							
				}	
				?>
		</td>
				<?php
			}
			else{
				?>
		<td class="text"><?php echo $row[$columns[$i]];?></td>
				<?php
			}
		}
		?>	
	</tr>				
		<?php
	}
	?>
</table>