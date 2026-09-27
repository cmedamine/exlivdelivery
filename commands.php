<?php
session_start();
include("config.php");

$_SESSION['errorimport'] = "";

if(!isset($_SESSION['id'])){
	header('location: login.php');
}
else{
	if(!in_array($_SESSION['type'], ["client", "dlm", "subdlm", "worker", "moderator"], true)
		OR ($_SESSION['type'] == "moderator" AND !preg_match("#Commandes#",$_SESSION['roles']))){
	header('location: 404.php');
	exit;
	}
}

if(isset($_SESSION['id']) AND isset($_SESSION['fullname'])){
?>
<!DOCTYPE html>
<html lang="zxx">
	<head>
		<meta charset="utf-8">
		<title><?php echo $settings['appname'];?> - CPanel</title>
		<meta name="description" content="<?php echo $settings['appname'];?> - CPanel">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex,nofollow" />
		<!-- General CSS Settings -->
		<link rel="stylesheet" href="css/general_style.css">
		<!-- Main Style of the template -->
		<link rel="stylesheet" href="css/main_style.php">
		<!-- Landing Page Style -->
		<link rel="stylesheet" href="css/reset_style.css">
		<!-- Awesomefont -->
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
		<!-- QRCode Scanner -->
		<link rel="stylesheet" type="text/css" href="css/qrcode-reader.css" />
		<!-- DateRangePicker -->
		<link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
		<!-- Fav Icon -->
		<link rel="shortcut icon" href="../favicon.ico">
		<?php include("onesignal.php");?>
	</head>
	<body>

		<!-- Wrapper -->
		<div class="lx-wrapper">
			<!-- Header -->
			<div class="lx-header">
				<?php include('header.php');?>
			</div>
			<!-- Main -->
			<div class="lx-main">
				<div class="lx-main-leftside">
					<?php include('mainmenu.php');?>
				</div>
				<!-- Main Content -->
				<div class="lx-main-content">
					<div class="lx-page-header">
						<a href="commands.php?archived=1" class="lx-header-btn">Archive</a>
						<?php
						if($_SESSION['type'] != "client" AND $_SESSION['type'] != "worker"){
						?>
						<div style="background:#FFFFFF;position:relative;top:-3px;z-index:5;float:right;margin-right:5px;padding:7px;border:1px solid #242424;border-radius:6px;">
							<span>Réception</span>
							<div class="lx-on-off" style="float:right;margin-left:5px;" id="reception" data-state="off">
								<div class="lx-on-off-fill">
									<i class="material-icons">check</i>
									<span></span>
								</div>
							</div>	
						</div>
						<?php
						}
						?>
						<h2>Commandes<a href="javascript:;" id="my-notification-button" class="active"><i class="fa fa-bell"></i></a>
							<?php
							if($_SESSION['type'] != "client"){
								$extrareq = $userwhere;
								if($_SESSION['type'] == "dlm" AND $_SESSION['roles'] == "Affichage commun"){
									$extrareq = " AND (dlm='".$_SESSION['id']."' OR city IN(SELECT city FROM shippingfees WHERE dlm='".$_SESSION['id']."' AND trash='1'))";
								}
								$back = $bdd->query("SELECT id FROM commands WHERE state='Reporté' AND (datereported BETWEEN '".(strtotime(date("d-m-Y")))."' AND '".(strtotime(date("d-m-Y"))+(60*60*24)-1)."') AND trash='1'".$extrareq);
								if($back->rowCount() > 0){
									?>
							<a href="javascript:;" class="lx-reported-blink blink" data-datereport="<?php echo date("d/m/Y");?>"><?php echo $back->rowCount();?> reporté</a>
									<?php
								}
								$back = $bdd->query("SELECT id FROM commands WHERE state='Interessé' AND trash='1'".$extrareq);
								if($back->rowCount() > 0){
									?>
							<a href="javascript:;" class="lx-interested-blink blink"><?php echo $back->rowCount();?> Interessé</a>
									<?php
								}
								$back = $bdd->query("SELECT id FROM commands WHERE state='Changement adresse' AND trash='1'".$extrareq);
								if($back->rowCount() > 0){
									?>
							<a href="javascript:;" class="lx-changeaddress-blink blink"><?php echo $back->rowCount();?> Changement adresse</a>
									<?php
								}
							}
							?>
						</h2>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-page-content">
						<div class="lx-g1">
							<div class="lx-keyword">
								<label><a href="javascript:;" class="lx-search-keyword"><i class="fa fa-search"></i></a><input type="text" name="keyword" id="keyword" placeholder="Mot clé" data-table="commands" /></label>
								<label class="lx-advanced-select" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
									<i class="fa fa-caret-down"></i>
									<input type="text" name="client" id="clients" placeholder="Choisissez un client" readonly />
									<div>
										<a href="javascript:;" class="lx-state-empty">Vider</a>
										<a href="javascript:;" class="lx-state-filter">Filtrer</a>
										<div class="lx-clear-fix"></div>
										<input type="text" name="searchadvanced" style="margin-bottom:20px;" />
										<ul>
											<?php
											$back = $bdd->query("SELECT fullname,store FROM users WHERE type='client' AND trash='1' ORDER BY fullname");
											if($_SESSION['type']=="client"){
												$back = $bdd->query("SELECT fullname,store FROM users WHERE type='client' AND id='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");		
											}
											while($row = $back->fetch()){
												?>
											<li><label><input type="checkbox" value="<?php echo $row['fullname']!=""?$row['fullname']:$row['store'];?>" /> <?php echo $row['store']." (".$row['fullname'].")";?><del class="checkmark"></del></label></li>
												<?php
											}
											?>
										</ul>
									</div>
								</label>
								<label class="lx-advanced-select" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
									<i class="fa fa-caret-down"></i>
									<input type="text" name="worker" id="workers" placeholder="Choisissez un agent" readonly />
									<div>
										<a href="javascript:;" class="lx-state-empty">Vider</a>
										<a href="javascript:;" class="lx-state-filter">Filtrer</a>
										<div class="lx-clear-fix"></div>
										<input type="text" name="searchadvanced" style="margin-bottom:20px;" />
										<ul>
											<?php
											$back = $bdd->query("SELECT fullname FROM users WHERE type='worker' AND trash='1' ORDER BY fullname");
											if($_SESSION['type']=="worker"){
												$back = $bdd->query("SELECT fullname FROM users WHERE type='worker' AND id='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");		
											}
											while($row = $back->fetch()){
												?>
											<li><label><input type="checkbox" value="<?php echo $row['fullname']!=""?$row['fullname']:$row['store'];?>" /> <?php echo $row['fullname'];?><del class="checkmark"></del></label></li>
												<?php
											}
											?>
										</ul>
									</div>
								</label>
								<label class="lx-advanced-select" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
									<i class="fa fa-caret-down"></i>
									<input type="text" name="dlm" id="dlms" placeholder="Choisissez un livreur" readonly />
									<div>
										<a href="javascript:;" class="lx-state-empty">Vider</a>
										<a href="javascript:;" class="lx-state-filter">Filtrer</a>
										<div class="lx-clear-fix"></div>
										<input type="text" name="searchadvanced" style="margin-bottom:20px;" />
										<ul>
											<?php
											$back = $bdd->query("SELECT fullname FROM users WHERE type='dlm' AND trash='1' ORDER BY fullname");
											if($_SESSION['type']=="dlm"){
												$back = $bdd->query("SELECT fullname FROM users WHERE type='dlm' AND id='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");		
											}
											while($row = $back->fetch()){
												?>
											<li><label><input type="checkbox" value="<?php echo $row['fullname'];?>" /> <?php echo $row['fullname'];?><del class="checkmark"></del></label></li>
												<?php
											}
											?>
										</ul>
									</div>
								</label>
								<label style="<?php if($_SESSION['type'] != "dlm"){echo "display:none;";}?>">
									<select name="subdlm" id="subdlm">
										<option value="">Tous les sous livreur</option>
										<?php
										$back = $bdd->query("SELECT u.id AS id,dlm,fullname FROM subdlm sd,users u WHERE sd.subdlm=u.id AND type='subdlm' AND trash='1' ORDER BY fullname");
										if($_SESSION['type'] == "dlm"){
											$back = $bdd->query("SELECT * FROM subdlm sd,users u WHERE sd.subdlm=u.id AND type='subdlm' AND dlm='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");
										}
										while($row = $back->fetch()){
											?>
										<option value="<?php echo $row['id'];?>" data-dlm="<?php echo $row['dlm']?>"><?php echo $row['fullname'];?></option>
											<?php 
										}
										?>
									</select>
								</label>
								<label class="lx-advanced-select">
									<i class="fa fa-caret-down"></i>
									<input type="text" name="city" id="cities" placeholder="Choisissez une ville" readonly />
									<div>
										<a href="javascript:;" class="lx-state-empty">Vider</a>
										<a href="javascript:;" class="lx-state-filter">Filtrer</a>
										<div class="lx-clear-fix"></div>
										<input type="text" name="searchadvanced" style="margin-bottom:20px;" />
										<ul>
											<?php
											// The global city filter must use the configured city list.  Shipping
											// fees are assigned later and can legitimately be empty.
											$back = $bdd->query("SELECT city FROM cities WHERE trash='1' ORDER BY city");
											if($_SESSION['type'] == "dlm"){
												$back = $bdd->query("SELECT DISTINCT city FROM shippingfees WHERE trash='1' AND dlm='".$_SESSION['id']."' ORDER BY city");
											}
											while($row = $back->fetch()){
												?>
											<li><label><input type="checkbox" value="<?php echo $row['city'];?>" /> <?php echo $row['city'];?><del class="checkmark"></del></label></li>
												<?php
											}
											?>
										</ul>
									</div>
								</label>
								<label style="<?php if($_SESSION['type'] == "dlm"){echo "display:none;";}?>">
									<select name="product" id="product">
										<option value="">Tous les produits</option>
										<?php
										$back = $bdd->query("SELECT id,title FROM stocks WHERE trash='1' ORDER BY id");
										if($_SESSION['type'] == "client"){
											$back = $bdd->query("SELECT id,title FROM stocks WHERE client='".$_SESSION['id']."' AND trash='1' ORDER BY id");
										}
										if($_SESSION['type'] == "dlm"){
											$back = $bdd->query("SELECT id,title FROM stocks WHERE 1=2");
										}
										while($row = $back->fetch()){
											?>
										<option value="<?php echo $row['id'];?>"><?php echo $row['title'];?></option>
											<?php 
										}
										?>
									</select>
								</label>
								<label class="lx-advanced-select">
									<i class="fa fa-caret-down"></i>
									<input type="text" name="state" id="states" value="<?php if(isset($_GET['s'])){if($_GET['s'] != ""){echo $_GET['s'];}}?>" placeholder="Choisissez une état" readonly />
									<div>
										<a href="javascript:;" class="lx-state-empty">Vider</a>
										<a href="javascript:;" class="lx-state-filter">Filtrer</a>
										<div class="lx-clear-fix"></div>
										<input type="text" name="searchadvanced" style="margin-bottom:20px;" />
										<ul>
											<?php
											$extrareq = "";
											if(isset($_GET['action'])){
												if($_GET['action'] == "loadcmdencours"){
													$extrareq .= " AND state NOT IN('Livré','Annulé','Refusé','Retourné','Retourné vers agence','Retour client reçu','Demande de retour','Demande de retour par administration','Hors zone','Manque de stock','Retour reçu agence casablanca') AND state NOT LIKE '%Retour%'";
												}
												if($_GET['action'] == "loadcmddelivered"){
													$extrareq .= " AND state='Livré'";
												}
												if($_GET['action'] == "loadcmdfailed"){
													$extrareq .= " AND (state IN('Annulé','Refusé','Retourné','Retourné vers agence','Retour client reçu','Demande de retour','Demande de retour par administration','Hors zone','Manque de stock','Retour reçu agence casablanca') OR state LIKE '%Retour%')";
												}
											}
											// A command may be cancelled, returned, confirmed, or in delivery;
											// do not hide valid active states by restricting this filter to one phase.
											$back = $bdd->query("SELECT state FROM trackingstates WHERE trash='1'".$extrareq." ORDER BY state");
											while($row = $back->fetch()){
												?>
											<li><label><input type="checkbox" value="<?php echo $row['state'];?>" <?php if(isset($_GET['s'])){if(preg_match("#".str_replace(",","|",$_GET['s'])."#",$row['state'])){echo "checked";}}?> /> <?php echo $row['state'];?><del class="checkmark"></del></label></li>
												<?php
											}
											?>
										</ul>
									</div>
								</label>
								<label>
									<select name="invoiced" id="invoiced">
										<option value="">Facturé Oui/Non</option>
										<option value="on">Oui</option>
										<option value="off">Non</option>
									</select>
								</label>
								<label><input type="text" name="dateadd" id="dateadd" data-table="commands" placeholder="Date ajout" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestart" id="datestart" />
								<input type="hidden" name="dateend" id="dateend" />
								<label><input type="text" name="dateupdate" id="dateupdate" data-table="commands" value="<?php echo isset($_GET['dateupdatestart'])?$_GET['dateupdatestart']." - ".$_GET['dateupdateend']:"";?>" placeholder="Date mise à jour" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestartupdate" id="datestartupdate" value="<?php echo isset($_GET['dateupdatestart'])?$_GET['dateupdatestart']:"";?>" />
								<input type="hidden" name="dateendupdate" id="dateendupdate" value="<?php echo isset($_GET['dateupdatestart'])?$_GET['dateupdateend']:"";?>" />
								<label><input type="text" name="datereport" id="datereport" data-table="commands" placeholder="Date reportation" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestartreport" id="datestartreport" />
								<input type="hidden" name="dateendreport" id="dateendreport" />
								<input type="hidden" name="sortby" value="" />
								<input type="hidden" name="orderby" value="DESC" />
								<input type="hidden" name="archived" id="archived" value="<?php echo isset($_GET['archived'])?1:0;?>" />
								<input type="hidden" name="archived" id="treated" value="<?php echo isset($_GET['treated'])?$_GET['treated']:"";?>" />
								<input type="hidden" name="sansdlm" id="sansdlm" value="<?php echo isset($_GET['dlm'])?$_GET['dlm']:"";?>" />
								<input type="hidden" name="codes" id="codes" />
							</div>
							<div class="lx-add-form">
								<?php
								if($_SESSION['type'] == "moderator"){
									?>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="tickets"><i class="fa fa-ticket-alt"></i> Etiquettes</a>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="bl"><i class="fa fa-print"></i> Bon de livraison</a>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="be"><i class="fa fa-print"></i> Bon d'envoi</a>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="br"><i class="fa fa-print"></i> Bon de retour client</a>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="brl"><i class="fa fa-print"></i> Bon de retour livreur</a>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="importer"><i class="fa fa-upload"></i> Importer</a>
								<a href="ExampleImportationEtats.xlsx" class="lx-new">Examplaire</a>
									<?php
								}
								if($_SESSION['type'] == "dlm"){
									?>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="tickets"><i class="fa fa-ticket-alt"></i> Etiquettes</a>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="bl"><i class="fa fa-print"></i> Bon de livraison</a>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="bsl"><i class="fa fa-print"></i> Bon de sous livreurs</a>
									<?php
								}
								?>
								<a href="javascript:;" class="lx-new lx-export-commands"><i class="fa fa-download"></i> Exporter</a>
								<?php
								if($_SESSION['type'] == "moderator"){
									?>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="smsmodel"><i class="fa fa-envelope"></i> SMS</a>
									<?php
								}
								if($_SESSION['type'] == "moderator" OR $_SESSION['type'] == "dlm" OR $_SESSION['type'] == "subdlm"){
									?>
								<a href="javascript:;" class="lx-new lx-new-scan lx-open-popup" data-title="qrcodereader">+ Scan QRCode / BarCode</a>
								<!--<a href="javascript:;" class="lx-new qrcode-reader" id="openreader-single" data-qrr-target="#qrcode" data-qrr-audio-feedback="true">Scan QRCode</a>
								<input type="hidden" id="qrcode" />-->
									<?php
								}
								?>
							</div>
							<div class="lx-table lx-table-commands">

							</div>
							<?php
							if($_SESSION['type'] == "moderator"){
							?>
							<div class="lx-action-bulk">
								<label><span>Action: </span>
									<select name="statebulk">
										<option value="">Choisissez une action</option>
										<option value="delete">Supprimer vers corbeille</option>
										<option value="deletepermenantly">Supprimer definitivement</option>
										<option value="restore">Restaurer</option>
										<?php
										$back = $bdd->query("SELECT state FROM trackingstates WHERE state NOT IN('Ajouté','Changement adresse','Annulé','Refusé','Livré','Retourné','Retourné vers agence') AND trash='1' ORDER BY state");
										while($row = $back->fetch()){
											?>
										<option value="<?php echo $row['state'];?>"><?php echo $row['state'];?></option>
											<?php 
										}
										?>
										<option value="addtoarchive">Envoyer vers l'archive</option>
										<option value="removefromarchive">Restaurer de l'archive</option>
									</select>
								</label>
								<a href="javascript:;">Appliquer</a>
							</div>
							<?php
							}
							$nb = 20;					
							?>
							<div class="lx-action-bulk">
								<label><span>Afficher: </span>
									<select name="nbrows">
										<option value="20" <?php if($nb==20){echo "selected";}?>>20</option>
										<option value="50" <?php if($nb==50){echo "selected";}?>>50</option>
										<option value="100" <?php if($nb==100){echo "selected";}?>>100</option>
										<option value="250" <?php if($nb==250){echo "selected";}?>>250</option>
									</select>
								</label><span>lignes par page</span>
							</div>
							<?php
							$extrareq = $userwhere;
							if($_SESSION['type'] == "dlm" AND $_SESSION['roles'] == "Affichage commun"){
								$extrareq .= " AND (dlm='".$_SESSION['id']."' OR city IN(SELECT city FROM shippingfees WHERE dlm='".$_SESSION['id']."' AND trash='1'))";
							}
							$req = "SELECT COUNT(*) AS nb FROM commands WHERE state NOT IN('Ajouté') AND trash='1'".$extrareq;
							if(isset($_GET['archived'])){
								if($_GET['archived'] == "1"){
									$req = "SELECT COUNT(*) AS nb FROM commandsarchive WHERE state NOT IN('Ajouté') AND trash='1'".$extrareq;
								}
							}
							$back = $bdd->query($req);
							$row = $back->fetch();
							?>
							<div class="lx-pagination" style="<?php if($row['nb'] <= $nb){echo "display:none;";}?>">
								<?php
								$nbpages = ceil($row['nb']/$nb);
								?>
								<ul data-table="commands" data-action="<?php echo isset($_GET['action'])?$_GET['action']:'';?>" data-state="1" data-start="0" data-nbpage="<?php echo $nb;?>" data-posts="<?php echo $row['nb'];?>">
									<li><span>Page <ins>1</ins> sur <abbr><?php echo $nbpages;?></abbr></span></li>
									<li><a href="javascript:;" class="previous disabled"><i class="fa fa-angle-left"></i></a></li>
									<li>
										<select id="pgbumber">
											<?php
											for($i=1;$i<=$nbpages;$i++){
												?>
											<option value="<?php echo ($i-1);?>"><?php echo $i;?></option>
												<?php
											}
											?>
										</select>
									</li>
									<li><a href="javascript:;" class="next <?php if($nbpages == 1){echo 'disabled';}?>"><i class="fa fa-angle-right"></i></a></li>
								</ul>
								<div class="lx-clear-fix"></div>
							</div>
							<div class="lx-clear-fix"></div>
						</div>
						<div class="lx-clear-fix"></div>
					</div>
					<div class="lx-clear-fix"></div>
				</div>
				<div class="lx-clear-fix"></div>
			</div>
			<?php
			if($_SESSION['type'] == "moderator" OR $_SESSION['type'] == "dlm"){
			?>
			<!-- End Popup -->
			<div tabindex="0" class="lx-popup command">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Mise à jour commande</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="commandsform">
										<div class="lx-textfield lx-g1 lx-pb-0" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
											<label><span>Client:</span>
												<select name="client" data-isnotempty="" data-message="Choisissez un client!!">
													<option value="">Tous les client</option>
													<?php
													$back = $bdd->query("SELECT id,fullname,store FROM users WHERE type='client' AND trash='1' ORDER BY fullname");
													if($_SESSION['type'] == "client"){
														$back = $bdd->query("SELECT id,fullname,store FROM users WHERE type='client' AND id='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");		
													}
													while($row = $back->fetch()){
														?>
													<option value="<?php echo $row['id'];?>" <?php if($row['id'] == $_SESSION['id']){echo "selected";}?>><?php echo $row['store']." (".$row['fullname'].")";?></option>
														<?php 
													}
													?>
												</select>
											</label>
										</div>
										<div class="lx-clear-fix"></div>
										<div style="<?php echo $_SESSION['type']=="dlm"?"display:none;":"";?>">
										<div class="lx-textfield lx-g3 lx-pb-0" style="display:none;">
											<label><span>Code d'envoi: </span><input type="text" name="code" /></label>
										</div>
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Destinataire: </span><input type="text" name="fullname" data-isnotempty="" data-message="Saisissez un destinataire!!" /></label>
										</div>
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Téléphone: </span><input type="text" name="phone" data-isphone="" data-message="Ex: 06xxxxxxxx, 07xxxxxxxx ..." /></label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Livreur:</span>
												<select name="dlm" data-isnotempty="" data-message="Choisissez un livreur!!">
													<option value="">Choisissez un livreur</option>
													<?php
													$back = $bdd->query("SELECT id,fullname FROM users WHERE type='dlm' AND trash='1' ORDER BY fullname");
													while($row = $back->fetch()){
														?>
													<option value="<?php echo $row['id'];?>"><?php echo $row['fullname'];?></option>
														<?php 
													}
													?>
												</select>
											</label>
										</div>
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Ville:</span>
												<select name="city" class="todropdown" data-isnotempty="" data-message="Choisissez une ville!!">
													<option value="">Choisissez une ville</option>
													<?php
													$back = $bdd->query("SELECT DISTINCT city FROM shippingfees ORDER BY city");
													while($row = $back->fetch()){
														?>
													<option value="<?php echo $row['city'];?>"><?php echo $row['city'];?></option>
														<?php 
													}
													?>
												</select>
											</label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>Addresse: </span><input type="text" name="address" data-isnotempty="" data-message="Saisissez une adresse!!" /></label>
										</div>	
										<div class="lx-clear-fix"></div>
										<div>
											<div class="lx-textfield lx-g1 lx-pb-0">
												<label><span>Produit: </span><input type="text" name="product" /></label>
											</div>	
											<div class="lx-clear-fix"></div>
											<div class="lx-textfield lx-g1 lx-pb-0">
												<label><input type="checkbox" name="fromstock" value="0" /> Depuis stock<del class="checkmark"></del></label>
											</div>
										</div>
										<div class="fromstock" style="display:none;">
											<?php
											$back = $bdd->query("SELECT id,title,qty,client FROM stocks WHERE trash='1' ORDER BY id");
											if($_SESSION['type'] == "client"){
												$back = $bdd->query("SELECT id,title,qty,client FROM stocks WHERE client='".$_SESSION['id']."' AND trash='1' ORDER BY id");
											}	
											?>
											<div>
												<div class="lx-textfield lx-g2 lx-pb-0">
													<label><span>Produit:</span>
														<select name="product">
															<option value="0">Tous les produits</option>
															<?php
															while($row = $back->fetch()){
																?>
															<option value="<?php echo $row['id'];?>" data-client="<?php echo $row['client'];?>"><?php echo $row['title'];?></option>
																<?php
															}
															?>
														</select>
													</label>
												</div>
												<div class="lx-textfield lx-g2 lx-pb-0">
													<label><span>Quantité: </span><input type="text" name="qty" /></label>
												</div>
												<div class="lx-clear-fix"></div>
											</div>
											<a href="javascript:;" class="lx-add-other-stock">Ajouter autre produit</a>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>Prix: </span><input type="text" name="price" data-isnumber="" data-message="Saisissez un prix" /></label>
										</div>
										<div class="lx-clear-fix"></div>
										</div>
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Frais supplémentaire: </span><input type="text" name="extrafees" /></label>
										</div>											
										<div style="<?php echo $_SESSION['type']=="dlm"?"display:none;":"";?>">
											<div class="lx-textfield lx-g2 lx-pb-0">
												<label><span>Emballage:</span>
													<select name="package">
														<option value="0">Choisissez un emballage</option>
														<?php
														$back = $bdd->query("SELECT id,title FROM packaging WHERE trash='1'");
														while($row = $back->fetch()){
															?>
														<option value="<?php echo $row['id'];?>"><?php echo $row['title'];?></option>
															<?php 
														}
														?>
													</select>
												</label>
											</div>
											<div class="lx-clear-fix"></div>
											<div class="lx-textfield lx-g1 lx-pb-0">
												<label><span>Note: </span><textarea name="note" /></textarea></label>
											</div>
											<div class="lx-clear-fix"></div>							
										</div>
										<div style="display:none;">
											<div class="lx-textfield lx-g1 lx-pb-0">
												<label><input type="checkbox" name="change" value="0" /> Change (S'il y a un colis a retourné)<del class="checkmark"></del></label>
											</div>
											<div class="lx-clear-fix"></div>
											<div class="lx-textfield lx-g1 lx-pb-0">
												<label><input type="checkbox" name="openpackage" value="1" /> Permettre l'ouverture de colis par le client<del class="checkmark"></del></label>
											</div>	
											<div class="lx-clear-fix"></div>							
										</div>
										<div class="lx-clear-fix"></div>							
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="0" />
											<a href="javascript:;">Enregistrer</a>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
			}
			?>
			<!-- End Popup -->	
			<div tabindex="0" class="lx-popup editstate">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Modifier état</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="editstateform">
										<div class="lx-textfield lx-g1 lx-pb-0">
											<?php
											$agent = "Modérateur";
											if($_SESSION['type'] == "dlm"){
												$agent = "Livreur";
											}
											if($_SESSION['type'] == "client"){
												$agent = "Client";
											}
											if($_SESSION['type'] == "worker"){
												$agent = "Agent de confirmation";
											}
											// State permissions are configured per role in trackingstates.  Do not
											// restrict the editor to the delivery phase: clients also need their
											// confirmation/cancellation states and delivery staff need pickup states.
											$back = $bdd->query("SELECT state,color FROM trackingstates WHERE agent LIKE '%".$agent."%' AND trash='1' ORDER BY state");
											while($row = $back->fetch()){
												?>
											<a href="javascript:;" class="lx-coli-state-delivarymen" data-state="<?php echo $row['state'];?>" style="font-weight:500;background:<?php echo $row['color'];?>;color:#FFFFFF;"><?php echo $row['state'];?></a>
												<?php
											}
											?>
											<input type="hidden" name="state" data-required="yes" /></label>
										</div>
										<div class="lx-textfield lx-g1 lx-pb-0 datereported" style="display:none;">
											<label><span>Date reporté: </span><input type="text" name="datereported" /></label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>Commentaire: </span><textarea name="comment" /></textarea></label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="0" />
											<a href="javascript:;">Enregistrer</a>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->
			<div tabindex="0" class="lx-popup changeaddress">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Changement d'adresse</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="changeaddressform">
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Destinataire: </span><input type="text" name="fullname" data-isnotempty="" data-message="Saisissez un destinataire!!" /></label>
										</div>
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Téléphone: </span><input type="text" name="phone" data-isphone="" data-message="Ex: 06xxxxxxxx, 07xxxxxxxx ..." /></label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>Addresse: </span><input type="text" name="address" data-isnotempty="" data-message="Saisissez une adresse!!" /></label>
										</div>										
										<div class="lx-clear-fix"></div>							
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="0" />
											<a href="javascript:;">Enregistrer</a>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->
			<div tabindex="0" class="lx-popup tickets">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Impression étiquettes</h3>
								</div>
								<div class="lx-add-form">
									<div class="lx-tickets-layouts">
										<a href="javascript:;" class="lx-print-tickets" data-model="2">
											<span class="lx-model2"></span>
											<span class="lx-model2"></span>
											1/2
										</a>
										<a href="javascript:;" class="lx-print-tickets" data-model="3">
											<span class="lx-model3"></span>
											<span class="lx-model3"></span>
											<span class="lx-model3"></span>
											<span class="lx-model3"></span>
											1/4
										</a>
										<a href="javascript:;" class="lx-print-tickets" data-model="1">
											<span class="lx-model1"></span>
											<span class="lx-model1"></span>
											<span class="lx-model1"></span>
											<span class="lx-model1"></span>
											<span class="lx-model1"></span>
											<span class="lx-model1"></span>
											1/6
										</a>
										<a href="javascript:;" class="lx-print-tickets" data-model="4">
											<span class="lx-model4"></span>
											<span class="lx-model4"></span>
											<span class="lx-model4"></span>
											<span class="lx-model4"></span>
											<span class="lx-model4"></span>
											<span class="lx-model4"></span>
											<span class="lx-model4"></span>
											<span class="lx-model4"></span>
											1/8
										</a>
										<a href="javascript:;" class="lx-print-tickets" data-model="5">
											<span class="lx-model5"></span><br />
											<span class="lx-model5"></span><br />
											<span class="lx-model5"></span><br />
											10x10 CM
										</a>
										<input type="hidden" name="idticket" id="idticket" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->
			<div tabindex="0" class="lx-popup importer">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Importer états</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="importstateform" enctype="multipart/form-data">
										<div class="lx-textfield lx-g1 lx-pb-0">
											<div class="lx-importer">
												<input type="file" name="xlsfile" id="importstates" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
												<span>Choisissez un fichier (excel)</span>
											</div>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="0" />
											<a href="javascript:;">Importer</a>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->
			<div tabindex="0" class="lx-popup commandhistory">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Historique du commande</h3>
								</div>
								<div class="lx-add-form">

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->	
			<div tabindex="0" class="lx-popup bl">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Impression bon de livraison</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="blform">
										<div class="lx-textfield lx-g1 lx-pb-0" style="<?php if($_SESSION['type'] != "moderator" AND $_SESSION['type'] != "worker"){echo "display:none;";}?>">
											<label><span>Livreur:</span>
												<select name="dlm" class="todropdown" data-isnotempty="" data-message="Choisissez un livreur!!">
													<option value="">Choisissez un livreur</option>
													<?php
													$back = $bdd->query("SELECT id,fullname FROM users WHERE type='dlm' AND trash='1' ORDER BY fullname");
													while($row = $back->fetch()){
														?>
													<option value="<?php echo $row['id'];?>"><?php echo $row['fullname'];?></option>
														<?php 
													}
													?>
												</select>
											</label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="0" />
											<a href="javascript:;">Enregistrer</a>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->	
			<div tabindex="0" class="lx-popup be">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Impression bon de livraison</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="beform">
										<div class="lx-textfield lx-g1 lx-pb-0" style="<?php if($_SESSION['type'] != "moderator" AND $_SESSION['type'] != "worker"){echo "display:none;";}?>">
											<label><span>Livreur:</span>
												<select name="dlm" class="todropdown" data-isnotempty="" data-message="Choisissez un livreur!!">
													<option value="">Choisissez un livreur</option>
													<?php
													$back = $bdd->query("SELECT id,fullname FROM users WHERE type='dlm' AND trash='1' ORDER BY fullname");
													while($row = $back->fetch()){
														?>
													<option value="<?php echo $row['id'];?>"><?php echo $row['fullname'];?></option>
														<?php 
													}
													?>
												</select>
											</label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="0" />
											<a href="javascript:;">Enregistrer</a>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->	
			<div tabindex="0" class="lx-popup bsl">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Impression bon de livraison</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="bslform">
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>Sous livreur:</span>
												<select name="subdlm" class="todropdown" data-isnotempty="" data-message="Choisissez un sous livreur!!">
													<option value="">Choisissez un sous livreur</option>
													<?php
													$back = $bdd->query("SELECT u.id AS id,dlm,fullname FROM subdlm sd,users u WHERE sd.subdlm=u.id AND type='subdlm' AND trash='1' ORDER BY fullname");
													if($_SESSION['type'] == "dlm"){
														$back = $bdd->query("SELECT u.id AS id,dlm,fullname FROM subdlm sd,users u WHERE sd.subdlm=u.id AND type='subdlm' AND dlm='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");
													}
													while($row = $back->fetch()){
														?>
													<option value="<?php echo $row['id'];?>"><?php echo $row['fullname'];?></option>
														<?php 
													}
													?>
												</select>
											</label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="0" />
											<a href="javascript:;" class="">Enregistrer</a>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->	
			<div tabindex="0" class="lx-popup br">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Impression bon de retour client</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="brform">
										<div class="lx-textfield lx-g1 lx-pb-0" style="<?php if($_SESSION['type'] != "moderator" AND $_SESSION['type'] != "worker"){echo "display:none;";}?>">
											<label><span>Client:</span>
												<select name="client" class="todropdown" data-isnotempty="" data-message="Choisissez un client!!">
													<option value="">Choisissez un client</option>
													<?php
													$back = $bdd->query("SELECT id,fullname,store FROM users WHERE type='client' AND trash='1' ORDER BY fullname");
													while($row = $back->fetch()){
														?>
													<option value="<?php echo $row['id'];?>" <?php if($row['id'] == $_SESSION['id']){echo "selected";}?>><?php echo $row['store']." (".$row['fullname'].")";?></option>
														<?php 
													}
													?>
												</select>
											</label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="0" />
											<a href="javascript:;">Enregistrer</a>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->	
			<div tabindex="0" class="lx-popup brl">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Impression bon de retour livreur</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="brlform">
										<div class="lx-textfield lx-g1 lx-pb-0" style="<?php if($_SESSION['type'] != "moderator" AND $_SESSION['type'] != "worker"){echo "display:none;";}?>">
											<label><span>Livreur:</span>
												<select name="dlm" class="todropdown" data-isnotempty="" data-message="Choisissez un livreur!!">
													<option value="">Choisissez un livreur</option>
													<?php
													$back = $bdd->query("SELECT id,fullname FROM users WHERE type='dlm' AND trash='1' ORDER BY fullname");
													while($row = $back->fetch()){
														?>
													<option value="<?php echo $row['id'];?>"><?php echo $row['fullname'];?></option>
														<?php 
													}
													?>
												</select>
											</label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="0" />
											<a href="javascript:;">Enregistrer</a>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->
			<div tabindex="0" class="lx-popup phone">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Téléphone</h3>
								</div>
								<div class="lx-add-form">
									<div class="lx-phone-layouts">
										<a href="#">
											<img src="images/phone-icon.png" />
										</a>
										<a href="#">
											<img src="images/sms-icon.png" />
										</a>
										<a href="#" target="_blank">
											<img src="images/whatsapp-icon.png" />
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->	
			<div tabindex="0" class="lx-popup smsmodel">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Envoi SMS</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="sendsmsform">
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>SMS:</span>
												<select name="sms" data-isnotempty="" data-message="Choisissez un modèle!!">
													<option value="">Choisissez un modèle</option>
													<?php
													$back = $bdd->query("SELECT id,state FROM smsmodels WHERE active='on' ORDER BY state");
													while($row = $back->fetch()){
														?>
													<option value="<?php echo $row['id'];?>"><?php echo $row['state'];?></option>
														<?php 
													}
													?>
												</select>
											</label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>Message: </span><textarea name="message" /></textarea></label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="" />
											<a href="javascript:;" class="">Envoyer</a>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->	
			<a href="javascript:;" class="lx-open-commande-receiver lx-open-popup" data-title="receivecommand"></a>
			<div tabindex="0" class="lx-popup receivecommand">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Réception commande</h3>
								</div>
								<div class="lx-add-form">
									<div class="lx-delete-box lx-commande-receiver">
										<p></p>
										<a href="javascript:;" class="lx-receive-record" data-code="">Oui</a>
										<a href="javascript:;" class="lx-cancel-delete">Non</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->	
			<div tabindex="0" class="lx-popup duplicatecommand">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Dupliquer une commande</h3>
								</div>
								<div class="lx-add-form">
									<div class="lx-delete-box">
										<p>Voulez vous vraiment dupliquer cette commande?</p>
										<a href="javascript:;" class="lx-duplicate-command" data-id="" data-phase="confirmation" data-state="Nouveau">Oui</a>
										<a href="javascript:;" class="lx-cancel-delete">Non</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Popup -->	
			<div tabindex="0" class="lx-popup qrcodereader">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Scanner</h3>
								</div>
								<div class="lx-add-form">
									<div class="lx-delete-box">
										<div id="reader" width="600px"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>		
			<!-- End Popup -->	
			<div tabindex="0" class="lx-popup deleterecord">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Confirmation suppression</h3>
								</div>
								<div class="lx-add-form">
									<div class="lx-delete-box">
										<p>Voulez vous vraiment supprimer cette commande?</p>
										<a href="javascript:;" class="lx-delete-record" data-action="deletecommand" data-id="">Oui</a>
										<a href="javascript:;" class="lx-cancel-delete">Non</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- JQuery Script -->
		<script src="js/jquery-1.12.4.min.js"></script>
		<!-- Popup Script -->
		<script src="js/jquery.popup.js"></script>
		<!-- Calendar Script -->
		<script src="js/moment.min.js"></script>
		<script src="js/daterangepicker.js"></script>
		<!-- Main Script -->
		<script src="js/qrcode-reader.min.js"></script>
		<script src="js/script.js"></script>
		<script src="js/html5-qrcode.min.js"></script>
		<script>
			$(".lx-new-scan").on("click",function(){
				$("#reader__dashboard_section_csr span button:eq(1)").trigger("click");
			});

			$(document).ready(function(){
				loadCommands($(".lx-pagination ul").attr("data-state"));
				toDropDown();
			});
			
			function onScanSuccess(decodedText, decodedResult) {
				// Handle on success condition with the decoded text or result.
				// console.log(`Scan result: ${decodedText}`, decodedResult);
				<?php
				if($_SESSION['type'] == "moderator"){
				?>
				if($("#reception").attr("data-state") === "off" && $(".scanstate").css("display") !== "block"){
					//returnThis(`${decodedText}`);
				}
				<?php
				}
				if($_SESSION['type'] != "client"){
				?>
				if($("#reception").attr("data-state") === "on"){
					receiveThis(`${decodedText}`);
				}
				<?php
				}
				?>
				if($(".scanstate").css("display") === "block"){
					scanThis($("#scanstateform input[name='state']").val(),`${decodedText}`);
				}
				$("#keyword").val(`${decodedText}`);
				$("#codes").val($("#codes").val()+","+`${decodedText}`);
			}
			
			var qrboxwidth = 250;
			if($(window).width() < 768){
				qrboxwidth = 150;
			}
			
			var html5QrcodeScanner = new Html5QrcodeScanner(
				"reader", { fps: 2, qrbox: qrboxwidth });
			html5QrcodeScanner.render(onScanSuccess);
	
			var barcode = '';
			var interval;
			document.addEventListener('keydown', function(evt) {
				if (interval)
					clearInterval(interval);
				if (evt.code == 'Enter') {
					if (barcode)
						handleBarcode(barcode);
					barcode = '';
					return;
				}
				if (evt.key != 'Shift')
					barcode += evt.key;
				interval = setInterval(() => barcode = '', 20);
			});

			function handleBarcode(scanned_barcode) {
				var scanned_barcode = scanned_barcode.replace("CapsLock","");
				scanned_barcode = scanned_barcode.replace("NumLock","");
				<?php
				if($_SESSION['type'] == "moderator"){
				?>
				if($("#reception").attr("data-state") === "off" && $(".scanstate").css("display") !== "block"){
					//returnThis(scanned_barcode);
				}
				<?php
				}
				if($_SESSION['type'] != "client"){
				?>
				if($("#reception").attr("data-state") === "on"){
					receiveThis(scanned_barcode);
				}
				<?php
				}
				?>
				if($(".scanstate").css("display") === "block"){
					scanThis($("#scanstateform input[name='state']").val(),scanned_barcode);
				}
				$("#keyword").val(scanned_barcode);
				$("#codes").val($("#codes").val()+","+scanned_barcode);
			}
			
			function scanThis(state,code){
				// console.log(state,code);
				if(state !== ""){
					var ajaxurl = "ajax.php";
					$.ajax({
						url : ajaxurl,
						type : 'post',
						data : {
							state : state,
							code : code,
							action : 'scanthis'
						},
						success : function(response){
							if(response !== ""){
								var audio = new Audio('scanerror.mp3');
								audio.play();
								$(".lx-floating-response").remove();
								window.clearTimeout(timer);
								$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> '+response+' !!<i class="material-icons">close</i></p></div>');
								$(".lx-floating-response").fadeIn();
								timer = window.setTimeout(function(){
									$(".lx-floating-response").fadeOut();
								},5000);
							}
							else{
								var audio = new Audio('scan.mp3');
								audio.play();
								$(".lx-floating-response").remove();
								window.clearTimeout(timer);
								$("body").append('<div class="lx-floating-response"><p class="lx-succes"><i class="material-icons">check</i> Etat enregistré<i class="material-icons">close</i></p></div>');
								$(".lx-floating-response").fadeIn();
								timer = window.setTimeout(function(){
									$(".lx-floating-response").fadeOut();
								},5000);
							}
						}
					});	
				}
				else{
					$(".lx-floating-response").remove();
					window.clearTimeout(timer);
					$("body").append('<div class="lx-floating-response"><p class="lx-error"><i class="material-icons">error_outline</i> Choisissez une état !!<i class="material-icons">close</i></p></div>');
					$(".lx-floating-response").fadeIn();
					timer = window.setTimeout(function(){
						$(".lx-floating-response").fadeOut();
					},5000);						
				}
			}

			$(".lx-receive-record").on("click",function(){
				receiveThis($(this).attr("data-code"));
			});

			$(function() {
				/*$(".qrcode-reader").qrCodeReader({
					callback: function(codes) {
						<?php
						if(!isset($_GET['dlm'])){
							?>
						$("#keyword").val(codes);
						loadCommands($(".lx-pagination ul").attr("data-state"));
							<?php
							if($_SESSION['type'] == 'moderator'){
								?>
						if($("#reception").attr("data-state") === "off"){
							returnThis(codes);
						}
								<?php
							}
							?>
						if($("#reception").attr("data-state") === "on"){
							receiveThis(codes);
							// $(".lx-open-commande-receiver").trigger("click");
							// $(".lx-commande-receiver p").html(codes);
							// $(".lx-receive-record").attr("data-code",codes);
						}
							<?php
						}
						else{
							?>
						$("input[type='checkbox'][name='command'][data-code='"+codes+"']").prop("checked",true);
							<?php
						}
						?>
					}
				});*/
				
				$('input[name="dateadd"]').daterangepicker({
					locale: {
					  format: 'DD/MM/YYYY'
					},
					ranges: {
						'Today': [moment(), moment()],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
						'Last 30 Days': [moment().subtract(29, 'days'), moment()],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					},
					"linkedCalendars": false,
					"autoUpdateInput": false,
					"showCustomRangeLabel": false,
					"alwaysShowCalendars": true
					}, function(start, end, label) {
						$('input[name="dateadd"]').val(start.format('DD/MM/YYYY') + " - " + end.format('DD/MM/YYYY'));
						$('input[name="datestart"]').val(start.format('DD/MM/YYYY'));
						$('input[name="dateend"]').val(end.format('DD/MM/YYYY'));
						loadCommands($(".lx-pagination ul").attr("data-state"));
				});
				$('input[name="dateadd"]').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('input[name="datestart"]').val('');
					$('input[name="dateend"]').val('');
					loadCommands($(".lx-pagination ul").attr("data-state"));
				});
				$('input[name="dateupdate"]').daterangepicker({
					locale: {
					  format: 'DD/MM/YYYY'
					},
					ranges: {
						'Today': [moment(), moment()],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
						'Last 30 Days': [moment().subtract(29, 'days'), moment()],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					},
					"linkedCalendars": false,
					"autoUpdateInput": false,
					"showCustomRangeLabel": false,
					"alwaysShowCalendars": true
					}, function(start, end, label) {
						$('input[name="dateupdate"]').val(start.format('DD/MM/YYYY') + " - " + end.format('DD/MM/YYYY'));
						$('input[name="datestartupdate"]').val(start.format('DD/MM/YYYY'));
						$('input[name="dateendupdate"]').val(end.format('DD/MM/YYYY'));
						loadCommands($(".lx-pagination ul").attr("data-state"));
				});
				$('input[name="dateupdate"]').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('input[name="datestartupdate"]').val('');
					$('input[name="dateendupdate"]').val('');
					loadCommands($(".lx-pagination ul").attr("data-state"));
				});
				$('input[name="datereport"]').daterangepicker({
					locale: {
					  format: 'DD/MM/YYYY'
					},
					ranges: {
						'Today': [moment(), moment()],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
						'Last 30 Days': [moment().subtract(29, 'days'), moment()],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					},
					"linkedCalendars": false,
					"autoUpdateInput": false,
					"showCustomRangeLabel": false,
					"alwaysShowCalendars": true
					}, function(start, end, label) {
						$('input[name="datereport"]').val(start.format('DD/MM/YYYY') + " - " + end.format('DD/MM/YYYY'));
						$('input[name="datestartreport"]').val(start.format('DD/MM/YYYY'));
						$('input[name="dateendreport"]').val(end.format('DD/MM/YYYY'));
						loadCommands($(".lx-pagination ul").attr("data-state"));
				});
				$('input[name="datereport"]').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('input[name="datestartreport"]').val('');
					$('input[name="dateendreport"]').val('');
					loadCommands($(".lx-pagination ul").attr("data-state"));
				});
				$('input[name="datereported"]').daterangepicker({
					locale: {
					  format: 'DD/MM/YYYY'
					},
					singleDatePicker: true,
					showDropdowns: true
				});
			});
		</script>
	</body>
</html>
<?php

}
?>
