<div class="lx-header-content">
	<a href="javascript:;" class="lx-mobile-menu"><i class="material-icons">menu</i></a>
	<div class="lx-header-admin">
		<ul>
			<li>
				<img src="uploads/cropped_<?php echo $_SESSION['picture'];?>" class="lx-account-menu-toggle" role="button" tabindex="0" aria-label="Ouvrir le menu du compte" aria-expanded="false" />
				<div class="lx-account-settings" aria-hidden="true">
					<div>
						<strong><?php echo $_SESSION['fullname'];?></strong>
						<p><?php echo $_SESSION['email'];?></p>
					</div>
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
					<a href="disconnect.php"><i class="fa fa-power-off"></i> Déconnexion</a>
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
