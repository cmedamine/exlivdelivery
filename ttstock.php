<?php
session_start();
include("config.php");

if(!isset($_SESSION['id'])){
	header('location: login.php');
}
else{
	if(!preg_match("#Stock#",$_SESSION['roles'])){	
		header('location: 404.php');
	}
}

if(isset($_SESSION['id']) AND isset($_SESSION['fullname'])){
?>
<!DOCTYPE html>
<html lang="zxx">
	<head>
		<meta charset="utf-8">
		<title>StockOUT - CPanel</title>
		<meta name="description" content="StockOUT - CPanel">
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
						<?php
						if($_SESSION['type'] == "moderator"){
						?>
						<a href="stocks.php" class="lx-header-btn">Stock</a>
						<?php
						}
						?>
						<h2>Stock magasin</h2>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-page-content">
						<div class="lx-g1">
							<div class="lx-keyword">
								<label>
									<select name="product" id="product">
										<option value="">Tous les produits</option>
										<?php
										$back = $bdd->query("SELECT id,title FROM products WHERE trash='1' ORDER BY id");
										while($row = $back->fetch()){
											?>
										<option value="<?php echo $row['id'];?>"><?php echo $row['title'];?></option>
											<?php 
										}
										?>
									</select>
								</label>
								<label><input type="text" name="dateadd" id="dateadd" placeholder="Date" data-table="ttstock" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestart" id="datestart" />
								<input type="hidden" name="dateend" id="dateend" />
								<input type="hidden" name="sortby" value="" />
								<input type="hidden" name="orderby" value="DESC" />
							</div>
							<div class="lx-add-form">
								<a href="javascript:;" class="lx-new lx-new-stock lx-open-popup" data-title="ttstock">+ Nouveau stock</a>
							</div>
							<div class="lx-table lx-table-ttstock">

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
										<option value="10" <?php if($nb==10){echo "selected";}?>>10</option>
										<option value="20" <?php if($nb==20){echo "selected";}?>>20</option>
										<option value="50" <?php if($nb==50){echo "selected";}?>>50</option>
										<option value="100" <?php if($nb==100){echo "selected";}?>>100</option>
										<option value="200" <?php if($nb==200){echo "selected";}?>>200</option>
									</select>
								</label><span>lignes par page</span>
							</div>
							<?php
							$back = $bdd->query("SELECT COUNT(*) AS nb FROM ttstock WHERE trash='1'");
							$row = $back->fetch();
							?>
							<div class="lx-pagination" style="<?php if($row['nb'] <= $nb){echo "display:none;";}?>">
								<?php
								$nbpages = ceil($row['nb']/$nb);
								?>
								<ul data-table="ttstock" data-state="1" data-start="0" data-nbpage="<?php echo $nb;?>" data-posts="<?php echo $row['nb'];?>">
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
			<div tabindex="0" class="lx-popup ttstock">
				<div class="lx-popup-inside">
					<div class="lx-popup-content">
						<a href="javascript:;"><i class="material-icons">close</i></a>
						<div class="lx-popup-details">
							<div class="lx-form">
								<div class="lx-form-title">
									<h3>Mise à jour stock</h3>
								</div>
								<div class="lx-add-form">
									<form action="#" method="post" id="ttstockform">
										<div class="lx-textfield lx-g2 lx-pb-0">
											<label><span>Produit:</span>
												<select name="product" data-isnotempty="" data-message="Choisissez un produit!!">
													<option value="">Choisissez un produit</option>
													<?php
													$back = $bdd->query("SELECT id,title FROM products WHERE trash='1' ORDER BY title");
													while($row = $back->fetch()){
														?>
													<option value="<?php echo $row['id'];?>"><?php echo $row['title'];?></option>
														<?php 
													}
													?>
												</select>
											</label>
										</div>
										<div class="lx-textfield lx-g4 lx-pb-0">
											<label><span>Quantité: </span><input type="text" name="qty" data-isnumber="" data-message="Saisissez une quantité!!" /></label>
										</div>		
										<div class="lx-textfield lx-g4 lx-pb-0">
											<label><span>Prix: </span><input type="text" name="price" /></label>
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
										<p>Voulez vous vraiment supprimer ce stock?</p>
										<a href="javascript:;" class="lx-delete-record" data-action="deletettstock" data-id="">Oui</a>
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
				loadTTStock($(".lx-pagination ul").attr("data-state"));
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
						loadTTStock($(".lx-pagination ul").attr("data-state"));
				});
				$('input[name="dateadd"]').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('input[name="datestart"]').val('');
					$('input[name="dateend"]').val('');
					loadTTStock($(".lx-pagination ul").attr("data-state"));
				});
			});
		</script>
	</body>
</html>
<?php

}
?>