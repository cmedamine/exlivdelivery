<div class="lx-header-content">
	<?php
	$profilePicture = basename((string) ($_SESSION['picture'] ?? ''));
	$croppedProfilePath = __DIR__ . '/uploads/cropped_' . $profilePicture;
	$originalProfilePath = __DIR__ . '/uploads/' . $profilePicture;
	if ($profilePicture !== '' && file_exists($croppedProfilePath)) {
		$profilePictureUrl = 'uploads/cropped_' . rawurlencode($profilePicture);
	} elseif ($profilePicture !== '' && file_exists($originalProfilePath)) {
		$profilePictureUrl = 'uploads/' . rawurlencode($profilePicture);
	} else {
		$profilePictureUrl = 'images/avatar.png';
	}
	?>
	<a href="javascript:;" class="lx-mobile-menu"><i class="material-icons">menu</i></a>
	<div class="lx-header-admin">
		<ul>
			<li>
				<img src="<?php echo htmlspecialchars($profilePictureUrl, ENT_QUOTES, 'UTF-8');?>" class="lx-account-menu-toggle" role="button" tabindex="0" aria-label="Ouvrir le menu du compte" aria-expanded="false" onerror="this.onerror=null;this.src='images/avatar.png';" />
				<div class="lx-account-settings" aria-hidden="true">
					<a href="account.php" class="lx-account-identity" style="display:block;cursor:pointer;pointer-events:auto;position:relative;z-index:2147483647;">
						<strong><?php echo $_SESSION['fullname'];?></strong>
						<p><?php echo $_SESSION['email'];?></p>
					</a>
					<a href="account.php"><i class="fa fa-user"></i> Mon profile</a>
					<a href="account.php"><i class="fa fa-lock"></i> Changer mot de passe</a>
					<a href="settings.php"><i class="fa fa-cog"></i> Paramétres</a>
					<?php
					if($_SESSION['type'] == "moderator"){
					?>
					<a href="smsdevices.php"><i class="fa fa-cog"></i> Appareils SMS</a>
					<a href="smsmodels.php"><i class="fa fa-cog"></i> Modèles SMS</a>
					<?php
					}
					?>
					<a href="logout.php" class="lx-logout-link" onclick="window.location.assign('logout.php'); return false;" onmouseover="this.style.cursor='pointer';" style="display:block !important;width:100% !important;color:#c94b32 !important;opacity:1 !important;pointer-events:auto !important;position:relative;z-index:2147483647;cursor:pointer !important;"><i class="fa fa-power-off"></i> Déconnexion</a>
				</div>
			</li>
			<div class="lx-clear-fix"></div>
		</ul>
		<div class="lx-clear-fix"></div>
	</div>
	<div class="lx-clear-fix"></div>
</div>
<div class="lx-clear-fix"></div>
<input type="hidden" id="appname" value="<?php echo $settings['appname'];?>" />
<input type="hidden" id="requirednote" value="<?php echo $settings['requirednote'];?>" />
<?php
// Désactiver les console.log en production si APP_DEBUG n'est pas activé
$app_debug = false;
if(isset($_ENV['APP_DEBUG'])){
	$val = strtolower(trim($_ENV['APP_DEBUG']));
	if($val === '1' || $val === 'true') $app_debug = true;
}
?>
<script>
(function(){
	var debug = <?php echo $app_debug ? 'true' : 'false'; ?>;
	if(!debug){
		['log','info','warn','error','debug'].forEach(function(m){
			if(typeof console !== 'undefined') console[m] = function(){};
		});
	}
})();

// Keep the account menu available even when the page-wide jQuery script fails.
(function () {
	var toggle = document.querySelector('.lx-account-menu-toggle');
	var menu = document.querySelector('.lx-account-settings');
	if (!toggle || !menu) return;

	function toggleAccountMenu(event) {
		if (event) {
			event.preventDefault();
			event.stopImmediatePropagation();
		}
		var isOpen = menu.style.display === 'block';
		menu.style.display = isOpen ? 'none' : 'block';
		menu.setAttribute('aria-hidden', isOpen ? 'true' : 'false');
		toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
	}

	toggle.addEventListener('click', toggleAccountMenu);
	toggle.addEventListener('keydown', function (event) {
		if (event.key === 'Enter' || event.key === ' ') toggleAccountMenu(event);
	});
})();
</script>
