<div class="lx-main-menu">
	<a href="javascript:;" class="lx-mobile-menu-hide"><i class="material-icons">close</i></a>
		<?php
		if($settings['appname'] == "Hbabna Livraison"){
			?>
	<div class="lx-logo" style="padding:10px;margin:0px;">
		<a href="index.php"><img src="images/logo1.png" style="width:150px;" /></a>
	</div>
			<?php
		}
		else{
			?>
	<div class="lx-logo">
		<a href="index.php"><?php echo $settings['appname'];?></a>
	</div>
			<?php
		}
		?>
	<ul>
	<ul>
		<li <?php echo ($_SESSION['type'] == "moderator")?'':'style="display:none;"';?>><a href="index.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "index.php"){echo 'active';}?>"><i class="fa fa-chart-bar"></i>Statistiques</a></li>
		<?php
		if($_SESSION['type'] == "moderator"){
			?>
		<li <?php echo (preg_match("#Modérateurs#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="users.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "users.php"){echo 'active';}?>"><i class="fa fa-user-tie"></i>Modérateurs</a></li>
		<li <?php echo (preg_match("#Livreurs|Frais de livraison|Villes#",$_SESSION['roles']))?'':'style="display:none;"';?>>
			<a href="javascript:;" class="<?php echo preg_match("#^(dlm.php|subdlm.php|shippingfees|cities.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-motorcycle"></i>Agences</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(dlm.php|subdlm.php|shippingfees|cities.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li <?php echo (preg_match("#Livreurs#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="dlm.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "dlm.php"){echo 'active';}?>">Livreurs</a></li>
				<li <?php echo (preg_match("#Frais de livraison#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="shippingfees.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "shippingfees.php"){echo 'active';}?>">Frais de livraison</a></li>
				<li <?php echo (preg_match("#Livreurs#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="subdlm.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "subdlm.php"){echo 'active';}?>">Sous livreurs</a></li>
				<li <?php echo (preg_match("#Villes#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="cities.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "cities.php"){echo 'active';}?>">Villes</a></li>
			</ul>
		</li>		<li <?php echo (preg_match("#Ramassage Agences#",$_SESSION['roles']) OR $_SESSION['type'] == "dlm")?'':'style="display:none;"';?>><a href="shipramassages.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "shipramassages.php"){echo 'active';}?>"><i class="fa fa-truck"></i>Ramassage Agences</a></li>
		<li <?php echo (preg_match("#Clients|Frais de livraison#",$_SESSION['roles']))?'':'style="display:none;"';?>>
			<a href="javascript:;" class="<?php echo preg_match("#^(clients.php|clientfees.php|gshippingfees.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-user-tie"></i>Clients</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(clients.php|clientfees.php|gshippingfees.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li <?php echo (preg_match("#Clients#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="clients.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "clients.php"){echo 'active';}?>">Clients <span class="lx-clients-notif"></span></a></li>
				<li <?php echo (preg_match("#Frais de livraison#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="clientfees.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "clientfees.php"){echo 'active';}?>">Frais de livraison</a></li>
				<li <?php echo (preg_match("#Frais de livraison#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="gshippingfees.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "gshippingfees.php"){echo 'active';}?>">Frais par ville</a></li>
			</ul>
		</li>
		<li <?php echo (preg_match("#Envois|Stocks|Emballages#",$_SESSION['roles']))?'':'style="display:none;"';?>>
			<a href="javascript:;" class="<?php echo preg_match("#^(shipments.php|stocks.php|stockdlms.php|packaging.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-warehouse"></i>Warehouse</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(shipments.php|stocks.php|stockdlms.php|packaging.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li <?php echo (preg_match("#Envois#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="shipments.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "shipments.php"){echo 'active';}?>">Envois <span class="lx-shipments-notif"></span></a></li>
				<li <?php echo (preg_match("#Stocks#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="stocks.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "stocks.php"){echo 'active';}?>">Stocks clients</a></li>
				<li <?php echo (preg_match("#Stocks#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="stockdlms.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "stockdlms.php"){echo 'active';}?>">Stocks livreurs</a></li>
				<li <?php echo (preg_match("#Emballage#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="packaging.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "packaging.php"){echo 'active';}?>">Emballages</a></li>
			</ul>
		</li>
		<li <?php echo (preg_match("#Confirmation|Google Sheets|Agents de confirmation#",$_SESSION['roles']))?'':'style="display:none;"';?>>
			<a href="javascript:;" class="<?php echo preg_match("#^(confirmation.php|spreadsheets.php|workers.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-headset"></i>Call Center</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(confirmation.php|spreadsheets.php|workers.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li <?php echo (preg_match("#Confirmation#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="confirmation.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "confirmation.php"){echo 'active';}?>">Confirmation <span class="lx-confirmation-notif"></span></a></li>
				<li <?php echo (preg_match("#Google Sheets#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="spreadsheets.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "spreadsheets.php"){echo 'active';}?>">Google Sheets</a></li>
				<li <?php echo (preg_match("#Agents de confirmation#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="workers.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "workers.php"){echo 'active';}?>">Agents de confirmation</a></li>
			</ul>
		</li>
		<li <?php echo (preg_match("#Ramassage|Commandes#",$_SESSION['roles']))?'':'style="display:none;"';?>>
			<a href="javascript:;" class="<?php echo preg_match("#^(ramassage.php|commands.php|bs.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-boxes"></i>Commandes</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(ramassage.php|commands.php|bs.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li <?php echo (preg_match("#Ramassage#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="ramassage.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "ramassage.php"){echo 'active';}?>">Ramassage <span class="lx-commands-notif"></span></a></li>
				<li <?php echo (preg_match("#Commandes#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="commands.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "commands.php" AND !isset($_GET['dlm']) AND !isset($_GET['dateupdateend'])){echo 'active';}?>">Commandes <span class="lx-shipped-notif"></span></a></li>
				<li <?php echo (preg_match("#Commandes#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="commands.php?dlm=0" class="<?php echo isset($_GET['dlm'])?($_GET['dlm']=='0'?'active':''):'';?>">Commandes sans livreur</a></li>
				<li <?php echo (preg_match("#Commandes#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="bs.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "bs.php"){echo 'active';}?>">Affectation commandes</a></li>
				<li <?php echo (preg_match("#Commandes#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="commands.php?dateupdatestart=<?php echo date("d/m/Y");?>&dateupdateend=<?php echo date("d/m/Y");?>&treated=off" class="<?php echo isset($_GET['dateupdateend'])?($_GET['dateupdateend']==date("d/m/Y")?'active':''):'';?>">Commandes traité aujourd'hui <span class="lx-today-notif"></span></a></li>
			</ul>
		</li>
		<li <?php echo (preg_match("#BLS#",$_SESSION['roles']))?'':'style="display:none;"';?>>
			<a href="javascript:;" class="<?php echo preg_match("#^(bls.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-file-alt"></i>Bons</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(bls.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li <?php echo (preg_match("#BLS#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="bls.php?type=BRA" class="<?php if(preg_match("#(BRA)#",$_SERVER['REQUEST_URI'])){echo 'active';}?>">Bon de ramassage <span class="lx-bra-notif"></span></a></li>
				<li <?php echo (preg_match("#BLS#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="bls.php?type=BL" class="<?php if(preg_match("#(BL)#",$_SERVER['REQUEST_URI'])){echo 'active';}?>">Bon de livraison</a></li>
				<li <?php echo (preg_match("#BLS#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="bls.php?type=BR" class="<?php if(preg_match("#(BR)$#",$_SERVER['REQUEST_URI'])){echo 'active';}?>">Bon de retour clients</a></li>
				<li <?php echo (preg_match("#BLS#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="bls.php?type=BRL" class="<?php if(preg_match("#(BRL)#",$_SERVER['REQUEST_URI'])){echo 'active';}?>">Bon de retour livreurs</a></li>
			</ul>
		</li>
		<li <?php echo (preg_match("#Factures#",$_SESSION['roles']))?'':'style="display:none;"';?>>
			<a href="javascript:;" class="<?php echo preg_match("#^(factures.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-file-invoice-dollar"></i>Factures</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(factures.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li <?php echo (preg_match("#Factures#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="factures.php?type=client" class="<?php if(preg_match("#client#",$_SERVER['REQUEST_URI'])){echo 'active';}?>">Factures clients <span class="lx-fctclient-notif"></span></a></li>
				<li <?php echo (preg_match("#Factures#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="factures.php?type=dlm" class="<?php if(preg_match("#dlm#",$_SERVER['REQUEST_URI'])){echo 'active';}?>">Factures livreurs <span class="lx-fctdlm-notif"></span></a></li>
			</ul>
		</li>
		<li <?php echo (preg_match("#Annonces|Etats|Dépences#",$_SESSION['roles']))?'':'style="display:none;"';?>>
			<a href="javascript:;" class="<?php echo preg_match("#^(notices.php|trackingstates.php|expenses.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-cogs"></i>Paramètres</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(notices.php|trackingstates.php|expenses.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li <?php echo (preg_match("#Annonces#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="notices.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "notices.php"){echo 'active';}?>">Announces</a></li>
				<li <?php echo (preg_match("#Etats#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="trackingstates.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "trackingstates.php"){echo 'active';}?>">Etats</a></li>
				<li <?php echo (preg_match("#Dépences#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="expenses.php" class="<?php if(preg_match("#expenses#",$_SERVER['REQUEST_URI'])){echo 'active';}?>">Dépences</a></li>
			</ul>
		</li>
		<li <?php echo (preg_match("#Réclamations#",$_SESSION['roles']) OR $_SESSION['type'] == "client" OR $_SESSION['type'] == "worker")?'':'style="display:none;"';?>><a href="reclamations.php" class="<?php if(preg_match("#reclamations#",$_SERVER['REQUEST_URI'])){echo 'active';}?>"><i class="fa fa-headset"></i>Réclamations <span class="lx-reclamation-notif"></span></a></li>
			<?php
		}
		if($_SESSION['type'] == "dlm"){
			?>
		<li><a href="subdlm.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "subdlm.php"){echo 'active';}?>"><i class="fa fa-motorcycle"></i>Sous livreurs</a></li>
		<li><a href="stockdlms.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "stockdlms.php"){echo 'active';}?>"><i class="fa fa-warehouse"></i>Stocks livreurs</a></li>
		<li><a href="ramassage.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "ramassage.php"){echo 'active';}?>"><i class="fa fa-boxes"></i>Ramassage <span class="lx-commands-notif"></span></a></li>
		<li><a href="commands.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "commands.php" AND !isset($_GET['dlm']) AND !isset($_GET['dateupdateend'])){echo 'active';}?>"><i class="fa fa-boxes"></i>Commandes <span class="lx-shipped-notif"></span></a></li>
		<li <?php echo (preg_match("#BLS#",$_SESSION['roles']))?'':'style="display:none;"';?>><a href="bls.php?type=BL" class="<?php if(preg_match("#(BL)#",$_SERVER['REQUEST_URI'])){echo 'active';}?>"><i class="fa fa-file-alt"></i>Bon de livraison</a></li>
		<li><a href="factures.php?type=dlm" class="<?php if(preg_match("#dlm#",$_SERVER['REQUEST_URI'])){echo 'active';}?>"><i class="fa fa-file-invoice-dollar"></i>Factures <span class="lx-fctdlm-notif"></span></a></li>
			<?php
		}
		if($_SESSION['type'] == "subdlm"){
			?>
		<li><a href="commands.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "commands.php" AND !isset($_GET['dlm']) AND !isset($_GET['dateupdateend'])){echo 'active';}?>"><i class="fa fa-boxes"></i>Commandes <span class="lx-shipped-notif"></span></a></li>
			<?php
		}
		if($_SESSION['type'] == "client"){
			?>
		<li><a href="addpackage.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "addpackage.php"){echo 'active';}?>"><i class="fa fa-plus-circle"></i>Ajouter Colis</a></li>
		<li>
			<a href="javascript:;" class="<?php echo preg_match("#^(shipments.php|stocks.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-warehouse"></i>Warehouse</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(shipments.php|stocks.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li><a href="shipments.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "shipments.php"){echo 'active';}?>">Envois <span class="lx-shipments-notif"></span></a></li>
				<li><a href="stocks.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "stocks.php"){echo 'active';}?>">Stocks clients</a></li>
			</ul>
		</li>
		<li>
			<a href="javascript:;" class="<?php echo preg_match("#^(ramassage.php|commands.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-boxes"></i>Commandes</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(ramassage.php|commands.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li><a href="ramassage.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "ramassage.php"){echo 'active';}?>">Ramassage <span class="lx-commands-notif"></span></a></li>
				<li><a href="commands.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "commands.php" AND !isset($_GET['dlm']) AND !isset($_GET['dateupdateend'])){echo 'active';}?>">Commandes <span class="lx-shipped-notif"></span></a></li>
			</ul>
		</li>
		<li>
			<a href="javascript:;" class="<?php echo preg_match("#^(bls.php)$#",basename($_SERVER['PHP_SELF']))?'active':'';?>"><i class="fa fa-file-alt"></i>Bons</a>
			<i class="fa fa-angle-down"></i>
			<ul style="<?php echo preg_match("#^(bls.php)$#",basename($_SERVER['PHP_SELF']))?'display:block;':'';?>">
				<li><a href="bls.php?type=BRA" class="<?php if(preg_match("#(BRA)#",$_SERVER['REQUEST_URI'])){echo 'active';}?>">Bon de ramassage <span class="lx-bra-notif"></span></a></li>
				<li><a href="bls.php?type=BR" class="<?php if(preg_match("#(BR)$#",$_SERVER['REQUEST_URI'])){echo 'active';}?>">Bon de retour clients</a></li>
			</ul>
		</li>
		<li><a href="factures.php?type=client" class="<?php if(preg_match("#client#",$_SERVER['REQUEST_URI'])){echo 'active';}?>"><i class="fa fa-file-invoice-dollar"></i>Factures <span class="lx-fctclient-notif"></span></a></li>
		<li><a href="reclamations.php" class="<?php if(preg_match("#reclamations#",$_SERVER['REQUEST_URI'])){echo 'active';}?>"><i class="fa fa-headset"></i>Réclamations <span class="lx-reclamation-notif"></span></a></li>
			<?php
		}
		if($_SESSION['type'] == "worker"){
			?>
		<li><a href="confirmation.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "confirmation.php"){echo 'active';}?>"><i class="fa fa-headset"></i>Confirmation <span class="lx-confirmation-notif"></span></a></li>
		<li><a href="commands.php" class="<?php if(basename($_SERVER['PHP_SELF']) == "commands.php" AND !isset($_GET['dlm']) AND !isset($_GET['dateupdateend'])){echo 'active';}?>"><i class="fa fa-boxes"></i>Commandes <span class="lx-shipped-notif"></span></a></li>
			<?php
		}
		?>
	</ul>
	</ul>
</div>
