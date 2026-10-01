<?php
session_start();
include("config.php");

$moderatorManagementActions = ['loadusers', 'adduser', 'deleteuser', 'restoreuser', 'deleteuserpermanently'];
$isModeratorManagementRequest = isset($_POST['action']) && (
	in_array($_POST['action'], $moderatorManagementActions, true)
	|| (in_array($_POST['action'], ['changestate', 'updatebulk'], true) && ($_POST['table'] ?? '') === 'users')
);
$sessionRoles = array_filter(array_map('trim', explode(',', (string) ($_SESSION['roles'] ?? ''))));
$canManageModerators = !empty($_SESSION['id'])
	&& ($_SESSION['type'] ?? '') === 'moderator'
	&& (in_array('all', $sessionRoles, true) || in_array('Modérateurs', $sessionRoles, true));
if ($isModeratorManagementRequest && !$canManageModerators) {
	http_response_code(403);
	header('Content-Type: text/plain; charset=utf-8');
	echo 'Accès interdit';
	exit;
}

$clientManagementActions = ['loadclients', 'addclient', 'deleteclient', 'restoreclient', 'deleteclientpermanently'];
$isClientManagementRequest = isset($_POST['action']) && (
	in_array($_POST['action'], $clientManagementActions, true)
	|| (in_array($_POST['action'], ['changestate', 'updatebulk'], true) && ($_POST['table'] ?? '') === 'clients')
);
$canManageClients = !empty($_SESSION['id'])
	&& ($_SESSION['type'] ?? '') === 'moderator'
	&& (in_array('all', $sessionRoles, true) || in_array('Clients', $sessionRoles, true));
if ($isClientManagementRequest && !$canManageClients) {
	http_response_code(403);
	header('Content-Type: text/plain; charset=utf-8');
	echo 'Accès interdit';
	exit;
}

include("vendor/autoload.php");

if(isset($_SESSION['id']) AND isset($_SESSION['fullname'])){
	if($_SESSION['id'] != "" AND $_SESSION['fullname'] != ""){
		if(isset($_POST['action'])){
			if($_POST['action'] == "addnotice"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO notices(id,description,shown,trash) VALUES ('0','".sanitize_vars($_POST['description'])."','off','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE notices SET description='".sanitize_vars($_POST['description'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}

			if($_POST['action'] == "deletenotice"){
				$req = $bdd->prepare("UPDATE notices SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restorenotice"){
				$req = $bdd->prepare("UPDATE notices SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deletenoticepermanently"){
				$req = $bdd->prepare("DELETE FROM notices WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadnotices"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-notice"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-notice"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Annonce</td>
						<td>Afficher</td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM notices WHERE trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND (title LIKE '%".$_POST['keyword']."%' OR ref LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="notice" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['description'];?></span></td>
						<td>
							<?php
							$class = "";
							if($row['shown'] == "on"){
								$class = " lx-on-off-blue";
							}
							?>
							<div class="lx-on-off<?php echo $class;?>" data-state="<?php echo $row['shown'];?>" data-table="notices" data-column="shown" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
						</td>					
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-notice lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-description="<?php echo addslashes($row['description']);?>" data-title="notice"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-notice lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-notice" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-notice" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> annonce(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> annonce(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}			
			
			if($_POST['action'] == "adduser"){
				$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."'");
				if($back->rowCount() == 0){
					if($_POST['id'] == "0"){
						$req = $bdd->prepare("INSERT INTO users(id,fullname,picture,email,password,phone,sav,city,cin,store,bank,rib,stockout,type,roles,active,datesignup,trash) VALUES ('0','".sanitize_vars($_POST['fullname'])."','avatar.png','".sanitize_vars($_POST['email'])."','".sanitize_vars($_POST['password'])."','".sanitize_vars($_POST['phone'])."','','','','','','','','moderator','".sanitize_vars(substr($_POST['roles'],1))."','on','".time()."','1')");
						$req->execute();
					}
					else{
						$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',email='".sanitize_vars($_POST['email'])."',password='".sanitize_vars($_POST['password'])."',phone='".sanitize_vars($_POST['phone'])."',roles='".sanitize_vars(substr($_POST['roles'],1))."' WHERE id='".$_POST['id']."'");
						$req->execute();
					}
				}
				else{
					$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."' AND id='".$_POST['id']."'");
					if($back->rowCount() != 0){
						$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',email='".sanitize_vars($_POST['email'])."',password='".sanitize_vars($_POST['password'])."',phone='".sanitize_vars($_POST['phone'])."',roles='".sanitize_vars(substr($_POST['roles'],1))."' WHERE id='".$_POST['id']."'");
						$req->execute();						
					}
					else{
						echo "Email exist déja !!";	
					}
				}
			}

			if($_POST['action'] == "deleteuser"){
				$req = $bdd->prepare("UPDATE users SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoreuser"){
				$req = $bdd->prepare("UPDATE users SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deleteuserpermanently"){
				$req = $bdd->prepare("DELETE FROM users WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadusers"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-user"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-user"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Nom et prénom <i class="fa fa-sort" data-sort="fullname"></i></td>
						<td>Téléphone <i class="fa fa-sort" data-sort="phone"></i></td>
						<td>E-mail <i class="fa fa-sort" data-sort="email"></i></td>
						<td>Droits <i class="fa fa-sort" data-sort="roles"></i></td>
						<td>Date inscription <i class="fa fa-sort" data-sort="datesignup"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM users WHERE type='moderator' AND trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND (fullname LIKE '%".sanitize_vars($_POST['keyword'])."%' OR phone LIKE '%".sanitize_vars($_POST['keyword'])."%' OR email LIKE '%".sanitize_vars($_POST['keyword'])."%')";
					}
					if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
						$req .= " AND (datesignup BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="user" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['fullname'];?></span></td>
						<td><span><?php echo $row['phone'];?></span></td>
						<td><span><?php echo $row['email'];?></span></td>
						<td><span><?php echo str_replace(",",", ",$row['roles']);?></span></td>
						<td><span><?php echo gmdate("d/m/Y H:i",$row['datesignup']+3600);?></span></td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-user lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-email="<?php echo $row['email'];?>"
								data-password="<?php echo $row['password'];?>"
								data-phone="<?php echo $row['phone'];?>"
								data-roles=",<?php echo $row['roles'];?>" data-title="user"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-user lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-user" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-user" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> utilisateur(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> utilisateur(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}
			
			if($_POST['action'] == "editaccount"){
				$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',picture='".sanitize_vars($_POST['picture'])."',phone='".sanitize_vars($_POST['phone'])."' WHERE id='".$_SESSION['id']."'");
				$req->execute();
			}	

			if($_POST['action'] == "editpassword"){
				if($_POST['oldpassword'] == "" OR $_POST['newpassword1'] == "" OR $_POST['newpassword2'] == ""){
					echo '2';
				}
				else{
					$back = $bdd->query("SELECT id FROM users WHERE id='".$_SESSION['id']."' AND password='".$_POST['oldpassword']."'");
					if($back->rowCount() == 0){
						echo '3';
					}
					elseif($_POST['newpassword1'] != $_POST['newpassword2']){
						echo '4';
					}
					else{
						$req = $bdd->prepare("UPDATE users SET password='".sanitize_vars($_POST['newpassword1'])."' WHERE id='".$_SESSION['id']."'");
						$req->execute();
						echo '1';
					}
				}
			}
			
			if($_POST['action'] == "editsettings"){
				if($_SESSION['type'] == "moderator"){
					$req = $bdd->prepare("UPDATE settings SET logo='".$_POST['logo']."',appname='".sanitize_vars($_POST['appname'])."',cmdprefix='".sanitize_vars($_POST['cmdprefix'])."',currency='".sanitize_vars($_POST['currency'])."',sepdelivered='".$_POST['sepdelivered']."',simplestats='".$_POST['simplestats']."',requirednote='".$_POST['requirednote']."',reseller='".$_POST['reseller']."',confirmationfees='".$_POST['confirmationfees']."'");
					$req->execute();
				}
				$back = $bdd->query("SELECT id FROM parametres WHERE user='".$_SESSION['id']."'");
				if($back->rowCount() == 0){
					$req = $bdd->prepare("INSERT INTO parametres(id,user,nbrows,rowcolor) VALUES('','".$_SESSION['id']."','20','".$_POST['rowcolor']."')");
				}
				else{
					$req = $bdd->prepare("UPDATE parametres SET rowcolor='".$_POST['rowcolor']."' WHERE user='".$_SESSION['id']."'");
				}
				$req->execute();
			}
			
			if($_POST['action'] == "setstandardfees"){
				$req = $bdd->prepare("UPDATE settings SET standardfees='".$_POST['standardfees']."'");
				$req->execute();			
			}

			if($_POST['action'] == "addcity"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO cities VALUES('0','".sanitize_vars($_POST['city'])."','1')");
					$req->execute();
				}
				else{
					$back = $bdd->query("SELECT city FROM cities WHERE id='".$_POST['id']."'");
					$row = $back->fetch();
					$req = $bdd->prepare("UPDATE cities SET city='".sanitize_vars($_POST['city'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
					$req = $bdd->prepare("UPDATE commands SET city='".sanitize_vars($_POST['city'])."' WHERE city='".$row['city']."'");
					$req->execute();	
					$req = $bdd->prepare("UPDATE shippingfees SET city='".sanitize_vars($_POST['city'])."' WHERE city='".$row['city']."'");
					$req->execute();
					$req = $bdd->prepare("UPDATE clientfees SET city='".sanitize_vars($_POST['city'])."' WHERE city='".$row['city']."'");
					$req->execute();
					$req = $bdd->prepare("UPDATE statistics SET city='".sanitize_vars($_POST['city'])."' WHERE city='".$row['city']."'");
					$req->execute();					
				}
			}
			
			if($_POST['action'] == "deletecity"){
				$req = $bdd->prepare("UPDATE cities SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restorecity"){
				$req = $bdd->prepare("UPDATE cities SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}

			if($_POST['action'] == "deletecitypermanently"){
				$req = $bdd->prepare("DELETE FROM cities WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadcities"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-cities"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-cities"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Ville</td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM cities WHERE trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND city LIKE '%".$_POST['keyword']."%'";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="city" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo ucfirst(strtolower($row['city']));?></td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-city lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-city="<?php echo ucfirst(strtolower($row['city']));?>" data-title="city"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-city lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-city" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-city" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> ville(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> ville(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "adddlm"){
				$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."'");
				if($back->rowCount() == 0){				
					if($_POST['id'] == "0"){
						$req = $bdd->prepare("INSERT INTO users(id,fullname,picture,email,password,phone,sav,city,cin,store,bank,rib,stockout,emailstockout,type,roles,active,datesignup,trash) VALUES ('0','".sanitize_vars($_POST['fullname'])."','avatar.png','".sanitize_vars($_POST['email'])."','".sanitize_vars($_POST['password'])."','".sanitize_vars($_POST['phone'])."','','','','','','','".sanitize_vars($_POST['stockout'])."','".sanitize_vars($_POST['emailstockout'])."','dlm','".sanitize_vars(substr($_POST['roles'],1))."','on','".time()."','1')");
						$req->execute();
					}
					else{
						$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',email='".sanitize_vars($_POST['email'])."',password='".sanitize_vars($_POST['password'])."',phone='".sanitize_vars($_POST['phone'])."',stockout='".sanitize_vars($_POST['stockout'])."',emailstockout='".sanitize_vars($_POST['emailstockout'])."',roles='".sanitize_vars(substr($_POST['roles'],1))."' WHERE id='".$_POST['id']."'");
						$req->execute();
					}
				}
				else{
					$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."' AND id='".$_POST['id']."'");
					if($back->rowCount() != 0){
						$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',email='".sanitize_vars($_POST['email'])."',password='".sanitize_vars($_POST['password'])."',phone='".sanitize_vars($_POST['phone'])."',stockout='".sanitize_vars($_POST['stockout'])."',emailstockout='".sanitize_vars($_POST['emailstockout'])."',roles='".sanitize_vars(substr($_POST['roles'],1))."' WHERE id='".$_POST['id']."'");
						$req->execute();						
					}
					else{
						echo "Email exist déja !!";	
					}
				}				
			}

			if($_POST['action'] == "deletedlm"){
				$req = $bdd->prepare("UPDATE users SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoredlm"){
				$req = $bdd->prepare("UPDATE users SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deletedlmpermanently"){
				$req = $bdd->prepare("DELETE FROM users WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loaddlm"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-dlm"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-dlm"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Nom et prénom <i class="fa fa-sort" data-sort="fullname"></i></td>
						<td>Téléphone <i class="fa fa-sort" data-sort="phone"></i></td>
						<td>E-mail <i class="fa fa-sort" data-sort="email"></i></td>
						<td>Mot de passe <i class="fa fa-sort" data-sort="password"></i></td>
						<td>StockOUT</td>
						<td>Droits</td>
						<td>Date inscription <i class="fa fa-sort" data-sort="datesignup"></i></td>
						<?php
						if($_SESSION['type'] != "client"){
						?>
						<td>Action</td>
						<?php
						}
						?>
					</tr>
					<?php
					$req = "SELECT * FROM users WHERE type='dlm' AND trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND (fullname LIKE '%".sanitize_vars($_POST['keyword'])."%' OR phone LIKE '%".sanitize_vars($_POST['keyword'])."%' OR email LIKE '%".sanitize_vars($_POST['keyword'])."%')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="dlm" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['fullname'];?></span></td>
						<td><span><?php echo $row['phone'];?></span></td>
						<td><span><?php echo $row['email'];?></span></td>
						<td><span><?php echo $row['password'];?></span></td>
						<td>
							<span><?php echo $row['stockout'];?></span>
							<span><?php echo $row['emailstockout'];?></span>
						</td>
						<td><span><?php echo $row['roles'];?></span></td>
						<td><span><?php echo gmdate("d/m/Y H:i",$row['datesignup']+3600);?></span></td>
						<?php
						if($_SESSION['type'] != "client"){
						?>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-dlm lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-email="<?php echo $row['email'];?>"
								data-password="<?php echo $row['password'];?>"
								data-phone="<?php echo $row['phone'];?>"
								data-stockout="<?php echo $row['stockout'];?>"
								data-emailstockout="<?php echo $row['emailstockout'];?>"
								data-roles=",<?php echo $row['roles'];?>" data-title="dlm"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-dlm lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-dlm" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-dlm" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
						<?php
						}
						?>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> livreur(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> livreur(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addsubdlm"){
				if($_POST['id'] == "0"){
					$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."'");
					if($back->rowCount() == 0){
						$req = $bdd->prepare("INSERT INTO users(id,fullname,picture,email,password,phone,sav,city,cin,store,bank,rib,type,roles,datesignup,active,trash) VALUES ('0','".sanitize_vars($_POST['fullname'])."','avatar.png','".sanitize_vars($_POST['email'])."','".sanitize_vars($_POST['password'])."','".sanitize_vars($_POST['phone'])."','','','','','','','subdlm','','".time()."','on','1')");
						$req->execute();
						$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."'");
						$row = $back->fetch();
						$req = $bdd->prepare("INSERT INTO subdlm(id,dlm,subdlm) VALUES ('0','".sanitize_vars($_POST['dlm'])."','".sanitize_vars($row['id'])."')");
						$req->execute();
					}
					else{
						echo "Email exist déja !!";
					}
				}
				else{
					$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',password='".sanitize_vars($_POST['password'])."',phone='".sanitize_vars($_POST['phone'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}

			if($_POST['action'] == "deletesubdlm"){
				$req = $bdd->prepare("UPDATE users SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoresubdlm"){
				$req = $bdd->prepare("UPDATE users SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deletesubdlmpermanently"){
				$req = $bdd->prepare("DELETE FROM users WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadsubdlm"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-subdlm"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-subdlm"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
							?>
						<td>Livreur <i class="fa fa-sort" data-sort="dlm"></i></td>
							<?php
						}
						?>
						<td>Nom et prénom <i class="fa fa-sort" data-sort="fullname"></i></td>
						<td>Téléphone <i class="fa fa-sort" data-sort="phone"></i></td>
						<td>E-mail <i class="fa fa-sort" data-sort="email"></i></td>
						<td>Mot de passe <i class="fa fa-sort" data-sort="password"></i></td>
						<td>Date inscription <i class="fa fa-sort" data-sort="datesignup"></i></td>
						<td>Active <i class="fa fa-sort" data-sort="active"></i></td>
						<?php
						if($_SESSION['type'] != "worker"){
						?>
						<td>Action</td>
						<?php
						}
						?>
					</tr>
					<?php
					$req = "SELECT * FROM subdlm sd,users u WHERE sd.subdlm=u.id AND type='subdlm' AND trash='".$_POST['state']."'";
					if($_SESSION['type'] == "dlm"){
						$req .= " AND dlm='".$_SESSION['id']."'";
					}	
					if($_POST['dlm'] != ""){
						$req .= " AND dlm='".$_POST['dlm']."'";
					}						
					if($_POST['keyword'] != ""){
						$req .= " AND (fullname LIKE '%".sanitize_vars($_POST['keyword'])."%' OR phone LIKE '%".sanitize_vars($_POST['keyword'])."%' OR email LIKE '%".sanitize_vars($_POST['keyword'])."%')";
					}
					if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
						$req .= " AND (datesignup BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY u.id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="subdlm" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						$back1 = $bdd->query("SELECT fullname FROM users WHERE id='".$row['dlm']."'");
						$row1 = $back1->fetch();
						?>
						<td><span><?php echo $row1['fullname'];?></span></td>
						<?php
						}
						?>
						<td><span><?php echo $row['fullname'];?></span></td>
						<td><span><?php echo $row['phone'];?></span></td>
						<td><span><?php echo $row['email'];?></span></td>
						<td><span><?php echo $row['password'];?></span></td>
						<td><span><?php echo gmdate("d/m/Y H:i",$row['datesignup']+3600);?></span></td>
						<td>
							<?php
							$class = '';
							if($row['active'] == "on"){
								$class = ' lx-on-off-blue';
							}
							?>
							<div class="lx-on-off<?php echo $class?>" data-state="<?php echo $row['active']?>" data-table="users" data-column="active" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>						
						</td>
						<?php
						if($_SESSION['type'] != "worker"){
						?>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-subdlm lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-dlm="<?php echo $row['dlm'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-email="<?php echo $row['email'];?>"
								data-password="<?php echo $row['password'];?>"
								data-phone="<?php echo $row['phone'];?>" data-title="subdlm"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-subdlm lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-subdlm" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-subdlm" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
						<?php
						}
						?>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> sous livreur(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> sous livreur(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addshippingfee"){
				if($_POST['id'] == "0"){
					$back = $bdd->query("SELECT city FROM shippingfees WHERE dlm='".$_POST['dlm']."' AND city='".sanitize_vars($_POST['city'])."' AND trash='1'");
					if($back->rowCount() == 0){
						$req = $bdd->prepare("INSERT INTO shippingfees VALUES('0','".sanitize_vars($_POST['dlm'])."','".sanitize_vars($_POST['city'])."','".sanitize_vars($_POST['deliveredfees'])."','".sanitize_vars($_POST['refusedfees'])."','1')");
						$req->execute();
					}
				}
				else{
					$req = $bdd->prepare("UPDATE shippingfees SET dlm='".sanitize_vars($_POST['dlm'])."',city='".sanitize_vars($_POST['city'])."',deliveredfees='".sanitize_vars($_POST['deliveredfees'])."',refusedfees='".sanitize_vars($_POST['refusedfees'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}
			
			if($_POST['action'] == "deleteshippingfee"){
				$req = $bdd->prepare("UPDATE shippingfees SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoreshippingfee"){
				$req = $bdd->prepare("UPDATE shippingfees SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}

			if($_POST['action'] == "deleteshippingfeepermanently"){
				$req = $bdd->prepare("DELETE FROM shippingfees WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadshippingfees"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-shippingfee"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-shippingfee"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Livreur</td>
						<td>Ville</td>
						<td>Frais livré</td>
						<td>Frais refusé</td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM shippingfees WHERE trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND city LIKE '%".$_POST['keyword']."%'";
					}
					if($_POST['dlm'] != ""){
						$req .= " AND dlm='".$_POST['dlm']."'";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="shippingfee" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<?php
						$back1 = $bdd->query("SELECT fullname FROM users WHERE id='".$row['dlm']."' AND type='dlm'");
						$row1 = $back1->fetch();
						?>
						<td><span><?php echo $row1['fullname'];?></span></td>
						<td><span><?php echo $row['city'];?></span></td>
						<td><span><?php echo $row['deliveredfees'];?> <?php echo $settings['currency'];?></span></td>
						<td><span><?php echo $row['refusedfees'];?> <?php echo $settings['currency'];?></span></td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-shippingfee lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-dlm="<?php echo $row['dlm'];?>"
								data-city="<?php echo $row['city'];?>"
								data-deliveredfees="<?php echo $row['deliveredfees'];?>"
								data-refusedfees="<?php echo $row['refusedfees'];?>" data-title="shippingfee"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-shippingfee lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-shippingfee" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-shippingfee" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> frai(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> frai(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addtrackingstate"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO trackingstates VALUES('0','".sanitize_vars($_POST['state'])."','".sanitize_vars($_POST['color'])."','".sanitize_vars(substr($_POST['agents'],1))."','".sanitize_vars(substr($_POST['phase'],1))."','".sanitize_vars(substr($_POST['kpi'],1))."','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE trackingstates SET state='".sanitize_vars($_POST['state'])."',color='".sanitize_vars($_POST['color'])."',agent='".sanitize_vars(substr($_POST['agents'],1))."',phase='".sanitize_vars(substr($_POST['phase'],1))."',kpi='".sanitize_vars(substr($_POST['kpi'],1))."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}
			
			if($_POST['action'] == "deletetrackingstate"){
				$req = $bdd->prepare("UPDATE trackingstates SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoretrackingstate"){
				$req = $bdd->prepare("UPDATE trackingstates SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}

			if($_POST['action'] == "deletetrackingstatepermanently"){
				$req = $bdd->prepare("DELETE FROM trackingstates WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadtrackingstates"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-trackingstate"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-trackingstate"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Etat</td>
						<td>Utilisateurs</td>
						<td>Etapes</td>
						<td>Emplacement statistiques</td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM trackingstates WHERE trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND state LIKE '%".$_POST['keyword']."%'";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="trackingstate" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row['color'];?>;color:#FFFFFF;border-radius:5px;"><?php echo $row['state'];?></span></td>
						<td><span><?php echo str_replace(",",", ",$row['agent']);?></span></td>
						<td><span><?php echo str_replace(",",", ",$row['phase']);?></span></td>
						<td><span><?php echo str_replace(",",", ",$row['kpi']);?></span></td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-trackingstate lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-state="<?php echo $row['state'];?>"
								data-color="<?php echo $row['color'];?>"
								data-agents=",<?php echo $row['agent'];?>"
								data-phase=",<?php echo $row['phase'];?>"
								data-kpi=",<?php echo $row['kpi'];?>" data-title="trackingstate"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-trackingstate lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-trackingstate" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-trackingstate" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> états de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> états de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addclient"){
				$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."'");
				if($back->rowCount() == 0){				
					if($_POST['id'] == "0"){
						if (strlen((string) $_POST['password']) < 6) {
							http_response_code(422);
							echo "Mot de passe invalide";
							exit;
						}
						$req = $bdd->prepare("INSERT INTO users(id,fullname,picture,email,password,phone,sav,city,cin,store,bank,rib,stockout,type,roles,active,datesignup,trash) VALUES ('0','".sanitize_vars($_POST['fullname'])."','avatar.png','".sanitize_vars($_POST['email'])."','".hash_password((string) $_POST['password'])."','".sanitize_vars($_POST['phone'])."','".sanitize_vars($_POST['sav'])."','".sanitize_vars($_POST['city'])."','".sanitize_vars($_POST['cin'])."','".sanitize_vars($_POST['store'])."','".sanitize_vars($_POST['bank'])."','".sanitize_vars($_POST['rib'])."','".sanitize_vars($_POST['stockout'])."','client','','on','".time()."','1')");
						$req->execute();
					}
					else{
						if ($_POST['password'] !== '' && strlen((string) $_POST['password']) < 6) {
							http_response_code(422);
							echo "Mot de passe invalide";
							exit;
						}
						$passwordUpdate = $_POST['password'] !== '' ? ",password='".hash_password((string) $_POST['password'])."'" : '';
						$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',email='".sanitize_vars($_POST['email'])."'".$passwordUpdate.",phone='".sanitize_vars($_POST['phone'])."',sav='".sanitize_vars($_POST['sav'])."',city='".sanitize_vars($_POST['city'])."',cin='".sanitize_vars($_POST['cin'])."',store='".sanitize_vars($_POST['store'])."',bank='".sanitize_vars($_POST['bank'])."',rib='".sanitize_vars($_POST['rib'])."',stockout='".sanitize_vars($_POST['stockout'])."' WHERE id='".$_POST['id']."'");
						$req->execute();
					}
				}
				else{
					$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."' AND id='".$_POST['id']."'");
					if($back->rowCount() != 0){
						if ($_POST['password'] !== '' && strlen((string) $_POST['password']) < 6) {
							http_response_code(422);
							echo "Mot de passe invalide";
							exit;
						}
						$passwordUpdate = $_POST['password'] !== '' ? ",password='".hash_password((string) $_POST['password'])."'" : '';
						$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',email='".sanitize_vars($_POST['email'])."'".$passwordUpdate.",phone='".sanitize_vars($_POST['phone'])."',sav='".sanitize_vars($_POST['sav'])."',city='".sanitize_vars($_POST['city'])."',cin='".sanitize_vars($_POST['cin'])."',store='".sanitize_vars($_POST['store'])."',bank='".sanitize_vars($_POST['bank'])."',rib='".sanitize_vars($_POST['rib'])."',stockout='".sanitize_vars($_POST['stockout'])."' WHERE id='".$_POST['id']."'");
						$req->execute();						
					}
					else{
						echo "Email exist déja !!";	
					}
				}				
			}

			if($_POST['action'] == "deleteclient"){
				$req = $bdd->prepare("UPDATE users SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoreclient"){
				$req = $bdd->prepare("UPDATE users SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deleteclientpermanently"){
				$req = $bdd->prepare("DELETE FROM users WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadclients"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-client"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-client"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Boutique <i class="fa fa-sort" data-sort="store"></i></td>
						<td>Nom et prénom (CIN) <i class="fa fa-sort" data-sort="fullname"></i></td>
						<td>Téléphone <i class="fa fa-sort" data-sort="phone"></i></td>
						<td>E-mail <i class="fa fa-sort" data-sort="email"></i></td>
						<td>Mot de passe <i class="fa fa-sort" data-sort="password"></i></td>
						<td>Ville <i class="fa fa-sort" data-sort="city"></i></td>
						<td>Bank <i class="fa fa-sort" data-sort="bank"></i></td>
						<td>RIB <i class="fa fa-sort" data-sort="rib"></i></td>
						<td>StockOUT <i class="fa fa-sort" data-sort="stockout"></i></td>
						<td>Date inscription <i class="fa fa-sort" data-sort="datesignup"></i></td>
						<td>Statut <i class="fa fa-sort" data-sort="active"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM users WHERE type='client' AND trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND (store LIKE '%".sanitize_vars($_POST['keyword'])."%' OR fullname LIKE '%".sanitize_vars($_POST['keyword'])."%' OR phone LIKE '%".sanitize_vars($_POST['keyword'])."%' OR email LIKE '%".sanitize_vars($_POST['keyword'])."%' OR city LIKE '%".sanitize_vars($_POST['keyword'])."%' OR cin LIKE '%".sanitize_vars($_POST['keyword'])."%' OR store LIKE '%".sanitize_vars($_POST['keyword'])."%' OR bank LIKE '%".sanitize_vars($_POST['keyword'])."%')";
					}
					if($_POST['active'] != ""){
						$req .= " active='".$_POST['active']."'";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="client" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['store'];?></span></td>
						<td><span><?php echo $row['fullname']." (".$row['cin'].")";?></span></td>
						<td><span><?php echo $row['phone'];?></span></td>
						<td><span><?php echo $row['email'];?></span></td>
						<td><span><?php echo $row['password'];?></span></td>
						<td><span><?php echo $row['city'];?></span></td>
						<td><span><?php echo $row['bank'];?></span></td>
						<td><span><?php echo $row['rib'];?></span></td>
						<td><span><?php echo $row['stockout'];?></span></td>
						<td><span><?php echo gmdate("d/m/Y H:i",$row['datesignup']+3600);?></span></td>
						<td>
							<?php
							$class = '';
							if($row['active'] == "on"){
								$class = ' lx-on-off-blue';
							}
							?>
							<span><?php echo $row['active'] === 'on' ? 'Approuvé' : 'En attente'; ?></span>
							<div class="lx-on-off<?php echo $class?>" title="<?php echo $row['active'] === 'on' ? 'Retirer l\'approbation' : 'Approuver ce client'; ?>" data-state="<?php echo $row['active']?>" data-table="clients" data-column="active" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>						
						</td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-client lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-email="<?php echo $row['email'];?>"
								data-phone="<?php echo $row['phone'];?>"
								data-sav="<?php echo $row['sav'];?>"
								data-city="<?php echo $row['city'];?>"
								data-cin="<?php echo $row['cin'];?>"
								data-store="<?php echo $row['store'];?>"
								data-bank="<?php echo $row['bank'];?>"
								data-stockout="<?php echo $row['stockout'];?>"
								data-rib="<?php echo $row['rib'];?>" data-title="client"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-client lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-client" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-client" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> client(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> client(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addworker"){
				$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."'");
				if($back->rowCount() == 0){				
					if($_POST['id'] == "0"){
						$req = $bdd->prepare("INSERT INTO users(id,upuser,fullname,picture,email,password,phone,sav,city,cin,store,bank,rib,stockout,type,roles,active,datesignup,trash) VALUES ('0','".$_SESSION['id']."','".sanitize_vars($_POST['fullname'])."','avatar.png','".sanitize_vars($_POST['email'])."','".sanitize_vars($_POST['password'])."','".sanitize_vars($_POST['phone'])."','','','','','','','','worker','','on','".time()."','1')");
						$req->execute();
					}
					else{
						$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',email='".sanitize_vars($_POST['email'])."',password='".sanitize_vars($_POST['password'])."',phone='".sanitize_vars($_POST['phone'])."' WHERE id='".$_POST['id']."'");
						$req->execute();
					}
				}
				else{
					$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."' AND id='".$_POST['id']."'");
					if($back->rowCount() != 0){
						$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',email='".sanitize_vars($_POST['email'])."',password='".sanitize_vars($_POST['password'])."',phone='".sanitize_vars($_POST['phone'])."' WHERE id='".$_POST['id']."'");
						$req->execute();						
					}
					else{
						echo "Email exist déja !!";	
					}
				}				
			}

			if($_POST['action'] == "deleteworker"){
				$req = $bdd->prepare("UPDATE users SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoreworker"){
				$req = $bdd->prepare("UPDATE users SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deleteworkerpermanently"){
				$req = $bdd->prepare("DELETE FROM users WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadworkers"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-worker"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-worker"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Nom et prénom <i class="fa fa-sort" data-sort="fullname"></i></td>
						<td>Téléphone <i class="fa fa-sort" data-sort="phone"></i></td>
						<td>E-mail <i class="fa fa-sort" data-sort="email"></i></td>
						<td>Date inscription <i class="fa fa-sort" data-sort="datesignup"></i></td>
						<td>Active <i class="fa fa-sort" data-sort="active"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM users WHERE type='worker' AND trash='".$_POST['state']."' AND upuser='".$_SESSION['id']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND (store LIKE '%".sanitize_vars($_POST['keyword'])."%' OR fullname LIKE '%".sanitize_vars($_POST['keyword'])."%' OR phone LIKE '%".sanitize_vars($_POST['keyword'])."%' OR email LIKE '%".sanitize_vars($_POST['keyword'])."%' OR city LIKE '%".sanitize_vars($_POST['keyword'])."%' OR cin LIKE '%".sanitize_vars($_POST['keyword'])."%' OR store LIKE '%".sanitize_vars($_POST['keyword'])."%' OR bank LIKE '%".sanitize_vars($_POST['keyword'])."%')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="worker" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['fullname']." (".$row['cin'].")";?></span></td>
						<td><span><?php echo $row['phone'];?></span></td>
						<td><span><?php echo $row['email'];?></span></td>
						<td><span><?php echo gmdate("d/m/Y H:i",$row['datesignup']+3600);?></span></td>
						<td>
							<?php
							$class = '';
							if($row['active'] == "on"){
								$class = ' lx-on-off-blue';
							}
							?>
							<div class="lx-on-off<?php echo $class?>" data-state="<?php echo $row['active']?>" data-table="users" data-column="active" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>						
						</td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-worker lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-email="<?php echo $row['email'];?>"
								data-password="<?php echo $row['password'];?>"
								data-phone="<?php echo $row['phone'];?>" data-title="worker"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-worker lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-worker" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-worker" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> employé(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> employé(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addclientfee"){
				if($_POST['id'] == "0"){
					$back = $bdd->query("SELECT city FROM clientfees WHERE client='".sanitize_vars($_POST['client'])."' AND city='".sanitize_vars($_POST['city'])."' AND trash='1'");
					if($back->rowCount() == 0){
						$req = $bdd->prepare("INSERT INTO clientfees VALUES('0','".sanitize_vars($_POST['client'])."','".sanitize_vars($_POST['city'])."','".sanitize_vars($_POST['deliveredfees'])."','".sanitize_vars($_POST['refusedfees'])."','".sanitize_vars($_POST['returnedfees'])."','1')");
						$req->execute();
					}
				}
				else{
					$req = $bdd->prepare("UPDATE clientfees SET client='".sanitize_vars($_POST['client'])."',city='".sanitize_vars($_POST['city'])."',deliveredfees='".sanitize_vars($_POST['deliveredfees'])."',refusedfees='".sanitize_vars($_POST['refusedfees'])."',returnedfees='".sanitize_vars($_POST['returnedfees'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}
			
			if($_POST['action'] == "deleteclientfee"){
				$req = $bdd->prepare("UPDATE clientfees SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoreclientfee"){
				$req = $bdd->prepare("UPDATE clientfees SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}

			if($_POST['action'] == "deleteclientfeepermanently"){
				$req = $bdd->prepare("DELETE FROM clientfees WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadclientfees"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-clientfee"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-clientfee"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Client</td>
						<td>Ville</td>
						<td>Frais livré</td>
						<td>Frais refusé</td>
						<td>Frais retour</td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM clientfees WHERE trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND city LIKE '%".$_POST['keyword']."%'";
					}
					if($_POST['client'] != ""){
						$req .= " AND client='".$_POST['client']."'";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="clientfee" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<?php
						$back1 = $bdd->query("SELECT fullname,store FROM users WHERE id='".$row['client']."' AND type='client'");
						$row1 = $back1->fetch();
						?>
						<td><span><?php echo $row1['store']." (".$row1['fullname'].")";?></span></td>
						<td><span><?php echo $row['city'];?></span></td>
						<td><span><?php echo $row['deliveredfees'];?> <?php echo $settings['currency'];?></span></td>
						<td><span><?php echo $row['refusedfees'];?> <?php echo $settings['currency'];?></span></td>
						<td><span><?php echo $row['returnedfees'];?> <?php echo $settings['currency'];?></span></td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-clientfee lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-client="<?php echo $row['client'];?>"
								data-city="<?php echo $row['city'];?>"
								data-deliveredfees="<?php echo $row['deliveredfees'];?>"
								data-refusedfees="<?php echo $row['refusedfees'];?>"
								data-returnedfees="<?php echo $row['returnedfees'];?>" data-title="clientfee"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-clientfee lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-clientfee" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-clientfee" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> frai(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> frai(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}
			
			if($_POST['action'] == "addgshippingfee"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO gshippingfees VALUES('0','".sanitize_vars($_POST['city'])."','".sanitize_vars($_POST['deliveredfees'])."','".sanitize_vars($_POST['refusedfees'])."','".sanitize_vars($_POST['returnedfees'])."','".sanitize_vars($_POST['delay'])."','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE gshippingfees SET city='".sanitize_vars($_POST['city'])."',deliveredfees='".sanitize_vars($_POST['deliveredfees'])."',refusedfees='".sanitize_vars($_POST['refusedfees'])."',returnedfees='".sanitize_vars($_POST['returnedfees'])."',delay='".sanitize_vars($_POST['delay'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}
			
			if($_POST['action'] == "deletegshippingfee"){
				$req = $bdd->prepare("UPDATE gshippingfees SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoregshippingfee"){
				$req = $bdd->prepare("UPDATE gshippingfees SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}

			if($_POST['action'] == "deletegshippingfeepermanently"){
				$req = $bdd->prepare("DELETE FROM gshippingfees WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadgshippingfees"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-gshippingfee"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-gshippingfee"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Ville</td>
						<td>Frais livré</td>
						<td>Frais refusé</td>
						<td>Frais retour</td>
						<td>Delai de livraison</td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM gshippingfees WHERE trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND city LIKE '%".$_POST['keyword']."%'";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="gshippingfee" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['city'];?></span></td>
						<td><span><?php echo $row['deliveredfees'];?> <?php echo $settings['currency'];?></span></td>
						<td><span><?php echo $row['refusedfees'];?> <?php echo $settings['currency'];?></span></td>
						<td><span><?php echo $row['returnedfees'];?> <?php echo $settings['currency'];?></span></td>
						<td><span><?php echo $row['delay'];?></span></td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-gshippingfee lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-city="<?php echo $row['city'];?>"
								data-deliveredfees="<?php echo $row['deliveredfees'];?>"
								data-refusedfees="<?php echo $row['refusedfees'];?>"
								data-returnedfees="<?php echo $row['returnedfees'];?>"
								data-delay="<?php echo $row['delay'];?>" data-title="gshippingfee"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-gshippingfee lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-gshippingfee" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-gshippingfee" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> frai(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> frai(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}
			if($_POST['action'] == "addshipramassage"){				if(sanitize_vars($_POST['id']) == "0"){					$req = $bdd->prepare("INSERT INTO shipramassages(id,dlm,nbcolis,camp,ref,note,received,dateadd,datereceived,trash) VALUES ('0','".sanitize_vars($_SESSION['id'])."','".sanitize_vars($_POST['nbcolis'])."','".sanitize_vars($_POST['camp'])."','".sanitize_vars($_POST['ref'])."','".sanitize_vars($_POST['note'])."','off','".time()."','','1')");					$req->execute();				}				else{					$req = $bdd->prepare("UPDATE shipramassages SET nbcolis='".sanitize_vars($_POST['nbcolis'])."',camp='".sanitize_vars($_POST['camp'])."',ref='".sanitize_vars($_POST['ref'])."',note='".sanitize_vars($_POST['note'])."' WHERE id='".sanitize_vars($_POST['id'])."'");					$req->execute();				}			}			if($_POST['action'] == "deleteshipramassage"){				if($_SESSION['type'] == "moderator"){					saveLog($_SESSION['fullname']." a met en corbeille un envoi [ID:".sanitize_vars($_POST['id'])."]");				}				$req = $bdd->prepare("UPDATE shipramassages SET trash='0' WHERE id='".sanitize_vars($_POST['id'])."'");				$req->execute();			}						if($_POST['action'] == "restoreshipramassage"){				$req = $bdd->prepare("UPDATE shipramassages SET trash='1' WHERE id='".sanitize_vars($_POST['id'])."'");				$req->execute();			}						if($_POST['action'] == "deleteshipramassagepermanently"){				if($_SESSION['type'] == "moderator"){					saveLog($_SESSION['fullname']." a supprimé un envoi [ID:".sanitize_vars($_POST['id'])."]");				}				$req = $bdd->prepare("DELETE FROM shipramassages WHERE id='".sanitize_vars($_POST['id'])."'");				$req->execute();			}						if($_POST['action'] == "loadshipramassages"){				?>				<a href="javascript:;" class="lx-trash lx-trash-shipramassage"><i class="fa fa-trash-alt"></i> Corbeille</a>				<a href="javascript:;" class="lx-trash lx-published-shipramassage"><i class="fa fa-bars"></i> Publiés</a>				<table cellpadding="0" cellspacing="0">					<tr class="lx-first-tr">						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>						<?php						if($_SESSION['type'] == "moderator"){						?>						<td>Livreur <i class="fa fa-sort" data-sort="client"></i></td>						<?php						}						?>						<td>Société de transport <i class="fa fa-sort" data-sort="camp"></i></td>						<td>Référence <i class="fa fa-sort" data-sort="ref"></i></td>						<td>Nombre de colis <i class="fa fa-sort" data-sort="nbcolis"></i></td>						<td>Note <i class="fa fa-sort" data-sort="not"></i></td>						<td>Date d'envoi <i class="fa fa-sort" data-sort="dateadd"></i></td>						<td>Date de reception <i class="fa fa-sort" data-sort="datereceived"></i></td>						<td>Reçu <i class="fa fa-sort" data-sort="received"></i></td>						<td>Action</td>					</tr>					<?php					$req = "SELECT * FROM shipramassages WHERE trash='".sanitize_vars($_POST['state'])."'".$userwhere;					if(sanitize_vars($_POST['keyword']) != ""){						$req .= " AND product LIKE '%".sanitize_vars($_POST['keyword'])."%'";					}					if(sanitize_vars($_POST['dlm']) != ""){						$req .= " AND dlm='".sanitize_vars($_POST['dlm'])."'";					}					if(sanitize_vars($_POST['datestart']) != "" AND sanitize_vars($_POST['dateend']) != ""){						$datestart = strtotime(str_replace("/","-",sanitize_vars($_POST['datestart'])));						$dateend = strtotime(str_replace("/","-",sanitize_vars($_POST['dateend']))) + (60*60*24) - 1;						$req .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";					}					if(sanitize_vars($_POST['sortby']) != ""){						$req .= " ORDER BY ".sanitize_vars($_POST['sortby']);					}					else{						$req .= " ORDER BY id";					}					$req .= " ".sanitize_vars($_POST['orderby']);					$back2 = $bdd->query($req);					$req .= " LIMIT ".sanitize_vars($_POST['start']).",".sanitize_vars($_POST['nbpage']);					$back = $bdd->query($req);					while($row = $back->fetch()){						?>					<tr>						<td><label><input type="checkbox" name="shipramassage" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>						<?php						if($_SESSION['type'] == "moderator"){						$back1 = $bdd->query("SELECT fullname FROM users WHERE id='".$row['dlm']."'");						$row1 = $back1->fetch();						?>						<td><span><?php echo $row1['fullname'];?></span></td>						<?php						}						?>						<td><span><?php echo $row['camp'];?></span></td>						<td><span><?php echo $row['ref'];?></span></td>						<td><span><?php echo $row['nbcolis'];?></span></td>						<td><span><?php echo $row['note'];?></span></td>						<td><span><?php echo $row['dateadd']!=""?date("d/m/Y",$row['dateadd']):"";?></span></td>						<td><span><?php echo $row['datereceived']!=""?date("d/m/Y",$row['datereceived']):"";?></span></td>						<td>							<?php							if($_SESSION['type'] == "moderator"){								if($row['received'] == "off"){									?>							<div class="lx-on-off" data-state="<?php echo $row['received'];?>" data-table="shipramassages" data-column="received" data-id="<?php echo $row['id'];?>">								<div class="lx-on-off-fill">									<i class="material-icons">check</i>									<span></span>								</div>							</div>									<?php								}								else{									?>							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:4px;">Oui</span>									<?php								}							}							else{								if($row['received'] == "on"){									?>							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:4px;">Oui</span>									<?php								}								else{									?>							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#FFA500;color:#FFFFFF;border-radius:4px;">En attente</span>									<?php								}							}							?>						</td>						<td>							<?php							if($row['received'] == 'off'){								if(sanitize_vars($_POST['state']) == 1){									?>							<a href="javascript:;" class="lx-edit lx-edit-shipramassage lx-open-popup" 								data-id="<?php echo $row['id'];?>"								data-nbcolis="<?php echo $row['nbcolis'];?>"								data-camp="<?php echo $row['camp'];?>"								data-ref="<?php echo $row['ref'];?>"								data-note="<?php echo $row['note'];?>" data-title="shipramassage" title="Modifier"><i class="fa fa-edit"></i></a>							<a href="javascript:;" class="lx-delete lx-delete-shipramassage lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>" title="Supprimer"><i class="fa fa-trash"></i></a>									<?php								}								else{									?>							<a href="javascript:;" class="lx-edit lx-restore-shipramassage" data-id="<?php echo $row['id'];?>" title="Restaurer"><i class="fa fa-upload"></i></a>							<a href="javascript:;" class="lx-delete lx-delete-permanently-shipramassage" data-id="<?php echo $row['id'];?>" title="Supprimer"><i class="fa fa-trash"></i></a>									<?php								}							}							else{								?>							<a href="printshipramassage.php?id=<?php echo $row['id'];?>" class="lx-delete"><i class="fa fa-print"></i></a>								<?php							}							?>						</td>					</tr>						<?php					}					?>				</table>				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />				<?php				if($back2->rowCount() > (sanitize_vars($_POST['start']) + sanitize_vars($_POST['nbpage']))){				?>				<p><?php echo (sanitize_vars($_POST['start']) + sanitize_vars($_POST['nbpage']));?> envoi(s) de <?php echo $back2->rowCount();?></p>				<?php				}				else{				?>				<p><?php echo $back2->rowCount();?> envoi(s) de <?php echo $back2->rowCount();?></p>				<?php				}			}
			if($_POST['action'] == "addshipment"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO shipments(id,client,stock,title,ref,qty,received,dateadd,trash) VALUES ('0','".sanitize_vars($_SESSION['id'])."','".sanitize_vars($_POST['stock'])."','".sanitize_vars($_POST['title'])."','".sanitize_vars($_POST['ref'])."','".sanitize_vars($_POST['qty'])."','off','".time()."','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE shipments SET stock='".sanitize_vars($_POST['stock'])."',title='".sanitize_vars($_POST['title'])."',ref='".sanitize_vars($_POST['ref'])."',qty='".sanitize_vars($_POST['qty'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}

			if($_POST['action'] == "deleteshipment"){
				$req = $bdd->prepare("UPDATE shipments SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoreshipment"){
				$req = $bdd->prepare("UPDATE shipments SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deleteshipmentpermanently"){
				$req = $bdd->prepare("DELETE FROM shipments WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadshipments"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-shipment"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-shipment"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<td>Client <i class="fa fa-sort" data-sort="title"></i></td>
						<?php
						}
						?>
						<td>Titre <i class="fa fa-sort" data-sort="title"></i></td>
						<td>Réf <i class="fa fa-sort" data-sort="ref"></i></td>
						<td>Quantité <i class="fa fa-sort" data-sort="qty"></i></td>
						<td>Date envoi <i class="fa fa-sort" data-sort="dateadd"></i></td>
						<td>Validé <i class="fa fa-sort" data-sort="received"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM shipments WHERE trash='".$_POST['state']."'".$userwhere;
					if($_POST['keyword'] != ""){
						$req .= " AND (title LIKE '%".$_POST['keyword']."%' OR ref LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['client'] != ""){
						$req .= " AND client='".$_POST['client']."'";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						$sm = 0;
						$back1 = $bdd->query("SELECT qty,product FROM commands WHERE (product='".$row['id']."' OR product LIKE '".$row['id'].",%' OR product LIKE '%,".$row['id'].",%' OR product LIKE '%,".$row['id']."') AND state IN('Livré')");
						while($row1 = $back1->fetch()){
							$shipments = explode(",",$row1['product']);
							$j = 0;
							for($i=0;$i<count($shipments);$i++){
								if($shipments[$i] == $row['id']){
									$j = $i;
								}
							}
							$qtys = explode(",",$row1['qty']);
							$sm += $qtys[$j];
						}
						?>
					<tr style="<?php if(($row['qty'] - $sm) <= 0){echo "background:rgba(".hexdec("ff").",".hexdec("e5").",".hexdec("e5").",0.5)";}?>">
						<td><label><input type="checkbox" name="shipment" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<td>
							<?php
							$back1 = $bdd->query("SELECT fullname,store FROM users WHERE type='client' AND id='".$row['client']."'");
							$row1 = $back1->fetch();
							?>
							<span><?php echo $row1['store']." (".$row1['fullname'].")";?></span>
						</td>
						<?php
						}
						?>
						<td><span><?php echo $row['title'];?></span></td>
						<td><span><?php echo $row['ref'];?></span></td>
						<td><span><?php echo $row['qty'];?></span></td>
						<td><span><?php echo gmdate("d/m/Y",$row['dateadd']+3600);?></span></td>
						<td>
							<?php
							if($_SESSION['type'] != "moderator"){
								if($row['received'] == "on"){
									?>
								<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:5px;">Oui</span>
									<?php
								}
								else{
									?>
								<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#FFA500;color:#FFFFFF;border-radius:5px;">Non</span>
									<?php								
								}
							}
							else{
								$class = "";
								if($row['received'] == "on"){
									$class = " lx-on-off-blue";
								}
								?>
							<div class="lx-on-off<?php echo $class;?>" data-state="<?php echo $row['received'];?>" data-table="shipments" data-column="received" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
								<?php
							}
							?>
						</td>					
						<td>
							<?php
							if($_POST['state'] == 1){
								if(($row['received'] == "off" AND $_SESSION['type'] == "client") OR $_SESSION['type'] == "moderator"){
									?>
							<a href="javascript:;" class="lx-edit lx-edit-shipment lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-stock="<?php echo $row['stock'];?>"
								data-titl="<?php echo $row['title'];?>" 
								data-ref="<?php echo $row['ref'];?>" 
								data-qty="<?php echo $row['qty'];?>" data-title="shipment"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-shipment lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
									<?php
								}
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-shipment" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-shipment" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> envoi(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> envoi(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addstock"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO stocks(id,client,title,ref,qty,received,dateadd,trash) VALUES ('0','".sanitize_vars($_POST['client'])."','".sanitize_vars($_POST['title'])."','".sanitize_vars($_POST['ref'])."','".sanitize_vars($_POST['qty'])."','off','".time()."','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE stocks SET client='".sanitize_vars($_POST['client'])."',title='".sanitize_vars($_POST['title'])."',ref='".sanitize_vars($_POST['ref'])."',qty='".sanitize_vars($_POST['qty'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}

			if($_POST['action'] == "deletestock"){
				$req = $bdd->prepare("UPDATE stocks SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restorestock"){
				$req = $bdd->prepare("UPDATE stocks SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deletestockpermanently"){
				$req = $bdd->prepare("DELETE FROM stocks WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadstocks"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-stock"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-stock"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<td>Client <i class="fa fa-sort" data-sort="title"></i></td>
						<?php
						}
						?>
						<td>Titre <i class="fa fa-sort" data-sort="title"></i></td>
						<td>Réf <i class="fa fa-sort" data-sort="ref"></i></td>
						<td>Quantité initial <i class="fa fa-sort" data-sort="qty"></i></td>
						<td>Quantité sortie</td>
						<td>Quantité restant</td>
						<td>Date ajout <i class="fa fa-sort" data-sort="dateadd"></i></td>
						<td>Validé <i class="fa fa-sort" data-sort="received"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM stocks WHERE trash='".$_POST['state']."'".$userwhere;
					if($_POST['keyword'] != ""){
						$req .= " AND (title LIKE '%".$_POST['keyword']."%' OR ref LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['client'] != ""){
						$req .= " AND client='".$_POST['client']."'";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						$sm = 0;
						$codes = "";
						$back1 = $bdd->query("SELECT code,qty,product FROM commands WHERE client='".$row['client']."' AND code NOT LIKE 'CHANGE-%' AND (product='".$row['id']."' OR product LIKE '".$row['id'].",%' OR product LIKE '%,".$row['id'].",%' OR product LIKE '%,".$row['id']."') AND state NOT IN('Ajouté','Retour client reçu')");
						while($row1 = $back1->fetch()){
							$stocks = explode(",",$row1['product']);
							$j = 0;
							for($i=0;$i<count($stocks);$i++){
								if($stocks[$i] == $row['id']){
									$j = $i;
								}
							}
							$qtys = explode(",",$row1['qty']);
							$sm += $qtys[$j];
							$codes .= "<br />".$row1['code'];
						}
						?>
					<tr style="<?php if(($row['qty'] - $sm) <= 0){echo "background:rgba(".hexdec("ff").",".hexdec("e5").",".hexdec("e5").",0.5)";}?>">
						<td><label><input type="checkbox" name="stock" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<td>
							<?php
							$back1 = $bdd->query("SELECT fullname,store FROM users WHERE type='client' AND id='".$row['client']."'");
							$row1 = $back1->fetch();
							?>
							<span><?php echo $row1['store']." (".$row1['fullname'].")";?></span>
						</td>
						<?php
						}
						?>
						<td><span><?php echo $row['title'];?></span></td>
						<td><span><?php echo $row['ref'];?></span></td>
						<td><span><?php echo $row['qty'];?></span></td>
						<td>
							<span><?php echo $sm;?></span>
							<p style="display:none;"><?php echo $codes;?></p>
						</td>
						<td><span><?php echo ($row['qty']-$sm);?></span></td>
						<td><span><?php echo gmdate("d/m/Y",$row['dateadd']+3600);?></span></td>
						<td>
							<?php
							if($_SESSION['type'] != "moderator"){
								if($row['received'] == "on"){
									?>
								<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:5px;">Oui</span>
									<?php
								}
								else{
									?>
								<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#FFA500;color:#FFFFFF;border-radius:5px;">Non</span>
									<?php								
								}
							}
							else{
								$class = "";
								if($row['received'] == "on"){
									$class = " lx-on-off-blue";
								}
								?>
							<div class="lx-on-off<?php echo $class;?>" data-state="<?php echo $row['received'];?>" data-table="stocks" data-column="received" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
								<?php
							}
							?>
						</td>					
						<td>
							<?php
							if($_POST['state'] == 1){
								if(($row['received'] == "off" AND $_SESSION['type'] == "client") OR $_SESSION['type'] == "moderator"){
									?>
							<a href="javascript:;" class="lx-edit lx-edit-stock lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-client="<?php echo $row['client'];?>"
								data-titl="<?php echo $row['title'];?>" 
								data-ref="<?php echo $row['ref'];?>" 
								data-qty="<?php echo $row['qty'];?>" data-title="stock"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-stock lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
									<?php
								}
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-stock" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-stock" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> stock(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> stock(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addstockdlm"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO stockdlms(id,dlm,stock,qty,received,dateadd,trash) VALUES ('0','".sanitize_vars($_POST['dlm'])."','".sanitize_vars($_POST['stock'])."','".sanitize_vars($_POST['qty'])."','off','".time()."','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE stockdlms SET dlm='".sanitize_vars($_POST['dlm'])."',stock='".sanitize_vars($_POST['stock'])."',qty='".sanitize_vars($_POST['qty'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}

			if($_POST['action'] == "deletestockdlm"){
				$req = $bdd->prepare("UPDATE stockdlms SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restorestockdlm"){
				$req = $bdd->prepare("UPDATE stockdlms SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deletestockdlmpermanently"){
				$req = $bdd->prepare("DELETE FROM stockdlms WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadstockdlms"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-stockdlm"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-stockdlm"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<td>Livreur</td>
						<?php
						}
						?>
						<td>Titre <i class="fa fa-sort" data-sort="title"></i></td>
						<td>Réf <i class="fa fa-sort" data-sort="ref"></i></td>
						<td>Quantité initial <i class="fa fa-sort" data-sort="qty"></i></td>
						<td>Quantité restant</td>
						<td>Date ajout <i class="fa fa-sort" data-sort="dateadd"></i></td>
						<td>Validé <i class="fa fa-sort" data-sort="received"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM stockdlms WHERE trash='".$_POST['state']."'".$userwhere;
					if($_POST['keyword'] != ""){
						$req .= " AND (title LIKE '%".$_POST['keyword']."%' OR ref LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['dlm'] != ""){
						$req .= " AND dlm='".$_POST['dlm']."'";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						$sm = 0;
						$back1 = $bdd->query("SELECT qty,product FROM commands WHERE (product='".$row['stock']."' OR product LIKE '".$row['stock'].",%' OR product LIKE '%,".$row['stock'].",%' OR product LIKE '%,".$row['stock']."') AND state IN('Livré')");
						while($row1 = $back1->fetch()){
							$stocks = explode(",",$row1['product']);
							$j = 0;
							for($i=0;$i<count($stocks);$i++){
								if($stocks[$i] == $row['id']){
									$j = $i;
								}
							}
							$qtys = explode(",",$row1['qty']);
							$sm += $qtys[$j];
						}
						?>
					<tr style="<?php if(($row['qty'] - $sm) <= 0){echo "background:rgba(".hexdec("ff").",".hexdec("e5").",".hexdec("e5").",0.5)";}?>">
						<td><label><input type="checkbox" name="stockdlm" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<td>
							<?php
							$back1 = $bdd->query("SELECT fullname,store FROM users WHERE type='dlm' AND id='".$row['dlm']."'");
							$row1 = $back1->fetch();
							?>
							<span><?php echo $row1['fullname'];?></span>
						</td>
						<?php
						}
						$back1 = $bdd->query("SELECT * FROM stocks WHERE id='".$row['stock']."'");
						$row1 = $back1->fetch();
						?>
						<td><span><?php echo $row1['title'];?></span></td>
						<td><span><?php echo $row1['ref'];?></span></td>
						<td><span><?php echo $row['qty'];?></span></td>
						<td><span><?php echo ($row['qty']-$sm);?></span></td>
						<td><span><?php echo gmdate("d/m/Y",$row['dateadd']+3600);?></span></td>
						<td>
							<?php
							if($_SESSION['type'] != "moderator"){
								if($row['received'] == "on"){
									?>
								<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:5px;">Oui</span>
									<?php
								}
								else{
									?>
								<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#FFA500;color:#FFFFFF;border-radius:5px;">Non</span>
									<?php								
								}
							}
							else{
								$class = "";
								if($row['received'] == "on"){
									$class = " lx-on-off-blue";
								}
								?>
							<div class="lx-on-off<?php echo $class;?>" data-state="<?php echo $row['received'];?>" data-table="stockdlms" data-column="received" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
								<?php
							}
							?>
						</td>					
						<td>
							<?php
							if($_POST['state'] == 1){
								if(($row['received'] == "off" AND $_SESSION['type'] == "dlm") OR $_SESSION['type'] == "moderator"){
									?>
							<a href="javascript:;" class="lx-edit lx-edit-stockdlm lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-dlm="<?php echo $row['dlm'];?>"
								data-stock="<?php echo $row['stock'];?>" 
								data-qty="<?php echo $row['qty'];?>" data-title="stockdlm"><i class="fa fa-edit"></i></a>
								<a href="javascript:;" class="lx-delete lx-delete-stockdlm lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
									<?php
								}
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-stockdlm" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-stockdlm" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> stock(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> stock(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addpackaging"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO packaging VALUES('0','".sanitize_vars($_POST['title'])."','".sanitize_vars($_POST['price'])."','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE packaging SET title='".sanitize_vars($_POST['title'])."',price='".sanitize_vars($_POST['title'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}
			
			if($_POST['action'] == "deletepackaging"){
				$req = $bdd->prepare("UPDATE packaging SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restorepackaging"){
				$req = $bdd->prepare("UPDATE packaging SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}

			if($_POST['action'] == "deletepackagingpermanently"){
				$req = $bdd->prepare("DELETE FROM packaging WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadpackaging"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-packaging"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-packaging"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Emballage</td>
						<td>Prix</td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM packaging WHERE trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND city LIKE '%".$_POST['keyword']."%'";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="packaging" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['title'];?></span></td>
						<td><span><?php echo $row['price'];?> <?php echo $settings['currency'];?></span></td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-packaging lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-titl="<?php echo $row['title'];?>"
								data-price="<?php echo $row['price'];?>" data-title="packaging"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-packaging lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-packaging" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-packaging" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> emballage(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> emballage(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addspreadsheet"){
				if(sanitize_vars($_POST['id']) == "0"){
					$req = $bdd->prepare("INSERT INTO `spreadsheets`(`id`, `client`, `title`, `sheetid`, `sheetname`, `lastrow`, `autofetch`, `trash`) 
					VALUES ('0','".$_POST['client']."','".sanitize_vars($_POST['title'])."','".sanitize_vars($_POST['sheetid'])."','".sanitize_vars($_POST['sheetname'])."','".sanitize_vars($_POST['lastrow'])."','off','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE spreadsheets SET title='".sanitize_vars($_POST['title'])."',sheetid='".sanitize_vars($_POST['sheetid'])."',sheetname='".sanitize_vars($_POST['sheetname'])."',lastrow='".sanitize_vars($_POST['lastrow'])."' WHERE id='".sanitize_vars($_POST['id'])."'");
					$req->execute();
				}
			}
			
			if($_POST['action'] == "deletespreadsheet"){
				$req = $bdd->prepare("UPDATE spreadsheets SET trash='0' WHERE MD5(id)='".sanitize_vars($_POST['id'])."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restorespreadsheet"){
				$req = $bdd->prepare("UPDATE spreadsheets SET trash='1' WHERE id='".sanitize_vars($_POST['id'])."'");
				$req->execute();
			}

			if($_POST['action'] == "deletespreadsheetpermanently"){
				$req = $bdd->prepare("DELETE FROM spreadsheets WHERE id='".sanitize_vars($_POST['id'])."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadspreadsheets"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-spreadsheets"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-spreadsheets"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<td>Client</td>
						<?php
						}
						?>
						<td>Titre</td>
						<td>Sheet ID</td>
						<td>Sheet Name</td>
						<td>Last Row Number</td>
						<td>Activer</td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM spreadsheets WHERE trash='".sanitize_vars($_POST['state'])."'".$userwhere;
					if(sanitize_vars($_POST['keyword']) != ""){
						$req .= " AND (title LIKE '%".sanitize_vars($_POST['keyword'])."%' OR sheetid LIKE '%".sanitize_vars($_POST['keyword'])."%' OR sheetname LIKE '%".sanitize_vars($_POST['keyword'])."%')";
					}
					if(sanitize_vars($_POST['sortby']) != ""){
						$req .= " ORDER BY ".sanitize_vars($_POST['sortby']);
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".sanitize_vars($_POST['orderby']);
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".sanitize_vars($_POST['start']).",".sanitize_vars($_POST['nbpage']);
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="spreadsheet" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
							$back1 = $bdd->query("SELECT store,fullname FROM users WHERE id='".$row['client']."'");
							$row1 = $back1->fetch();
						?>
						<td><span><?php echo $row1['store']." (".$row1['fullname'].")";?></span></td>
						<?php
						}
						?>
						<td><span><?php echo $row['title'];?></span></td>
						<td><span><?php echo $row['sheetid'];?></span></td>
						<td><span><?php echo $row['sheetname'];?></span></td>
						<td><span><?php echo $row['lastrow'];?></span></td>
						<td>
							<?php
							$class = '';
							if($row['autofetch'] == "on"){
								$class = ' lx-on-off-blue';
							}
							?>
							<div class="lx-on-off<?php echo $class?>" data-state="<?php echo $row['autofetch']?>" data-table="spreadsheets" data-column="autofetch" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
						</td>
						<td>
							<?php
							if(sanitize_vars($_POST['state']) == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-spreadsheet lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-titl="<?php echo $row['title'];?>"
								data-sheetid="<?php echo $row['sheetid'];?>"
								data-sheetname="<?php echo $row['sheetname'];?>"
								data-lastrow="<?php echo $row['lastrow'];?>" data-title="spreadsheet" title="Modifier"><i class="fa fa-edit"></i></a>
							<a href="javascript:;" class="lx-delete lx-delete-spreadsheet lx-open-popup" data-title="deleterecord" data-id="<?php echo md5($row['id']);?>" title="Supprimer"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-spreadsheet" data-id="<?php echo $row['id'];?>" title="Restaurer"><i class="fa fa-upload"></i></a>
							<a href="javascript:;" class="lx-delete lx-delete-permanently-spreadsheet" data-id="<?php echo $row['id'];?>" title="Supprimer"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > (sanitize_vars($_POST['start']) + sanitize_vars($_POST['nbpage']))){
				?>
				<p><?php echo (sanitize_vars($_POST['start']) + sanitize_vars($_POST['nbpage']));?> Feuille(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> Feuille(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
			}	

			if($_POST['action'] == "addcommand"){
				if($_POST['id'] == "0"){
					$rand = '';
					if($_POST['code'] != ""){
						$back = $bdd->query("SELECT fullname FROM users WHERE id='".$_POST['client']."' AND trash='1'");
						$row = $back->fetch();
						$rand = strtoupper(substr($row['fullname'],0,5))."-".$_POST['code'];
					}
					else{
						do{
							$rand = $settings['cmdprefix'].'-'.gmdate('dmY').'-'.random();
							$back = $bdd->query("SELECT id FROM commands WHERE code='".$rand."'");
						}
						while($back->rowCount() != 0);						
					}
					if($_POST['change'] == "1"){
						$rand = "CHANGE-".$rand;
					}
					$note = "";
					if($_POST['note'] != ""){
						$note = "<b>Client: </b>".sanitize_vars($_POST['note'])."<br />";
					}
					$req = $bdd->prepare("INSERT INTO commands(id,code,product,qty,dlm,client,clientname,clientphone,fullname,phone,address,city,price,state,phase,datereported,note,collected,invoiced,invoiceddlm,confirmed,treated,archived,echange,openpackage,dateadd,dateupdate,trash) 
					VALUES ('0','".$rand."','".sanitize_vars(substr($_POST['product'],1))."','".sanitize_vars(substr($_POST['qty'],1))."','0','".$_POST['client']."','".sanitize_vars($_POST['clientname'])."','".sanitize_vars($_POST['clientphone'])."','".sanitize_vars($_POST['fullname'])."','".sanitize_vars($_POST['phone'])."','".sanitize_vars($_POST['address'])."','".sanitize_vars($_POST['city'])."','".sanitize_vars($_POST['price'])."','".sanitize_vars($_POST['state'])."','".sanitize_vars($_POST['phase'])."','','".$note."','off','off','off','off','".sanitize_vars($_POST['confirmed'])."','0','".sanitize_vars($_POST['change'])."','".sanitize_vars($_POST['openpackage'])."','".time()."','".time()."','1')");
					$req->execute();
					$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$rand."','".sanitize_vars($_POST['state'])."','".$_SESSION['fullname']."','".time()."')");
					$req->execute();
				}
				else{
					$back = $bdd->query("SELECT id,code,dlm,qty,city,price,extrafees,package,state FROM commands WHERE id='".$_POST['id']."'");
					$command = $back->fetch();
					if($command['state'] == "Livré"){
						$oldprice = $command['price'];
						$newprice = $_POST['price'];
						if($command['city'] != $_POST['city']){
							$command['city'] = $_POST['city'];
						}
						if($oldprice != $newprice){
							$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$command['id']."'");
							$row = $back->fetch();
							$back = $bdd->query("SELECT deliveredfees FROM shippingfees WHERE city='".$command['city']."'");
							$city = $back->fetch();
							$price = ($newprice - $city['deliveredfees']) - ($oldprice - $city['deliveredfees']);
							$req = $bdd->prepare("UPDATE factures SET price=(price+".$price.") WHERE code='".$row['facture']."'");
							$req->execute();
						}
						if($command['package'] != $_POST['package']){
							$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$command['id']."'");
							$row = $back->fetch();
							$back = $bdd->query("SELECT price FROM packaging WHERE id='".$command['package']."'");
							$oldpackage = $back->fetch();
							$back = $bdd->query("SELECT price FROM packaging WHERE id='".$_POST['package']."'");
							$newpackage = $back->fetch();
							$price = intval($newpackage['price']) - intval($oldpackage['price']);;
							$req = $bdd->prepare("UPDATE factures SET price=(price-".$price.") WHERE code='".$row['facture']."'");
							$req->execute();
						}
						if($command['extrafees'] != $_POST['extrafees']){
							$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$command['id']."'");
							$row = $back->fetch();
							$price = intval($_POST['extrafees']) - intval($command['extrafees']);;
							$req = $bdd->prepare("UPDATE factures SET price=(price-".$price.") WHERE code='".$row['facture']."'");
							$req->execute();
						}
					}
					
					$note = "";
					if($_POST['note'] != ""){
						$note = "<b>Client: </b>".sanitize_vars($_POST['note'])."<br />";
						if($_SESSION['type'] == "moderator"){
							$note = "<b>Modérateur: </b>".sanitize_vars($_POST['note'])."<br />";
						}
						elseif($_SESSION['type'] == "dlm"){
							$note = "<b>Livreur: </b>".sanitize_vars($_POST['note'])."<br />";
						}
					}

					$req = $bdd->prepare("UPDATE commands SET code='".sanitize_vars($_POST['code'])."',
															  product='".sanitize_vars(substr($_POST['product'],1))."',
															  qty='".sanitize_vars(substr($_POST['qty'],1))."',
															  dlm='".sanitize_vars($_POST['dlm'])."',
															  client='".sanitize_vars($_POST['client'])."',
															  clientname='".sanitize_vars($_POST['clientname'])."',
															  clientphone='".sanitize_vars($_POST['clientphone'])."',
															  fullname='".sanitize_vars($_POST['fullname'])."',
															  phone='".sanitize_vars($_POST['phone'])."',
															  address='".sanitize_vars($_POST['address'])."',
															  city='".sanitize_vars($_POST['city'])."',
															  price='".sanitize_vars($_POST['price'])."',
															  extrafees='".sanitize_vars($_POST['extrafees'])."',
															  package='".sanitize_vars($_POST['package'])."',
															  note=CONCAT(note,'".$note."'),
															  echange='".sanitize_vars($_POST['change'])."',
															  openpackage='".sanitize_vars($_POST['openpackage'])."',
															  dateupdate='".time()."' WHERE id='".$_POST['id']."'");
					$req->execute();
					if($command['price'] != $_POST['price']){
						$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a changé le prix du commande N° ".$command['code']." de [".$command['price']." DH] à [".$_POST['price']." DH]','".time()."')");
						$req->execute();
					}
					if($command['qty'] != substr($_POST['qty'],1)){
						$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a changé la quantité du commande N° ".$command['code']." de [".$command['qty']."] à [".substr($_POST['qty'],1)."]','".time()."')");
						$req->execute();
					}	
					if($command['dlm'] != $_POST['dlm']){
						$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a changé le livreur du commande N° ".$command['code']." de [".$command['dlm']."] à [".$_POST['dlm']."]','".time()."')");
						$req->execute();
						if($settings['onesignal'] != ""){
							$back = $bdd->query("SELECT idplayer FROM users WHERE id='".$_POST['dlm']."' AND idplayer<>''");
							if($back->rowCount() > 0){
								$dlm = $back->fetch();
								sendMessage($dlm['idplayer'],$command['code'],"Nouveau commande à traiter","https://servicehl.ma/is-admin",$settings['onesignal']);
							}
						}
					}					
				}
			}
			
			if($_POST['action'] == "changeaddress"){
				$back = $bdd->query("SELECT code,fullname,phone FROM commands WHERE id='".$_POST['id']."'");
				$command = $back->fetch();
				$req = $bdd->prepare("UPDATE commands SET fullname='".sanitize_vars($_POST['fullname'])."',phone='".sanitize_vars($_POST['phone'])."',address='".sanitize_vars($_POST['address'])."',state='Changement adresse',note=CONCAT(note,'<br /><b>Client: </b>Changement adresse de (".$command['fullname']." - ".$command['phone'].") à (".$_POST['fullname']." - ".$_POST['phone'].")') WHERE id='".$_POST['id']."'");
				$req->execute();
				$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','Changement adresse','".$_SESSION['fullname']."','".time()."')");
				$req->execute();
			}

			if($_POST['action'] == "deletecommand"){
				$back = $bdd->query("SELECT code FROM commands WHERE id='".$_POST['id']."'");
				$command = $back->fetch();				
				$req = $bdd->prepare("UPDATE commands SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
				$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a supprimé (corbeille) la commande N° ".$command['code']."','".time()."')");
				$req->execute();
			}
			
			if($_POST['action'] == "restorecommand"){
				$req = $bdd->prepare("UPDATE commands SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deletecommandpermanently"){
				$back = $bdd->query("SELECT code FROM commands WHERE id='".$_POST['id']."'");
				$command = $back->fetch();	
				$req = $bdd->prepare("DELETE FROM commands WHERE id='".$_POST['id']."'");
				$req->execute();
				$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a supprimé la commande N° ".$command['code']."','".time()."')");
				$req->execute();
			}
		
			if($_POST['action'] == "collectthis"){
				if($_SESSION['type'] == "moderator"){
					$back = $bdd->query("SELECT id,code,state,collected FROM commands WHERE code='".$_POST['code']."'");
					if($back->rowCount() != 0){
						$command = $back->fetch();
						if($command['state'] == "Ajouté" AND $command['collected'] == "off"){
							$req = $bdd->prepare("UPDATE commands SET state='En cours',collected='on',dateupdate='".time()."' WHERE id='".$command['id']."'");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,dateadd) VALUES ('0','".$_POST['code']."','En cours','".time()."')");
							$req->execute();
						}
						elseif($command['state'] != "Ajouté"){
							echo "La commande est déja ramassé est a l'état suivante: ".$command['state'];
						}
					}
					else{
						echo "La commande n'exist pas";
					}
				}
			}
			
			if($_POST['action'] == "loadramassage"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-command"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-command"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
							?>
						<td>Client <i class="fa fa-sort" data-sort="client"></i></td>
							<?php
						}
						?>
						<td>Destinataire <i class="fa fa-sort" data-sort="fullname"></i></td>
						<?php
						if($settings['reseller'] == "1"){
						?>
						<td>Vendeur</td>
						<?php
						}
						?>
						<td>Produits</td>
						<td>Prix <i class="fa fa-sort" data-sort="price"></i></td>
						<td>Etat</td>
						<td>Note</td>
						<td>Date <i class="fa fa-sort" data-sort="dateadd"></i></td>
						<?php
						if($_SESSION['type'] == "moderator" OR $_SESSION['type'] == "dlm"){
							?>
						<td><?php echo $_POST['phase']=="confirmation"?"Valider":"Ramasser";?></td>
							<?php
						}
						?>						
						<td>Action</td>
					</tr>
					<?php
					if($_SESSION['type'] == "dlm"){
						$back = $bdd->query("SELECT GROUP_CONCAT(city SEPARATOR ',') AS cities FROM shippingfees WHERE dlm='".$_SESSION['id']."' AND trash='1'");
						$row = $back->fetch();
						$userwhere = " AND client IN(SELECT id FROM users WHERE city IN('".str_replace(",","','",addslashes($row['cities']))."'))";
					}
					$req = "SELECT * FROM commands WHERE trash='".$_POST['state']."' AND collected='off'".$userwhere;
					if($_POST['keyword'] != ""){
						$req .= " AND (code LIKE '%".$_POST['keyword']."%' OR fullname LIKE '%".$_POST['keyword']."%' OR phone LIKE '%".$_POST['keyword']."%' OR address LIKE '%".$_POST['keyword']."%' OR city LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['client'] != ""){
						$req .= " AND client='".$_POST['client']."'";
					}
					if($_POST['worker'] != ""){
						$req .= " AND worker='".$_POST['worker']."'";
					}
					if($_POST['city'] != ""){
						$req .= " AND city IN('".str_replace(",","','",$_POST['city'])."')";
					}
					if($_POST['product'] != ""){
						$req .= " AND (product='".$_POST['product']."' OR product LIKE '".$_POST['product'].",%' OR product LIKE '%,".$_POST['product'].",%' OR product LIKE '%,".$_POST['product']."')";
					}
					if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
						$req .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					if($_POST['phase'] != ""){
						$req .= " AND phase='".$_POST['phase']."'";
					}
					if($_POST['statee'] != ""){
						$req .= " AND state IN ('".str_replace(",","','",sanitize_vars($_POST['statee']))."')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back3 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".$row['state']."'");
						$state = $back1->fetch();
						$background = "";
						if($parametres['rowcolor'] == "1"){
							$background = "background:rgba(".hexdec(substr($state['color'],1,2)).",".hexdec(substr($state['color'],3,2)).",".hexdec(substr($state['color'],5,2)).",0.2)";
						}
						?>
					<tr style="<?php echo $background;?>">
						<td><label><input type="checkbox" name="command" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
							$back1 = $bdd->query("SELECT fullname,store FROM users WHERE id='".$row['client']."'");
							$row1 = $back1->fetch();
							?>
						<td><span><?php echo $row1['store']." (".$row1['fullname'].")";?></span></td>
							<?php
						}
						?>
						<td>
							<span><b><?php echo $row['code'];?></b><br />
							<?php echo $row['fullname'];?><br />
							<?php echo $row['phone'];?><br />
							<?php echo $row['address'];?><br />
							<?php echo $row['city'];?></span>
						</td>
						<?php
						if($settings['reseller'] == "1"){
						?>
						<td>
							<span><b><?php echo $row['clientname'];?></b><br />
							<?php echo $row['clientphone'];?></span>
						</td>
						<?php
						}
						?>
						<td>
							<?php
							if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])){
								$i = 0;
								$qtys = explode(",",$row['qty']);
								$back1 = $bdd->query("SELECT title FROM stocks WHERE id IN(".$row['product'].") ORDER BY FIELD(id,".$row['product'].")");
								while($row1 = $back1->fetch()){
									?>
							<span><?php echo $row1['title']." x ".$qtys[$i];?></span>
									<?php
									$i++;
								}
								if($row['product'] != "0"){
									?>
							<strong>Depuis Stock</strong>
									<?php
								}
							}
							else{
								$qtys = explode(",",$row['qty']);
								$products = explode(",",$row['product']);
								for($i=0;$i<count($products);$i++){
								?>
							<span><?php echo $products[$i];?></span>
								<?php
								}
							}
							?>
						</td>
						<td><span><?php echo $row['price'];?> <?php echo $settings['currency'];?></span></td>
						<td>
							<?php
							$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".addslashes($row['state'])."'");
							$row1 = $back1->fetch();
							if($_SESSION['type'] == "moderator" OR $_SESSION['type'] == "worker"){
								?>
							<span class="lx-edit-state lx-open-popup"
								data-id="<?php echo $row['id'];?>" 
								data-state="<?php echo $row['state'];?>"
								data-datereported="<?php echo ($row['datereported']!=""?date('d/m/Y',$row['datereported']):'');?>" 
								data-note="" data-title="editstate"
								style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
								<?php									
							}
							else{
								?>
							<span class="lx-edit-state lx-open-popup" style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
								<?php								
							}
							if($row['datereported'] != ""){
								?>
							<span><?php echo gmdate("d/m/Y",$row['datereported']);?></span>
								<?php
							}
							?>
						</td>
						<td>
							<?php
							$back1 = $bdd->query("SELECT fullname FROM users WHERE id='".$row['worker']."'");
							if($back1->rowCount() > 0){
								$row1 = $back1->fetch();
								?>
							<span><strong>Agent: </strong><?php echo $row1['fullname'];?></span>
								<?php
							}
							?>
							<span><?php echo $row['note'];?></span>
						</td>
						<td><span><?php echo ($row['dateadd']!=""?gmdate("d/m/Y H:i",$row['dateadd']+3600):"&mdash;");?></span></td>
						<?php
						if($_SESSION['type'] == "moderator" OR $_SESSION['type'] == "dlm"){
							if($_POST['phase'] == "shipping"){
								$class = "";
								if($row['collected'] == "on"){
									$class = " lx-on-off-blue";
								}
								?>
						<td>
							<div class="lx-on-off<?php echo $class;?>" data-state="<?php echo $row['collected'];?>" data-table="commands" data-column="collected" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
						</td>							
								<?php
							}
							else{
								?>
						<td>
								<?php
								if($row['state'] == "Confirmé"){
								?>
							<div class="lx-on-off" data-state="off" data-table="commands" data-column="phases" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
								<?php
								}
								?>
						</td>							
								<?php								
							}
						}
						?>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-command lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-product="<?php echo $row['product'];?>"
								data-fromstock="<?php echo preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])?true:false;?>"
								data-qty="<?php echo $row['qty'];?>" 
								data-client="<?php echo $row['client'];?>"
								data-clientname="<?php echo $row['clientname'];?>"
								data-clientphone="<?php echo $row['clientphone'];?>"
								data-code="<?php echo $row['code'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-phone="<?php echo $row['phone'];?>"
								data-address="<?php echo $row['address'];?>"
								data-city="<?php echo $row['city'];?>"
								data-price="<?php echo $row['price'];?>"
								data-note=""
								data-change="<?php echo $row['echange'];?>"
								data-openpackage="<?php echo $row['openpackage'];?>" data-title="command"><i class="fa fa-edit"></i></a><!--
								--><a href="javascript:;" class="lx-delete lx-print-ticket lx-open-popup" data-title="tickets" data-id="<?php echo $row['id'];?>"><i class="fa fa-print"></i></a><!--
								--><a href="javascript:;" class="lx-delete lx-delete-command lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-command" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-command" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back3->rowCount();?>" />
				<?php
				if($back3->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> commande(s) de <?php echo $back3->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back3->rowCount();?> commande(s) de <?php echo $back3->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "grabcommand"){
				$req = "";
				$encours = $bdd->query("SELECT id,phone FROM commands WHERE phase='confirmation' AND worker='".$_SESSION['id']."' AND state='Nouveau' AND trash='1'".$req);
				if($encours->rowCount() == 0){
					$back = $bdd->query("SELECT id,phone FROM commands WHERE phase='confirmation' AND worker='0' AND state='Nouveau' AND trash='1'".$req." ORDER BY dateadd DESC");
					if($back->rowCount() > 0){
						$row = $back->fetch();
						$back = $bdd->query("SELECT id FROM commands WHERE phone='".addslashes($row['phone'])."' AND state='Nouveau' AND trash='1'".$req);
						while($row = $back->fetch()){
							$req = $bdd->prepare("UPDATE commands SET worker='".$_SESSION['id']."' WHERE id='".$row['id']."'");
							$req->execute();							
						}
					}
					else{
						echo "Il y a pas de commandes pour le moment essayer plus tard";
					}
				}
				else{
					echo "Vous avez déja des commandes nouveau à confirmés";
				}
			}

			if($_POST['action'] == "editstate"){
				$commandRequest = $bdd->prepare("SELECT * FROM commands WHERE id = ?");
				$commandRequest->execute([$_POST['id']]);
				$command = $commandRequest->fetch();
				if (!$command) {
					http_response_code(404);
					echo 'Commande introuvable';
					exit;
				}

				$stateAgent = 'Modérateur';
				if ($_SESSION['type'] == 'client') {
					$stateAgent = 'Client';
					if ((string) $command['client'] !== (string) $_SESSION['id']) {
						http_response_code(403);
						echo 'Accès interdit';
						exit;
					}
				}
				elseif ($_SESSION['type'] == 'worker') {
					$stateAgent = 'Agent de confirmation';
					if ((string) $command['worker'] !== (string) $_SESSION['id']) {
						http_response_code(403);
						echo 'Accès interdit';
						exit;
					}
				}
				elseif ($_SESSION['type'] == 'dlm' || $_SESSION['type'] == 'subdlm') {
					$stateAgent = 'Livreur';
				}

				$allowedStateRequest = $bdd->prepare("SELECT id FROM trackingstates WHERE state = ? AND trash = '1' AND agent LIKE ?");
				$allowedStateRequest->execute([$_POST['state'], '%' . $stateAgent . '%']);
				if (!$allowedStateRequest->fetch()) {
					http_response_code(403);
					echo 'Cet état n’est pas autorisé pour votre compte';
					exit;
				}

				if($_SESSION['type'] == "dlm" AND $_SESSION['roles'] == "Affichage commun"){
					$req = $bdd->prepare("UPDATE commands SET dlm='".sanitize_vars($_SESSION['id'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
				$req = "";
				if($_POST['state'] == "Ajouté"){
					$req = ",collected='off'";
				}
				
				$note = "";
				if($_POST['note'] != ""){
					$note = "<b>Client: </b>".sanitize_vars($_POST['note'])."<br />";
					if($_SESSION['type'] == "moderator"){
						$note = "<b>Modérateur: </b>".sanitize_vars($_POST['note'])."<br />";
					}
					elseif($_SESSION['type'] == "dlm"){
						$note = "<b>Livreur: </b>".sanitize_vars($_POST['note'])."<br />";
					}
				}
					
				$confirmationfees = 0;
				if($command['confirmed'] == "1"){
					$confirmationfees = $settings['confirmationfees'];
				}
					
				// A report date is optional.  Sending an empty string to an integer
				// column causes MariaDB (in strict mode) to reject the whole update.
				$reportedAt = null;
				if ($_POST['datereported'] !== '') {
					$reportedAt = strtotime(str_replace("/", "-", $_POST['datereported']));
				}
				$req = $bdd->prepare("UPDATE commands SET treated='off', state=:state".$req.", datereported=:datereported, note=CONCAT(note,:note), dateupdate=:dateupdate WHERE id=:id");
				$req->execute([
					':state' => sanitize_vars($_POST['state']),
					':datereported' => $reportedAt,
					':note' => $note,
					':dateupdate' => time(),
					':id' => $_POST['id'],
				]);
				$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".sanitize_vars($_POST['state'])."','".$_SESSION['fullname']."','".time()."')");
				$req->execute();
				if($_POST['state'] == "Livré" AND $command['state'] != "Livré"){
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
						$price = $command['price'] - $city['deliveredfees'] - intval($package['price']) - $confirmationfees;
						$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$_POST['id']."')");
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
						$price = $command['price'] - $city['deliveredfees'] - intval($package['price']) - $confirmationfees;						
						$rand = '';
						do{
							$rand = 'FCT-'.gmdate('dmY').'-'.random();
							$back2 = $bdd->query("SELECT id FROM factures WHERE code='".$rand."'");
						}
						while($back2->rowCount() != 0);
						$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','0','".$command['client']."','1','".$price."','0',' ','off','off','".time()."','','1')");
						$req->execute();
						$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$_POST['id']."')");
						$req->execute();
					}
					
					// Factures DML
					$back = $bdd->query("SELECT code FROM factures WHERE dlm='".$command['dlm']."' AND client='0' AND validated='off' AND trash='1'");
					if($back->rowCount() == 1){
						$row = $back->fetch();
						$back = $bdd->query("SELECT deliveredfees FROM shippingfees WHERE city='".$command['city']."' AND dlm='".$command['dlm']."' AND trash='1'");
						$city = $back->fetch();	
						$price = $command['price'] - $command['extrafees'] - $city['deliveredfees'];
						$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$_POST['id']."')");
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
						$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','".$command['dlm']."','0','1','".$price."','0',' ','off','off','".time()."','','1')");
						$req->execute();
						$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$_POST['id']."')");
						$req->execute();
					}
				}
				elseif($_POST['state'] != "Livré" AND $command['state'] == "Livré"){
					// Factures Clients
					$back = $bdd->query("SELECT deliveredfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
					if($back->rowCount() == 0){
						$back = $bdd->query("SELECT deliveredfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
					}
					$city = $back->fetch();
					$back = $bdd->query("SELECT price FROM packaging WHERE id='".$command['package']."' AND trash='1'");
					$package = $back->fetch();
					$price = $command['price'] - $city['deliveredfees'] - intval($package['price']) - $confirmationfees;						
					$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$_POST['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
					$row = $back->fetch();
					$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$_POST['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
					$req->execute();
					$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands-1),price=(price-".$price.") WHERE code='".$row['facture']."'");
					$req->execute();
					
					// Factures DLM
					$back = $bdd->query("SELECT deliveredfees FROM shippingfees WHERE city='".$command['city']."' AND dlm='".$command['dlm']."' AND trash='1'");
					$city = $back->fetch();
					$price = $command['price'] - $city['deliveredfees'];					
					$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$_POST['id']."' AND facture IN(SELECT code FROM factures WHERE client='0' AND dlm='".$command['dlm']."')");
					$row = $back->fetch();
					$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$_POST['id']."' AND facture IN(SELECT code FROM factures WHERE client='0' AND dlm='".$command['dlm']."')");
					$req->execute();
					$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands-1),price=(price-".$price.") WHERE code='".$row['facture']."'");
					$req->execute();
				}
				
				if($_POST['state'] == "Livré" AND $command['state'] != "Livré" AND $command['state'] != "Annulé" AND $command['state'] != "Refusé"){
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
				elseif($_POST['state'] == "Livré" AND ($command['state'] == "Annulé" OR $command['state'] == "Refusé")){
					$back = $bdd->query("SELECT id FROM statistics WHERE dlm='".$command['dlm']."' AND city='".$command['city']."' AND product='".$command['product']."' AND client='".$command['client']."' AND (dateadd BETWEEN ".strtotime(gmdate("d-m-Y"))." AND ".(strtotime(gmdate("d-m-Y")) + (60*60*24) - 1).")");
					if($back->rowCount() == 1){
						$row = $back->fetch();
						$req = $bdd->prepare("UPDATE statistics SET delivered=(delivered+1),canceled=(canceled-1) WHERE id='".$row['id']."'");
						$req->execute();
					}				
				}
				elseif(($_POST['state'] == "Annulé" OR $_POST['state'] == "Refusé") AND $command['state'] != "Livré" AND $command['state'] != "Annulé" AND $command['state'] != "Refusé"){
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
				elseif(($_POST['state'] == "Annulé" OR $_POST['state'] == "Refusé") AND $command['state'] == "Livré"){
					$back = $bdd->query("SELECT id FROM statistics WHERE dlm='".$command['dlm']."' AND city='".$command['city']."' AND product='".$command['product']."' AND client='".$command['client']."' AND (dateadd BETWEEN ".strtotime(gmdate("d-m-Y"))." AND ".(strtotime(gmdate("d-m-Y")) + (60*60*24) - 1).")");
					if($back->rowCount() == 1){
						$row = $back->fetch();
						$req = $bdd->prepare("UPDATE statistics SET delivered=(delivered-1),canceled=(canceled+1) WHERE id='".$row['id']."'");
						$req->execute();
					}						
				}
				elseif(($_POST['state'] != "Livré" AND $_POST['state'] != "Annulé" AND $_POST['state'] != "Refusé") AND $command['state'] == "Livré"){
					$back = $bdd->query("SELECT id FROM statistics WHERE dlm='".$command['dlm']."' AND city='".$command['city']."' AND product='".$command['product']."' AND client='".$command['client']."' AND (dateadd BETWEEN ".strtotime(gmdate("d-m-Y"))." AND ".(strtotime(gmdate("d-m-Y")) + (60*60*24) - 1).")");
					if($back->rowCount() == 1){
						$row = $back->fetch();
						$req = $bdd->prepare("UPDATE statistics SET delivered=(delivered-1) WHERE id='".$row['id']."'");
						$req->execute();
					}						
				}
				elseif(($_POST['state'] != "Livré" AND $_POST['state'] != "Annulé" AND $_POST['state'] != "Refusé") AND ($command['state'] == "Annulé" OR $command['state'] == "Refusé")){
					$back = $bdd->query("SELECT id FROM statistics WHERE dlm='".$command['dlm']."' AND city='".$command['city']."' AND product='".$command['product']."' AND client='".$command['client']."' AND (dateadd BETWEEN ".strtotime(gmdate("d-m-Y"))." AND ".(strtotime(gmdate("d-m-Y")) + (60*60*24) - 1).")");
					if($back->rowCount() == 1){
						$row = $back->fetch();
						$req = $bdd->prepare("UPDATE statistics SET canceled=(canceled-1) WHERE id='".$row['id']."'");
						$req->execute();
					}						
				}
				
				if($_POST['state'] != $command['state']){
					$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a changé l\'état du commande N° ".$command['code']." de [".$command['state']."] à [".$_POST['state']."]','".time()."')");
					$req->execute();
				}
							
				if($settings['onesignal'] != ""){
					$back = $bdd->query("SELECT idplayer FROM users WHERE id='".$command['client']."' AND idplayer<>''");
					if($back->rowCount() > 0){
						$client = $back->fetch();
						sendMessage($client['idplayer'],$command['code'],$_POST['state'],"https://servicehl.ma/is-admin",$settings['onesignal']);
					}
				}
				
				$back = $bdd->query("SELECT stockout FROM users WHERE id='".$command['client']."'");
				$row = $back->fetch();
				if($row['stockout'] != ""){
					if($row['stockout'] == "onessta"){
						sendToOnessta($row['stockout'],$command['code'],$_POST['state'],$_POST['datereported'],$_POST['note']);
					}
					else{
						sendToStockOUT($row['stockout'],$command['code'],$_POST['state'],$_POST['datereported'],$_POST['note']);
					}
				}
			}		
			
			if($_POST['action'] == "returnthis"){
				if($_SESSION['type'] == "moderator"){
					$back = $bdd->query("SELECT id,state,collected FROM commands WHERE code='".$_POST['code']."'");
					if($back->rowCount() != 0){
						$row = $back->fetch();
						if($row['state'] != "Retourné vers agence casablanca" AND $row['collected'] == "on"){
							$req = $bdd->prepare("UPDATE commands SET state='Retourné vers agence casablanca' WHERE code='".$_POST['code']."'");
							$req->execute();
						}
						elseif($row['state'] == "Retourné vers agence casablanca"){
							echo "Déja traité comme retourné";
						}
					}
					else{
						echo "La commande n'exist pas";
					}
				}
			}		

			if($_POST['action'] == "receivethis"){
				$back = $bdd->query("SELECT id,state FROM commands WHERE code='".$_POST['code']."'");
				if($back->rowCount() != 0){
					$row = $back->fetch();
					$req = $bdd->prepare("UPDATE commands SET dlm='".$_SESSION['id']."' WHERE code='".$_POST['code']."'");
					$req->execute();
				}
				else{
					echo "La commande n'exist pas";
				}
			}				

			if($_POST['action'] == "saveplayer"){
				$req = $bdd->prepare("UPDATE users SET idplayer='".$_POST['player']."' WHERE id='".$_SESSION['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "duplicatecommand"){
				$req = $bdd->prepare("INSERT INTO commands(id,code,product,qty,dlm,client,clientname,clientphone,fullname,phone,address,city,price,state,datereported,note,collected,invoiced,invoiceddlm,archived,echange,openpackage,dateadd,dateupdate,trash) 
										SELECT 0,CONCAT('DUP-',code),product,qty,dlm,client,clientname,clientphone,fullname,phone,address,city,'0','Change',datereported,note,'on','off','off',archived,echange,openpackage,'".time()."','".time()."',trash FROM commands WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadcommands"){
				$extrareq = $userwhere;
				if($_SESSION['type'] == "dlm" AND $_SESSION['roles'] == "Affichage commun"){
					$extrareq = " AND (dlm='".$_SESSION['id']."' OR city IN(SELECT city FROM shippingfees WHERE dlm='".$_SESSION['id']."' AND trash='1'))";
				}
				if($_POST['type'] == "loadcmdencours"){
					$extrareq .= " AND state IN(SELECT state FROM trackingstates WHERE kpi='En cours')";
				}
				if($_POST['type'] == "loadcmddelivered"){
					$extrareq .= " AND state IN(SELECT state FROM trackingstates WHERE kpi='Livrées')";
				}
				if($_POST['type'] == "loadcmdfailed"){
					$extrareq .= " AND state IN(SELECT state FROM trackingstates WHERE kpi='Echouées')";
				}
				?>
				<a href="javascript:;" class="lx-trash lx-trash-command"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-command"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<td>Client <i class="fa fa-sort" data-sort="client"></i></td>
						<?php
						}
						?>
						<td>Destinataire <i class="fa fa-sort" data-sort="fullname"></i></td>
						<?php
						if($settings['reseller'] == "1"){
						?>
						<td>Vendeur</td>
						<?php
						}
						?>
						<td>Produits <i class="fa fa-sort" data-sort="product"></i></td>
						<td>Prix <i class="fa fa-sort" data-sort="price"></i></td>
						<td>Etat <i class="fa fa-sort" data-sort="state"></i></td>
						<?php
						if($_SESSION['type'] == "moderator" AND $_POST['treated'] != "" AND $_POST['datestartupdate'] != "" AND $_POST['dateendupdate'] != ""){
						?>
						<td>Traité</td>
						<?php
						}
						?>
						<td>Facturé <i class="fa fa-sort" data-sort="invoiced"></i></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<td>Facturé livreur</td>
						<?php
						}
						?>
						<td>Note</td>
						<td>Date <i class="fa fa-sort" data-sort="dateupdate"></i></td>
						<td>Action</td>				
					</tr>
					<?php
					// Client-created packages begin as `Nouveau` and are not collected
					// yet. Clients must still be able to see their own submissions.
					$collectedFilter = $_SESSION['type'] === 'client' ? '' : " AND collected='on'";
					$req = "SELECT * FROM commands c WHERE trash='".$_POST['state']."' AND state NOT IN('Ajouté')".$collectedFilter.$extrareq;
					if($_POST['archived'] == "1"){
						$req = "SELECT * FROM commandsarchive c WHERE trash='".$_POST['state']."' AND state NOT IN('Ajouté') AND collected='on'".$extrareq;
					}
					if($_POST['keyword'] != ""){
						$req .= " AND (clientname LIKE '%".$_POST['keyword']."%' OR clientphone LIKE '%".$_POST['keyword']."%' OR code LIKE '%".$_POST['keyword']."%' OR fullname LIKE '%".$_POST['keyword']."%' OR phone LIKE '%".$_POST['keyword']."%' OR address LIKE '%".$_POST['keyword']."%' OR city LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['client'] != ""){
						$req .= " AND client IN(SELECT id FROM users WHERE (fullname IN('".str_replace(",","','",$_POST['client'])."') OR store IN('".str_replace(",","','",$_POST['client'])."')) AND type='client' AND trash='1')";
					}
					if($_POST['worker'] != ""){
						$req .= " AND worker IN(SELECT id FROM users WHERE (fullname IN('".str_replace(",","','",$_POST['worker'])."')) AND type='worker' AND trash='1')";
					}
					if($_POST['dlm'] != ""){
						$req .= " AND (dlm IN(SELECT id FROM users WHERE fullname IN('".str_replace(",","','",$_POST['dlm'])."') AND type='dlm' AND trash='1') OR (city IN(SELECT city FROM shippingfees WHERE dlm IN(SELECT id FROM users WHERE fullname IN('".str_replace(",","','",$_POST['dlm'])."') AND type='dlm' AND trash='1')) AND dlm='0'))";
					}
					if($_POST['sansdlm'] != ""){
						$req .= " AND dlm='".$_POST['sansdlm']."'";
						if($_POST['codes'] != ""){
							$req .= " AND code IN('".str_replace(",","','",$_POST['codes'])."')";
						}
					}	
					if($_POST['subdlm'] != ""){
						$req .= " AND subdlm='".$_POST['subdlm']."'";
					}	
					if($_POST['city'] != ""){
						$req .= " AND city IN('".str_replace(",","','",$_POST['city'])."')";
					}
					if($_POST['product'] != ""){
						$req .= " AND (product='".$_POST['product']."' OR product LIKE '".$_POST['product'].",%' OR product LIKE '%,".$_POST['product'].",%' OR product LIKE '%,".$_POST['product']."')";
					}
					if($_POST['statee'] != ""){
						$req .= " AND state IN ('".str_replace(",","','",sanitize_vars($_POST['statee']))."')";
					}
					if($_POST['invoiced'] != ""){
						$req .= " AND invoiced='".$_POST['invoiced']."'";
					}
					if($_POST['treated'] != ""){
						$req .= " AND treated='".$_POST['treated']."'";
					}
					if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
						$req .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					if($_POST['datestartupdate'] != "" AND $_POST['dateendupdate'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestartupdate']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateendupdate'])) + (60*60*24) - 1;
						$req .= " AND (dateupdate BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					if($_POST['datestartreport'] != "" AND $_POST['dateendreport'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestartreport']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateendreport'])) + (60*60*24) - 1;
						$req .= " AND (datereported BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY CASE WHEN state IN('En cours') THEN 0 ELSE 1 END, dateupdate";
					}
					$req .= " ".$_POST['orderby'];
					$back3 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".addslashes($row['state'])."'");
						$state = $back1->fetch();
						$background = "";
						if($parametres['rowcolor'] == "1"){
							$background = "background:rgba(".hexdec(substr($state['color'],1,2)).",".hexdec(substr($state['color'],3,2)).",".hexdec(substr($state['color'],5,2)).",0.2)";
						}
						?>
					<tr style="<?php echo $background;?>">
						<td><label><input type="checkbox" name="command" value="<?php echo $row['id'];?>" data-code="<?php echo $row['code'];?>" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
							$back1 = $bdd->query("SELECT fullname,store FROM users WHERE id='".$row['client']."' AND type='client'");
							$row1 = $back1->fetch();
							?>
						<td><span><?php echo $row1['store']." (".$row1['fullname'].")";?></span></td>
							<?php
						}
						?>
						<td>
							<span><a href="javascript:;" class="lx-show-history lx-open-popup" style="color:#242424;font-weight:500;white-space:nowrap;" data-id="<?php echo $row['code'];?>" data-title="commandhistory"><?php echo $row['code'];?></a><br />
							<?php echo $row['fullname'];?><br />
							<a href="javascript:;" class="lx-open-popup lx-phone-choice" data-title="phone" data-phone="<?php echo $row['phone'];?>" style="color:#242424;font-weight:500;"><?php echo $row['phone'];?></a>
							<br />
							<?php echo $row['address'];?><br />
							<?php echo $row['city'];?></span>
							<?php
							if($_SESSION['type'] == "moderator" OR $_SESSION['type'] == "client"){
								$back1 = $bdd->query("SELECT fullname,phone FROM users WHERE id='".$row['dlm']."' AND type='dlm'");
								$row1 = $back1->fetch();
								if($row1['fullname'] != "" AND $_SESSION['type'] == "moderator"){
								?>
							<span style="font-weight:500;"><?php echo $row1['fullname']." (".$row1['phone'].")";?></span>
								<?php
								}
								elseif($row1['fullname'] != "" AND $_SESSION['type'] == "client"){
								?>
							<span style="font-weight:500;">Livreur: <?php echo $row1['phone'];?></span>
								<?php									
								}
							}
							if($_SESSION['type'] == "dlm"){
								$back1 = $bdd->query("SELECT * FROM users WHERE id='".$row['subdlm']."' AND type='subdlm'");
								$row1 = $back1->fetch();
								if($row1['fullname'] != ""){
								?>
							<span style="font-weight:500;"><?php echo $row1['fullname']." (".$row1['phone'].")";?></span>
								<?php
								}
								$back1 = $bdd->query("SELECT * FROM users WHERE id='".$row['client']."' AND type='client'");
								$row1 = $back1->fetch();
								if($row1['fullname'] != ""){
								?>
							<span style="font-weight:500;"><?php echo $row1['fullname']." (".$row1['phone'].")";?></span>
								<?php
								}
							}
							?>
						</td>
						<?php
						if($settings['reseller'] == "1"){
						?>
						<td>
							<span><b><?php echo $row['clientname'];?></b><br />
							<?php echo $row['clientphone'];?></span>
						</td>
						<?php
						}
						?>
						<td>
							<?php
							if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])){
								$i = 0;
								$qtys = explode(",",$row['qty']);
								$back1 = $bdd->query("SELECT title FROM stocks WHERE id IN(".$row['product'].") ORDER BY FIELD(id,".$row['product'].")");
								while($row1 = $back1->fetch()){
									?>
							<span><?php echo $row1['title']." x ".$qtys[$i];?></span>
									<?php
									$i++;
								}
								if($row['product'] != "0"){
									?>
							<strong>Depuis Stock</strong>
									<?php
								}
							}
							else{
								$qtys = explode(",",$row['qty']);
								$products = explode(",",$row['product']);
								for($i=0;$i<count($products);$i++){
								?>
							<span><?php echo $products[$i];?></span>
								<?php
								}
							}
							?>
						</td>
						<td style="white-space:nowrap;">
							<span><?php echo $row['price'];?> <?php echo $settings['currency'];?></span>
							<?php
							if($_SESSION['type'] != "dlm"){		
								if($row['package'] != "0"){
									$back1 = $bdd->query("SELECT title,price FROM packaging WHERE id='".$row['package']."' AND trash='1'");
									$row1 = $back1->fetch();
									?>
							<span>Emballage (<?php echo $row1['title'];?>): <?php echo $row1['price'];?> <?php echo $settings['currency'];?></span>
									<?php
								}
							}
							?>
						</td>
						<td>
							<?php
							$back1 = $bdd->query("SELECT id FROM bls WHERE code LIKE 'BL-%' AND done='on' AND (cmds='".$row['id']."' OR cmds LIKE '%,".$row['id'].",%' OR cmds LIKE '%,".$row['id']."' OR cmds LIKE '".$row['id'].",%')");
							$received = $back1->rowCount();
							$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".addslashes($row['state'])."'");
							$row1 = $back1->fetch();
							if($_SESSION['type'] == "moderator"){
								?>
							<span class="lx-edit-state lx-open-popup"
								data-id="<?php echo $row['id'];?>" 
								data-state="<?php echo $row['state'];?>"
								data-datereported="<?php echo ($row['datereported']!=""?date('d/m/Y',$row['datereported']):'');?>" 
								data-note="" data-title="editstate"
								style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
								<?php									
							}
							elseif($row['invoiced'] == "off" AND ($_SESSION['type'] == "dlm" OR $_SESSION['type'] == "subdlm") AND $row['state'] != "Préparation du retour" AND $row['state'] != "Livré"){
								?>
							<span class="lx-edit-state lx-open-popup" 
								data-id="<?php echo $row['id'];?>" 
								data-state="<?php echo $row['state'];?>"
								data-datereported="<?php echo ($row['datereported']!=""?date('d/m/Y',$row['datereported']):'');?>" 
								data-note="" data-title="editstate"
								style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
								<?php									
							}
							elseif($row['invoiced'] == "off" AND ($_SESSION['type'] == "client" OR $_SESSION['type'] == "worker") AND !preg_match("#Livré|Retourné|Refusé|Annulé|Retourné vers agence Laayoune|Préparation du retour|Préparation du retour|Retourné vers agence Guelmim|Retourné vers agence casablanca#",$row['state'])){
								?>
							<span class="lx-edit-state lx-open-popup" 
								data-id="<?php echo $row['id'];?>" 
								data-state="<?php echo $row['state'];?>"
								data-datereported="<?php echo ($row['datereported']!=""?date('d/m/Y',$row['datereported']):'');?>" 
								data-note="" data-title="editstate"
								style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
								<?php									
							}
							else{
								?>
							<span class="lx-edit-state lx-open-popup" style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
								<?php								
							}
							if($row['datereported'] != ""){
								?>
							<span><?php echo gmdate("d/m/Y",$row['datereported']);?></span>
								<?php
							}
							?>
						</td>
						<?php
						if($_SESSION['type'] == "moderator" AND $_POST['treated'] != "" AND $_POST['datestartupdate'] != "" AND $_POST['dateendupdate'] != ""){
						?>
						<td>
							<?php
							$class = '';
							if($row['treated'] == "on"){
								$class = ' lx-on-off-blue';
							}
							?>
							<div class="lx-on-off<?php echo $class?>" data-state="<?php echo $row['treated']?>" data-table="commands" data-column="treated" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>						
						</td>
						<?php
						}
						?>
						<td>
							<?php
							if(preg_match("#^(Livré|Refusé|Retour reçu agence casablanca)$#",$row['state'])){
								$back1 = $bdd->query("SELECT received FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND command='".$row['id']."' AND client<>'0' AND dlm='0'");
								if($_SESSION['type'] == "dlm"){
									$back1 = $bdd->query("SELECT received FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND command='".$row['id']."' AND dlm<>'0' AND client='0'");
								}
								$row1 = $back1->fetch();
								if($row1['received'] == 'off'){
									?>
						<span style="display:inline-block;padding:2px 5px;font-weight:500;background:orange;color:#FFFFFF;border-radius:5px;">Non</span>
									<?php
								}
								else{
									?>
						<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:5px;">Oui</span>
									<?php
								}
							}
							?>
						</td>
						<?php
						if($_SESSION['type'] == "moderator"){
							?>
						<td>
							<?php
							if(preg_match("#^(Livré|Refusé)$#",$row['state'])){
								$back1 = $bdd->query("SELECT received FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND command='".$row['id']."' AND dlm<>'0' AND client='0'");
								$row1 = $back1->fetch();
								if($row1['received'] == 'off'){
									?>
						<span style="display:inline-block;padding:2px 5px;font-weight:500;background:orange;color:#FFFFFF;border-radius:5px;">Non</span>
									<?php
								}
								else{
									?>
						<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:5px;">Oui</span>
									<?php
								}
							}
							?>
						</td>						
							<?php
						}
						?>
						<td>
							<span><?php echo ($row['note']!=""?$row['note']:"&mdash;");?></span>
						</td>
						<td>
							<span><b>Date ajout:</b><br /><?php echo ($row['dateadd']!=""?gmdate("d/m/Y H:i",$row['dateadd']+3600):"&mdash;");?></span>
							<span><b>Date mise à jour:</b><br /><?php echo ($row['dateupdate']!=""?gmdate("d/m/Y H:i",$row['dateupdate']+3600):"&mdash;");?></span>
						</td>
						<td>
							<?php
							if($_SESSION['type'] == "moderator"){
								if($_POST['state'] == 1){
									?>
							<a href="javascript:;" class="lx-edit lx-edit-command lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-fromstock="<?php echo preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])?true:false;?>"
								data-product="<?php echo $row['product'];?>"
								data-qty="<?php echo $row['qty'];?>" 
								data-dlm="<?php echo $row['dlm'];?>"
								data-client="<?php echo $row['client'];?>"
								data-code="<?php echo $row['code'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-phone="<?php echo $row['phone'];?>"
								data-address="<?php echo $row['address'];?>"
								data-city="<?php echo $row['city'];?>"
								data-price="<?php echo $row['price'];?>"
								data-extrafees="<?php echo $row['extrafees'];?>"
								data-package="<?php echo $row['package'];?>"
								data-note=""
								data-change="<?php echo $row['echange'];?>"
								data-openpackage="<?php echo $row['openpackage'];?>" data-title="command"><i class="fa fa-edit"></i></a>
								<a href="javascript:;" class="lx-edit lx-duplicatecommand lx-open-popup" data-title="duplicatecommand" data-id="<?php echo $row['id'];?>"><i class="fa fa-clone"></i></a>
								<a href="javascript:;" class="lx-delete lx-print-ticket lx-open-popup" data-title="tickets" data-id="<?php echo $row['id'];?>"><i class="fa fa-print"></i></a>
								<a href="javascript:;" class="lx-delete lx-open-sms-model lx-open-popup"
								data-id="<?php echo $row['id'];?>"
								data-phone="<?php echo $row['phone'];?>"
								data-message="" data-title="smsmodel" ><i class="fa fa-paper-plane"></i></a><!--
								--><a href="javascript:;" class="lx-delete lx-delete-command lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
									<?php
								}
								else{
									?>
							<a href="javascript:;" class="lx-edit lx-restore-command" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-command" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
									<?php
								}
							}
							elseif($_SESSION['type'] == "dlm"){
								if($_POST['state'] == 1){
									?>
							<a href="javascript:;" class="lx-edit lx-edit-command lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-fromstock="<?php echo preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])?true:false;?>"
								data-product="<?php echo $row['product'];?>"
								data-qty="<?php echo $row['qty'];?>" 
								data-dlm="<?php echo $row['dlm'];?>"
								data-client="<?php echo $row['client'];?>"
								data-code="<?php echo $row['code'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-phone="<?php echo $row['phone'];?>"
								data-address="<?php echo $row['address'];?>"
								data-city="<?php echo $row['city'];?>"
								data-price="<?php echo $row['price'];?>"
								data-extrafees="<?php echo $row['extrafees'];?>"
								data-package="<?php echo $row['package'];?>"
								data-note=""
								data-change="<?php echo $row['echange'];?>"
								data-openpackage="<?php echo $row['openpackage'];?>" data-title="command"><i class="fa fa-edit"></i></a>
								<a href="javascript:;" class="lx-delete lx-print-ticket lx-open-popup" data-title="tickets" data-id="<?php echo $row['id'];?>"><i class="fa fa-print"></i></a>
								<a href="javascript:;" class="lx-edit lx-duplicatecommand lx-open-popup" data-title="duplicatecommand" data-id="<?php echo $row['id'];?>"><i class="fa fa-clone"></i></a>
									<?php
								}
							}
							elseif(($_SESSION['type'] == "client" OR $_SESSION['type'] == "worker") AND $row['state'] != "Livré" AND $row['invoiced'] != "on"){
								?>
								<a href="javascript:;" class="lx-edit lx-change-address lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-phone="<?php echo $row['phone'];?>"
								data-address="<?php echo $row['address'];?>" data-title="changeaddress"><i class="fa fa-user-friends"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back3->rowCount();?>" />
				<?php
				if($back3->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> commande(s) de <?php echo $back3->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back3->rowCount();?> commande(s) de <?php echo $back3->rowCount();?></p>
				<?php			
				}
			}
			
			if($_POST['action'] == "assigncommand"){
				$req = $bdd->prepare("UPDATE commands SET dlm='".$_POST['dlm']."',collected='on',state='En cours' WHERE code='".$_POST['code']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadbs"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-command"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-command"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Client <i class="fa fa-sort" data-sort="client"></i></td>
						<td>Destinataire <i class="fa fa-sort" data-sort="fullname"></i></td>
						<td>Produits <i class="fa fa-sort" data-sort="product"></i></td>
						<td>Prix <i class="fa fa-sort" data-sort="price"></i></td>
						<td>Etat <i class="fa fa-sort" data-sort="state"></i></td>
						<td>Note</td>
						<td>Date <i class="fa fa-sort" data-sort="dateupdate"></i></td>
					</tr>
					<?php
					$req = "SELECT * FROM commands WHERE trash='1' AND code='".$_POST['keyword']."'";
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".addslashes($row['state'])."'");
						$state = $back1->fetch();
						$background = "";
						if($parametres['rowcolor'] == "1"){
							$background = "background:rgba(".hexdec(substr($state['color'],1,2)).",".hexdec(substr($state['color'],3,2)).",".hexdec(substr($state['color'],5,2)).",0.2)";
						}
						?>
					<tr style="<?php echo $background;?>">
						<td><label><input type="checkbox" name="command" value="<?php echo $row['id'];?>" data-code="<?php echo $row['code'];?>" /><del class="checkmark"></del></label></td>
						<?php
						$back1 = $bdd->query("SELECT fullname,store FROM users WHERE id='".$row['client']."' AND type='client'");
						$row1 = $back1->fetch();
						?>
						<td><span><?php echo $row1['store']." (".$row1['fullname'].")";?></span></td>
						<td>
							<span><a href="javascript:;" class="lx-show-history lx-open-popup" style="color:#242424;font-weight:500;white-space:nowrap;" data-id="<?php echo $row['code'];?>" data-title="commandhistory"><?php echo $row['code'];?></a><br />
							<?php echo $row['fullname'];?><br />
							<a href="javascript:;" class="lx-open-popup lx-phone-choice" data-title="phone" data-phone="<?php echo $row['phone'];?>" style="color:#242424;font-weight:500;"><?php echo $row['phone'];?></a>
							<br />
							<?php echo $row['address'];?><br />
							<?php echo $row['city'];?></span>
							<?php
							if($_SESSION['type'] == "moderator"){
								$back1 = $bdd->query("SELECT fullname,phone FROM users WHERE id='".$row['dlm']."'");
								$row1 = $back1->fetch();
								if($row1['fullname'] != "" AND $_SESSION['type'] == "moderator"){
								?>
							<span style="font-weight:500;"><?php echo $row1['fullname']." (".$row1['phone'].")";?></span>
								<?php
								}
							}
							?>
						</td>
						<td>
							<?php
							if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row['product'])){
								$i = 0;
								$qtys = explode(",",$row['qty']);
								$back1 = $bdd->query("SELECT title FROM stocks WHERE id IN(".$row['product'].") ORDER BY FIELD(id,".$row['product'].")");
								while($row1 = $back1->fetch()){
									?>
							<span><?php echo $row1['title']." x ".$qtys[$i];?></span>
									<?php
									$i++;
								}
								if($row['product'] != "0"){
									?>
							<strong>Depuis Stock</strong>
									<?php
								}
							}
							else{
								$qtys = explode(",",$row['qty']);
								$products = explode(",",$row['product']);
								for($i=0;$i<count($products);$i++){
								?>
							<span><?php echo $products[$i];?></span>
								<?php
								}
							}
							?>
						</td>
						<td style="white-space:nowrap;">
							<span><?php echo $row['price'];?> <?php echo $settings['currency'];?></span>
							<?php
							if($_SESSION['type'] != "dlm"){		
								if($row['package'] != "0"){
									$back1 = $bdd->query("SELECT title,price FROM packaging WHERE id='".$row['package']."' AND trash='1'");
									$row1 = $back1->fetch();
									?>
							<span>Emballage (<?php echo $row1['title'];?>): <?php echo $row1['price'];?> <?php echo $settings['currency'];?></span>
									<?php
								}
							}
							?>
						</td>
						<td>
							<?php
							$back1 = $bdd->query("SELECT id FROM bls WHERE code LIKE 'BL-%' AND done='on' AND (cmds='".$row['id']."' OR cmds LIKE '%,".$row['id'].",%' OR cmds LIKE '%,".$row['id']."' OR cmds LIKE '".$row['id'].",%')");
							$received = $back1->rowCount();
							$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".addslashes($row['state'])."'");
							$row1 = $back1->fetch();
							if($row['invoiced'] == "off" OR preg_match("#Refusé#",$row['state'])){
								if($_SESSION['type'] == "moderator"){
									?>
							<span class="lx-edit-state lx-open-popup"
								data-id="<?php echo $row['id'];?>" 
								data-state="<?php echo $row['state'];?>"
								data-datereported="<?php echo ($row['datereported']!=""?date('d/m/Y',$row['datereported']):'');?>" 
								data-note="" data-title="editstate"
								style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
									<?php									
								}
								elseif(($_SESSION['type'] == "dlm" OR $_SESSION['type'] == "subdlm") AND $row['state'] != "Préparation du retour" AND ($row['state'] != "Livré" OR $settings['appname'] == "Awid Livraison") AND ($received>0 OR $settings['appname'] != "Nhanik")){
									?>
							<span class="lx-edit-state lx-open-popup" 
								data-id="<?php echo $row['id'];?>" 
								data-state="<?php echo $row['state'];?>"
								data-datereported="<?php echo ($row['datereported']!=""?date('d/m/Y',$row['datereported']):'');?>" 
								data-note="" data-title="editstate"
								style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
									<?php									
								}
								elseif($_SESSION['type'] == "client" AND !preg_match("#Livré|Retourné|Retourné vers agence Laayoune|Préparation du retour|Préparation du retour','Retourné vers agence Guelmim#",$row['state'])){
									?>
							<span class="lx-edit-state lx-open-popup" 
								data-id="<?php echo $row['id'];?>" 
								data-state="<?php echo $row['state'];?>"
								data-datereported="<?php echo ($row['datereported']!=""?date('d/m/Y',$row['datereported']):'');?>" 
								data-note="" data-title="editstate"
								style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
									<?php									
								}
								else{
									?>
							<span class="lx-edit-state lx-open-popup" style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
									<?php
								}
							}
							else{
								?>
							<span class="lx-edit-state lx-open-popup" style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></span>
								<?php								
							}
							if($row['datereported'] != ""){
								?>
							<span><?php echo gmdate("d/m/Y",$row['datereported']);?></span>
								<?php
							}
							?>
						</td>
						<td>
							<span><?php echo ($row['note']!=""?$row['note']:"&mdash;");?></span>
						</td>
						<td>
							<span><b>Date ajout:</b><br /><?php echo ($row['dateadd']!=""?gmdate("d/m/Y H:i",$row['dateadd']+3600):"&mdash;");?></span>
							<span><b>Date mise à jour:</b><br /><?php echo ($row['dateupdate']!=""?gmdate("d/m/Y H:i",$row['dateupdate']+3600):"&mdash;");?></span>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<?php
			}
			
			if($_POST['action'] == "showcommandhistory"){
				$back = $bdd->query("SELECT code,fullname,phone,address,city FROM commands WHERE code='".$_POST['id']."'");
				$row = $back->fetch();
				?>
				<div class="lx-command-history">
					<p><?php echo $row['fullname'];?> (<?php echo $row['phone'];?>)</p>
					<p><?php echo $row['address'];?> <?php echo $row['city'];?></p>
					<ul>
					<?php
					$i = 1;
					$back = $bdd->query("SELECT state,agent,dateadd FROM commandshistory WHERE command='".$row['code']."' ORDER BY dateadd");
					while($row = $back->fetch()){
						?>					
						<li>
							<?php
							$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".$row['state']."'");
							$state = $back1->fetch();
							?>
							<span>
							<?php
							if($i%2==0){
							?>
							<del style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $state['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></del>
							<?php
							echo "<br />".$row['agent'];
							}
							?>
							</span>
							<ins><?php echo gmdate("d/m/Y H:i",$row['dateadd']+3600);?></ins>
							<?php
							$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".$row['state']."'");
							$state = $back1->fetch();
							?>
							<span>
							<?php
							if($i%2!=0){
							?>
							<del style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $state['color'];?>;color:#FFFFFF;border-radius:5px;cursor:pointer;"><?php echo $row['state'];?></del>
							<?php
							echo "<br />".$row['agent'];
							}
							?>
							</span>
						</li>
						<?php
						$i++;
					}
					?>
					</ul>				
				</div>
				<?php
			}

			if($_POST['action'] == "deletebl"){
				$req = $bdd->prepare("DELETE FROM bls WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "addtobr"){
				$back = $bdd->query("SELECT id FROM commands WHERE code='".$_POST['code']."' AND client='".$_POST['client']."'");
				if($back->rowCount() > 0){
					$command = $back->fetch();
					$back = $bdd->query("SELECT * FROM bls WHERE code LIKE 'BR%' AND client='".$_POST['client']."' AND done='off' AND dateadd > ".strtotime(date("d-m-Y")));
					if($back->rowCount() > 0){
						$row = $back->fetch();
						$req = $bdd->prepare("UPDATE bls SET cmds=CONCAT(cmds,',','".$command['id']."') WHERE id='".$row['id']."'");
						$req->execute();						
					}
					else{
						$bl = 'BR-'.date('dmY').'-'.date('His');
						$req = $bdd->prepare("INSERT INTO bls(id,dlm,client,code,cmds,done,dateadd,trash) VALUES('0','0','".$_POST['client']."','".$bl."','0,".$command['id']."','off','".time()."','1')");
						$req->execute();					
					}		
				}
				else{
					echo "Colis n'appartien pas à ce client";
				}
			}
			
			if($_POST['action'] == "addtobl"){
				$back = $bdd->query("SELECT id FROM commands WHERE code='".$_POST['code']."' AND city IN(SELECT city FROM shippingfees WHERE dlm='".$_POST['dlm']."')");
				if($back->rowCount() > 0){
					$command = $back->fetch();
					$back = $bdd->query("SELECT * FROM bls WHERE code LIKE 'BL%' AND dlm='".$_POST['dlm']."' AND done='off' AND dateadd > ".strtotime(date("d-m-Y")));
					if($back->rowCount() > 0){
						$row = $back->fetch();
						$req = $bdd->prepare("UPDATE bls SET cmds=CONCAT(cmds,',','".$command['id']."') WHERE id='".$row['id']."'");
						$req->execute();						
					}
					else{
						$bl = 'BL-'.date('dmY').'-'.date('His');
						$req = $bdd->prepare("INSERT INTO bls(id,dlm,client,code,cmds,done,dateadd,trash) VALUES('0','".$_POST['dlm']."','0','".$bl."','0,".$command['id']."','off','".time()."','1')");
						$req->execute();					
					}
					$req = $bdd->prepare("UPDATE commands SET dlm='".$_POST['dlm']."',collected='on',state='Expédié' WHERE id='".$command['id']."'");
					$req->execute();
				}
				else{
					echo "Colis ne peut pas être ajouter à ce livreur";
				}
			}

			if($_POST['action'] == "loadbls"){
				$ortype = $_POST['type'];
				if($_POST['type'] == "BRA"){
					$_POST['type'] = "BL";
				}
				$req = " AND code LIKE '".$_POST['type']."-%'";
				if($_SESSION['type'] == "moderator"){
					if(($_POST['type'] == "BL" OR $_POST['type'] == "BRL") AND $ortype != "BRA"){
						$req .= " AND client='0'";
					}
					else{
						$req .= " AND dlm='0'";
					}
				}
				if($_SESSION['type'] == "client"){
					$req .= " AND dlm='0'";
				}	
				if($_SESSION['type'] == "dlm"){
					$req .= " AND client='0'";
				}	
				?>
				<a href="javascript:;" class="lx-trash lx-trash-bl"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-bl"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Code <i class="fa fa-sort" data-sort="product"></i></td>
						<?php
						if($_SESSION['type'] == "moderator"){
							if(($_POST['type'] == "BL" OR $_POST['type'] == "BRL") AND $ortype != "BRA"){
								?>
						<td>Livreur <i class="fa fa-sort" data-sort="dlm"></i></td>
								<?php
							}
							else{
								?>
						<td>Client <i class="fa fa-sort" data-sort="client"></i></td>
								<?php
							}
						}
						?>
						<td>Nb. commandes</td>
						<td>Livrés</td>
						<td>Restants</td>
						<td>Date creation <i class="fa fa-sort" data-sort="datecreated"></i></td>
						<td>Traité</td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM bls WHERE trash='".$_POST['state']."'".$req.$userwhere;
					if($_SESSION['type'] == "moderator"){
						if(($_POST['type'] == "BL" OR $_POST['type'] == "BRL") AND $ortype != "BRA"){
							$req .= " AND client='0'";
						}
						else{
							$req .= " AND dlm='0'";
						}
					}
					if($_SESSION['type'] == "client"){
						$req .= " AND dlm='0'";
					}			
					if($_SESSION['type'] == "dlm"){
						$req .= " AND client='0'";
					}					
					if($_POST['keyword'] != ""){
						$req .= " AND code LIKE '%".$_POST['keyword']."%'";
					}
					if($_POST['dlm'] != ""){
						$req .= " AND dlm='".$_POST['dlm']."'";
					}
					if($_POST['subdlm'] != ""){
						$req .= " AND subdlm='".$_POST['subdlm']."'";
					}					
					if($_POST['client'] != ""){
						$req .= " AND client='".$_POST['client']."'";
					}
					if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
						$req .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						$background = "";
						if($row['done'] == "on"){
							$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='Livré'");
							$state = $back1->fetch();
							$background = "background:rgba(".hexdec(substr($state['color'],1,2)).",".hexdec(substr($state['color'],3,2)).",".hexdec(substr($state['color'],5,2)).",0.1)";
						}
						?>
					<tr style="<?php echo $background;?>">
						<td><label><input type="checkbox" name="bl" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td>
							<?php
							if($_SESSION['type'] == "client"){
								if($_POST['type'] == "BL"){
									?>
							<a href="printblclient.php?tid=<?php echo $row['cmds'];?>&client=<?php echo $row['client'];?>"><?php echo $row['code'];?></a>
									<?php
								}
								else{
									?>
							<a href="printbrmoderator.php?tid=<?php echo $row['cmds'];?>&client=<?php echo $row['client'];?>"><?php echo $row['code'];?></a>
									<?php									
								}
							}
							elseif($_SESSION['type'] == "moderator"){
							if(($_POST['type'] == "BL" OR $_POST['type'] == "BRL") AND $ortype != "BRA"){
									?>
							<a href="printblmoderator.php?tid=<?php echo $row['cmds'];?>&dlm=<?php echo $row['dlm'];?>"><?php echo $row['code'];?></a>
									<?php
								}
								elseif($_POST['type'] == "BR"){
									?>
							<a href="printbrmoderator.php?tid=<?php echo $row['cmds'];?>&client=<?php echo $row['client'];?>"><?php echo $row['code'];?></a>
									<?php									
								}
								elseif($_POST['type'] == "BL" AND $ortype == "BRA"){
									?>
							<a href="printblclient.php?tid=<?php echo $row['cmds'];?>&client=<?php echo $row['client'];?>"><?php echo $row['code'];?></a>
									<?php									
								}									
							}
							elseif($_SESSION['type'] == "dlm"){
								if($_POST['type'] == "BSL"){
									?>
							<a href="printbslmoderator.php?tid=<?php echo $row['cmds'];?>&subdlm=<?php echo $row['subdlm'];?>"><?php echo $row['code'];?></a>
									<?php
								}	
								elseif($_POST['type'] == "BL"){
									?>
							<a href="printblmoderator.php?tid=<?php echo $row['cmds'];?>&dlm=<?php echo $row['dlm'];?>"><?php echo $row['code'];?></a>
									<?php
								}		
								elseif($_POST['type'] == "BRL"){
									?>
							<a href="printbrlmoderator.php?tid=<?php echo $row['cmds'];?>&dlm=<?php echo $row['dlm'];?>"><?php echo $row['code'];?></a>
									<?php									
								}									
							}
							?>
						</td>
						<?php
						if($_SESSION['type'] == "moderator"){
							if(($_POST['type'] == "BL" OR $_POST['type'] == "BRL") AND $ortype != "BRA"){
								$back1 = $bdd->query("SELECT fullname FROM users WHERE id='".$row['dlm']."'");
								$row1 = $back1->fetch();
								?>
						<td><span><?php echo $row1['fullname'];?></span></td>
								<?php
							}
							else{
								$back1 = $bdd->query("SELECT fullname,store FROM users WHERE id='".$row['client']."'");
								$row1 = $back1->fetch();
								?>
						<td><span><?php echo $row1['store']." (".$row1['fullname'].")";?></span></td>
								<?php
							}
						}
						if($_SESSION['type'] == "dlm"){
							if($_POST['type'] == "BSL"){
								$back1 = $bdd->query("SELECT * FROM users WHERE id='".$row['subdlm']."'");
								$row1 = $back1->fetch();
								?>
						<td><span><?php echo $row1['fullname'];?></span></td>
								<?php
							}
						}
						?>
						<td><?php echo count(array_unique(explode(",",$row['cmds'])))-1;?></td>
						<?php
						$back1 = $bdd->query("SELECT id FROM commands WHERE state='Livré' AND id IN(".$row['cmds'].")");
						?>
						<td><?php echo $back1->rowCount();?></td>
						<?php
						$back1 = $bdd->query("SELECT id FROM commands WHERE state<>'Livré' AND id IN(".$row['cmds'].")");
						?>
						<td><?php echo $back1->rowCount();?></td>
						<td><span><?php echo ($row['dateadd']!=""?gmdate("d/m/Y",$row['dateadd']+3600):"&mdash;");?></span></td>
						<td>
							<?php
							if($_SESSION['type'] == "moderator" OR ($_POST['type'] == "BL" AND $ortype != "BRA")){
							$class = '';
							if($row['done'] == "on"){
								$class = ' lx-on-off-blue';
							}
							?>
							<div class="lx-on-off<?php echo $class?>" data-state="<?php echo $row['done']?>" data-table="bls" data-column="done" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
							<?php
							}
							else{
								if($row['done'] == 'off'){
									?>
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:orange;color:#FFFFFF;border-radius:5px;">Non</span>
									<?php
								}
								else{
									?>
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:5px;">Oui</span>
									<?php
								}
							}
							?>						
						</td>
						<td>
							<a href="javascript:;" class="lx-delete lx-print-ticket lx-open-popup" data-title="tickets" data-id="<?php echo $row['cmds'];?>"><i class="fa fa-print"></i></a>
							<a href="javascript:;" class="lx-delete lx-delete-bl lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> bon(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> bon(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}
			
			if($_POST['action'] == "editnotefacture"){
				$req = $bdd->prepare("UPDATE factures SET note='".$_POST['note']."',price='".$_POST['price']."',charges='".$_POST['charges']."',nbcommands='".$_POST['nbcommands']."' WHERE id='".$_POST['id']."'");
				$req->execute();
			}

			if($_POST['action'] == "deletefacture"){
				$req = $bdd->prepare("DELETE FROM factures WHERE id='".$_POST['id']."' AND nbcommands='0' AND price='0'");
				$req->execute();
			}
			
			if($_POST['action'] == "mergefct"){
				$codes = explode(",",$_POST['codes']);
				$maincode = "";
				if(isset($codes[1])){
					$maincode = $codes[1];
				}
				$req = $bdd->prepare("UPDATE factures SET nbcommands='".$_POST['nbcommands']."',price='".$_POST['price']."' WHERE code='".$maincode."'");
				$req->execute();
				for($i=2;$i<count($codes);$i++){
					$req = $bdd->prepare("UPDATE facturesdetails SET facture='".$maincode."' WHERE facture='".$codes[$i]."'");
					$req->execute();
					$req = $bdd->prepare("UPDATE factures SET trash='0' WHERE code='".$codes[$i]."'");
					$req->execute();					
				}
				echo "";
			}

			if(preg_match("#loadfacturecommandstoadd|loadfacturecommandsadded#",$_POST['action'])){
				?>
				<div>
					<table cellpadding="0" cellspacing="0">
						<tr class="lx-first-tr">
							<?php
							if(preg_match("#loadfacturecommandstoadd#",$_POST['action'])){
								?>
							<td><label><input type="checkbox" name="selectall" value="facturecommands" /><del class="checkmark"></del></label></td>
								<?php
							}
							else{
								?>
							<td></td>
								<?php
							}
							?>
							<td>Destinataire <i class="fa fa-sort" data-sort="fullname"></i></td>
							<td>Ville <i class="fa fa-sort" data-sort="city"></i></td>
							<td>Prix <i class="fa fa-sort" data-sort="price"></i></td>
							<td>Etat <i class="fa fa-sort" data-sort="state"></i></td>
							<td>Date ajout <i class="fa fa-sort" data-sort="dateadd"></i></td>
						</tr>
						<?php
						$req = "SELECT * FROM commands WHERE 1=1";
						if(preg_match("#loadfacturecommandstoadd#",$_POST['action'])){
							$req .= " AND 1=2";
						}
						else{
							$req .= " AND id IN(".(($_POST['commands']!="")?$_POST['commands']:0).")";
						}
						$req .= " ORDER BY dateadd DESC";
						$back = $bdd->query($req);
						while($row = $back->fetch()){
							?>
						<tr>
							<?php
							if(preg_match("#loadfacturecommandstoadd#",$_POST['action'])){
								?>
							<td><label><input type="checkbox" name="facturecommands" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
								<?php
							}
							else{
								?>
							<td><a href="javascript:;" class="lx-commands-to-remove" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a></td>
								<?php
							}
							?>
							<td>
								<span><strong><?php echo $row['code'];?></strong><span>
								<span><?php echo $row['fullname'];?><span>
								<span><?php echo $row['phone'];?><span>
								<span><?php echo $row['address'];?><span>
							</td>
							<td><span><?php echo $row['city'];?><span></td>
							<td><span><?php echo $row['price'];?>DH</span></td>
							<?php
							$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".$row['state']."'");
							$row1 = $back1->fetch();
							?>
							<td><span style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:5px;"><?php echo $row1['state'];?></span></td>
							<td><span><?php echo ($row['dateadd']!=""?gmdate("d/m/y H:i",$row['dateadd']+3600):"&mdash;");?></span></td>
						</tr>
							<?php
						}
						?>
					</table>
					<?php
					if($back->rowCount() == 0){
						?>
					<div style="padding:40px 0px;text-align:center;border:1px solid #EEEEEE;">
						<p style="margin:0px;font-weight:400;">Pas de commands pour ajouter/supprimer à cette facture</p>
					</div>
						<?php
					}
					?>
				</div>
				<p><?php echo $back->rowCount();?> commands de <?php echo $back->rowCount();?></p>
				<?php
			}
			
			if($_POST['action'] == "addfacture"){
				$rand = '';
				do{
					$rand = 'FCT-'.date('dmY').'-'.random();
					$back = $bdd->query("SELECT id FROM factures WHERE code='".$rand."'");
				}
				while($back->rowCount() != 0);
				$commands = array();
				if($_POST['commands']){
					$commands = explode(",",$_POST['commands']);
				}
				if($_POST['type'] == "dlm"){
					$req = "";
					if($_POST['id'] == 0){
						$req = $bdd->prepare("INSERT INTO factures(id,code,dlm,client,nbcommands,price,note,validated,received,datecreated,datereceived,trash) VALUES('0','".$rand."','".sanitize_vars($_POST['user'])."','0','".count($commands)."','0','','off','off','".time()."','','1')");
						$req->execute();	
						for($i=0;$i<count($commands);$i++){
							$req = $bdd->prepare("UPDATE commands SET invoiceddlm='on' WHERE id='".$commands[$i]."'");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO facturesdetails VALUES('','".$rand."','".$commands[$i]."')");
							$req->execute();						
						}
						$req = " code='".$rand."'";
					}
					else{
						$back = $bdd->query("SELECT code FROM factures WHERE id='".$_POST['id']."'");
						$row = $back->fetch();
						$facture = $row['code'];
						$back = $bdd->query("SELECT code,command FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND f.id='".$_POST['id']."'");
						while($row = $back->fetch()){
							$req = $bdd->prepare("UPDATE commands SET invoiceddlm='off' WHERE id='".$row['command']."'");
							$req->execute();
							$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$row['command']."' AND facture='".$row['code']."'");
							$req->execute();	
						}
						for($i=0;$i<count($commands);$i++){
							$req = $bdd->prepare("UPDATE commands SET invoiceddlm='on' WHERE id='".$commands[$i]."'");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO facturesdetails VALUES('','".$facture."','".$commands[$i]."')");
							$req->execute();
						}
						$req = $bdd->prepare("UPDATE factures SET nbcommands='".count($commands)."' WHERE id='".$_POST['id']."'");
						$req->execute();
						$req = " id='".$_POST['id']."'";
					}
					$price = 0;
					$back = $bdd->query("SELECT * FROM commands WHERE id IN(".(($_POST['commands']!="")?$_POST['commands']:0).")");
					while($row = $back->fetch()){
						$back1 = $bdd->query("SELECT * FROM shippingfees WHERE city='".$row['city']."' AND dlm='".$row['dlm']."'");
						$city = $back1->fetch();
						if($row['state'] == "Livré"){
							$price += $row['price'] - $city['deliveredfees'];						
						}
						elseif($row['state'] == "Refusé"){
							$price += 0 - $city['refusedfees'];
						}
					}
					$req = $bdd->prepare("UPDATE factures SET price='".$price."' WHERE".$req);
					$req->execute();					
				}
				elseif($_POST['type'] == "client"){
					$req = "";
					if($_POST['id'] == 0){
						$req = $bdd->prepare("INSERT INTO factures(id,code,dlm,client,nbcommands,price,note,validated,received,datecreated,datereceived,trash) VALUES('0','".$rand."','0','".sanitize_vars($_POST['user'])."','".count($commands)."','0','','off','off','".time()."','','1')");
						$req->execute();	
						for($i=0;$i<count($commands);$i++){
							$req = $bdd->prepare("UPDATE commands SET invoiced='on' WHERE id='".$commands[$i]."'");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO facturesdetails VALUES('','".$rand."','".$commands[$i]."')");
							$req->execute();						
						}
						$req = " code='".$rand."'";
					}
					else{
						$back = $bdd->query("SELECT code FROM factures WHERE id='".$_POST['id']."'");
						$row = $back->fetch();
						$facture = $row['code'];
						$back = $bdd->query("SELECT code,command FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND f.id='".$_POST['id']."'");
						while($row = $back->fetch()){
							$req = $bdd->prepare("UPDATE commands SET invoiced='off' WHERE id='".$row['command']."'");
							$req->execute();
							$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$row['command']."' AND facture='".$row['code']."'");
							$req->execute();	
						}
						for($i=0;$i<count($commands);$i++){
							$req = $bdd->prepare("UPDATE commands SET invoiced='on' WHERE id='".$commands[$i]."'");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO facturesdetails VALUES('','".$facture."','".$commands[$i]."')");
							$req->execute();
						}
						$req = $bdd->prepare("UPDATE factures SET nbcommands='".count($commands)."' WHERE id='".$_POST['id']."'");
						$req->execute();
						$req = " id='".$_POST['id']."'";
					}
					$price = 0;
					$back = $bdd->query("SELECT * FROM commands WHERE id IN(".(($_POST['commands']!="")?$_POST['commands']:0).")");
					while($row = $back->fetch()){
						$back1 = $bdd->query("SELECT * FROM clientfees WHERE city='".$row['city']."' AND client='".$row['client']."' AND trash='1'");
						if($back1->rowCount() == 0){
							$back1 = $bdd->query("SELECT * FROM gshippingfees WHERE city='".$row['city']."' AND trash='1'");
						}
						$city = $back1->fetch();
						if($row['state'] == "Livré"){
							$price += $row['price'] - $city['deliveredfees'];						
						}
						elseif($row['state'] == "Refusé"){
							$price += 0 - $city['refusedfees'];
						}
					}
					$req = $bdd->prepare("UPDATE factures SET price='".$price."' WHERE".$req);
					$req->execute();					
				}
			}

			if($_POST['action'] == "loadfactures"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-facture"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-facture"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Code <i class="fa fa-sort" data-sort="code"></i></td>
						<?php
						if($_SESSION['type'] == "moderator"){
							if($_POST['type'] == "client"){
								?>
						<td>Client <i class="fa fa-sort" data-sort="client"></i></td>
								<?php
							}
							if($_POST['type'] == "dlm"){
								?>
						<td>Livreur <i class="fa fa-sort" data-sort="dlm"></i></td>
								<?php
							}
						}
						?>
						<td>Nb. com.</td>
						<td>Montant</td>
						<td>Date création et cloture <i class="fa fa-sort" data-sort="datecreated"></i></td>
						<td>Date versement <i class="fa fa-sort" data-sort="datereceived"></i></td>
						<td>Cloturé <i class="fa fa-sort" data-sort="validated"></i></td>
						<td>Versé <i class="fa fa-sort" data-sort="received"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM factures WHERE trash='".$_POST['state']."'".$userwhere;
					if($_POST['keyword'] != ""){
						$req .= " AND (code LIKE '%".$_POST['keyword']."%' OR code IN(SELECT facture FROM facturesdetails fd,commands c WHERE fd.command=c.id AND c.code LIKE '%".$_POST['keyword']."%'))";
					}
					if($_POST['client'] != ""){
						$req .= " AND client='".$_POST['client']."'";
					}
					if($_POST['dlm'] != ""){
						$req .= " AND dlm='".$_POST['dlm']."'";
					}
					if($_POST['validated'] != ""){
						$req .= " AND validated='".$_POST['validated']."'";
					}
					if($_POST['received'] != ""){
						$req .= " AND received='".$_POST['received']."'";
					}
					if($_POST['type'] == "client"){
						$req .= " AND client<>'0' AND dlm='0'";
					}
					if($_POST['type'] == "dlm"){
						$req .= " AND client='0' AND dlm<>'0'";
					}					
					if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
						$req .= " AND ((datecreated BETWEEN '".$datestart."' AND '".$dateend."') OR (datereceived BETWEEN '".$datestart."' AND '".$dateend."'))";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						$commands = "";
						$back1 = $bdd->query("SELECT command FROM facturesdetails WHERE facture='".$row['code']."'");
						while($row1 = $back1->fetch()){
							$commands .= ",".$row1['command'];
						}
						$commands = substr($commands,1);
						?>
					<tr>
						<td><label><input type="checkbox" name="facture" 
							value="<?php echo $row['id'];?>" 
							data-code="<?php echo $row['code'];?>"
							data-nbcommands="<?php echo $row['nbcommands'];?>"
							data-price="<?php echo $row['price'];?>" /><del class="checkmark"></del></label></td>
						<td><a href="printfacture.php?f=<?php echo $row['code'];?>"><?php echo $row['code'];?></a></td>
						<?php
						if($_SESSION['type'] == "moderator"){
							$back1 = $bdd->query("SELECT fullname,store FROM users WHERE id='".$row[$_POST['type']]."'");
							$row1 = $back1->fetch();
							if($_POST['type'] == "client"){
								?>
						<td><span><?php echo $row1['store']." (".$row1['fullname'].")";?></span></td>
								<?php
							}
							else{
								?>
						<td><span><?php echo $row1['fullname'];?></span></td>
								<?php
							}
						}
						?>
						<td><span><?php echo $row['nbcommands'];?></span></td>
						<td>
							<span><?php echo $row['price']-$row['charges'];?> <?php echo $settings['currency'];?></span>
							<?php
							if($row['note'] != ""){
								?>
							<b><?php echo $row['note'];?></b>
								<?php
							}
							if($row['charges'] != "0"){
								?>
							<b>Charges sup: <?php echo $row['charges'];?> <?php echo $settings['currency'];?></b>
								<?php
							}
							?>
						</td>
						<td><span><?php echo ($row['datecreated']!=""?gmdate("d/m/Y",$row['datecreated']+3600):"&mdash;");?></span></td>
						<td><span><?php echo ($row['datereceived']!=""?gmdate("d/m/Y",$row['datereceived']+3600):"&mdash;");?></span></td>
						<td>
							<?php
							if($_SESSION['type'] == "moderator"){
								$class = '';
								if($row['validated'] == "on"){
									$class = ' lx-on-off-blue';
								}
								?>
							<div class="lx-on-off<?php echo $class?>" data-state="<?php echo $row['validated']?>" data-table="factures" data-column="validated" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
								<?php
							}
							else{
								if($row['validated'] == "on"){
									?>
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:5px;">Oui</span>
									<?php
								}
								else{
									?>
							<div class="lx-on-off" data-state="<?php echo $row['validated']?>" data-table="factures" data-column="validated" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
									<?php
								}								
							}
							?>
						</td>
						<td>
							<?php
							if($_SESSION['type'] == "moderator"){
							$class = '';
							if($row['received'] == "on"){
								$class = ' lx-on-off-blue';
							}
							?>
							<div class="lx-on-off<?php echo $class?>" data-state="<?php echo $row['received']?>" data-table="factures" data-column="received" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
							<?php
							}
							else{
								if($row['received'] == 'off'){
									?>
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:orange;color:#FFFFFF;border-radius:5px;">Non</span>
									<?php
								}
								else{
									?>
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:5px;">Oui</span>
									<?php
								}
							}
							?>
						</td>
						<td>
							<?php
							if($_SESSION['type'] == "moderator"){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-facture lx-open-popup" 
								data-id="<?php echo $row['id'];?>" 
								data-client="<?php echo $row['client'];?>"
								data-dlm="<?php echo $row['dlm'];?>"
								data-commands="<?php echo $commands;?>"
								data-nbcommands="<?php echo $row['nbcommands'];?>" data-title="facture" title="Modifier"><i class="fa fa-edit"></i></a>
								<?php
							}
							?>
							<a href="javascript:;" class="lx-edit lx-edit-note-facture lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-price="<?php echo $row['price'];?>" 
								data-charges="<?php echo $row['charges'];?>" 
								data-nbcommands="<?php echo $row['nbcommands'];?>" 
								data-note="<?php echo $row['note'];?>" data-title="notefacture"><i class="fa fa-comment"></i></a>
							<a href="printfacture.php?f=<?php echo $row['code'];?>"><i class="fa fa-download"></i></a>
							<?php
							if($_SESSION['type'] == "moderator"){
								?>
							<a href="javascript:;" class="lx-delete lx-delete-facture lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> facture(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> facture(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}
			
			if($_POST['action'] == "addexpense"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO expenses(id,cost,dlm,description,dateadd,trash) VALUES ('0','".sanitize_vars($_POST['cost'])."','".sanitize_vars($_POST['dlm'])."','".sanitize_vars($_POST['description'])."','".time()."','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE expenses SET cost='".sanitize_vars($_POST['cost'])."',dlm='".sanitize_vars($_POST['dlm'])."',description='".sanitize_vars($_POST['description'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}

			if($_POST['action'] == "deleteexpense"){
				$req = $bdd->prepare("UPDATE expenses SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoreexpense"){
				$req = $bdd->prepare("UPDATE expenses SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deleteexpensepermanently"){
				$req = $bdd->prepare("DELETE FROM expenses WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadexpenses"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-expense"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-expense"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Montant <i class="fa fa-sort" data-sort="cost"></i></td>
						<td>Description</td>
						<td>Date <i class="fa fa-sort" data-sort="dateadd"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM expenses WHERE trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND (title LIKE '%".$_POST['keyword']."%' OR ref LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['dlm'] != ""){
						$req .= " AND dlm='".$_POST['dlm']."'";
					}
					if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
						$req .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="expense" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['cost'];?> <?php echo $settings['currency'];?></span></td>
						<td><span><?php echo $row['description'];?></span></td>
						<td><span><?php echo date("d/m/Y",$row['dateadd']);?></span></td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-expense lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-cost="<?php echo $row['cost'];?>"
								data-dlm="<?php echo $row['dlm'];?>"
								data-description="<?php echo addslashes($row['description']);?>" data-title="expense"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-expense lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-expense" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-expense" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> dépence(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> dépence(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addreclamation"){
				if($_SESSION['type'] == "moderator"){
					$message = "<p><strong>Modérateur: </strong>".sanitize_vars($_POST['message'])."<ins>".date("d/m/y H:i")."</ins></p>";
				}
				elseif($_SESSION['type'] == "dlm"){
					$message = "<p><strong>Livreur: </strong>".sanitize_vars($_POST['message'])."<ins>".date("d/m/y H:i")."</ins></p>";
				}
				else{
					$message = "<p><strong>".$_SESSION['store'].": </strong>".sanitize_vars($_POST['message'])."<ins>".date("d/m/y H:i")."</ins></p>";
				}
				$req = $bdd->prepare("INSERT INTO reclamations(id,client,subject,service,code,city,message,treated,dateadd,trash) VALUES ('0','".$_SESSION['id']."','".sanitize_vars($_POST['subject'])."','".sanitize_vars($_POST['service'])."','".sanitize_vars($_POST['code'])."','".sanitize_vars($_POST['city'])."','".$message."','off','".time()."','1')");
				$req->execute();
			}
			
			if($_POST['action'] == "addreply"){
				if($_SESSION['type'] == "moderator"){
					$message = "<p><strong>Modérateur: </strong>".sanitize_vars($_POST['message'])."<ins>".date("d/m/y H:i")."</ins></p>";
				}
				elseif($_SESSION['type'] == "dlm"){
					$message = "<p><strong>Livreur: </strong>".sanitize_vars($_POST['message'])."<ins>".date("d/m/y H:i")."</ins></p>";
				}
				else{
					$message = "<p><strong>".$_SESSION['store'].": </strong>".sanitize_vars($_POST['message'])."<ins>".date("d/m/y H:i")."</ins></p>";
				}
				$req = $bdd->prepare("UPDATE reclamations SET message=CONCAT(message,'".$message."') WHERE id='".sanitize_vars($_POST['id'])."'");
				$req->execute();
			}

			if($_POST['action'] == "deletereclamation"){
				$req = $bdd->prepare("UPDATE reclamations SET trash='0' WHERE id='".sanitize_vars($_POST['id'])."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restorereclamation"){
				$req = $bdd->prepare("UPDATE reclamations SET trash='1' WHERE id='".sanitize_vars($_POST['id'])."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deletereclamationpermanently"){
				$req = $bdd->prepare("DELETE FROM reclamations WHERE id='".sanitize_vars($_POST['id'])."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadreclamations"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-reclamation"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-reclamation"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<td>Client <i class="fa fa-sort" data-sort="client"></i></td>
						<?php
						}
						?>
						<td>Objet <i class="fa fa-sort" data-sort="subject"></i></td>
						<td>Service <i class="fa fa-sort" data-sort="service"></i></td>
						<td>Code <i class="fa fa-sort" data-sort="code"></i></td>
						<td>Ville <i class="fa fa-sort" data-sort="city"></i></td>
						<td>Message</td>
						<td>Traité</td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM reclamations WHERE trash='".sanitize_vars($_POST['state'])."'".$userwhere;
					if(sanitize_vars($_POST['keyword']) != ""){
						$req .= " AND (subject LIKE '".sanitize_vars($_POST['keyword'])."' OR code LIKE '%".sanitize_vars($_POST['keyword'])."%' OR city LIKE '%".sanitize_vars($_POST['keyword'])."%' OR message LIKE '%".sanitize_vars($_POST['keyword'])."%')";
					}
					if(sanitize_vars($_POST['client']) != ""){
						$req .= " AND client='".sanitize_vars($_POST['client'])."'";
					}
					if(sanitize_vars($_POST['service']) != ""){
						$req .= " AND service='".sanitize_vars($_POST['service'])."'";
					}
					if(sanitize_vars($_POST['datestart']) != "" AND sanitize_vars($_POST['dateend']) != ""){
						$datestart = strtotime(str_replace("/","-",sanitize_vars($_POST['datestart'])));
						$dateend = strtotime(str_replace("/","-",sanitize_vars($_POST['dateend']))) + (60*60*24) - 1;
						$req .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					if(sanitize_vars($_POST['sortby']) != ""){
						$req .= " ORDER BY ".sanitize_vars($_POST['sortby']);
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".sanitize_vars($_POST['orderby']);
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".sanitize_vars($_POST['start']).",".sanitize_vars($_POST['nbpage']);
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="reclamation" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<?php
						if($_SESSION['type'] == "moderator"){
						$back1 = $bdd->query("SELECT store,fullname FROM users WHERE id='".$row['client']."'");
						$row1 = $back1->fetch();
						?>
						<td><span><?php echo $row1['store']." (".$row1['fullname'].")";?></span></td>
						<?php
						}
						?>
						<td><span><?php echo $row['subject'];?></span></td>
						<td><span><?php echo $row['service'];?></span></td>
						<td><span><?php echo $row['code'];?></span></td>
						<td><span><?php echo $row['city'];?></span></td>
						<td><?php echo $row['message'];?></td>
						<td>
							<?php
							if($_SESSION['type'] == "moderator"){
								$class = "";
								if($row['treated'] == "on"){
									$class = " lx-on-off-blue";
								}
								?>
							<div class="lx-on-off<?php echo $class;?>" data-state="<?php echo $row['treated'];?>" data-table="reclamations" data-column="treated" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>
								<?php
							}
							else{
								if($row['treated'] == "on"){
									?>
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#EC7C27;color:#FFFFFF;border-radius:4px;">Oui</span>
									<?php
								}
								else{
									?>
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#FFA500;color:#FFFFFF;border-radius:4px;">En attente</span>
									<?php
								}
							}
							?>
						</td>
						<td>
							<?php
							if(sanitize_vars($_POST['state']) == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-add-reply lx-open-popup" 
								data-id="<?php echo $row['id'];?>" 
								data-message="<?php echo addslashes($row['message']);?>" data-title="reply" title="Modifier"><i class="fa fa-reply"></i></a>
							<a href="javascript:;" class="lx-delete lx-delete-reclamation lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>" title="Supprimer"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-reclamation" data-id="<?php echo $row['id'];?>" title="Restaurer"><i class="fa fa-upload"></i></a>
							<a href="javascript:;" class="lx-delete lx-delete-permanently-reclamation" data-id="<?php echo $row['id'];?>" title="Supprimer"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > (sanitize_vars($_POST['start']) + sanitize_vars($_POST['nbpage']))){
				?>
				<p><?php echo (sanitize_vars($_POST['start']) + sanitize_vars($_POST['nbpage']));?> reclamation(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> reclamation(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
			}	

			if($_POST['action'] == "addsmsdevice"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO smsdevices(id,device,apikey,active,trash) VALUES ('0','".sanitize_vars($_POST['device'])."','".sanitize_vars($_POST['apikey'])."','on','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE smsdevices SET device='".sanitize_vars($_POST['device'])."',apikey='".sanitize_vars($_POST['apikey'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}

			if($_POST['action'] == "deletesmsdevice"){
				$req = $bdd->prepare("UPDATE smsdevices SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoresmsdevice"){
				$req = $bdd->prepare("UPDATE smsdevices SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deletesmsdevicepermanently"){
				$req = $bdd->prepare("DELETE FROM smsdevices WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadsmsdevices"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-smsdevice"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-smsdevice"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Appareil ID <i class="fa fa-sort" data-sort="device"></i></td>
						<td>API KEY</td>
						<td>Active <i class="fa fa-sort" data-sort="active"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM smsdevices WHERE trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND (device LIKE '%".$_POST['keyword']."%' OR apikey LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="smsdevice" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['device'];?></span></td>
						<td><span><?php echo $row['apikey'];?></span></td>
						<td>
							<?php
							$class = '';
							if($row['active'] == "on"){
								$class = ' lx-on-off-blue';
							}
							?>
							<div class="lx-on-off<?php echo $class?>" data-state="<?php echo $row['active']?>" data-table="smsdevices" data-column="active" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>						
						</td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-smsdevice lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-device="<?php echo $row['device'];?>"
								data-apikey="<?php echo $row['apikey'];?>" data-title="smsdevice"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-smsdevice lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-smsdevice" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-smsdevice" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> appareil(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> appareil(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}

			if($_POST['action'] == "addsmsmodel"){
				if($_POST['id'] == "0"){
					$req = $bdd->prepare("INSERT INTO smsmodels(id,state,message,active,trash) VALUES ('0','".sanitize_vars($_POST['title'])."','".sanitize_vars($_POST['message'])."','on','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE smsmodels SET state='".sanitize_vars($_POST['title'])."',message='".sanitize_vars($_POST['message'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
			}

			if($_POST['action'] == "deletesmsmodel"){
				$req = $bdd->prepare("UPDATE smsmodels SET trash='0' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "restoresmsmodel"){
				$req = $bdd->prepare("UPDATE smsmodels SET trash='1' WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "deletesmsmodelpermanently"){
				$req = $bdd->prepare("DELETE FROM smsmodels WHERE id='".$_POST['id']."'");
				$req->execute();
			}
			
			if($_POST['action'] == "loadsmsmodels"){
				?>
				<a href="javascript:;" class="lx-trash lx-trash-smsmodel"><i class="fa fa-trash-alt"></i> Corbeille</a>
				<a href="javascript:;" class="lx-trash lx-published-smsmodel"><i class="fa fa-bars"></i> Publiés</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Titre <i class="fa fa-sort" data-sort="state"></i></td>
						<td>Message</td>
						<td>Active <i class="fa fa-sort" data-sort="active"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM smsmodels WHERE trash='".$_POST['state']."'";
					if($_POST['keyword'] != ""){
						$req .= " AND (state LIKE '%".$_POST['keyword']."%' OR message LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['sortby'] != ""){
						$req .= " ORDER BY ".$_POST['sortby'];
					}
					else{
						$req .= " ORDER BY id";
					}
					$req .= " ".$_POST['orderby'];
					$back2 = $bdd->query($req);
					$req .= " LIMIT ".$_POST['start'].",".$_POST['nbpage'];
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="smsmodel" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['state'];?></span></td>
						<td><span><?php echo $row['message'];?></span></td>
						<td>
							<?php
							$class = '';
							if($row['active'] == "on"){
								$class = ' lx-on-off-blue';
							}
							?>
							<div class="lx-on-off<?php echo $class?>" data-state="<?php echo $row['active']?>" data-table="smsmodels" data-column="active" data-id="<?php echo $row['id'];?>">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>						
						</td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-smsmodel lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-titl="<?php echo $row['state'];?>"
								data-message="<?php echo $row['message'];?>" data-title="smsmodel"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-smsmodel lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							else{
								?>
							<a href="javascript:;" class="lx-edit lx-restore-smsmodel" data-id="<?php echo $row['id'];?>"><i class="fa fa-upload"></i></a><a href="javascript:;" class="lx-delete lx-delete-permanently-smsmodel" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
								<?php
							}
							?>
						</td>
					</tr>
						<?php
					}
					?>
				</table>
				<input type="hidden" id="posts" value="<?php echo $back2->rowCount();?>" />
				<?php
				if($back2->rowCount() > ($_POST['start'] + $_POST['nbpage'])){
				?>
				<p><?php echo ($_POST['start'] + $_POST['nbpage']);?> modèle(s) de <?php echo $back2->rowCount();?></p>
				<?php
				}
				else{
				?>
				<p><?php echo $back2->rowCount();?> modèle(s) de <?php echo $back2->rowCount();?></p>
				<?php			
				}
			}
			
			if($_POST['action'] == "loadsms"){
				$back = $bdd->query("SELECT message FROM smsmodels WHERE id='".$_POST['id']."'");
				$row = $back->fetch();
				echo $row['message'];
			}
			
			if($_POST['action'] == "sendsms"){
				$back = $bdd->query("SELECT * FROM smsdevices WHERE active='on' AND trash='1' ORDER BY RAND() LIMIT 0,1");
				if($back->rowCount() > 0){
					$row = $back->fetch();
					$back1 = $bdd->query("SELECT message FROM smsmodels WHERE id='".$_POST['sms']."'");
					$row1 = $back1->fetch();
					$back2 = $bdd->query("SELECT fullname,phone,city,address,price,product FROM commands WHERE id IN(".$_POST['ids'].")");
					while($row2 = $back2->fetch()){
						$message = $row1['message'];
						$message = str_replace("[fullname]",$row2['fullname'],$message);
						$message = str_replace("[phone]",$row2['phone'],$message);
						$message = str_replace("[city]",$row2['city'],$message);
						$message = str_replace("[address]",$row2['address'],$message);
						$message = str_replace("[price]",$row2['price'],$message);
					
						$product = "";
						if(preg_match("#^[0-9]+(,[0-9]+)*$#",$row2['product'])){
							$back3 = $bdd->query("SELECT title FROM stocks WHERE id IN(".$row2['product'].") ORDER BY FIELD(id,".$row2['product'].")");
							while($row3 = $back3->fetch()){
								$product .= " + ".$row3['title'];
							}
							$product = substr($product,3);
						}
						else{
							$product = $row2['product'];						
						}
						$message = str_replace("[product]",$product,$message);								
						sendSMS($row2['phone'],$message,$row['device'],$row['apikey']);
					}
				}
			}
			
			if($_POST['action'] == "loadsalaries"){
				?>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Livreur</td>
						<td>Details frais de livraison</td>
						<td>Details charges</td>
						<td>Salaire</td>
					</tr>
					<?php
					$req = "SELECT id,fullname FROM users WHERE type='dlm' AND trash='1'";
					$back = $bdd->query($req);
					while($row = $back->fetch()){
						?>
					<tr>
						<td><label><input type="checkbox" name="expense" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['fullname'];?></span></td>
						<td>
							<?php
							$req = "";
							$totalfees = 0;
							if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
								$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
								$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
								$req = " AND (dateupdate BETWEEN '".$datestart."' AND '".$dateend."')";
							}
							$back1 = $bdd->query("SELECT city,COUNT(city) AS nb FROM commands WHERE state='Livré' AND dlm='".$row['id']."' AND trash='1'".$req." GROUP BY city");
							while($row1 = $back1->fetch()){
								$back2 = $bdd->query("SELECT deliveredfees FROM shippingfees WHERE city='".$row1['city']."' AND dlm='".$row['id']."'");
								$row2 = $back2->fetch();
								?>
							<span><?php echo $row1['city']." (".$row1['nb']." x ".$row2['deliveredfees']."DH): ".($row1['nb']*$row2['deliveredfees']);?>DH</span>
								<?php
								$totalfees += ($row1['nb']*$row2['deliveredfees']);
							}
							?>
							<strong>Total: <?php echo $totalfees;?>DH</strong>
						</td>
						<td>
							<?php							
							$totalcharges = 0;
							if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
								$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
								$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
								$req = " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
							}
							$back1 = $bdd->query("SELECT description,cost FROM expenses WHERE dlm='".$row['id']."' AND trash='1'".$req);
							while($row1 = $back1->fetch()){
								?>
							<span><?php echo $row1['description'].": ".$row1['cost'];?>DH</span>
								<?php
								$totalcharges += $row1['cost'];
							}
							?>						
							<strong>Total: <?php echo $totalcharges;?>DH</strong>
						</td>
						<td><span><strong><?php echo $totalfees-$totalcharges;?>DH</strong></span></td>
					</tr>
						<?php
					}
					?>
				</table>
				<?php
			}

			if($_POST['action'] == "loadgains"){
				?>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td width="80%">Description</td>
						<td>Total</td>
					</tr>
					<tr>
						<td width="80%">Bénifice des factures versés</td>
						<td>
							<?php
							$req = "";
							if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
								$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
								$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
								$req .= " AND (datecreated BETWEEN '".$datestart."' AND '".$dateend."')";
							}
							$back = $bdd->query("SELECT command FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND dlm='0' AND client<>'0' AND trash='1' AND received='on'".$req);
							$clientfees = 0;
							while($row = $back->fetch()){
								$back1 = $bdd->query("SELECT client,city,extrafees,(SELECT price FROM packaging WHERE id=c.package) AS packageprice,state FROM commands c WHERE id='".$row['command']."'");
								$fd = $back1->fetch();
								$back1 = $bdd->query("SELECT deliveredfees,refusedfees,returnedfees FROM clientfees WHERE city='".addslashes($fd['city'])."' AND client='".$fd['client']."' AND trash='1'");
								if($back1->rowCount() == 0){
									$back1 = $bdd->query("SELECT deliveredfees,refusedfees,returnedfees FROM gshippingfees WHERE city='".addslashes($fd['city'])."'");
								}
								$row1 = $back1->fetch();
								if($fd['state'] == "Livré"){
									$clientfees += $row1['deliveredfees']+$fd['extrafees']+intval($fd['packageprice']);
								}
								elseif($fd['state'] == "Refusé"){
									$clientfees += $row1['refusedfees']+$fd['extrafees']+intval($fd['packageprice']);					
								}
								else{
									$clientfees += $row1['returnedfees']+$fd['extrafees']+intval($fd['packageprice']);						
								}	
							}
							?>
							<span><?php echo $clientfees;?> DH</span>
						</td>
					</tr>
					<tr>
						<td width="80%">Frais de livraison (livreurs)</td>
						<td>
							<?php
							$req = "";
							if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
								$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
								$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
								$req .= " AND (datecreated BETWEEN '".$datestart."' AND '".$dateend."')";
							}
							$back = $bdd->query("SELECT command FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND dlm='0' AND client<>'0' AND trash='1' AND received='on'".$req);
							$dlmfees = 0;
							while($row = $back->fetch()){
								$back1 = $bdd->query("SELECT dlm,client,city,extrafees,(SELECT price FROM packaging WHERE id=c.package) AS packageprice,state FROM commands c WHERE id='".$row['command']."'");
								$fd = $back1->fetch();
								$back1 = $bdd->query("SELECT deliveredfees,refusedfees,'0' AS returnedfees FROM shippingfees WHERE city='".addslashes($fd['city'])."' AND dlm='".$fd['dlm']."' AND trash='1'");
								$row1 = $back1->fetch();
								if($fd['state'] == "Livré"){
									$dlmfees += $row1['deliveredfees']+$fd['extrafees'];
								}
								elseif($fd['state'] == "Refusé"){
									$dlmfees += $row1['refusedfees']+$fd['extrafees'];					
								}
								else{
									$dlmfees += $row1['returnedfees']+$fd['extrafees'];						
								}	
							}
							?>
							<span>-<?php echo $dlmfees;?> DH</span>
						</td>
					</tr>
					<tr>
						<td width="80%">Dépenses générale</td>
						<td>
							<?php
							$req = "";
							if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
								$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
								$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
								$req .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
							}
							$back = $bdd->query("SELECT cost FROM expenses WHERE trash='1'".$req);
							$expenses = 0;
							while($row = $back->fetch()){
								$expenses += $row['cost'];	
							}
							?>
							<span>-<?php echo $expenses;?> DH</span>						
						</td>
					</tr>
					<tr>
						<td width="80%"><b>Reste</b></td>
						<td>
							<span><b><?php echo $clientfees-$dlmfees-$expenses;?> DH</b></span>						
						</td>
					</tr>
				</table>
				<?php
			}			

			if($_POST['action'] == "loadkpi2"){
				$dlm = "";
				$date = $userwhere;
				if($_POST['dlm'] != ""){
					$dlm .= " AND dlm='".$_POST['dlm']."'";
				}
				if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
					$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
					$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
					$date .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
				}
				$back = $bdd->query("SELECT id FROM commands WHERE archived='0' AND state<>'Ajouté' AND trash='1'".$dlm.$date);
				$nbtotal = $back->rowCount();
				if($nbtotal == 0){
					$nbtotal = 1;
				}
				$states = "''";
				$back = $bdd->query("SELECT state FROM trackingstates WHERE kpi='En cours' AND trash='1'");
				while($row = $back->fetch()){
					$states .= ",'".$row['state']."'";
				}
				$back = $bdd->query("SELECT COUNT(id) AS nb FROM commands WHERE archived='0' AND state IN(".$states.") AND trash='1'".$dlm.$date);
				$row = $back->fetch();
				?>
				<div class="lx-g3 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="background:#FFA500;">
						<a href="javascript:;">
							<span style="color:#FFFFFF;">En cours</span>
							<div class="lx-clear-fix"></div>
							<strong style="color:#FFFFFF;"><?php echo $row['nb'];?></strong>
							<br />
							<del style="color:#FFFFFF;"><?php echo round($row['nb']*100/$nbtotal);?>%</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<?php
				$states = "''";
				$back = $bdd->query("SELECT state FROM trackingstates WHERE kpi='Livrées' AND trash='1'");
				while($row = $back->fetch()){
					$states .= ",'".$row['state']."'";
				}
				$back = $bdd->query("SELECT COUNT(id) AS nb FROM commands WHERE archived='0' AND state IN(".$states.") AND trash='1'".$dlm.$date);
				$row = $back->fetch();
				?>
				<div class="lx-g3 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="background:#008000;">
						<a href="javascript:;">
							<span style="color:#FFFFFF;">Livrées</span>
							<div class="lx-clear-fix"></div>
							<strong style="color:#FFFFFF;"><?php echo $row['nb'];?></strong>
							<br />
							<del style="color:#FFFFFF;"><?php echo round($row['nb']*100/$nbtotal);?>%</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<?php
				$states = "''";
				$back = $bdd->query("SELECT state FROM trackingstates WHERE kpi='Echouées' AND trash='1'");
				while($row = $back->fetch()){
					$states .= ",'".addslashes($row['state'])."'";
				}
				$back = $bdd->query("SELECT COUNT(id) AS nb FROM commands WHERE archived='0' AND state IN(".$states.") AND trash='1'".$dlm.$date);
				$row = $back->fetch();
				?>
				<div class="lx-g3 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="background:#CC0000;">
						<a href="javascript:;">
							<span style="color:#FFFFFF;">Echouées</span>
							<div class="lx-clear-fix"></div>
							<strong style="color:#FFFFFF;"><?php echo $row['nb'];?></strong>
							<br />
							<del style="color:#FFFFFF;"><?php echo round($row['nb']*100/$nbtotal);?>%</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<div class="lx-clear-fix"></div>
				<?php
			}

			if($_POST['action'] == "loadkpi"){
				if($settings['simplestats'] == "1"){
					include("simplestats.php");
				}
				else{
					include("fullstats.php");
				}
			}

			if($_POST['action'] == "loaddlmrate"){
				$req = $userwhere;
				if($_POST['product'] != ""){
					$req .= "AND (product='".$row['id']."' OR product LIKE '".$row['id'].",%' OR product LIKE '%,".$row['id'].",%' OR product LIKE '%,".$row['id']."')";
				}
				if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
					$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
					$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
					$req .= " AND (dateupdate BETWEEN '".$datestart."' AND '".$dateend."')";
				}
				?>
				<tr>
					<td>Client</td>
					<td>Livrés</td>
					<td>En cours</td>
					<td>Annulé</td>
				</tr>
				<?php
				$back = $bdd->query("SELECT client,COUNT(id) AS nbdelivered,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE dlm=c.dlm)) AS sm FROM commands c WHERE state='Livré'".$req." GROUP BY dlm ORDER BY sm DESC");
				while($row = $back->fetch()){
					$back1 = $bdd->query("SELECT fullname FROM users WHERE id='".$row['client']."'");
					$row1 = $back1->fetch();
					?>
				<tr>
					<td><span><?php echo $row1['fullname'];?></span></td>
					<td><span><?php echo $row['nbdelivered']." (".round($row['sm'],2)."%)";?></span></td>
					<?php
					$back1 = $bdd->query("SELECT dlm,COUNT(id) AS nbencours,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE dlm=c.dlm)) AS sm FROM commands c WHERE state NOT IN('Livré','Annulé','Refusé','Retourné') AND dlm='".$row['dlm']."'".$req." GROUP BY dlm ORDER BY sm DESC");
					$row1 = $back1->fetch();
					?>
					<td><span><?php echo ($row1['nbencours']!=""?$row1['nbencours']:"0")." (".round($row1['sm'],2)."%)";?></span></td>
					<?php
					$back1 = $bdd->query("SELECT dlm,COUNT(id) AS nbcanceled,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE dlm=c.dlm)) AS sm FROM commands c WHERE state IN('Annulé','Refusé','Retourné') AND dlm='".$row['dlm']."'".$req." GROUP BY dlm ORDER BY sm DESC");
					$row1 = $back1->fetch();
					?>
					<td><span><?php echo ($row1['nbcanceled']!=""?$row1['nbcanceled']:"0")." (".round($row1['sm'],2)."%)";?></span></td>
				</tr>
					<?php
				}
			}
			
			if($_POST['action'] == "loadcityrate"){
				$req = $userwhere;
				if($_POST['product'] != ""){
					$req .= " AND (product='".$row['id']."' OR product LIKE '".$row['id'].",%' OR product LIKE '%,".$row['id'].",%' OR product LIKE '%,".$row['id']."')";;
				}
				if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
					$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
					$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
					$req .= " AND (dateupdate BETWEEN '".$datestart."' AND '".$dateend."')";
				}
				?>
				<tr>
					<td>Ville</td>
					<td>Livrés</td>
					<td>En cours</td>
					<td>Annulé</td>
				</tr>
				<?php
				$back = $bdd->query("SELECT city,COUNT(id) AS nbdelivered,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE city=c.city)) AS sm FROM commands c WHERE state='Livré'".$req." GROUP BY city ORDER BY sm DESC");
				while($row = $back->fetch()){
					?>
				<tr>
					<td><span><?php echo $row['city'];?></span></td>
					<td><span><?php echo $row['nbdelivered']." (".round($row['sm'],2)."%)";?></span></td>
					<?php
					$back1 = $bdd->query("SELECT city,COUNT(id) AS nbencours,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE city=c.city)) AS sm FROM commands c WHERE state NOT IN('Livré','Annulé','Refusé','Retourné') AND city='".$row['city']."'".$req." GROUP BY city ORDER BY sm DESC");
					$row1 = $back1->fetch();
					?>
					<td><span><?php echo ($row1['nbencours']!=""?$row1['nbencours']:"0")." (".round($row1['sm'],2)."%)";?></span></td>
					<?php
					$back1 = $bdd->query("SELECT city,COUNT(id) AS nbcanceled,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE city=c.city)) AS sm FROM commands c WHERE state IN('Annulé','Refusé','Retourné') AND city='".$row['city']."'".$req." GROUP BY city ORDER BY sm DESC");
					$row1 = $back1->fetch();
					?>
					<td><span><?php echo ($row1['nbcanceled']!=""?$row1['nbcanceled']:"0")." (".round($row1['sm'],2)."%)";?></span></td>
				</tr>
					<?php
				}
			}
				
			if($_POST['action'] == "loadchartdata"){
				$req = "SELECT SUM(delivered) AS smd,SUM(canceled) AS smc,dateadd FROM statistics WHERE 1=1".$userwhere;
				if($_POST['client'] != ""){
					$req .= " AND client='".$_POST['client']."'";
				}
				if($_POST['dlm'] != ""){
					$req .= " AND dlm='".$_POST['dlm']."'";
				}
				if($_POST['city'] != ""){
					$req .= " AND city='".$_POST['city']."'";
				}
				if($_POST['product'] != ""){
					$req .= " AND (product='".$_POST['product']."' OR product LIKE '".$_POST['product'].",%' OR product LIKE '%,".$_POST['product'].",%' OR product LIKE '%,".$_POST['product']."')";
				}
				if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
					$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
					$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
					$req .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
				}
				$req .= " GROUP BY dateadd";
				$back = $bdd->query($req);
				$dates = "";
				$delivered = "";
				$canceled = "";
				while($row = $back->fetch()){
					$dates .= ",".gmdate("d/m/Y",$row['dateadd']);
					$delivered .= ",".$row['smd'];
					$canceled .= ",".$row['smc'];
				}
				$dates = substr($dates,1);
				$delivered = substr($delivered,1);
				$canceled = substr($canceled,1);
				echo $dates."|".$delivered."|".$canceled;
			}			

			if($_POST['action'] == "loadlog"){
				?>
				<tr>
					<td>Date</td>
					<td>Description</td>
				</tr>
				<?php
				$back = $bdd->query("SELECT * FROM historylog WHERE dateadd BETWEEN ".(strtotime(str_replace("/","-",$_POST['datelog'])))." AND ".(strtotime(str_replace("/","-",$_POST['datelog'])) + (60*60*24))." ORDER BY id DESC");
				while($row = $back->fetch()){
					?>
				<tr>
					<td><span><?php echo gmdate("d/m/Y H:i",$row['dateadd'] + 3600);?></span></td>
					<td><span><?php echo $row['description'];?></span></td>
				</tr>
					<?php
				}		
			}

			if($_POST['action'] == "changestate"){
				if($_POST['table'] == "clients"){
					if(($_POST['column'] ?? '') !== 'active' || !in_array($_POST['state'] ?? '', ['on', 'off'], true)){
						http_response_code(422);
						echo 'Statut client invalide';
						exit;
					}

					$clientId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
					if(!$clientId){
						http_response_code(422);
						echo 'Client invalide';
						exit;
					}

					$account = $bdd->prepare("SELECT active FROM users WHERE id = ? AND type = 'client' AND trash = '1'");
					$account->execute([$clientId]);
					$client = $account->fetch(PDO::FETCH_ASSOC);
					if(!$client){
						http_response_code(404);
						echo 'Client introuvable';
						exit;
					}

					$update = $bdd->prepare("UPDATE users SET active = ? WHERE id = ? AND type = 'client' AND trash = '1'");
					$update->execute([$_POST['state'], $clientId]);
					add_audit_log($_SESSION['id'], 'update', 'users', $clientId, ['active' => $client['active']], ['active' => $_POST['state']]);
					echo 'OK';
					exit;
				}

				if($_POST['table'] == "shipments"){
					$back = $bdd->query("SELECT client,stock,title,ref,qty,received FROM shipments WHERE id='".$_POST['id']."'");
					$row = $back->fetch();
					if($_POST['state'] == "on" AND $row['received'] == "off"){
						$back = $bdd->query("SELECT id FROM stocks WHERE id='".$row['stock']."' AND client='".$row['client']."'");
						if($back->rowCount() != 0){
							$req = $bdd->prepare("UPDATE stocks SET qty=qty+".$row['qty']." WHERE id='".$row['stock']."' AND client='".$row['client']."' AND trash='1'");
							$req->execute();
						}
						else{
							$req = $bdd->prepare("INSERT INTO stocks(id,client,title,ref,qty,received,dateadd,trash) VALUES ('0','".sanitize_vars($row['client'])."','".sanitize_vars($row['title'])."','".sanitize_vars($row['ref'])."','".sanitize_vars($row['qty'])."','on','".time()."','1')");
							$req->execute();
						}
					}
					elseif($_POST['state'] == "off" AND $row['received'] == "on"){
						$req = $bdd->prepare("UPDATE stocks SET qty=qty-".$row['qty']." WHERE id='".$row['stock']."' AND dlm='".$row['client']."'");
						$req->execute();
					}
				}
				if($_POST['table'] == "commands" AND $_POST['column'] == "phases"){
					if($_POST['state'] == "on"){
						$req = $bdd->prepare("UPDATE commands SET state='Ajouté',phase='shipping',dateupdate='".time()."' WHERE id='".$_POST['id']."'");
						$req->execute();
						$back = $bdd->query("SELECT code,state FROM commands WHERE id='".$_POST['id']."'");
						$command = $back->fetch();
						$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
						$req->execute();						
					}
					else{
						$req = $bdd->prepare("UPDATE commands SET state='Confirmé',phase='confirmation',dateupdate='".time()."' WHERE id='".$_POST['id']."'");
						$req->execute();
						$back = $bdd->query("SELECT code,state FROM commands WHERE id='".$_POST['id']."'");
						$command = $back->fetch();
						$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
						$req->execute();							
					}
				}
				if($_POST['table'] == "commands" AND $_POST['column'] == "collected"){
					if($_POST['state'] == "on"){
						if($settings['appname'] == "Colis Web Express"){
							$req = $bdd->prepare("UPDATE commands SET state='Ramassé',dateupdate='".time()."' WHERE id='".$_POST['id']."'");
						}
						elseif($settings['appname'] == "Colivraison24"){
							$req = $bdd->prepare("UPDATE commands SET state='Expédié',dateupdate='".time()."' WHERE id='".$_POST['id']."'");
						}
						else{
							$req = $bdd->prepare("UPDATE commands SET state='En cours',dateupdate='".time()."' WHERE id='".$_POST['id']."'");
						}
						$req->execute();
						$back = $bdd->query("SELECT code,state FROM commands WHERE id='".$_POST['id']."'");
						$command = $back->fetch();
						$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
						$req->execute();						
					}
					else{
						$req = $bdd->prepare("UPDATE commands SET state='Ajouté',dateupdate='".time()."' WHERE id='".$_POST['id']."'");
						$req->execute();
						$back = $bdd->query("SELECT code,state FROM commands WHERE id='".$_POST['id']."'");
						$command = $back->fetch();
						$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
						$req->execute();							
					}
				}
				if($_POST['table'] == "bls" AND $_POST['column'] == "done"){
					$back = $bdd->query("SELECT * FROM bls WHERE id='".$_POST['id']."'");
					$row = $back->fetch();
					if(preg_match("#BL\-#",$row['code']) AND $row['client'] == "0" AND $row['dlm'] != "0"){
						if($_POST['state'] == "on"){
							$req = $bdd->prepare("UPDATE commands SET state='Reçu par livreur',dateupdate='".time()."' WHERE state IN('Ramassé','Expédié','En cours') AND id IN(".$row['cmds'].")");
							$req->execute();
							$back = $bdd->query("SELECT code,state FROM commands WHERE id IN(".$row['cmds'].") AND state='Reçu par livreur'");
							while($command = $back->fetch()){
								$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
								$req->execute();	
							}						
						}
						else{
							$req = $bdd->prepare("UPDATE commands SET state='Expédié',dateupdate='".time()."' WHERE state IN('Reçu par livreur') AND id IN(".$row['cmds'].")");
							$req->execute();
							$back = $bdd->query("SELECT code,state FROM commands WHERE id IN(".$row['cmds'].") AND state='Expédié'");
							while($command = $back->fetch()){
								$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
								$req->execute();	
							}							
						}
					}
					if(preg_match("#BL\-#",$row['code']) AND $row['client'] != "0" AND $row['dlm'] == "0"){
						if($_POST['state'] == "on"){
							$req = $bdd->prepare("UPDATE commands SET collected='on',state='Ramassé',dateupdate='".time()."' WHERE state IN('Ajouté') AND id IN(".$row['cmds'].")");
							$req->execute();
							$back = $bdd->query("SELECT code,state FROM commands WHERE id IN(".$row['cmds'].") AND state='Ramassé'");
							while($command = $back->fetch()){
								$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
								$req->execute();	
							}						
						}
						else{
							$req = $bdd->prepare("UPDATE commands SET collected='off',state='Ajouté',dateupdate='".time()."' WHERE state IN('Ramassé') AND id IN(".$row['cmds'].")");
							$req->execute();
							$back = $bdd->query("SELECT code,state FROM commands WHERE id IN(".$row['cmds'].") AND state='Ajouté'");
							while($command = $back->fetch()){
								$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
								$req->execute();	
							}							
						}
					}
					$staterl = "Retour reçu agence casablanca";
					$staterc = "Retour client reçu";
					if($settings['appname'] == "Oscario Express"){
						$staterl = "Retourné vers l'agence";
						$staterc = "Retourner";						
					}
					if(preg_match("#BRL\-#",$row['code'])){
						if($_POST['state'] == "on"){
							$req = $bdd->prepare("UPDATE commands SET state='".addslashes($staterl)."',dateupdate='".time()."' WHERE id IN(".$row['cmds'].")");
							$req->execute();
							$back = $bdd->query("SELECT code,state FROM commands WHERE id IN(".$row['cmds'].") AND state='".addslashes($staterl)."'");
							while($command = $back->fetch()){
								$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
								$req->execute();	
							}						
						}						
					}
					if(preg_match("#BR\-#",$row['code'])){
						if($_POST['state'] == "on"){
							$req = $bdd->prepare("UPDATE commands SET state='".$staterc."',dateupdate='".time()."' WHERE id IN(".$row['cmds'].")");
							$req->execute();
							$back = $bdd->query("SELECT code,state FROM commands WHERE id IN(".$row['cmds'].") AND state='".$staterc."'");
							while($command = $back->fetch()){
								$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
								$req->execute();	
							}						
						}						
					}
				}
				if($_POST['table'] == "factures" AND $_POST['column'] == "received"){
					$back = $bdd->query("SELECT fd.facture AS facture,fd.command AS command,dlm FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND f.id='".$_POST['id']."'");
					$facture = "";
					while($row = $back->fetch()){
						if($row['dlm'] == "0"){
							$facture = $row['facture'];
							if($_POST['state'] == "on"){
								$req = $bdd->prepare("UPDATE commands SET invoiced='on' WHERE id='".$row['command']."'");
							}
							else{
								$req = $bdd->prepare("UPDATE commands SET invoiced='off' WHERE id='".$row['command']."'");
							}
							$req->execute();
						}
					}
					$req = $bdd->prepare("UPDATE factures SET datereceived='".time()."' WHERE id='".$_POST['id']."'");
					$req->execute();	
					if($_POST['state'] == "on"){
						$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a marqué la facture N° ".$facture." comme versé','".time()."')");
						$req->execute();
					}
					else{
						$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a marqué la facture N° ".$facture." comme non versé','".time()."')");
						$req->execute();						
					}					
				}
				if($_POST['table'] == "factures" AND $_POST['column'] == "validated"){
					$back = $bdd->query("SELECT code FROM factures WHERE id='".$_POST['id']."'");
					$row = $back->fetch();
					if($_POST['state'] == "on"){
						$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a marqué la facture N° ".$row['code']." comme cloturé','".time()."')");
						$req->execute();
					}
					else{
						$req = $bdd->prepare("INSERT INTO historylog(id,description,dateadd) VALUES('0','".$_SESSION['fullname']." a marqué la facture N° ".$row['code']." comme non cloturé','".time()."')");
						$req->execute();						
					}	
					$req = $bdd->prepare("UPDATE factures SET datecreated='".time()."' WHERE id='".$_POST['id']."'");
					$req->execute();					
				}
				$req = $bdd->prepare("UPDATE `".$_POST['table']."` SET `".$_POST['column']."`='".$_POST['state']."' WHERE id='".$_POST['id']."'");
				$req->execute();
			}

			if($_POST['action'] == "updatebulk"){
				if($_POST['state'] == "delete"){
					$req = $bdd->prepare("UPDATE `".$_POST['table']."` SET trash='0' WHERE `".$_POST['column']."` IN(".$_POST['ids'].")");
					$req->execute();
				}
				elseif($_POST['state'] == "deletepermenantly"){	
					$req = $bdd->prepare("DELETE FROM `".$_POST['table']."` WHERE `".$_POST['column']."` IN(".$_POST['ids'].")");
					$req->execute();
				}
				elseif($_POST['state'] == "restore"){	
					$req = $bdd->prepare("UPDATE `".$_POST['table']."` SET trash='1' WHERE `".$_POST['column']."` IN(".$_POST['ids'].")");
					$req->execute();
				}
				elseif($_POST['state'] == "collect"){	
					if($settings['appname'] == "Colis Web Express"){
						$req = $bdd->prepare("UPDATE commands SET state='Ramassé',collected='on',dateupdate='".time()."' WHERE id IN(".$_POST['ids'].")");
					}
					elseif($settings['appname'] == "Colivraison24"){
						$req = $bdd->prepare("UPDATE commands SET state='Ramassé',collected='on',dateupdate='".time()."' WHERE id IN(".$_POST['ids'].")");
					}
					else{
						$req = $bdd->prepare("UPDATE commands SET state='En cours',collected='on',dateupdate='".time()."' WHERE id IN(".$_POST['ids'].")");
					}
					$req->execute();
					$back = $bdd->query("SELECT code,state FROM commands WHERE id IN(".$_POST['ids'].")");
					while($command = $back->fetch()){
						$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
						$req->execute();	
					}
				}
				elseif($_POST['state'] == "addtoarchive"){
					$req = $bdd->prepare("INSERT INTO commandsarchive SELECT * FROM commands WHERE id IN(".$_POST['ids'].")");
					$req->execute();		
					$req = $bdd->prepare("DELETE FROM commands WHERE id IN(".$_POST['ids'].")");
					$req->execute();							
				}
				elseif($_POST['state'] == "removefromarchive"){
					$req = $bdd->prepare("INSERT INTO commands SELECT * FROM commandsarchive WHERE id IN(".$_POST['ids'].")");
					$req->execute();	
					$req = $bdd->prepare("DELETE FROM commandsarchive WHERE id IN(".$_POST['ids'].")");
					$req->execute();							
				}
				else{
					$req = $bdd->prepare("UPDATE `".$_POST['table']."` SET state='".$_POST['state']."',dateupdate='".time()."' WHERE `".$_POST['column']."` IN(".$_POST['ids'].") AND state NOT IN('Ajouté','Changement adresse','Annulé','Refusé','Livré','Retourné','Retourné vers agence')");
					$req->execute();	
					$back = $bdd->query("SELECT code,state FROM commands WHERE id IN(".$_POST['ids'].")");
					while($command = $back->fetch()){
						$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
						$req->execute();	
					}					
				}
			}
			
			if($_POST['action'] == "allnotifs"){
				$notifs = "";
				if($_SESSION['type'] == "moderator"){
					$clients = $bdd->query("SELECT id FROM users WHERE active='off' AND type='client' AND trash='1'");
					$confirmation = $bdd->query("SELECT id FROM commands WHERE state='Nouveau' AND trash='1'");
					$commands = $bdd->query("SELECT id FROM commands WHERE phase='shipping' AND collected='off' AND trash='1'");
					$shipped = $bdd->query("SELECT id FROM commands WHERE state IN('Ramassé','En cours','Reporté','Changement adresse') AND trash='1'");
					$today = $bdd->query("SELECT id FROM commands WHERE collected='on' AND (dateupdate BETWEEN ".(strtotime(date("d-m-Y")))." AND ".(strtotime(date("d-m-Y"))+(60*60*24)).") AND trash='1'");
					$delivered = $bdd->query("SELECT id FROM commands WHERE state IN('Livré') AND invoiced='off' AND trash='1'");
					$shipments = $bdd->query("SELECT id FROM shipments WHERE received='off' AND trash='1'");
					$bra = $bdd->query("SELECT id FROM bls WHERE dlm='0' AND client<>'0' AND code LIKE 'BL-%' AND done='off' AND trash='1'");
					$fctclient = $bdd->query("SELECT id FROM factures WHERE client<>'0' AND dlm='0' AND validated='on' AND received='off' AND trash='1'");
					$fctdlm = $bdd->query("SELECT id FROM factures WHERE client='0' AND dlm<>'0' AND validated='on' AND received='off' AND trash='1'");
					$notifs = '{ notifs : [';
					$notifs .= '{ "clients" : "'.$clients->rowCount().'" , "confirmation" : "'.$confirmation->rowCount().'" , "commands" : "'.$commands->rowCount().'" , "shipped" : "'.$shipped->rowCount().'" , "today" : "'.$today->rowCount().'" , "shipments" : "'.$shipments->rowCount().'" , "bra" : "'.$bra->rowCount().'" , "fctclient" : "'.$fctclient->rowCount().'" , "fctdlm" : "'.$fctdlm->rowCount().'" }';
					$notifs .= '] }';
				}
				elseif($_SESSION['type'] == "dlm"){
					$shipped = $bdd->query("SELECT id FROM commands WHERE state IN('Ramassé','En cours','Reporté','Changement adresse') AND trash='1'".$userwhere);
					if($_SESSION['type'] == "dlm"){
						$back = $bdd->query("SELECT GROUP_CONCAT(city SEPARATOR ',') AS cities FROM shippingfees WHERE dlm='".$_SESSION['id']."' AND trash='1'");
						$row = $back->fetch();
						$userwhere = " AND client IN(SELECT id FROM users WHERE city IN('".str_replace(",","','",addslashes($row['cities']))."'))";
					}
					$commands = $bdd->query("SELECT id FROM commands WHERE phase='shipping' AND collected='off' AND trash='1'".$userwhere);
					$fctdlm = $bdd->query("SELECT id FROM factures WHERE dlm='".$_SESSION['id']."' AND validated='on' AND received='off' AND trash='1'");
					$notifs = '{ notifs : [';
					$notifs .= '{ "clients" : "0" , "confirmation" : "0" , "commands" : "'.$commands->rowCount().'" , "shipped" : "'.$shipped->rowCount().'" , "today" : "0" , "shipments" : "0" , "fctclient" : "0" , "bra" : "0" , "fctdlm" : "'.$fctdlm->rowCount().'" }';
					$notifs .= '] }';
				}	
				elseif($_SESSION['type'] == "client"){
					$confirmation = $bdd->query("SELECT id FROM commands WHERE state='Nouveau' AND trash='1'".$userwhere);
					$commands = $bdd->query("SELECT id FROM commands WHERE phase='shipping' AND collected='off' AND trash='1'".$userwhere);
					$shipped = $bdd->query("SELECT id FROM commands WHERE state IN('Ramassé','En cours','Reporté','Changement adresse') AND trash='1'".$userwhere);
					$delivered = $bdd->query("SELECT id FROM commands WHERE state IN('Livré') AND trash='1'".$userwhere);
					$shipments = $bdd->query("SELECT id FROM shipments WHERE received='off' AND trash='1'".$userwhere);
					$fctclient = $bdd->query("SELECT id FROM factures WHERE client='".$_SESSION['id']."' AND validated='on' AND received='off' AND trash='1'");
					$notifs = '{ notifs : [';
					$notifs .= '{ "clients" : "0" , "confirmation" : "'.$confirmation->rowCount().'" , "commands" : "'.$commands->rowCount().'" , "shipped" : "'.$shipped->rowCount().'" , "today" : "0" , "shipments" : "'.$shipments->rowCount().'" , "bra" : "0" , "fctclient" : "'.$fctclient->rowCount().'" , "fctdlm" : "0" }';
					$notifs .= '] }';
				}				
				echo $notifs;
			}
			
			if($_POST['action'] == "getgooglesheetorders"){
				$back = $bdd->query("SELECT * FROM spreadsheets WHERE autofetch='on'");
				if($back->rowCount() == 0){
					exit;
				}
				if(!class_exists('Google_Client') || !class_exists('Google_Service_Sheets')){
					error_log('Google Sheets auto-fetch skipped: google/apiclient is not installed.');
					echo 'Google Sheets integration is not installed.';
					exit;
				}
				$client = new \Google_Client();
				$client->setApplicationName('Google Sheets and PHP');
				$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
				$client->setAccessType('offline');
				$client->setAuthConfig('eagle.json');
				$service = new Google_Service_Sheets($client);
				
				while($row = $back->fetch()){
					$spreadsheetId = $row['sheetid'];
					$range = $row['sheetname']."!A".$row['lastrow'].":Z";
					$response = $service->spreadsheets_values->get(
						$spreadsheetId,
						$range
					);
					$values = $response->getValues();
					if(!empty($values)){
						for($i=0;$i<count($values);$i++){
							print_r($values);
							$rand = '';
							do{
								$rand = 'CMD-'.gmdate('dmY').'-'.random();
								$back1 = $bdd->query("SELECT id FROM commands WHERE code='".$rand."'");
							}
							while($back1->rowCount() != 0);
							$req = $bdd->prepare("INSERT INTO commands(id,code,product,qty,dlm,subdlm,client,fullname,phone,address,city,price,package,state,phase,datereported,note,collected,invoiced,invoiceddlm,confirmed,archived,echange,openpackage,dateadd,dateupdate,trash) 
							VALUES ('0','".$rand."','".str_replace(";",",",sanitize_vars($values[$i][5]))."','".str_replace(";",",",sanitize_vars($values[$i][5]))."','0','0','".$row['client']."','".sanitize_vars($values[$i][0])."','".sanitize_vars($values[$i][1])."','".sanitize_vars($values[$i][3])."','".sanitize_vars($values[$i][2])."','".sanitize_vars($values[$i][4])."','0','Nouveau','confirmation','','".sanitize_vars($rowData[0][10])."','off','off','off','1','0','".sanitize_vars($rowData[0][8])."','".sanitize_vars($rowData[0][9])."','".time()."','".time()."','1')");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$rand."','".$state."','".$_SESSION['fullname']."','".time()."')");
							$req->execute();							
						}
					}
					else{
						echo "Nothing found";
					}
					$req = $bdd->prepare("UPDATE spreadsheets SET lastrow=(".$row['lastrow']."+".count($values).") WHERE id='".$row['id']."'");
					$req->execute();
				}
			}
		}
	}
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

function sendMessage($players,$header,$content,$url,$app_id){
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
}

function sendSMS($phone,$msg,$device,$smstoken){
	$patterns = array ('/\ /','/\-/','/\./','/\//','/\+/','/00212/','/^212/','/^0/');
	$replace = array ('','','','','','','','');
	$phone = preg_replace($patterns,$replace,$phone);
	$phone = "+212".$phone;	
	
	$message = [
		"secret" => $smstoken,
		"mode" => "devices",
		"device" => $device,
		"sim" => 1,
		"priority" => 1,
		"phone" => $phone,
		"message" => $msg
	];

	$cURL = curl_init("https://www.cloud.smschef.com/api/send/sms");
	curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($cURL, CURLOPT_POSTFIELDS, $message);
	$response = curl_exec($cURL);
	curl_close($cURL);
	
	$result = json_decode($response, true);

	print_r($result);
}

function sendToStockOUT($url,$code,$state,$datereported,$note){
	if(preg_match("#http#",$url)){
		$url = $url."/updatestate.php?code=".urlencode($code)."&state=".urlencode($state)."&datereported=".urlencode($datereported)."&note=".urlencode($note);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$result = curl_exec($ch);
		curl_close($ch);	
	}
}

function RGBTOHex($color){
	return "rgba(".hexdec(substr($color,1,2)).",".hexdec(substr($color,3,2)).",".hexdec(substr($color,5,2)).",0.2)";
}

function sendToOnessta($url,$code,$state,$datereported,$note){
	$apiUrl = 'https://api.onessta.com/api/v1/d/parcels/change_status';
	$state = $state;
	if(preg_match("#Livré#",$state)){
		$state = "DELIVERED";
	}
	elseif(preg_match("#Reporté#",$state)){
		$state = "POSTPONED";
	}
	elseif(preg_match("#Pas de réponse#",$state)){
		$state = "NOANSWER";
	}
	elseif(preg_match("#Injoignable#",$state)){
		$state = "UNREACHABLE";
	}
	elseif(preg_match("#Hors zone#",$state)){
		$state = "OUT_OF_AREA";
	}	
	elseif(preg_match("#Annulé#",$state)){
		$state = "CANCELED";
	}
	elseif(preg_match("#Refusé#",$state)){
		$state = "REFUSE";
	}
	elseif(preg_match("#Programmé#",$state)){
		$state = "PROGRAMED";
	}
	elseif(preg_match("#Interessé#",$state)){
		$state = "INT";
	}
	elseif(preg_match("#En cours|Expédié|Reçu par livreur|Ramassé#",$state)){
		$state = "DISTRIBUTION";
	}
	elseif(preg_match("#Changement adresse#",$state)){
		$state = "REPLACE";
	}
	
	$data = [
		'code' => $code,
		'status' => $state,
		'reported_date' => $datereported,
		'comment' => $note
	];

	print_r($data);

	$headers = [
		'Content-Type: application/json',
		'Authorization: Bearer dNlNNBBMAohuXG6pvaTuy9zEL2dcXr03udwzZekl7f60cdfd',
		'Accept: application/json',
		'API-Key: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo0MiwiZW1haWwiOiJvbWFyQG9uZXNzdGEuY29tIn0=.McZzksVkPJXIbljW1vP809T4LDHeBBaigc7BpkBgTaU=',
		'Client-ID: FB03FFEE-2761DA6D-431DD56A-097CF824-11271FF8-E9116C57'
	];

	$ch = curl_init($apiUrl);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($ch);

	if (curl_errno($ch)) {
		echo 'Curl error: ' . curl_error($ch);
	}

	curl_close($ch);

	$data = json_decode($response,true);
	print_r($data);
}

?>
