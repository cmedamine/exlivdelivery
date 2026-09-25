<?php
				$feestable = "shippingfees WHERE city=c.city AND dlm=c.dlm";
				if($_SESSION['type'] == "client"){
					$feestable = "clientfees WHERE city=c.city AND client=c.client";
				}
				$req = "";
				if($_POST['client'] != ""){
					$req .= " AND client='".$_POST['client']."'";
				}
				if($_POST['worker'] != ""){
					$req .= " AND worker='".$_POST['worker']."'";
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
				$back = $bdd->query("SELECT id FROM commands WHERE archived='0' AND trash='1'".$req);
				$nbtotal = $back->rowCount();
				if($nbtotal == 0){
					$nbtotal = 1;
				}
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price) AS sm FROM commands c WHERE archived='0' AND trash='1'".$req);
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
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE archived='0' AND trash='1'".$req);
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
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM((SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Livré') AND archived='0' AND trash='1'".$req);
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
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state NOT IN('Livré','Annulé','Refusé','Retourné') AND archived='0' AND trash='1'".$req);
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
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('En cours','Expédié') AND archived='0' AND trash='1'".$req);
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
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE (state IN('Injoignable') OR state LIKE '%Pas de réponse%') AND archived='0' AND trash='1'".$req);
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
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Reporté','Interessé') AND archived='0' AND trash='1'".$req);
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
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Annulé','Refusé','Retourné') AND archived='0' AND trash='1'".$req);
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
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Livré') AND archived='0' AND trash='1'".$req);
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
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Livré') AND invoiced='on' AND archived='0' AND trash='1'".$req);
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
				$back = $bdd->query("SELECT COUNT(id) AS nb,SUM(price - (SELECT SUM(deliveredfees) FROM ".$feestable.")) AS sm FROM commands c WHERE state IN('Livré') AND invoiced='off' AND archived='0' AND trash='1'".$req);
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
					$back = $bdd->query("SELECT COUNT(c.id) AS nb,SUM(cl.deliveredfees - sh.deliveredfees) AS sm FROM commands c,shippingfees sh,clientfees cl WHERE c.city=cl.city AND c.city=sh.city AND c.dlm=sh.dlm AND cl.client=c.client AND archived='0' AND c.trash='1' AND state='Livré'".$req);
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