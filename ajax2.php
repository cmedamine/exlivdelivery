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
				$back = $bdd->query("SELECT id FROM notices WHERE trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-notice"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM notices WHERE trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-notice"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
				$back = $bdd->query("SELECT id FROM users WHERE type='moderator' AND trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-user"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM users WHERE type='moderator' AND trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-user"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
						<td><span><?php echo gmdate("d/m/Y H:i",$row['datesignup']);?></span></td>
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

			if($_POST['action'] == "editnbrows"){
				$back = $bdd->query("SELECT id FROM parametres WHERE user='".$_SESSION['id']."'");
				if($back->rowCount() == 0){
					$req = $bdd->prepare("INSERT INTO parametres(id,user,nbrows,rowcolor) VALUE('','".$_SESSION['id']."','".$_POST['nbrows']."','')");
				}
				else{
					$req = $bdd->prepare("UPDATE parametres SET nbrows='".$_POST['nbrows']."'");
				}
				$req->execute();
			}
			
			if($_POST['action'] == "editsettings"){
				if($_SESSION['type'] == "moderator"){
					$back = $bdd->query("SELECT id FROM settings");
					if($back->rowCount() == 0){
						$req = $bdd->prepare("INSERT INTO settings(id,logo,standardfees,currency) VALUE('','".$_POST['logo']."','".sanitize_vars($_POST['appname'])."','0','".sanitize_vars($_POST['currency'])."')");
					}
					else{
						$req = $bdd->prepare("UPDATE settings SET logo='".$_POST['logo']."',appname='".sanitize_vars($_POST['appname'])."',currency='".sanitize_vars($_POST['currency'])."',sepdelivered='".$_POST['sepdelivered']."'");
					}
					$req->execute();
				}
				$back = $bdd->query("SELECT id FROM parametres WHERE user='".$_SESSION['id']."'");
				if($back->rowCount() == 0){
					$req = $bdd->prepare("INSERT INTO parametres(id,user,nbrows,rowcolor) VALUE('','".$_SESSION['id']."','".$_POST['nbrows']."','".$_POST['rowcolor']."')");
				}
				else{
					$req = $bdd->prepare("UPDATE parametres SET nbrows='".$_POST['nbrows']."',rowcolor='".$_POST['rowcolor']."' WHERE user='".$_SESSION['id']."'");
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
				$back = $bdd->query("SELECT id FROM cities WHERE trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-cities"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM cities WHERE trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-cities"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
				$back = $bdd->query("SELECT id FROM users WHERE type='dlm' AND trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-dlm"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM users WHERE type='dlm' AND trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-dlm"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Nom et prénom <i class="fa fa-sort" data-sort="fullname"></i></td>
						<td>Téléphone <i class="fa fa-sort" data-sort="phone"></i></td>
						<td>E-mail <i class="fa fa-sort" data-sort="email"></i></td>
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
						<td>
							<span><?php echo $row['stockout'];?></span>
							<span><?php echo $row['emailstockout'];?></span>
						</td>
						<td><span><?php echo $row['roles'];?></span></td>
						<td><span><?php echo gmdate("d/m/Y H:i",$row['datesignup']);?></span></td>
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
						$req = $bdd->prepare("INSERT INTO users(id,fullname,picture,email,password,phone,sav,city,cin,store,bank,rib,type,roles,datesignup,trash) VALUES ('0','".sanitize_vars($_POST['fullname'])."','avatar.png','".sanitize_vars($_POST['email'])."','".sanitize_vars($_POST['password'])."','".sanitize_vars($_POST['phone'])."','','','','','','','subdlm','','".time()."','1')");
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
				$back = $bdd->query("SELECT u.id FROM subdlm sd,users u WHERE sd.subdlm=u.id AND type='subdlm' AND trash='0'".$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-trash-subdlm"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT u.id FROM subdlm sd,users u WHERE sd.subdlm=u.id AND type='subdlm' AND trash='1'".$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-published-subdlm"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
						<td>Date inscription <i class="fa fa-sort" data-sort="datesignup"></i></td>
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
						<td><span><?php echo gmdate("d/m/Y H:i",$row['datesignup']);?></span></td>
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
				$back = $bdd->query("SELECT id FROM shippingfees WHERE trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-shippingfee"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM shippingfees WHERE trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-shippingfee"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
					$req = $bdd->prepare("INSERT INTO trackingstates VALUES('0','".sanitize_vars($_POST['state'])."','".sanitize_vars($_POST['color'])."','".sanitize_vars(substr($_POST['agents'],1))."','1')");
					$req->execute();
				}
				else{
					$req = $bdd->prepare("UPDATE trackingstates SET state='".sanitize_vars($_POST['state'])."',color='".sanitize_vars($_POST['color'])."',agent='".sanitize_vars(substr($_POST['agents'],1))."' WHERE id='".$_POST['id']."'");
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
				$back = $bdd->query("SELECT id FROM trackingstates WHERE trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-trackingstate"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM trackingstates WHERE trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-trackingstate"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Etat</td>
						<td>Utilisateurs</td>
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
						<td><span style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row['color'];?>;color:#FFFFFF;border-radius:4px;"><?php echo $row['state'];?></span></td>
						<td><span><?php echo str_replace(",",", ",$row['agent']);?></span></td>
						<td>
							<?php
							if($_POST['state'] == 1){
								?>
							<a href="javascript:;" class="lx-edit lx-edit-trackingstate lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-state="<?php echo $row['state'];?>"
								data-color="<?php echo $row['color'];?>"
								data-agents=",<?php echo $row['agent'];?>" data-title="trackingstate"><i class="fa fa-edit"></i></a><a href="javascript:;" class="lx-delete lx-delete-trackingstate lx-open-popup" data-title="deleterecord" data-id="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
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
						$req = $bdd->prepare("INSERT INTO users(id,fullname,picture,email,password,phone,sav,city,cin,store,bank,rib,stockout,type,roles,active,datesignup,trash) VALUES ('0','".sanitize_vars($_POST['fullname'])."','avatar.png','".sanitize_vars($_POST['email'])."','".sanitize_vars($_POST['password'])."','".sanitize_vars($_POST['phone'])."','".sanitize_vars($_POST['sav'])."','".sanitize_vars($_POST['city'])."','".sanitize_vars($_POST['cin'])."','".sanitize_vars($_POST['store'])."','".sanitize_vars($_POST['bank'])."','".sanitize_vars($_POST['rib'])."','".sanitize_vars($_POST['stockout'])."','client','','on','".time()."','1')");
						$req->execute();
					}
					else{
						$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',email='".sanitize_vars($_POST['email'])."',password='".sanitize_vars($_POST['password'])."',phone='".sanitize_vars($_POST['phone'])."',sav='".sanitize_vars($_POST['sav'])."',city='".sanitize_vars($_POST['city'])."',cin='".sanitize_vars($_POST['cin'])."',store='".sanitize_vars($_POST['store'])."',bank='".sanitize_vars($_POST['bank'])."',rib='".sanitize_vars($_POST['rib'])."',stockout='".sanitize_vars($_POST['stockout'])."' WHERE id='".$_POST['id']."'");
						$req->execute();
					}
				}
				else{
					$back = $bdd->query("SELECT id FROM users WHERE email='".$_POST['email']."' AND id='".$_POST['id']."'");
					if($back->rowCount() != 0){
						$req = $bdd->prepare("UPDATE users SET fullname='".sanitize_vars($_POST['fullname'])."',email='".sanitize_vars($_POST['email'])."',password='".sanitize_vars($_POST['password'])."',phone='".sanitize_vars($_POST['phone'])."',sav='".sanitize_vars($_POST['sav'])."',city='".sanitize_vars($_POST['city'])."',cin='".sanitize_vars($_POST['cin'])."',store='".sanitize_vars($_POST['store'])."',bank='".sanitize_vars($_POST['bank'])."',rib='".sanitize_vars($_POST['rib'])."',stockout='".sanitize_vars($_POST['stockout'])."' WHERE id='".$_POST['id']."'");
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
				$back = $bdd->query("SELECT id FROM users WHERE type='client' AND trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-client"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM users WHERE type='client' AND trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-client"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Boutique <i class="fa fa-sort" data-sort="store"></i></td>
						<td>Nom et prénom (CIN) <i class="fa fa-sort" data-sort="fullname"></i></td>
						<td>Téléphone <i class="fa fa-sort" data-sort="phone"></i></td>
						<td>E-mail <i class="fa fa-sort" data-sort="email"></i></td>
						<td>Ville <i class="fa fa-sort" data-sort="city"></i></td>
						<td>Bank <i class="fa fa-sort" data-sort="bank"></i></td>
						<td>RIB <i class="fa fa-sort" data-sort="rib"></i></td>
						<td>StockOUT <i class="fa fa-sort" data-sort="stockout"></i></td>
						<td>Date inscription <i class="fa fa-sort" data-sort="datesignup"></i></td>
						<td>Active <i class="fa fa-sort" data-sort="active"></i></td>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM users WHERE type='client' AND trash='".$_POST['state']."'";
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
						<td><label><input type="checkbox" name="client" value="<?php echo $row['id'];?>" /><del class="checkmark"></del></label></td>
						<td><span><?php echo $row['store'];?></span></td>
						<td><span><?php echo $row['fullname']." (".$row['cin'].")";?></span></td>
						<td><span><?php echo $row['phone'];?></span></td>
						<td><span><?php echo $row['email'];?></span></td>
						<td><span><?php echo $row['city'];?></span></td>
						<td><span><?php echo $row['bank'];?></span></td>
						<td><span><?php echo $row['rib'];?></span></td>
						<td><span><?php echo $row['stockout'];?></span></td>
						<td><span><?php echo gmdate("d/m/Y H:i",$row['datesignup']);?></span></td>
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
							<a href="javascript:;" class="lx-edit lx-edit-client lx-open-popup" 
								data-id="<?php echo $row['id'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-email="<?php echo $row['email'];?>"
								data-password="<?php echo $row['password'];?>"
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
				$back = $bdd->query("SELECT id FROM clientfees WHERE trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-clientfee"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM clientfees WHERE trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-clientfee"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
				$back = $bdd->query("SELECT id FROM gshippingfees WHERE trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-gshippingfee"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM gshippingfees WHERE trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-gshippingfee"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
				$back = $bdd->query("SELECT id FROM shipments WHERE trash='0'".$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-trash-shipment"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM shipments WHERE trash='1'".$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-published-shipment"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
						<td><span><?php echo gmdate("d/m/Y",$row['dateadd']);?></span></td>
						<td>
							<?php
							if($_SESSION['type'] != "moderator"){
								if($row['received'] == "on"){
									?>
								<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#71b44c;color:#FFFFFF;border-radius:4px;">Oui</span>
									<?php
								}
								else{
									?>
								<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#FFA500;color:#FFFFFF;border-radius:4px;">Non</span>
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
				$back = $bdd->query("SELECT id FROM stocks WHERE trash='0'".$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-trash-stock"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM stocks WHERE trash='1'".$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-published-stock"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
						//$back1 = $bdd->query("SELECT qty,product FROM commands WHERE (product='".$row['id']."' OR product LIKE '".$row['id'].",%' OR product LIKE '%,".$row['id'].",%' OR product LIKE '%,".$row['id']."') AND state IN('En cours','Pas de réponse 1','Injoignable','Reporté','Interessé','Pas de réponse 2 fois','Pas de réponse 3 fois','Expédié','Changement adresse','En cours de livraison','Ajouté','Livré')");
						$back1 = $bdd->query("SELECT qty,product FROM commands WHERE (product='".$row['id']."' OR product LIKE '".$row['id'].",%' OR product LIKE '%,".$row['id'].",%' OR product LIKE '%,".$row['id']."') AND state IN('Livré')");
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
						<td><span><?php echo ($row['qty']-$sm);?></span></td>
						<td><span><?php echo gmdate("d/m/Y",$row['dateadd']);?></span></td>
						<td>
							<?php
							if($_SESSION['type'] != "moderator"){
								if($row['received'] == "on"){
									?>
								<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#71b44c;color:#FFFFFF;border-radius:4px;">Oui</span>
									<?php
								}
								else{
									?>
								<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#FFA500;color:#FFFFFF;border-radius:4px;">Non</span>
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
				$back = $bdd->query("SELECT id FROM packaging WHERE trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-packaging"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM packaging WHERE trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-packaging"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
							$rand = 'CMD-'.gmdate('dmY').'-'.random();
							$back = $bdd->query("SELECT id FROM commands WHERE code='".$rand."'");
						}
						while($back->rowCount() != 0);						
					}
					$req = $bdd->prepare("INSERT INTO commands(id,code,product,qty,dlm,client,fullname,phone,address,city,price,state,datereported,note,invoiced,collected,dateadd,dateupdate,trash) VALUES ('0','".$rand."','".sanitize_vars(substr($_POST['product'],1))."','".sanitize_vars(substr($_POST['qty'],1))."','0','".$_POST['client']."','".sanitize_vars($_POST['fullname'])."','".sanitize_vars($_POST['phone'])."','".sanitize_vars($_POST['address'])."','".sanitize_vars($_POST['city'])."','".sanitize_vars($_POST['price'])."','Ajouté','','".sanitize_vars($_POST['note'])."','off','off','".time()."','".time()."','1')");
					$req->execute();
					$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$rand."','Ajouté','".$_SESSION['fullname']."','".time()."')");
					$req->execute();
				}
				else{
					$back = $bdd->query("SELECT id,code,dlm,qty,city,price,extrafees,package,state FROM commands WHERE id='".$_POST['id']."'");
					$command = $back->fetch();
					if($command['state'] == "Livré"){
						$oldprice = $command['price'];
						$newprice = $_POST['price'];
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
					$req = $bdd->prepare("UPDATE commands SET product='".sanitize_vars(substr($_POST['product'],1))."',qty='".sanitize_vars(substr($_POST['qty'],1))."',dlm='".sanitize_vars($_POST['dlm'])."',client='".sanitize_vars($_POST['client'])."',fullname='".sanitize_vars($_POST['fullname'])."',phone='".sanitize_vars($_POST['phone'])."',address='".sanitize_vars($_POST['address'])."',city='".sanitize_vars($_POST['city'])."',price='".sanitize_vars($_POST['price'])."',extrafees='".sanitize_vars($_POST['extrafees'])."',package='".sanitize_vars($_POST['package'])."',note='".sanitize_vars($_POST['note'])."',dateupdate='".time()."' WHERE id='".$_POST['id']."'");
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
					}					
				}
			}
			
			if($_POST['action'] == "changeaddress"){
				$req = $bdd->prepare("UPDATE commands SET fullname='".sanitize_vars($_POST['fullname'])."',phone='".sanitize_vars($_POST['phone'])."',address='".sanitize_vars($_POST['address'])."',state='Changement adresse' WHERE id='".$_POST['id']."'");
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
							$req = $bdd->prepare("UPDATE commands SET state='En cours',dateupdate='".time()."' WHERE id='".$command['id']."'");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,dateadd) VALUES ('0','".$_POST['code']."','En cours','".time()."')");
							$req->execute();
						}
						elseif($commands['state'] != "Ajouté"){
							echo "La commande est déja ramassé est a l'état suivante: ".$command['state'];
						}
					}
					else{
						echo "La commande n'exist pas";
					}
				}
			}
			
			if($_POST['action'] == "loadramassage"){
				$back = $bdd->query("SELECT id FROM commands WHERE trash='0' AND state='Ajouté' AND collected='off'".$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-trash-command"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM commands WHERE trash='1' AND state='Ajouté' AND collected='off'".$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-published-command"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
						<td>Produits <i class="fa fa-sort" data-sort="product"></i></td>
						<td>Prix <i class="fa fa-sort" data-sort="price"></i></td>
						<td>Etat</td>
						<td>Date <i class="fa fa-sort" data-sort="dateadd"></i></td>
						<?php
						if($_SESSION['type'] == "moderator"){
							?>
						<td>Ramasser</td>
							<?php
						}
						?>						
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM commands WHERE trash='".$_POST['state']."' AND state='Ajouté' AND collected='off'".$userwhere;
					if($_POST['keyword'] != ""){
						$req .= " AND (code LIKE '%".$_POST['keyword']."%' OR fullname LIKE '%".$_POST['keyword']."%' OR phone LIKE '%".$_POST['keyword']."%' OR address LIKE '%".$_POST['keyword']."%' OR city LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['client'] != ""){
						$req .= " AND client='".$_POST['client']."'";
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
								?>
							<span><?php echo $row['product'];?></span>
								<?php
							}
							?>
						</td>
						<td><span><?php echo $row['price'];?> <?php echo $settings['currency'];?></span></td>
						<?php
						$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".$row['state']."'");
						$row1 = $back1->fetch();
						?>
						<td><span style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:4px;cursor:pointer;"><?php echo $row['state'];?></span></td>
						<td><span><?php echo ($row['dateadd']!=""?gmdate("d/m/Y H:i",$row['dateadd']):"&mdash;");?></span></td>
						<?php
						if($_SESSION['type'] == "moderator"){
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
								data-code="<?php echo $row['code'];?>"
								data-fullname="<?php echo $row['fullname'];?>"
								data-phone="<?php echo $row['phone'];?>"
								data-address="<?php echo $row['address'];?>"
								data-city="<?php echo $row['city'];?>"
								data-price="<?php echo $row['price'];?>"
								data-note="<?php echo $row['note'];?>" data-title="command"><i class="fa fa-edit"></i></a><!--
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

			if($_POST['action'] == "editstate"){
				if($_SESSION['type'] == "dlm" AND $_SESSION['roles'] == "Affichage commun"){
					$req = $bdd->prepare("UPDATE commands SET dlm='".sanitize_vars($_SESSION['id'])."' WHERE id='".$_POST['id']."'");
					$req->execute();
				}
				$back = $bdd->query("SELECT id,code,client,product,dlm,city,price,extrafees,package,state FROM commands WHERE id='".$_POST['id']."'");
				$command = $back->fetch();
				$req = "";
				if($_POST['state'] == "Ajouté"){
					$req = ",collected='off'";
				}
				$req = $bdd->prepare("UPDATE commands SET state='".sanitize_vars($_POST['state'])."'".$req.",datereported='".sanitize_vars(strtotime(str_replace("/","-",$_POST['datereported'])))."',note='".sanitize_vars($_POST['note'])."',dateupdate='".time()."' WHERE id='".$_POST['id']."'");
				$req->execute();
				$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".sanitize_vars($_POST['state'])."','".$_SESSION['fullname']."','".time()."')");
				$req->execute();
				if($_POST['state'] == "Livré" AND $command['state'] != "Livré"){
					// Factures Clients
					$back = $bdd->query("SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0' AND validated='off' AND trash='1'");
					if($back->rowCount() == 1){
						$row = $back->fetch();
						if($settings['standardfees'] == "0"){
							$back = $bdd->query("SELECT deliveredfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
						}
						else{
							$back = $bdd->query("SELECT deliveredfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
							if($back->rowCount() == 0){
								$back = $bdd->query("SELECT deliveredfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
							}
						}
						$city = $back->fetch();	
						$back = $bdd->query("SELECT price FROM packaging WHERE id='".$command['package']."' AND trash='1'");
						$package = $back->fetch();
						$price = $command['price'] - $city['deliveredfees'] - intval($package['price']);
						$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$_POST['id']."')");
						$req->execute();
						$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands+1),price=(price+".$price.") WHERE code='".$row['code']."'");
						$req->execute();
					}
					else{
						if($settings['standardfees'] == "0"){
							$back = $bdd->query("SELECT deliveredfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
						}
						else{
							$back = $bdd->query("SELECT deliveredfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
							if($back->rowCount() == 0){
								$back = $bdd->query("SELECT deliveredfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
							}
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
						$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','".$command['dlm']."','0','1','".$price."','','','off','off','".time()."','','1')");
						$req->execute();
						$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$_POST['id']."')");
						$req->execute();
					}
				}
				elseif($_POST['state'] != "Livré" AND $command['state'] == "Livré"){
					// Factures Clients
					if($settings['standardfees'] == "0"){
						$back = $bdd->query("SELECT deliveredfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
					}
					else{
						$back = $bdd->query("SELECT deliveredfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
						if($back->rowCount() == 0){
							$back = $bdd->query("SELECT deliveredfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
						}
					}
					$city = $back->fetch();
					$back = $bdd->query("SELECT price FROM packaging WHERE id='".$command['package']."' AND trash='1'");
					$package = $back->fetch();
					$price = $command['price'] - $city['deliveredfees'] - intval($package['price']);						
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
				if($_POST['state'] == "Refusé" AND $command['state'] != "Refusé"){
					// Factures DLM
					$back = $bdd->query("SELECT code FROM factures WHERE client='0' AND dlm='".$command['dlm']."' AND validated='off' AND trash='1'");
					if($back->rowCount() == 1){
						$row = $back->fetch();
						$back = $bdd->query("SELECT refusedfees FROM shippingfees WHERE city='".$command['city']."' AND dlm='".$command['dlm']."' AND trash='1'");
						$city = $back->fetch();
						if($city['refusedfees'] != 0){
							$price = 0 - $command['extrafees'] - $city['refusedfees'];
							$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$_POST['id']."')");
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
							$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','".$command['client']."','1','".$price."','','','off','off','".time()."','','1')");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$_POST['id']."')");
							$req->execute();
						}
					}
				}
				elseif($_POST['state'] != "Refusé" AND $command['state'] == "Refusé"){
					// Factures DLM
					$back = $bdd->query("SELECT refusedfees FROM shippingfees WHERE city='".$command['city']."' AND dlm='".$command['dlm']."' AND trash='1'");
					$city = $back->fetch();
					if($city['refusedfees'] != 0){
						$price = 0 - $command['extrafees'] - $city['refusedfees'];
						$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$_POST['id']."' AND facture IN(SELECT code FROM factures WHERE client='0' AND dlm='".$command['dlm']."')");
						$row = $back->fetch();
						$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$_POST['id']."' AND facture IN(SELECT code FROM factures WHERE client='0' AND dlm='".$command['dlm']."')");
						$req->execute();
						$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands-1),price=(price-".$price.") WHERE code='".$row['facture']."'");
						$req->execute();
					}
				}
				if($_POST['state'] == "Refusé" AND $command['state'] != "Refusé"){
					// Factures Clients
					$back = $bdd->query("SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0' AND validated='off' AND trash='1'");
					if($back->rowCount() == 1){
						$row = $back->fetch();
						if($settings['standardfees'] == "0"){
							$back = $bdd->query("SELECT refusedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
						}
						else{
							$back = $bdd->query("SELECT refusedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
							if($back->rowCount() == 0){
								$back = $bdd->query("SELECT refusedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
							}
						}
						$city = $back->fetch();
						if($city['refusedfees'] != 0){
							$price = 0 - $city['refusedfees'];
							$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$_POST['id']."')");
							$req->execute();
							$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands+1),price=(price+".$price.") WHERE code='".$row['code']."'");
							$req->execute();
						}
					}
					else{
						if($settings['standardfees'] == "0"){
							$back = $bdd->query("SELECT refusedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
						}
						else{
							$back = $bdd->query("SELECT refusedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
							if($back->rowCount() == 0){
								$back = $bdd->query("SELECT refusedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
							}
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
							$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','".$command['client']."','1','".$price."','','','off','off','".time()."','','1')");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$_POST['id']."')");
							$req->execute();
						}
					}
				}
				elseif($_POST['state'] != "Refusé" AND $command['state'] == "Refusé"){
					// Factures Clients
					if($settings['standardfees'] == "0"){
						$back = $bdd->query("SELECT refusedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
					}
					else{
						$back = $bdd->query("SELECT refusedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
						if($back->rowCount() == 0){
							$back = $bdd->query("SELECT refusedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
						}
					}
					$city = $back->fetch();
					if($city['refusedfees'] != 0){
						$price = 0 - $city['refusedfees'];
						$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$_POST['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
						$row = $back->fetch();
						$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$_POST['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
						$req->execute();
						$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands-1),price=(price-".$price.") WHERE code='".$row['facture']."'");
						$req->execute();
					}
				}
				if($_POST['state'] == "Retourné" AND $command['state'] != "Retourné"){
					// Factures Clients
					$back = $bdd->query("SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0' AND validated='off' AND trash='1'");
					if($back->rowCount() > 0){
						$row = $back->fetch();
						if($settings['standardfees'] == "0"){
							$back = $bdd->query("SELECT returnedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
						}
						else{
							$back = $bdd->query("SELECT returnedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
							if($back->rowCount() == 0){
								$back = $bdd->query("SELECT returnedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
							}
						}
						$city = $back->fetch();
						if($city['returnedfees'] != 0){
							$price = 0 - $city['returnedfees'];
							$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$row['code']."','".$_POST['id']."')");
							$req->execute();
							$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands+1),price=(price+".$price.") WHERE code='".$row['code']."'");
							$req->execute();
						}
					}
					else{
						if($settings['standardfees'] == "0"){
							$back = $bdd->query("SELECT returnedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
						}
						else{
							$back = $bdd->query("SELECT returnedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
							if($back->rowCount() == 0){
								$back = $bdd->query("SELECT returnedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
							}
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
							$req = $bdd->prepare("INSERT INTO factures VALUES ('0','".$rand."','".$command['client']."','1','".$price."','0','','off','off','".time()."','','1')");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO facturesdetails VALUES ('0','".$rand."','".$_POST['id']."')");
							$req->execute();
						}
					}
				}
				elseif($_POST['state'] != "Retourné" AND $command['state'] == "Retourné"){
					// Factures Clients
					if($settings['standardfees'] == "0"){
						$back = $bdd->query("SELECT returnedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
					}
					else{
						$back = $bdd->query("SELECT returnedfees FROM clientfees WHERE city='".$command['city']."' AND client='".$command['client']."' AND trash='1'");
						if($back->rowCount() == 0){
							$back = $bdd->query("SELECT returnedfees FROM gshippingfees WHERE city='".$command['city']."' AND trash='1'");
						}
					}
					$city = $back->fetch();
					if($city['returnedfees'] != 0){
						$price = 0 - $city['returnedfees'];
						$back = $bdd->query("SELECT facture FROM facturesdetails WHERE command='".$_POST['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
						$row = $back->fetch();
						$req = $bdd->prepare("DELETE FROM facturesdetails WHERE command='".$_POST['id']."' AND facture IN(SELECT code FROM factures WHERE client='".$command['client']."' AND dlm='0')");
						$req->execute();
						$req = $bdd->prepare("UPDATE factures SET nbcommands=(nbcommands-1),price=(price-".$price.") WHERE code='".$row['facture']."'");
						$req->execute();
					}
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
				
				$back = $bdd->query("SELECT stockout FROM users WHERE id='".$command['client']."'");
				$row = $back->fetch();
				if($row['stockout'] != ""){
					sendToStockOUT($row['stockout'],$command['code'],$_POST['state'],$_POST['datereported'],$_POST['note']);
				}
			}		
			
			if($_POST['action'] == "loadcommands"){
				$extrareq = $userwhere;
				if($_SESSION['type'] == "dlm" AND $_SESSION['roles'] == "Affichage commun"){
					$extrareq = " AND (dlm='".$_POST['dlm']."' OR city IN(SELECT city FROM shippingfees WHERE dlm='".$_POST['dlm']."' AND trash='1'))";
				}
				if($_SESSION['type'] == "dlm" AND $settings['sepdelivered'] == "1" AND $_POST['statee'] != "Livré"){
					$extrareq .= " AND id NOT IN(SELECT command FROM facturesdetails WHERE facture IN(SELECT code FROM factures WHERE dlm='".$_SESSION['id']."' AND received='on'))";
				}
				if($_SESSION['type'] == "dlm" AND $settings['sepdelivered'] == "1" AND $_POST['statee'] == "Livré"){
					$extrareq .= " AND id IN(SELECT command FROM facturesdetails WHERE facture IN(SELECT code FROM factures WHERE dlm='".$_SESSION['id']."' AND received='on'))";
				}
				if($_SESSION['type'] == "moderator" AND $settings['sepdelivered'] == "1" AND $_POST['statee'] != "Livré"){
					$extrareq .= " AND invoiced='off' AND id NOT IN(SELECT command FROM facturesdetails WHERE facture IN(SELECT code FROM factures WHERE received='on'))";
				}
				if($_SESSION['type'] == "moderator" AND $settings['sepdelivered'] == "1" AND $_POST['statee'] == "Livré"){
					$extrareq .= " AND invoiced='on' AND id IN(SELECT command FROM facturesdetails WHERE facture IN(SELECT code FROM factures WHERE received='on'))";
				}
				$back = $bdd->query("SELECT id FROM commands WHERE trash='0' AND state NOT IN('Ajouté') AND collected='on'".$extrareq);
				?>
				<a href="javascript:;" class="lx-trash lx-trash-command"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM commands WHERE trash='1' AND state NOT IN('Ajouté') AND collected='on'".$extrareq);
				?>
				<a href="javascript:;" class="lx-trash lx-published-command"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
						<td>Produits <i class="fa fa-sort" data-sort="product"></i></td>
						<td>Prix <i class="fa fa-sort" data-sort="price"></i></td>
						<td>Etat <i class="fa fa-sort" data-sort="state"></i></td>
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
					$req = "SELECT * FROM commands WHERE trash='".$_POST['state']."' AND state NOT IN('Ajouté') AND collected='on'".$extrareq;
					if($_POST['keyword'] != ""){
						$req .= " AND (code LIKE '%".$_POST['keyword']."%' OR fullname LIKE '%".$_POST['keyword']."%' OR phone LIKE '%".$_POST['keyword']."%' OR address LIKE '%".$_POST['keyword']."%' OR city LIKE '%".$_POST['keyword']."%')";
					}
					if($_POST['client'] != ""){
						$req .= " AND client IN(SELECT id FROM users WHERE store IN('".str_replace(",","','",$_POST['client'])."') AND type='client' AND trash='1')";
					}
					if($_POST['dlm'] != ""){
						if($_SESSION['type'] != "dlm"){
							$req .= " AND dlm IN(SELECT id FROM users WHERE fullname IN('".str_replace(",","','",$_POST['dlm'])."') AND type='dlm' AND trash='1')";
						}
					}
					if($_POST['subdlm'] != ""){
						$req .= " AND subdlm='".$_POST['subdlm']."'";
					}	
					if($_POST['city'] != ""){
						$req .= " AND city IN('".str_replace(",","','",$_POST['city'])."')";
					}
					if($_POST['product'] != ""){
						$req .= " AND (product='".$row['id']."' OR product LIKE '".$row['id'].",%' OR product LIKE '%,".$row['id'].",%' OR product LIKE '%,".$row['id']."')";
					}
					if($_POST['statee'] != ""){
						$req .= " AND state IN ('".str_replace(",","','",$_POST['statee'])."')";
					}
					if($_POST['invoiced'] != ""){
						$req .= " AND invoiced='".$_POST['invoiced']."'";
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
						if($settings['appname'] == "Awid Livraison"){
							$req .= " ORDER BY CASE WHEN state IN('En cours','Interessé') THEN 0 ELSE 1 END, dateupdate";
						}
						else{
							$req .= " ORDER BY id";
						}
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
							if($_SESSION['type'] == "moderator"){
								$back1 = $bdd->query("SELECT fullname,phone FROM users WHERE id='".$row['dlm']."'");
								$row1 = $back1->fetch();
								if($row1['fullname'] != ""){
								?>
							<span style="font-weight:500;"><?php echo $row1['fullname']." (".$row1['phone'].")";?></span>
								<?php
								}
							}
							if($_SESSION['type'] == "dlm"){
								$back1 = $bdd->query("SELECT * FROM users WHERE id='".$row['subdlm']."'");
								$row1 = $back1->fetch();
								if($row1['fullname'] != ""){
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
								?>
							<span><?php echo $row['product'];?></span>
								<?php
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
						<?php
						$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".$row['state']."'");
						$row1 = $back1->fetch();
						?>
						<td>
							<?php
							if($row['invoiced'] == "off"){
								if($_SESSION['type'] == "moderator"){
									?>
							<span class="lx-edit-state lx-open-popup"
								data-id="<?php echo $row['id'];?>" 
								data-state="<?php echo $row['state'];?>"
								data-datereported="<?php echo ($row['datereported']!=""?date('d/m/Y',$row['datereported']):'');?>" 
								data-note="<?php echo $row['note'];?>" data-title="editstate"
								style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:4px;cursor:pointer;"><?php echo $row['state'];?></span>
									<?php									
								}
								elseif($_SESSION['type'] == "dlm" AND $row['state'] != "Livré"){
									?>
							<span class="lx-edit-state lx-open-popup" 
								data-id="<?php echo $row['id'];?>" 
								data-state="<?php echo $row['state'];?>"
								data-datereported="<?php echo ($row['datereported']!=""?date('d/m/Y',$row['datereported']):'');?>" 
								data-note="<?php echo $row['note'];?>" data-title="editstate"
								style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:4px;cursor:pointer;"><?php echo $row['state'];?></span>
									<?php									
								}
								else{
									?>
							<span class="lx-edit-state lx-open-popup" style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:4px;cursor:pointer;"><?php echo $row['state'];?></span>
									<?php
								}
							}
							else{
								?>
							<span class="lx-edit-state lx-open-popup" style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:4px;cursor:pointer;"><?php echo $row['state'];?></span>
								<?php								
							}
							if($row['datereported'] != ""){
								?>
							<span><?php echo date("d/m/Y",$row['datereported']);?></span>
								<?php
							}
							?>
						</td>
						<td>
							<?php
							if(preg_match("#^(Livré|Annulé|Refusé|Change)$#",$row['state'])){
								$back1 = $bdd->query("SELECT received FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND command='".$row['id']."' AND client<>'0' AND dlm='0'");
								if($_SESSION['type'] == "dlm"){
									$back1 = $bdd->query("SELECT received FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND command='".$row['id']."' AND dlm<>'0' AND client='0'");
								}
								$row1 = $back1->fetch();
								if($row1['received'] == 'off'){
									?>
						<span style="display:inline-block;padding:2px 5px;font-weight:500;background:orange;color:#FFFFFF;border-radius:4px;">Non</span>
									<?php
								}
								else{
									?>
						<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#71b44c;color:#FFFFFF;border-radius:4px;">Oui</span>
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
							if(preg_match("#^(Livré|Annulé|Refusé|Change)$#",$row['state'])){
								$back1 = $bdd->query("SELECT received FROM factures f,facturesdetails fd WHERE f.code=fd.facture AND command='".$row['id']."' AND dlm<>'0' AND client='0'");
								$row1 = $back1->fetch();
								if($row1['received'] == 'off'){
									?>
						<span style="display:inline-block;padding:2px 5px;font-weight:500;background:orange;color:#FFFFFF;border-radius:4px;">Non</span>
									<?php
								}
								else{
									?>
						<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#71b44c;color:#FFFFFF;border-radius:4px;">Oui</span>
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
							<span><b>Date ajout:</b><br /><?php echo ($row['dateadd']!=""?gmdate("d/m/Y H:i",$row['dateadd']):"&mdash;");?></span>
							<span><b>Date mise à jour:</b><br /><?php echo ($row['dateupdate']!=""?gmdate("d/m/Y H:i",$row['dateupdate']):"&mdash;");?></span>
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
								data-note="<?php echo $row['note'];?>" data-title="command"><i class="fa fa-edit"></i></a><!--
								--><a href="javascript:;" class="lx-delete lx-print-ticket lx-open-popup" data-title="tickets" data-id="<?php echo $row['id'];?>"><i class="fa fa-print"></i></a><!--
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
								data-note="<?php echo $row['note'];?>" data-title="command"><i class="fa fa-edit"></i></a><!--
								--><a href="javascript:;" class="lx-delete lx-print-ticket lx-open-popup" data-title="tickets" data-id="<?php echo $row['id'];?>"><i class="fa fa-print"></i></a>
									<?php
								}
							}
							elseif($_SESSION['type'] == "client" AND $row['state'] != "Livré" AND $row['invoiced'] != "on"){
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
							<del style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $state['color'];?>;color:#FFFFFF;border-radius:4px;cursor:pointer;"><?php echo $row['state'];?></del>
							<?php
							echo "<br />".$row['agent'];
							}
							?>
							</span>
							<ins><?php echo gmdate("d/m/Y H:i",$row['dateadd']);?></ins>
							<?php
							$back1 = $bdd->query("SELECT state,color FROM trackingstates WHERE state='".$row['state']."'");
							$state = $back1->fetch();
							?>
							<span>
							<?php
							if($i%2!=0){
							?>
							<del style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $state['color'];?>;color:#FFFFFF;border-radius:4px;cursor:pointer;"><?php echo $row['state'];?></del>
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

			if($_POST['action'] == "loadbls"){
				$req = " AND code LIKE '".$_POST['type']."-%'";
				if($_SESSION['type'] == "moderator"){
					if($_POST['type'] == "BL" OR $_POST['type'] == "BRL"){
						$req .= " AND client='0'";
					}
					else{
						$req .= " AND dlm='0'";
					}
				}
				if($_SESSION['type'] == "client"){
					$req .= " AND dlm='0'";
				}	
				$back = $bdd->query("SELECT id FROM bls WHERE trash='0'".$req.$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-trash-bl"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM bls WHERE trash='1'".$req.$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-published-bl"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Code <i class="fa fa-sort" data-sort="product"></i></td>
						<?php
						if($_SESSION['type'] == "moderator"){
							if($_POST['type'] == "BL" OR $_POST['type'] == "BRL"){
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
						<td>Date creation <i class="fa fa-sort" data-sort="datecreated"></i></td>
						<?php
						if($_POST['type'] == "BL"){
						?>
						<td>Traité</td>
						<?php
						}
						?>
						<td>Action</td>
					</tr>
					<?php
					$req = "SELECT * FROM bls WHERE trash='".$_POST['state']."'".$req.$userwhere;
					if($_SESSION['type'] == "moderator"){
						if($_POST['type'] == "BL" OR $_POST['type'] == "BRL"){
							$req .= " AND client='0'";
						}
						else{
							$req .= " AND dlm='0'";
						}
					}
					if($_SESSION['type'] == "client"){
						$req .= " AND dlm='0'";
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
								if($_POST['type'] == "BL"){
									?>
							<a href="printblmoderator.php?tid=<?php echo $row['cmds'];?>&dlm=<?php echo $row['dlm'];?>"><?php echo $row['code'];?></a>
									<?php
								}
								elseif($_POST['type'] == "BR"){
									?>
							<a href="printbrmoderator.php?tid=<?php echo $row['cmds'];?>&client=<?php echo $row['client'];?>"><?php echo $row['code'];?></a>
									<?php									
								}
								elseif($_POST['type'] == "BRL"){
									?>
							<a href="printbrlmoderator.php?tid=<?php echo $row['cmds'];?>&dlm=<?php echo $row['dlm'];?>"><?php echo $row['code'];?></a>
									<?php									
								}							
							}
							elseif($_SESSION['type'] == "dlm"){
								if($_POST['type'] == "BSL"){
									?>
							<a href="printbslmoderator.php?tid=<?php echo $row['cmds'];?>&subdlm=<?php echo $row['subdlm'];?>"><?php echo $row['code'];?></a>
									<?php
								}						
							}
							?>
						</td>
						<?php
						if($_SESSION['type'] == "moderator"){
							if($_POST['type'] == "BL" OR $_POST['type'] == "BRL"){
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
						<td><?php echo count(explode(",",$row['cmds']))-1;?></td>
						<td><span><?php echo ($row['dateadd']!=""?gmdate("d/m/Y",$row['dateadd']):"&mdash;");?></span></td>
						<?php
						if($_POST['type'] == "BL"){
						?>
						<td>
							<?php
							if($_SESSION['type'] == "moderator"){
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
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:orange;color:#FFFFFF;border-radius:4px;">Non</span>
									<?php
								}
								else{
									?>
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#71b44c;color:#FFFFFF;border-radius:4px;">Oui</span>
									<?php
								}
							}
							?>						
						</td>
						<?php
						}
						?>
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
							if($_POST['type'] == "client"){
								$req .= " AND invoiced='off' AND state IN('Livré') AND trash='1'";
							}
							elseif($_POST['type'] == "dlm"){
								$req .= " AND invoiceddlm='off' AND state IN('Livré') AND trash='1'";
							}
						}
						else{
							$req .= " AND id IN(".(($_POST['commands']!="")?$_POST['commands']:0).") AND trash='1'";
						}
						if($_POST['type'] == "client"){
							$req .= " AND client='".$_POST['client']."'";
						}
						elseif($_POST['type'] == "dlm"){
							$req .= " AND dlm='".$_POST['dlm']."'";
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
							<td><span style="display:inline-block;padding:2px 5px;font-weight:500;background:<?php echo $row1['color'];?>;color:#FFFFFF;border-radius:4px;"><?php echo $row1['state'];?></span></td>
							<td><span><?php echo ($row['dateadd']!=""?gmdate("d/m/y H:i",$row['dateadd']):"&mdash;");?></span></td>
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
				$back = $bdd->query("SELECT id FROM factures WHERE trash='0'".$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-trash-facture"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM factures WHERE trash='1'".$userwhere);
				?>
				<a href="javascript:;" class="lx-trash lx-published-facture"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
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
						$req .= " AND code LIKE '%".$_POST['keyword']."%'";
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
						<td><span><?php echo ($row['datecreated']!=""?gmdate("d/m/Y",$row['datecreated']):"&mdash;");?></span></td>
						<td><span><?php echo ($row['datereceived']!=""?gmdate("d/m/Y",$row['datereceived']):"&mdash;");?></span></td>
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
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#71b44c;color:#FFFFFF;border-radius:4px;">Oui</span>
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
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:orange;color:#FFFFFF;border-radius:4px;">Non</span>
									<?php
								}
								else{
									?>
							<span style="display:inline-block;padding:2px 5px;font-weight:500;background:#71b44c;color:#FFFFFF;border-radius:4px;">Oui</span>
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
				$back = $bdd->query("SELECT id FROM expenses WHERE trash='0'");
				?>
				<a href="javascript:;" class="lx-trash lx-trash-expense"><i class="fa fa-trash-alt"></i> Corbeille (<?php echo $back->rowCount();?>)</a>
				<?php
				$back = $bdd->query("SELECT id FROM expenses WHERE trash='1'");
				?>
				<a href="javascript:;" class="lx-trash lx-published-expense"><i class="fa fa-trash-alt"></i> Publiés (<?php echo $back->rowCount();?>)</a>
				<table cellpadding="0" cellspacing="0">
					<tr class="lx-first-tr">
						<td><label><input type="checkbox" name="selectall" value="selectall" /><del class="checkmark"></del></label></td>
						<td>Montant <i class="fa fa-sort" data-sort="cost"></i></td>
						<td>Livreur <i class="fa fa-sort" data-sort="dlm"></i></td>
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
						<td>
							<?php
							$back1 = $bdd->query("SELECT fullname FROM users WHERE id='".$row['dlm']."'");
							$row1 = $back1->fetch();
							if($row1['fullname'] != ""){
								?>
							<span><?php echo $row1['fullname'];?></span>
								<?php
							}
							else{
								?>
							<span><?php echo $row['dlm'];?></span>
								<?php								
							}
							?>
						</td>
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

			if($_POST['action'] == "loadkpi"){
				$feestable = "shippingfees WHERE city=c.city AND dlm=c.dlm";
				if($_SESSION['type'] == "client"){
					$feestable = "clientfees WHERE city=c.city AND client=c.client";
				}
				$req = "";
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
					$req .= " AND (product='".$row['id']."' OR product LIKE '".$row['id'].",%' OR product LIKE '%,".$row['id'].",%' OR product LIKE '%,".$row['id']."')";
				}
				if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
					$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
					$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
					$req .= " AND (dateupdate BETWEEN '".$datestart."' AND '".$dateend."')";
				}
				$back = $bdd->query("SELECT id FROM commands WHERE trash='1'".$req);
				$nbtotal = $back->rowCount();
				if($nbtotal == 0){
					$nbtotal = 1;
				}
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price) AS sm FROM commands c WHERE trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g3 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #39add1;">
						<a href="javascript:;">
							<span>Total Chiffre d'affaire (CA)</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (100%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<?php
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g3 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #39add1;">
						<a href="javascript:;">
							<span>CA sans frais de livraison</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (100%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<?php
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM((SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Livré') AND trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g3 lx-pr-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #39add1;">
						<a href="javascript:;">
							<span>Frais de livraison</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (100%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<div class="lx-clear-fix"></div>
				<?php
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state NOT IN('Livré','Annulé','Refusé','Retourné') AND trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g5 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #8dce68;">
						<a href="commands.php?s=En cours,Expédié,Injoignable,Pas de réponse,Pas de réponse 2 fois,Pas de réponse 3 fois,Pas de réponse 4 fois,Pas de réponse 5 fois,Reporté,Interessé">
							<span>Commandes en circulation</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (<?php echo round(($row['nb']*100)/$nbtotal,2)?>%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<?php
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('En cours','Expédié') AND trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g5 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #FFAA00;">
						<a href="commands.php?s=En cours,Expédié">
							<span>En cours & Expédié</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (<?php echo round(($row['nb']*100)/$nbtotal,2)?>%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<?php
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE (state IN('Injoignable') OR state LIKE '%Pas de réponse%') AND trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g5 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #FF7700;">
						<a href="commands.php?s=Injoignable,Pas de réponse,Pas de réponse 2 fois,Pas de réponse 3 fois,Pas de réponse 4 fois,Pas de réponse 5 fois">
							<span>PDR & Injoignable</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (<?php echo round(($row['nb']*100)/$nbtotal,2)?>%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<?php
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Reporté','Interessé') AND trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g5 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #00CCFF;">
						<a href="commands.php?s=Reporté,Interessé">
							<span>Reporté & Interessé</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (<?php echo round(($row['nb']*100)/$nbtotal,2)?>%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<?php
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Annulé','Refusé','Retourné') AND trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g5 lx-plr-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #CC0000;">
						<a href="commands.php?s=Annulé,Refusé,Retourné">
							<span>Annulé & Refusé</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (<?php echo round(($row['nb']*100)/$nbtotal,2)?>%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<div class="lx-clear-fix"></div>
				<?php
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Livré') AND trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g3 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #008000;">
						<a href="commands.php?s=Livré">
							<span>Total livré</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (<?php echo round(($row['nb']*100)/$nbtotal,2)?>%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<?php
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Livré') AND invoiced='on' AND trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g3 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #008000;">
						<a href="commands.php?s=Livré">
							<span>Livré versé</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (<?php echo round(($row['nb']*100)/$nbtotal,2)?>%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<?php
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Livré') AND invoiced='off' AND trash='1'".$req);
				$row = $back->fetch();
				?>
				<div class="lx-g3 lx-plr-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #008000;">
						<a href="commands.php?s=Livré">
							<span>Livré non versé</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes (<?php echo round(($row['nb']*100)/$nbtotal,2)?>%)</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<div class="lx-clear-fix"></div>
				<?php
				if($_SESSION['type'] == "moderator"){
					$back = $bdd->query("SELECT COUNT(c.id) AS nb,SUM(cl.deliveredfees - sh.deliveredfees) AS sm FROM commands c,shippingfees sh,clientfees cl WHERE c.city=cl.city AND c.city=sh.city AND c.dlm=sh.dlm AND cl.client=c.client AND c.trash='1' AND state='Livré'".$req);
					$row = $back->fetch();
					$profit = $row['sm'];
					?>
				<div class="lx-g3 lx-plr-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #008000;">
						<a href="commands.php?s=Livré">
							<span>Benifices livraison</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del><?php echo $row['nb'];?> commandes</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
					<?php
					$req = "";
					if($_POST['dlm'] != ""){
						$req .= " AND c.dlm='".$_POST['dlm']."'";
					}
					if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
						$req .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					$back = $bdd->query("SELECT SUM(cost) AS sm FROM expenses WHERE trash='1'".$req);
					$row = $back->fetch();
					$expenses = $row['sm'];
					?>
				<div class="lx-g3 lx-plr-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #008000;">
						<a href="commands.php?s=Livré">
							<span>Dépenses et charges</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<del>Charges</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<div class="lx-g3 lx-plr-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:4px solid #008000;">
						<a href="commands.php?s=Livré">
							<span>Profit net</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $profit - $expenses;?> <?php echo $settings['currency'];?></strong>
							<br />
							<del>Gains</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<div class="lx-clear-fix"></div>
					<?php
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
				if($_POST['table'] == "commands" AND $_POST['column'] == "collected"){
					if($_POST['state'] == "on"){
						$req = $bdd->prepare("UPDATE commands SET state='En cours',dateupdate='".time()."' WHERE id='".$_POST['id']."'");
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
					$req = $bdd->prepare("UPDATE commands SET state='En cours',collected='on',dateupdate='".time()."' WHERE id IN(".$_POST['ids'].")");
					$req->execute();
					$back = $bdd->query("SELECT code,state FROM commands WHERE id IN(".$_POST['ids'].")");
					while($command = $back->fetch()){
						$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$command['code']."','".$command['state']."','".$_SESSION['fullname']."','".time()."')");
						$req->execute();	
					}
				}
				else{
					$req = $bdd->prepare("UPDATE `".$_POST['table']."` SET state='".$_POST['state']."' WHERE `".$_POST['column']."` IN(".$_POST['ids'].") AND state NOT IN('Ajouté','Changement adresse','Annulé','Refusé','Livré','Retourné','Retourné vers agence')");
					$req->execute();					
				}
			}
			
			if($_POST['action'] == "allnotifs"){
				$notifs = "";
				if($_SESSION['type'] == "moderator"){
					$commands = $bdd->query("SELECT id FROM commands WHERE state='Ajouté' AND trash='1'");
					$shipped = $bdd->query("SELECT id FROM commands WHERE state IN('En cours','Reporté','Changement adresse') AND trash='1'");
					$delivered = $bdd->query("SELECT id FROM commands WHERE state IN('Livré') AND invoiced='off' AND trash='1'");
					$shipments = $bdd->query("SELECT id FROM shipments WHERE received='off' AND trash='1'");
					$notifs = '{ notifs : [';
					$notifs .= '{ "commands" : "'.$commands->rowCount().'" , "shipped" : "'.$shipped->rowCount().'" , "shipments" : "'.$shipments->rowCount().'" }';
					$notifs .= '] }';
				}
				elseif($_SESSION['type'] == "dlm"){
					$shipped = $bdd->query("SELECT id FROM commands WHERE state IN('En cours','Reporté','Changement adresse') AND trash='1'".$userwhere);
					$notifs = '{ notifs : [';
					$notifs .= '{ "commands" : "0" , "shipped" : "'.$shipped->rowCount().'" , "shipments" : "0" }';
					$notifs .= '] }';
				}	
				elseif($_SESSION['type'] == "client"){
					$commands = $bdd->query("SELECT id FROM commands WHERE state='Ajouté' AND trash='1'".$userwhere);
					$shipped = $bdd->query("SELECT id FROM commands WHERE state IN('En cours','Reporté','Changement adresse') AND trash='1'".$userwhere);
					$delivered = $bdd->query("SELECT id FROM commands WHERE state IN('Livré') AND trash='1'".$userwhere);
					$shipments = $bdd->query("SELECT id FROM shipments WHERE received='off' AND trash='1'".$userwhere);
					$notifs = '{ notifs : [';
					$notifs .= '{ "commands" : "'.$commands->rowCount().'" , "shipped" : "'.$shipped->rowCount().'" , "shipments" : "'.$shipments->rowCount().'" }';
					$notifs .= '] }';
				}				
				echo $notifs;
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
?>
