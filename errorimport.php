<?php
session_start();
header("Content-Type: application/vnd.ms-excel"); 
header('Content-Disposition: attachment;filename="CommandError-'.date('dmYHis').'.xls"'); 
?>
		<table cellpadding="0" cellspacing="0" border="1">
			<tr align="center" valign="top">
				<td style="font-weight:bold;background:#EC7C27;">Destinataire</td>
				<td style="font-weight:bold;background:#EC7C27;">Telephone</td>
				<td style="font-weight:bold;background:#EC7C27;">Ville</td>
				<td style="font-weight:bold;background:#EC7C27;">Adresse</td>
				<td style="font-weight:bold;background:#EC7C27;">Produit Ref</td>
				<td style="font-weight:bold;background:#EC7C27;">Qte</td>
				<td style="font-weight:bold;background:#EC7C27;">Prix</td>
				<td style="font-weight:bold;background:#EC7C27;">Erreur</td>
			</tr>
			<?php
			echo $_SESSION['errorimport'];
			?>
		</table>