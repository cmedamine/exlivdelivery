<?php
session_start();
include("config.php");

$_SESSION['errorimport'] = "";

if(!isset($_SESSION['id'])){
	header('location: login.php');
}
else{
	if(!preg_match("#Ramassage#",$_SESSION['roles']) AND $_SESSION['type'] == "moderator"){	
		header('location: 404.php');
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
						<h2>Ramassage</h2>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-page-content">
						<div class="lx-g1">
							<div class="lx-keyword">
								<label><a href="javascript:;" class="lx-search-keyword"><i class="fa fa-search"></i></a><input type="text" name="keyword" id="keyword" placeholder="Mot clé" data-table="commands" /></label>
								<label style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
									<select name="client" id="client">
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
								<label style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
									<select name="worker" id="worker">
										<option value="">Tous les agents</option>
										<?php
										$back = $bdd->query("SELECT id,fullname FROM users WHERE type='worker' AND trash='1' ORDER BY fullname");
										if($_SESSION['type'] == "worker"){
											$back = $bdd->query("SELECT id,fullname FROM users WHERE type='worker' AND id='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");		
										}
										while($row = $back->fetch()){
											?>
										<option value="<?php echo $row['id'];?>" <?php if($row['id'] == $_SESSION['id']){echo "selected";}?>><?php echo $row['fullname'];?></option>
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
											$back = $bdd->query("SELECT DISTINCT city FROM shippingfees WHERE trash='1' ORDER BY city");
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
								<label>
									<select name="product" id="product">
										<option value="">Tous les produits</option>
										<?php
										$back = $bdd->query("SELECT id,title FROM stocks WHERE trash='1' ORDER BY id");
										if($_SESSION['type'] == "client"){
											$back = $bdd->query("SELECT id,title FROM stocks WHERE client='".$_SESSION['id']."' AND trash='1' ORDER BY id");
										}
										while($row = $back->fetch()){
											?>
										<option value="<?php echo $row['id'];?>"><?php echo $row['title'];?></option>
											<?php 
										}
										?>
									</select>
								</label>
								<label><input type="text" name="dateadd" id="dateadd" data-table="commands" placeholder="Date" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestart" id="datestart" />
								<input type="hidden" name="dateend" id="dateend" />
								<input type="hidden" name="loadphase" value="shipping" />
								<input type="hidden" name="sortby" value="" />
								<input type="hidden" name="orderby" value="DESC" />
							</div>
							<div class="lx-add-form">
								<a href="javascript:;" class="lx-new lx-new-command lx-open-popup" data-title="command">+ Nouveau commande</a>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="tickets"><i class="fa fa-ticket-alt"></i> Etiquettes</a>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="bl"><i class="fa fa-print"></i> Bon de livraison</a>
								<a href="javascript:;" class="lx-new lx-open-popup" data-title="importer"><i class="fa fa-upload"></i> Importer</a>
								<a href="ExampleImportationColis.xlsx" class="lx-new">Examplaire</a>
								<a href="errorimport.php" class="lx-new lx-error-import" style="display:none;">Commandes non importés</a>
								<?php
								if($_SESSION['type'] == "moderator"){
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
							<div class="lx-action-bulk">
								<label><span>Action: </span>
									<select name="statebulk">
										<option value="">Choisissez une action</option>
										<option value="delete">Supprimer vers corbeille</option>
										<option value="deletepermenantly">Supprimer definitivement</option>
										<option value="restore">Restaurer</option>
										<?php
										if($_SESSION['type'] == "moderator"){
										?>
										<option value="collect">Ramasser</option>
										<?php
										}
										?>
									</select>
								</label>
								<a href="javascript:;">Appliquer</a>
							</div>
							<?php
							$nb = 20;
							if($parametres['nbrows'] != "" AND $parametres['nbrows'] != "0"){
								$nb = $parametres['nbrows'];
							}						
							?>
							<div class="lx-action-bulk">
								<label><span>Afficher: </span>
									<select name="nbrows">
										<option value="20" <?php if($nb==20){echo "selected";}?>>20</option>
										<option value="50" <?php if($nb==50){echo "selected";}?>>50</option>
										<option value="100" <?php if($nb==100){echo "selected";}?>>100</option>
										<option value="250" <?php if($nb==250){echo "selected";}?>>250</option>
										<option value="500" <?php if($nb==500){echo "selected";}?>>500</option>
										<option value="1000" <?php if($nb==500){echo "selected";}?>>1000</option>
									</select>
								</label><span>lignes par page</span>
							</div>
							<?php
							if($_SESSION['type'] == "dlm"){
								$back = $bdd->query("SELECT GROUP_CONCAT(city SEPARATOR ',') AS cities FROM shippingfees WHERE dlm='".$_SESSION['id']."' AND trash='1'");
								$row = $back->fetch();
								$userwhere = " AND client IN(SELECT id FROM users WHERE city IN('".str_replace(",","','",addslashes($row['cities']))."'))";
							}
							$back = $bdd->query("SELECT COUNT(*) AS nb FROM commands WHERE trash='1'".$userwhere);
							$row = $back->fetch();
							?>
							<div class="lx-pagination" style="<?php if($row['nb'] <= $nb){echo "display:none;";}?>">
								<?php
								$nbpages = ceil($row['nb']/$nb);
								?>
								<ul data-table="ramassage" data-state="1" data-start="0" data-nbpage="<?php echo $nb;?>" data-posts="<?php echo $row['nb'];?>">
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
										<div class="lx-textfield lx-g1 lx-pb-0" style="<?php if($_SESSION['type'] != "moderator" AND $_SESSION['type'] != "dlm"){echo "display:none;";}?>">
											<label><span>Client:</span>
												<select name="client" data-isnotempty="" data-message="Choisissez un client!!">
													<option value="">Choisissez un client</option>
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
										<div style="<?php if($settings['reseller'] == "0"){echo "display:none;";}?>">
											<div class="lx-textfield lx-g2 lx-pb-0">
												<label><span>Nom Vendeur: </span><input type="text" name="clientname" /></label>
											</div>
											<div class="lx-textfield lx-g2 lx-pb-0">
												<label><span>Téléphone Vendeur: </span><input type="text" name="clientphone" /></label>
											</div>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g3 lx-pb-0">
											<label><span>Code d'envoi: </span><input type="text" name="code" /></label>
										</div>
										<div class="lx-textfield lx-g3 lx-pb-0">
											<label><span>Destinataire: </span><input type="text" name="fullname" data-isnotempty="" data-message="Saisissez un destinataire!!" /></label>
										</div>
										<div class="lx-textfield lx-g3 lx-pb-0">
											<label><span>Téléphone: </span><input type="text" name="phone" data-isphone="" data-message="Ex: 06xxxxxxxx, 07xxxxxxxx ..." /></label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0" style="display:none;">
											<label><span>Livreur:</span>
												<select name="dlm" data-isnotempty="" data-message="Choisissez un livreur!!">
													<option value="0">Choisissez un livreur</option>
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
										<div class="lx-textfield lx-g1 lx-pb-0">
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
															<option value="0">Choisissez un produit</option>
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
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>Note: </span><textarea name="note" /></textarea></label>
										</div>			
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><input type="checkbox" name="change" value="0" /> Change (S'il y a un colis a retourné)<del class="checkmark"></del></label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><input type="checkbox" name="openpackage" value="1" /> Permettre l'ouverture de colis par le client<del class="checkmark"></del></label>
										</div>												
										<div class="lx-clear-fix"></div>							
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="state" value="Ajouté" />
											<input type="hidden" name="phase" value="shipping" />
											<input type="hidden" name="confirmed" value="0" />
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
									<h3>Importer commandes</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="importform" enctype="multipart/form-data">
										<div class="lx-textfield lx-g1 lx-pb-0" style="display:none;">
											<label><span>Format:</span>
												<select name="format" id="importformat" data-isnotempty="" data-message="Choisissez un format!!">
													<option value="Stockout" selected>Stockout</option>
													<option value="Sendit">Sendit</option>
												</select>
											</label>
										</div>
										<div class="lx-textfield lx-g1 lx-pb-0" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
											<label><span>Client:</span>
												<select name="client" id="importclient" data-isnotempty="" data-message="Choisissez un client!!">
													<option value="">Choisissez un client</option>
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
										<div class="lx-textfield lx-g1 lx-pb-0">
											<div class="lx-importer">
												<input type="file" name="xlsfile" id="importcommands" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
												<span>Choisissez un fichier (excel)</span>
											</div>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="state" value="Ajouté" />
											<input type="hidden" name="phase" value="shipping" />
											<input type="hidden" name="confirmed" value="0" />
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
									<form action="#" method="post" id="blcform">
										<div class="lx-textfield lx-g1 lx-pb-0" style="<?php if($_SESSION['type'] != "moderator" AND $_SESSION['type'] != "worker"){echo "display:none;";}?>">
											<label><span>Client:</span>
												<select name="client" id="client2" data-isnotempty="" data-message="Choisissez un client!!">
													<option value="0">Choisissez un client</option>
													<?php
													$back = $bdd->query("SELECT id,fullname,store FROM users WHERE type='client' AND trash='1' ORDER BY fullname");
													if($_SESSION['type'] == "client"){
														$back = $bdd->query("SELECT id,fullname,store FROM users WHERE id='".$_SESSION['id']."' AND type='client' AND trash='1' ORDER BY fullname");
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
										<div class="lx-submit lx-g1 lx-pb-0">
											<input type="hidden" name="id" value="0" />
											<a href="javascript:;" class="lx-print-bl-client">Enregistrer</a>
										</div>
									</form>
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
										<div class="lx-g1 lx-pb-0">
											<div style="width:100%" id="reader"></div>
										</div>
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
		<script src="js/html5-qrcode.min.js"></script>
		<script src="js/script.js"></script>
		<script>
			function onScanSuccess(decodedText, decodedResult) {
				// Handle on success condition with the decoded text or result.
				console.log(`Scan result: ${decodedText}`, decodedResult);
				collectThis(`${decodedText}`);
			}
			
			var qrboxwidth = 250;
			if($(window).width() < 768){
				qrboxwidth = 150;
			}
			
			var html5QrcodeScanner = new Html5QrcodeScanner(
				"reader", { fps: 2, qrbox: qrboxwidth });
			html5QrcodeScanner.render(onScanSuccess);
		
			$(".lx-new-scan").on("click",function(){
				$("#reader__dashboard_section_csr span button:eq(1)").trigger("click");
			});
			
			$(document).ready(function(){
				loadRamassage($(".lx-pagination ul").attr("data-state"));
				toDropDown();
			});

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
				collectThis(scanned_barcode);
			}	
			
			$(function() {
				/*$(".qrcode-reader").qrCodeReader({
					callback: function(codes) {
						collectThis(codes);
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
						loadRamassage($(".lx-pagination ul").attr("data-state"));
				});
				$('input[name="dateadd"]').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('input[name="datestart"]').val('');
					$('input[name="dateend"]').val('');
					loadRamassage($(".lx-pagination ul").attr("data-state"));
				});
			});
		</script>
	</body>
</html>
<?php

}
?>