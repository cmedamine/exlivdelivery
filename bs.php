<?php
session_start();
include("config.php");

$_SESSION['errorimport'] = "";

if(!isset($_SESSION['id'])){
	header('location: login.php');
}
else{
	if($_SESSION['type'] != "moderator" OR !preg_match("#Commandes#",$_SESSION['roles'])){
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
						<h2>Affectation Commandes</h2>
					</div>
					<div class="lx-clear-fix"></div>
					<div class="lx-page-content">
						<div class="lx-g1">
							<div class="lx-keyword">
								<label><a href="javascript:;" class="lx-search-keyword"><i class="fa fa-search"></i></a><input type="text" name="keyword" id="keyword" placeholder="Mot clé" data-table="commands" /></label>
								<label>
									<select name="dlm" id="dlm">
										<option value="">Tous les sous livreur</option>
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
							<div class="lx-add-form">
								<a href="javascript:;" class="lx-new lx-new-scan lx-open-popup" data-title="qrcodereader">+ Scan QRCode / BarCode</a>
							</div>
							<div class="lx-table lx-table-commands">

							</div>
							<div class="lx-pagination">
								<ul data-table="bs">
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
			$(document).ready(function(){
				loadCommandsBS();
			});

			$(".lx-new-scan").on("click",function(){
				$("#reader__dashboard_section_csr span button:eq(1)").trigger("click");
			});
			
			function onScanSuccess(decodedText, decodedResult) {
				// Handle on success condition with the decoded text or result.
				// console.log(`Scan result: ${decodedText}`, decodedResult);
				assignThis(`${decodedText}`,$("#dlm").val());
				loadCommandsBS();
			}
			
			var qrboxwidth = 250;
			if($(window).width() < 768){
				qrboxwidth = 150;
			}
			
			var html5QrcodeScanner = new Html5QrcodeScanner(
				"reader", { fps: 2, qrbox: qrboxwidth });
			html5QrcodeScanner.render(onScanSuccess);
			
			/*var barcode = '';
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
				assignThis(scanned_barcode,$("#dlm").val());
				loadCommandsBS();
			}*/

			let scannedCode = '';
			let scanTimeout;

			document.addEventListener('keypress', (event) => {
				// Check if the key pressed is 'Enter'
				if (event.key === 'Enter') {
					if (scannedCode.length > 0) {
						// console.log('Scanned Code:', scannedCode);
						assignThis(scannedCode,$("#dlm").val());
						scannedCode = ''; // Reset the scanned code
					}
				} else {
					// Append the character to the scanned code
					scannedCode += event.key;
				}
			});			
			
			$(function() {
				/*$(".qrcode-reader").qrCodeReader({
					callback: function(codes) {
						assignThis(codes,$("#dlm").val());
						loadCommandsBS();
					}
				});*/
			});
		</script>
	</body>
</html>
<?php

}
?>
