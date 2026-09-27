<?php
// Vérifier si l'installation a été effectuée
if (!file_exists('.env') || !file_exists('config_installed.php')) {
    header('location: install.php');
    exit;
}

session_start();
include("config.php");

if(!isset($_SESSION['id'])){
	header('location: login.php');
	exit;
}
else{
	if($_SESSION['type'] != "moderator"){
		header('location: commands.php');
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
					<div class="lx-page-header lx-pb-5">
						<h2>Statistiques</h2>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-g1">
						<?php
						$back = $bdd->query("SELECT description FROM notices WHERE shown='on' AND trash='1'");
						while($row = $back->fetch()){
						?>
						<div class="lx-notices-item">
							<i class="fa fa-info-circle"></i>
							<p><?php echo $row['description'];?></p>
						</div>
						<?php
						}
						?>
					</div>
					<div class="lx-g1">
						<div class="lx-stats-bloc" style="max-height:1000px;">
							<div class="lx-keyword">
								<label style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
									<select name="dlm" id="dlm_kpi2">
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
								<label><input type="text" name="dateadd" id="dateadd_kpi2" value="<?php echo gmdate("d/m/Y")." - ".gmdate("d/m/Y");?>" placeholder="Tous les dates" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestart" id="datestart_kpi2" value="<?php echo gmdate("d/m/Y");?>" />
								<input type="hidden" name="dateend" id="dateend_kpi2" value="<?php echo gmdate("d/m/Y");?>" />
							</div>
							<div class="lx-kpis2">

							</div>
						</div>
					</div>
					<div class="lx-g1">
						<div class="lx-stats-bloc" style="max-height:1000px;">
							<div class="lx-keyword">
								<label style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
									<select name="client" id="client_kpi">
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
									<select name="worker" id="worker_kpi">
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
								<label style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
									<select name="dlm" id="dlm_kpi">
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
									<select name="city" id="city_kpi">
										<option value="">Tous les villes</option>
										<?php
										$back = $bdd->query("SELECT DISTINCT city FROM shippingfees ORDER BY city");
										if($_SESSION['type']=="dlm"){
											$back = $bdd->query("SELECT DISTINCT city FROM shippingfees WHERE dlm='".$_SESSION['id']."' AND trash='1' ORDER BY city");		
										}
										while($row = $back->fetch()){
											?>
										<option value="<?php echo $row['city'];?>"><?php echo $row['city'];?></option>
											<?php 
										}
										?>
									</select>
								</label>
								<label>
									<select name="product" id="product_kpi">
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
								<label><input type="text" name="dateadd" id="dateadd_kpi" value="<?php echo gmdate("d/m/Y",time()-(60*60*24*30))." - ".gmdate("d/m/Y");?>" placeholder="Tous les dates" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestart" id="datestart_kpi" value="<?php echo gmdate("d/m/Y",time()-(60*60*24*30));?>" />
								<input type="hidden" name="dateend" id="dateend_kpi" value="<?php echo gmdate("d/m/Y");?>" />
							</div>
							<div class="lx-kpis">

							</div>
						</div>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-g1">
						<div class="lx-stats-bloc lx-chart-container">
							<h3>Ventes au cours du dernier 30 jours</h3>
							<div class="lx-keyword">
								<label style="<?php if($_SESSION['type'] != "moderator"){echo "display:none;";}?>">
									<select name="client" id="client">
										<option value="">Tous les clients</option>
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
									<select name="dlm" id="dlm">
										<option value="">Tous les livreurs</option>
										<?php
										$back = $bdd->query("SELECT id,fullname,store FROM users WHERE type='dlm' AND trash='1' ORDER BY fullname");
										if($_SESSION['type'] == "dlm"){
											$back = $bdd->query("SELECT id,fullname,store FROM users WHERE type='dlm' AND id='".$_SESSION['id']."' AND trash='1' ORDER BY fullname");		
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
										$back = $bdd->query("SELECT DISTINCT city FROM shippingfees ORDER BY city");
										if($_SESSION['type']=="dlm"){
											$back = $bdd->query("SELECT DISTINCT city FROM shippingfees WHERE dlm='".$_SESSION['id']."' AND trash='1' ORDER BY city");		
										}
										while($row = $back->fetch()){
											?>
										<option value="<?php echo ucfirst(strtolower($row['city']));?>"><?php echo ucfirst(strtolower($row['city']));?></option>
											<?php 
										}
										?>
									</select>
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
								<label><input type="text" name="dateadd" id="dateadd" value="<?php echo gmdate("d/m/Y",time()-(60*60*24*30))." - ".gmdate("d/m/Y");?>" placeholder="Tous les dates" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestart" id="datestart" value="<?php echo gmdate("d/m/Y",time()-(60*60*24*30));?>" />
								<input type="hidden" name="dateend" id="dateend" value="<?php echo gmdate("d/m/Y");?>" />
								<input type="hidden" id="chartdates" />
								<input type="hidden" id="chartdelivered" />
								<input type="hidden" id="chartcanceled" />
							</div>
							<div id="salescontainer">
							
							</div>
						</div>
					</div>
					<div class="lx-clear-fix"></div>
					<?php
					if($_SESSION['type'] == "moderator"){
						?>
					<div class="lx-g1">
						<div class="lx-stats-bloc" style="max-height:1000px;">
							<h3>Bénifice</h3>
							<div class="lx-keyword">
								<label><input type="text" name="dateadd" id="dateadd_profit" value="<?php echo "01/".date("m/Y")." - ".date("t/m/Y");?>" placeholder="Date" data-table="workers" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestart" id="datestart_profit" value="<?php echo "01/".date("m/Y");?>" />
								<input type="hidden" name="dateend" id="dateend_profit" value="<?php echo date("t/m/Y");?>" />
							</div>
							<div class="lx-table lx-profit" style="border-bottom:1px solid #d3d3d3;">

							</div>
						</div>
					</div>						
					<div class="lx-g1">
						<div class="lx-stats-bloc">
							<h3>Taux de livraison</h3>
							<div class="lx-clear-fix"></div>
							<ul>
								<li><a href="javascript:;" class="active" data-tab="1">Par livreur</a></li>
								<li><a href="javascript:;" data-tab="2">Par ville</a></li>
							</ul>
							<div class="lx-keyword lx-keyword-rate">
								<label>
									<select name="product" id="product_rate">
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
								<label><input type="text" name="dateadd" id="dateadd_rate" placeholder="<?php echo gmdate("d/m/Y",time()-(60*60*24*30))." - ".gmdate("d/m/Y");?>" readonly style="background:white;cursor:pointer;" /></label>
								<input type="hidden" name="datestart" id="datestart_rate" value="" />
								<input type="hidden" name="dateend" id="dateend_rate" value="" />
							</div>
							<div class="lx-clear-fix"></div>
							<table cellpadding="0" cellspacing="0" data-tab="1" class="lx-table-dlm-rate">
								<tr>
									<td>Livreur</td>
									<td>Livrés</td>
									<td>En cours</td>
									<td>Annulé</td>
								</tr>
								<?php
								$back = $bdd->query("SELECT dlm,COUNT(id) AS nbdelivered,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE dlm=c.dlm)) AS sm FROM commands c WHERE state='Livré'".$userwhere." GROUP BY dlm ORDER BY sm DESC");
								while($row = $back->fetch()){
									$back1 = $bdd->query("SELECT fullname FROM users WHERE id='".$row['dlm']."'");
									$row1 = $back1->fetch();
									?>
								<tr>
									<td><span><?php echo $row1['fullname'];?></span></td>
									<td><span><?php echo $row['nbdelivered']." (".round($row['sm'],2)."%)";?></span></td>
									<?php
									$back1 = $bdd->query("SELECT dlm,COUNT(id) AS nbencours,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE dlm=c.dlm)) AS sm FROM commands c WHERE state NOT IN('Livré','Annulé','Refusé','Retourné') AND dlm='".$row['dlm']."'".$userwhere." GROUP BY dlm ORDER BY sm DESC");
									$row1 = $back1->fetch();
									?>
									<td><span><?php echo ($row1['nbencours']!=""?$row1['nbencours']:"0")." (".round($row1['sm'],2)."%)";?></span></td>
									<?php
									$back1 = $bdd->query("SELECT dlm,COUNT(id) AS nbcanceled,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE dlm=c.dlm)) AS sm FROM commands c WHERE state IN('Annulé','Refusé','Retourné') AND dlm='".$row['dlm']."'".$userwhere." GROUP BY dlm ORDER BY sm DESC");
									$row1 = $back1->fetch();
									?>
									<td><span><?php echo ($row1['nbcanceled']!=""?$row1['nbcanceled']:"0")." (".round($row1['sm'],2)."%)";?></span></td>
								</tr>
									<?php
								}
								?>
							</table>
							<table cellpadding="0" cellspacing="0" data-tab="2" class="lx-table-city-rate">
								<tr>
									<td>Ville</td>
									<td>Livrés</td>
									<td>En cours</td>
									<td>Annulé</td>
								</tr>
								<?php
								$back = $bdd->query("SELECT city,COUNT(id) AS nbdelivered,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE city=c.city)) AS sm FROM commands c WHERE state='Livré'".$userwhere." GROUP BY city ORDER BY sm DESC");
								while($row = $back->fetch()){
									?>
								<tr>
									<td><span><?php echo $row['city'];?></span></td>
									<td><span><?php echo $row['nbdelivered']." (".round($row['sm'],2)."%)";?></span></td>
									<?php
									$back1 = $bdd->query("SELECT city,COUNT(id) AS nbencours,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE city=c.city)) AS sm FROM commands c WHERE state NOT IN('Livré','Annulé','Refusé','Retourné') AND city='".addslashes($row['city'])."'".$userwhere." GROUP BY city ORDER BY sm DESC");
									$row1 = $back1->fetch();
									?>
									<td><span><?php echo ($row1['nbencours']!=""?$row1['nbencours']:"0")." (".round($row1['sm'],2)."%)";?></span></td>
									<?php
									$back1 = $bdd->query("SELECT city,COUNT(id) AS nbcanceled,((COUNT(id)*100)/(SELECT COUNT(id) FROM commands WHERE city=c.city)) AS sm FROM commands c WHERE state IN('Annulé','Refusé','Retourné') AND city='".addslashes($row['city'])."'".$userwhere." GROUP BY city ORDER BY sm DESC");
									$row1 = $back1->fetch();
									?>
									<td><span><?php echo ($row1['nbcanceled']!=""?$row1['nbcanceled']:"0")." (".round($row1['sm'],2)."%)";?></span></td>
								</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<div class="lx-clear-fix"></div>	
					<div class="lx-g1">
						<div class="lx-stats-bloc">
							<h3>Historique d'activités sur le site</h3>
							<div class="lx-keyword">
								<label><input type="text" name="datelog" id="datelog" placeholder="<?php echo gmdate("d/m/Y");?>" readonly style="background:white;cursor:pointer;" /></label>
							</div>
							<table cellpadding="0" cellspacing="0" id="loghistory">
								<tr>
									<td>Date</td>
									<td>Description</td>
								</tr>
								<?php
								$back = $bdd->query("SELECT * FROM historylog WHERE dateadd BETWEEN ".(strtotime(date("d-m-Y")))." AND ".(strtotime(date("d-m-Y")) + (60*60*24))." ORDER BY id DESC");
								while($row = $back->fetch()){
									?>
								<tr>
									<td><span><?php echo gmdate("d/m/Y H:i",$row['dateadd']);?></span></td>
									<td><span><?php echo $row['description'];?></span></td>
								</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
						<?php
					}
					?>
					<div class="lx-clear-fix"></div>
				</div>
			</div>
		</div>

		<!-- JQuery -->
		<script src="js/jquery-1.12.4.min.js"></script>
		<!-- Calendar Script -->
		<script src="js/moment.min.js"></script>
		<script src="js/daterangepicker.js"></script>
		<!-- Main Script -->
		<script src="js/script.js"></script>
		<script>
			$(function() {
				$('#dateadd').daterangepicker({
					locale: {
					  format: 'DD/MM/YYYY'
					},
					startDate: moment().startOf('day').subtract(30,'day'),
					endDate: moment(),
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
						$('#dateadd').val(start.format('DD/MM/YYYY') + " - " + end.format('DD/MM/YYYY'));
						$('#datestart').val(start.format('DD/MM/YYYY'));
						$('#dateend').val(end.format('DD/MM/YYYY'));
						loadChartData();
				});
				$('#dateadd').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('#datestart').val('');
					$('#dateend').val('');
					loadChartData();
				});
				$('#dateadd_kpi').daterangepicker({
					locale: {
					  format: 'DD/MM/YYYY'
					},
					startDate: moment().startOf('day').subtract(30,'day'),
					endDate: moment(),
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
						$('#dateadd_kpi').val(start.format('DD/MM/YYYY') + " - " + end.format('DD/MM/YYYY'));
						$('#datestart_kpi').val(start.format('DD/MM/YYYY'));
						$('#dateend_kpi').val(end.format('DD/MM/YYYY'));
						loadKPI();
				});
				$('#dateadd_kpi').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('#datestart_kpi').val('');
					$('#dateend_kpi').val('');
					loadKPI();
				});
				$('#dateadd_profit').daterangepicker({
					locale: {
					  format: 'DD/MM/YYYY'
					},
					startDate: moment().startOf('day').subtract(30,'day'),
					endDate: moment(),
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
						$('#dateadd_profit').val(start.format('DD/MM/YYYY') + " - " + end.format('DD/MM/YYYY'));
						$('#datestart_profit').val(start.format('DD/MM/YYYY'));
						$('#dateend_profit').val(end.format('DD/MM/YYYY'));
						loadGains();
				});
				$('#dateadd_profit').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('#datestart_kpi').val('');
					$('#dateend_kpi').val('');
					loadGains();
				});
				$('#dateadd_kpi2').daterangepicker({
					locale: {
					  format: 'DD/MM/YYYY'
					},
					startDate: moment().startOf('day').subtract(30,'day'),
					endDate: moment(),
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
						$('#dateadd_kpi2').val(start.format('DD/MM/YYYY') + " - " + end.format('DD/MM/YYYY'));
						$('#datestart_kpi2').val(start.format('DD/MM/YYYY'));
						$('#dateend_kpi2').val(end.format('DD/MM/YYYY'));
						loadKPI2();
				});
				$('#dateadd_kpi2').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('#datestart_kpi2').val('');
					$('#dateend_kpi2').val('');
					loadKPI2();
				});
				$('#dateadd_rate').daterangepicker({
					locale: {
					  format: 'DD/MM/YYYY'
					},
					startDate: moment().startOf('day').subtract(30,'day'),
					endDate: moment(),
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
						$('#dateadd_rate').val(start.format('DD/MM/YYYY') + " - " + end.format('DD/MM/YYYY'));
						$('#datestart_rate').val(start.format('DD/MM/YYYY'));
						$('#dateend_rate').val(end.format('DD/MM/YYYY'));
						loadDLMRate();
						loadCityRate();
				});
				$('#dateadd_rate').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
					$('#datestart_rate').val('');
					$('#dateend_rate').val('');
					loadDLMRate();
					loadCityRate();
				});
				$('input[name="datelog"]').daterangepicker({
					locale: {
					  format: 'DD/MM/YYYY'
					},
					autoUpdateInput: false,
					singleDatePicker: true,
					showDropdowns: true
					}, function(start, end, label) {
						$('input[name="datelog"]').val(start.format('DD/MM/YYYY'));
						loadLog();
				});
			});
		</script>
		<script src="js/highcharts.js"></script>
		<script src="js/exporting.js"></script>		
	</body>
</html>
<?php

}
?>
