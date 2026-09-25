<?php
session_start();
include("config.php");

$_SESSION['errorimport'] = "";

if(!isset($_SESSION['id'])){
	header('location: login.php');
}
else{
	if($settings['sepdelivered'] == "0"){	
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
						<h2>Commandes livrées</h2>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-page-content">
						<div class="lx-g1">
							<div class="lx-keyword">
								<label><a href="javascript:;" class="lx-search-keyword"><i class="fa fa-search"></i></a><input type="text" name="keyword" id="keyword" placeholder="Mot clé" data-table="commands" /></label>
								<label style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
									<select name="client" id="client">
										<option value="">Tous les clients</option>
										<?php
										$back = $bdd->query("SELECT id,fullname,store FROM users WHERE type='client' AND trash='1' ORDER BY fullname");
										if($_SESSION['type']=="client"){
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
									<select name="dlm" id="dlm">
										<option value="">Tous les livreurs</option>
										<?php
										$back = $bdd->query("SELECT id,fullname FROM users WHERE type='dlm' AND trash='1' ORDER BY fullname");
										if($_SESSION['type']=="dlm"){
											$back = $bdd->query("SELECT id,fullname FROM users WHERE type='dlm' AND id='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");		
										}
										while($row = $back->fetch()){
											?>
										<option value="<?php echo $row['id'];?>" <?php if($row['id'] == $_SESSION['id']){echo "selected";}?>><?php echo $row['fullname'];?></option>
											<?php 
										}
										?>
									</select>
								</label>
								<label>
									<select name="city" id="city">
										<option value="">Tous les villes</option>
										<?php
										$back = $bdd->query("SELECT DISTINCT city FROM shippingfees WHERE trash='1' ORDER BY city");
										if($_SESSION['type'] == "dlm"){
											$back = $bdd->query("SELECT DISTINCT city FROM shippingfees WHERE trash='1' AND dlm='".$_SESSION['id']."' ORDER BY city");
										}
										while($row = $back->fetch()){
											?>
										<option value="<?php echo $row['city'];?>"><?php echo $row['city'];?></option>
											<?php 
										}
										?>
									</select>
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
								<label class="lx-advanced-select" style="display:none;">
									<i class="fa fa-caret-down"></i>
									<input type="text" name="state" id="multistate" data-table="coli" value="Livré" placeholder="Choisissez une état" readonly />
									<div>
										<a href="javascript:;" class="lx-state-empty">Vider</a>
										<a href="javascript:;" class="lx-state-filter">Filtrer</a>
										<div class="lx-clear-fix"></div>
										<ul>
											<?php
											$back = $bdd->query("SELECT state FROM trackingstates WHERE trash='1' ORDER BY state");
											while($row = $back->fetch()){
												?>
											<li><label><input type="checkbox" name="multistate" value="<?php echo $row['state'];?>" <?php if(isset($_GET['s'])){if(preg_match("#".str_replace(",","|",$_GET['s'])."#",$row['state'])){echo "checked";}}?> /> <?php echo $row['state'];?><del class="checkmark"></del></label></li>
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
								<label><input type="text" name="dateupdate" id="dateupdate" data-table="commands" placeholder="Date mise à jour" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestartupdate" id="datestartupdate" />
								<input type="hidden" name="dateendupdate" id="dateendupdate" />
								<label><input type="text" name="datereport" id="datereport" data-table="commands" placeholder="Date reportation" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestartreport" id="datestartreport" />
								<input type="hidden" name="dateendreport" id="dateendreport" />
								<input type="hidden" name="sortby" value="" />
								<input type="hidden" name="orderby" value="DESC" />
							</div>
							<div class="lx-table lx-table-commands">

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
									</select>
								</label><span>lignes par page</span>
							</div>
							<?php
							$extrareq = $userwhere;
							if($_SESSION['type'] == "dlm" AND $_SESSION['roles'] == "Affichage commun"){
								$extrareq = " AND (dlm='".$_POST['dlm']."' OR city IN(SELECT city FROM shippingfees WHERE dlm='".$_POST['dlm']."' AND trash='1'))";
							}
							$back = $bdd->query("SELECT COUNT(*) AS nb FROM commands WHERE state IN('Livré') AND trash='1'".$extrareq);
							$row = $back->fetch();
							?>
							<div class="lx-pagination" style="<?php if($row['nb'] <= $nb){echo "display:none;";}?>">
								<?php
								$nbpages = ceil($row['nb']/$nb);
								?>
								<ul data-table="commands" data-state="1" data-start="0" data-nbpage="<?php echo $nb;?>" data-posts="<?php echo $row['nb'];?>">
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
		<script src="js/script.js"></script>
		<script>
			$(document).ready(function(){
				loadCommands($(".lx-pagination ul").attr("data-state"));
			});
			$(function() {
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