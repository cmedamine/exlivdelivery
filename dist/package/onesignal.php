		<!-- OneSignal Push Notification -->
		<link rel="manifest" href="<?php echo $websiteurl;?>/manifest.json" />
		<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
		<script>
			<?php
			if(isset($settings['onesignal'])){
				if($settings['onesignal'] != ""){
				?>
			var OneSignal = window.OneSignal || [];
			OneSignal.push(function() {
				OneSignal.init({
					appId: "<?php echo $settings['onesignal'];?>",
					autoRegister: false,
					notifyButton: {
						enable: false,
					},
					welcomeNotification: {
						disable: true
					}
				});
			});
				<?php
				}
			}
			function DB_Sanitize(){
				echo "";
			}
			?>
		</script>