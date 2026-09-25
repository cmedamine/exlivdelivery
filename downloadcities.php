<?php
session_start();
header("Content-Type: application/vnd.ms-excel; charset=utf-8"); 
header('Content-Disposition: attachment;filename="Villes.xls"'); 
include('config.php')
?>
<style>
	.text{
	  mso-number-format:"\@";/*force text*/
	}
</style>
<table cellpadding="0" cellspacing="0" border="1">
	<tr align="center" valign="top">
		<td style="width:160px;font-weight:bold;background:#39add1;">Villes</td>	
		<td style="width:200px;font-weight:bold;background:#39add1;">Frais livre</td>
		<td style="width:100px;font-weight:bold;background:#39add1;">Frais retour</td>
	</tr>
	<?php
	$back = $bdd->query("SELECT DISTINCT city,deliveredfees,refusedfees FROM shippingfees WHERE trash='1'");
	while($row = $back->fetch()){
		?>
	<tr align="center" valign="top">
		<td><?php echo $row['city'];?></td>
		<td><?php echo $row['deliveredfees'];?></td>
		<td><?php echo $row['refusedfees'];?></td>
	</tr>				
		<?php
	}
	?>
</table>