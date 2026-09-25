<?php
session_start();
include("config.php");

if(!isset($_SESSION['id'])){
	header('location: login.php');
}
else{
	if(!preg_match("#Commandes#",$_SESSION['roles']) AND $_SESSION['type'] == "moderator"){	
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
						<h2>Sous-Livreurs</h2>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-page-content">
						<div class="lx-g1">
							<div class="lx-keyword">
								<label><a href="javascript:;" class="lx-search-keyword"><i class="fa fa-search"></i></a><input type="text" name="keyword" id="keyword" placeholder="Mot clé" data-table="subdlm" /></label>
								<label style="<?php if($_SESSION['type']=="dlm"){echo "display:none;";}?>">
									<select name="dlm" id="dlm">
										<option value="">Tous les livreurs</option>
										<?php
										$back = $bdd->query("SELECT id,fullname FROM users WHERE type='dlm' AND trash='1' ORDER BY fullname");
										if($_SESSION['type']=="dlm"){
											$back = $bdd->query("SELECT id,fullname FROM users WHERE type='dlm' AND id='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");		
										}
										while($row = $back->fetch()){
											?>
										<option value="<?php echo $row['id'];?>"><?php echo $row['fullname'];?></option>
											<?php 
										}
										?>
									</select>
								</label>
								<label><input type="text" name="dateadd" id="dateadd" placeholder="Date" data-table="subdlm" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestart" id="datestart" />
								<input type="hidden" name="dateend" id="dateend" />
								<input type="hidden" name="sortby" value="" />
								<input type="hidden" name="orderby" value="DESC" />
							</div>
							<div class="lx-add-form">
								<a href="javascript:;" class="lx-new lx-new-subdlm lx-open-popup" data-title="subdlm">+ Nouveau</a>
							</div>
							<div class="lx-table lx-table-subdlm">

							</div>
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
							$nb = 20;
							if($parametres['nbrows'] != "" AND $parametres['nbrows'] != "0"){
								$nb = $parametres['nbrows'];
							}						
							?>
							<div class="lx-action-bulk">
								<label><span>Afficher: </span>
									<select name="nbrows">
										<option value="10" <?php if($nb==10){echo "selected";}?>>10</option>
										<option value="20" <?php if($nb==20){echo "selected";}?>>20</option>
										<option value="50" <?php if($nb==50){echo "selected";}?>>50</option>
										<option value="100" <?php if($nb==100){echo "selected";}?>>100</option>
										<option value="200" <?php if($nb==200){echo "selected";}?>>200</option>
									</select>
								</label><span>lignes par page</span>
							</div>
							<?php
							$back = $bdd->query("SELECT COUNT(*) AS nb FROM users WHERE type='subdlm' AND trash='1'");
							$row = $back->fetch();
							?>
							<div class="lx-pagination" style="<?php if($row['nb'] <= $nb){echo "display:none;";}?>">
								<?php
								$nbpages = ceil($row['nb']/$nb);
								?>
								<ul data-table="subdlm" data-state="1" data-start="0" data-nbpage="<?php echo $nb;?>" data-posts="<?php echo $row['nb'];?>">
									<li><span>Page <ins>1</ins> sur <abbr><?php echo $nbpages;?></abbr></span></li>
									<li><a href="javascript:;" class="previous disabled"><i class="fa fa-angle-left"></i></a></li>
									<li>
										<select id="pgnumber">
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
			<div tabindex="0" class="lx-popup subdlm">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Mise à jour sous-livreur</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="subdlmform">
										<div class="lx-textfield lx-g1 lx-pb-0" style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
											<label><span>Livreur:</span>
												<select name="dlm" data-isnotempty="" data-message="Choisissez un livreur!!">
													<option value="">Choisissez un livreur</option>
													<?php
													$back = $bdd->query("SELECT id,fullname FROM users WHERE type='dlm' AND trash='1' ORDER BY fullname");
													if($_SESSION['type'] == "dlm"){
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
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Titre / Nom complet: </span><input type="text" name="fullname" placeholder="Ex: Zakaria" data-isnotempty="" data-message="Saisissez un titre!!" /></label>
										</div>
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Téléphone: </span><input type="text" name="phone" data-isphone="" data-message="Ex: 06xxxxxxxx, 07xxxxxxxx ..." /></label>
										</div>
										<div class="lx-clear-fix"></div>
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Email: </span><input type="text" name="email" data-isemail="" data-message="Saisissez un email valide" /></label>
										</div>
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Mot de passe: </span><input type="password" name="password" data-ispassword="" data-message="Saisissez minimum 6 caratères" /></label>
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
										<p>Voulez vous vraiment supprimer ce sous-livreur?</p>
										<a href="javascript:;" class="lx-delete-record" data-action="deletesubdlm" data-id="">Oui</a>
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
				loadSubDLM($(".lx-pagination ul").attr("data-state"));
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
						loadSubDLM($(".lx-pagination ul").attr("data-state"));
				});
				$('input[name="dateadd"]').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('input[name="datestart"]').val('');
					$('input[name="dateend"]').val('');
					loadSubDLM($(".lx-pagination ul").attr("data-state"));
				});
			});
		</script>
	</body>
</html>
<?php

}
?>