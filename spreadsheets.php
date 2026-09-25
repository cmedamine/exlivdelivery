<?php
session_start();
include("config.php");

$_SESSION['errorimport'] = "";

if(!isset($_SESSION['id'])){
	header('location: login.php');
}
else{
	if(!preg_match("#Google Sheets#",$_SESSION['roles']) AND ($_SESSION['type'] == "moderator")){	
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
						<h2>Google Sheets</h2>
					</div>
					<div class="lx-g1">
						<div class="lx-notices-item lx-information">
							<i class="fa fa-exclamation-circle"></i>
							<p>Vous devez partager vos Google Sheets avec cette email: <b><?php echo $settings['google_sheet_email'] ?? 'istore-sheet-auth@istore-sheet-327221.iam.gserviceaccount.com'; ?></b></p>
						</div>
						<div class="lx-notices-item lx-warning">
							<i class="fa fa-exclamation-circle"></i>
							<p><b>NB: </b>Format de Google Sheet doit être comme le format d'importation des colis: <b>Destinataire - Téléphone - Ville - Adresse - Prix - Produit Ref - Qté - Change (0/1) - Ouvrir Colis (0/1) - Note</b></p>
						</div>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-page-content">
						<div class="lx-g1">
							<div class="lx-keyword">
								<label><a href="javascript:;" class="lx-search-keyword"><i class="fa fa-search"></i></a><input type="text" name="keyword" id="keyword" placeholder="Mot clé" data-table="spreadsheets" /></label>
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
								<input type="hidden" name="sortby" value="" />
								<input type="hidden" name="orderby" value="DESC" />
							</div>
							<div class="lx-add-form">
								<a href="javascript:;" class="lx-new lx-new-spreadsheet lx-open-popup" data-title="spreadsheet"><i class="fa fa-plus-square"></i> Nouveau</a>
							</div>
							<div class="lx-table lx-table-spreadsheets">

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
									</select>
								</label>
								<a href="javascript:;">Appliquer</a>
							</div>
							<?php
							}
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
							$back = $bdd->query("SELECT COUNT(*) AS nb FROM spreadsheets WHERE trash='1'".$userwhere);
							$row = $back->fetch();
							?>
							<div class="lx-pagination" style="<?php if($row['nb'] <= $nb){echo "display:none;";}?>">
								<?php
								$nbpages = ceil($row['nb']/$nb);
								?>
								<ul data-table="spreadsheets" data-state="1" data-start="0" data-nbpage="<?php echo $nb;?>" data-posts="<?php echo $row['nb'];?>">
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
			<div tabindex="0" class="lx-popup spreadsheet confirmation">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Mise à jour spreadsheete</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="spreadsheetsform">
										<div class="lx-textfield lx-g1 lx-pb-0" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
											<label><span>Client:</span>
												<select name="client" data-isnotempty="" data-message="Choisissez un client!!">
													<option value="">Choisissez un client</option>
													<?php
													$back = $bdd->query("SELECT id,fullname,store FROM users WHERE type IN('client') AND trash='1' ORDER BY fullname");
													if($_SESSION['type'] == "client"){
														$back = $bdd->query("SELECT id,fullname,store FROM users WHERE type IN('client') AND id='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");		
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
											<label><span>Titre: </span><input type="text" name="title" data-isnotempty="" data-message="Saisissez un titre!!" /></label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>Sheet ID: </span><input type="text" name="sheetid" data-isnotempty="" data-message="Saisissez un Sheet ID!!" /></label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>Sheet Name: </span><input type="text" name="sheetname" data-isnotempty="" data-message="Saisissez un Sheet Name!!" /></label>
										</div>		
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g1 lx-pb-0">
											<label><span>Last Row Number: </span><input type="text" name="lastrow" data-isnotempty="" data-message="Saisissez un Last Row Number!!" /></label>
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
										<p>Voulez vous vraiment supprimer cette spreadsheete?</p>
										<a href="javascript:;" class="lx-delete-record" data-action="deletespreadsheet" data-id="">Oui</a>
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
		<script>
			$(document).ready(function(){
				loadSpreadSheets($(".lx-pagination ul").attr("data-state"));
				toDropDown();
			});
		</script>
	</body>
</html>
<?php
DB_Sanitize();
}
?>