<?php

if( \system\classes\Core::isUserLoggedIn() ){
	\system\classes\Core::redirectTo('dashboard');
}else{
	?>

	<section>
		<div class="container login">
			<div class="row" style="width:480px; margin:auto">
				<div class="center span4 well">
					<div class="col-md-4">
						<h3 style="margin-top:0"><strong><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> &nbsp;Sign in</strong></h3>
					</div>
					<div class="col-md-8">
						<a id="duckietownLogo" href="http://duckietown.org" target="_blank">
							<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>/images/logo.png">
						</a>
					</div>
					<br>
					<br>
					<br>
					<legend></legend>

					<?php
					generateForm( 'post', null, 'login-form', array('Username:', 'Password:'), array('username', 'password'), array('Type your username here', 'Type your password here'), array('', ''), array('text', 'password'), array(true, true), 'md-4', 'md-7', null, null );
					?>

					<br>
					<p class="text-right" style="margin:0">
						<a href="#" data-toggle="modal" data-target="#password-recovery-modal" data-service="adminprofile" data-insert-what="your username">[ forgot your password? ]</a>
					</p>
					<legend style="margin-top:4px"></legend>

					<p class="text-right">
						<button class="btn btn-primary" type="button" style="width:100%" onclick="userLogIn('<?php echo \system\classes\Configuration::$BASE_URL ?>', '<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>', '<?php echo $_SESSION['TOKEN'] ?>', 'login-form');"><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> &nbsp;Sign in</button>
					</p>

				</div>
			</div>
		</div>
		<p class="text-center muted" style="color:grey; margin-top:-10px">&copy; Copyright <?php echo date("Y"); ?> - <?php echo \system\classes\Configuration::$SHORT_SITE_LINK; ?></p>
	</section>

	<?php
	require_once __DIR__.'/modals/password-recovery-modal.php';
	?>

	<script type="text/javascript">
		$('#login-form #password').keyup(function(e){
			if(e.keyCode == 13){
				// enter key pressed
				userLogIn('<?php echo \system\classes\Configuration::$BASE_URL ?>', '<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>', '<?php echo $_SESSION['TOKEN'] ?>', 'login-form');
			}
		});
	</script>

	<?php
}
?>
