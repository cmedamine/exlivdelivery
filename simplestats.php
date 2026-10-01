<?php
				$req = "";
				$reqfacture = "";
				$reqexpenses = "";
				$reqprofit = "";
				if($_POST['client'] != ""){
					$req .= " AND client='".$_POST['client']."'";
					$reqfacture .= " AND client='".$_POST['client']."'";
				}
				if($_POST['dlm'] != ""){
					$req .= " AND dlm='".$_POST['dlm']."'";
					$reqfacture .= " AND dlm='".$_POST['dlm']."'";
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
					$reqfacture .= " AND (datecreated BETWEEN '".$datestart."' AND '".$dateend."')";
					$reqexpenses .= " AND (dateadd BETWEEN '".$datestart."' AND '".$dateend."')";
					$reqprofit .= " AND (dateupdate BETWEEN '".$datestart."' AND '".$dateend."')";
				}
				$back = $bdd->query("SELECT id FROM commands WHERE state NOT IN('Ajouté') AND archived='0' AND trash='1'".$req);
				$nbtotals = $back->rowCount();
				$nbtotal = $nbtotals;
				if($nbtotal == 0){
					$nbtotal = 1;
				}
				if($_SESSION['type'] == "moderator"){
					$back = $bdd->query("SELECT SUM(price) AS sm FROM factures WHERE client<>'0' AND dlm='0' AND trash='1'".$reqfacture);
					$row = $back->fetch();
					$fctclient = $row['sm'];
					$back = $bdd->query("SELECT SUM(price) AS sm FROM factures WHERE client<>'0' AND dlm='0' AND received='off' AND trash='1'".$reqfacture);
					$row = $back->fetch();
					?>
				<div class="lx-g4 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #EC7C27;background:<?php echo RGBTOHex('#EC7C27');?>">
						<a href="factures.php?type=client&received=off">
							<span>Factures clients à versés</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
					<?php
					$back = $bdd->query("SELECT SUM(price) AS sm FROM factures WHERE dlm<>'0' AND client='0' AND trash='1'".$reqfacture);
					$row = $back->fetch();
					$fctdlm = $row['sm'];
					$back = $bdd->query("SELECT SUM(price) AS sm FROM factures WHERE dlm<>'0' AND client='0' AND received='off' AND trash='1'".$reqfacture);
					$row = $back->fetch();
					?>
				<div class="lx-g4 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #EC7C27;background:<?php echo RGBTOHex('#EC7C27');?>">
						<a href="factures.php?type=dlm&received=off">
							<span>Factures livreurs à versés</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
					<?php
					$back = $bdd->query("SELECT SUM(cost) AS sm FROM expenses WHERE trash='1'".$reqexpenses);
					$row = $back->fetch();
					$expenses = $row['sm'];
					?>
				<div class="lx-g4 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #EE0000;background:<?php echo RGBTOHex('#EE0000');?>">
						<a href="expenses.php">
							<span>Dépenses et charges</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
					<?php
					$back = $bdd->query("SELECT SUM(cl.deliveredfees-d.deliveredfees) AS sm FROM commands c,gshippingfees cl,shippingfees d WHERE c.city=cl.city AND c.city=d.city AND cl.city=d.city AND state='Livré' AND archived='0' AND c.trash='1'".$reqprofit);
					$row = $back->fetch();
					?>
				<div class="lx-g4 lx-plr-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #EC7C27;background:<?php echo RGBTOHex('#EC7C27');?>">
						<a href="javascript:;">
							<span>Benifices</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<div class="lx-clear-fix"></div>
					<?php
				}
				if($_SESSION['type'] == "client"){
					$req = "";
					$reqfacture = "";
					if($_POST['client'] != ""){
						$req .= " AND client='".$_POST['client']."'";
						$reqfacture .= " AND client='".$_POST['client']."'";
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
						$reqfacture .= " AND (datecreated BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					$back = $bdd->query("SELECT id FROM commands WHERE state NOT IN('Ajouté') AND archived='0' AND trash='1'".$req);
					$nbtotals = $back->rowCount();
					$nbtotal = $nbtotals;
					if($nbtotal == 0){
						$nbtotal = 1;
					}
					$back = $bdd->query("SELECT SUM(price) AS sm FROM factures WHERE trash='1'".$reqfacture);
					$row = $back->fetch();
					?>
				<div class="lx-g2 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #EC7C27;background:<?php echo RGBTOHex('#EC7C27');?>">
						<a href="javascript:;">
							<span>Total chiffre d'affaires</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
					<?php
					$back = $bdd->query("SELECT SUM(price) AS sm FROM factures WHERE received='off' AND trash='1'".$reqfacture);
					$row = $back->fetch();
					?>
				<div class="lx-g2 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #EC7C27;background:<?php echo RGBTOHex('#EC7C27');?>">
						<a href="factures.php?type=client&received=off">
							<span>Factures à versées</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<div class="lx-clear-fix"></div>
					<?php
				}
				if($_SESSION['type'] == "dlm"){
					$req = "";
					$reqfacture = "";
					if($_POST['dlm'] != ""){
						$req .= " AND c.dlm='".$_POST['dlm']."'";
						$reqfacture .= " AND dlm='".$_POST['dlm']."'";
					}
					if($_POST['city'] != ""){
						$req .= " AND c.city='".$_POST['city']."'";
					}
					if($_POST['product'] != ""){
						$req .= " AND (product='".$row['id']."' OR product LIKE '".$row['id'].",%' OR product LIKE '%,".$row['id'].",%' OR product LIKE '%,".$row['id']."')";
					}
					if($_POST['datestart'] != "" AND $_POST['dateend'] != ""){
						$datestart = strtotime(str_replace("/","-",$_POST['datestart']));
						$dateend = strtotime(str_replace("/","-",$_POST['dateend'])) + (60*60*24) - 1;
						$req .= " AND (dateupdate BETWEEN '".$datestart."' AND '".$dateend."')";
						$reqfacture .= " AND (datecreated BETWEEN '".$datestart."' AND '".$dateend."')";
					}
					$back = $bdd->query("SELECT id FROM commands c WHERE state NOT IN('Ajouté') AND archived='0' AND trash='1'".$req);
					$nbtotals = $back->rowCount();
					$nbtotal = $nbtotals;
					if($nbtotal == 0){
						$nbtotal = 1;
					}
					$back = $bdd->query("SELECT SUM(price) AS sm FROM factures WHERE received='off' AND trash='1'".$reqfacture);
					$row = $back->fetch();
					?>
				<div class="lx-g2 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #EC7C27;background:<?php echo RGBTOHex('#EC7C27');?>">
						<a href="factures.php?type=dlm&received=off">
							<span>Factures à versées</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
					<?php
					$back = $bdd->query("SELECT SUM(deliveredfees) AS sm FROM commands c,shippingfees sh WHERE c.dlm=sh.dlm AND c.city=sh.city AND state='Livré' AND archived='0' AND c.trash='1'".$req);
					$row = $back->fetch();
					?>
				<div class="lx-g2 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #EC7C27;background:<?php echo RGBTOHex('#EC7C27');?>">
						<a href="javascript:;">
							<span>Benifices</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo $row['sm'];?> <?php echo $settings['currency'];?></strong>
							<br />
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<div class="lx-clear-fix"></div>
					<?php
				}
				?>
				<div class="lx-g4 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #242424;background:<?php echo RGBTOHex('#242424');?>">
						<a href="commands.php">
							<span>Total commandes</span>
							<div class="lx-clear-fix"></div>
							<strong>100%</strong>
							<br />
							<del><?php echo $nbtotals;?> commandes</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
					<?php
					$back = $bdd->query("SELECT c.id FROM commands c,trackingstates t WHERE c.state=t.state AND kpi='Livrées' AND archived='0' AND c.trash='1'".$req);
					?>
				<div class="lx-g4 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #EC7C27;background:<?php echo RGBTOHex('#EC7C27');?>">
						<a href="commands.php?s=Livré">
							<span>Livrées</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo round($back->rowCount()*100/$nbtotal,2);?>%</strong>
							<br />
							<del><?php echo $back->rowCount();?> commandes</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
					<?php
					$states = "";
					$back = $bdd->query("SELECT state FROM trackingstates WHERE kpi='En cours' AND trash='1'");
					while($row = $back->fetch()){
						$states .= ",".$row['state'];
					}
					$states = substr($states,1);
					$back = $bdd->query("SELECT c.id FROM commands c,trackingstates t WHERE c.state=t.state AND kpi='En cours' AND archived='0' AND c.trash='1'".$req);
					?>
				<div class="lx-g4 lx-pl-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #FFA500;background:<?php echo RGBTOHex('#FFA500');?>">
						<a href="commands.php?s=<?php echo $states;?>">
							<span>En cours</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo round($back->rowCount()*100/$nbtotal,2);?>%</strong>
							<br />
							<del><?php echo $back->rowCount();?> commandes</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
					<?php
					$back = $bdd->query("SELECT c.id FROM commands c,trackingstates t WHERE c.state=t.state AND kpi='Echouées' AND archived='0' AND c.trash='1'".$req);
					?>
				<div class="lx-g4 lx-plr-0 lx-pb-0 lx-plr-0-mob">
					<div class="lx-state-count" style="border-bottom:5px solid #EE0000;background:<?php echo RGBTOHex('#EE0000');?>">
						<a href="commands.php?s=Annulé,Refusé,Retourné,Retourné vers agence">
							<span>Echouées</span>
							<div class="lx-clear-fix"></div>
							<strong><?php echo round($back->rowCount()*100/$nbtotal,2);?>%</strong>
							<br />
							<del><?php echo $back->rowCount();?> commandes</del>
							<div class="lx-clear-fix"></div>
						</a>
					</div>
				</div>
				<div class="lx-clear-fix"></div>