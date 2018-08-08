<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Tuesday, January 9th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Friday, January 26th 2018


use \system\classes\Configuration;
use \system\classes\Core;

?>

<section>
	<div class="container login">
		<div class="row" style="width:480px; margin:auto">
			<div class="center span4 well">
				<div class="col-md-4">
					<h3 style="margin-top:0"><strong><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> &nbsp;Sign in</strong></h3>
				</div>
				<div class="col-md-8">
					<img id="loginLogo" src="<?php echo Core::getSetting( 'logo_black' ) ?>"/>
				</div>
				<br>
				<br>
				<br>
				<legend></legend>

				<div class="text-center" style="height:140px; padding:35px 0 45px 0">
					<?php
					$login_enabled = Core::getSetting('login_enabled', 'core', False);
					if( $login_enabled ){
						?>
						<div class="g-signin2" id="google-signin-button" data-width="240" data-height="50" data-longtitle="true" style="margin-left:100px"></div>
						<!--  -->
						<img id="signin-loader" src="<?php echo Configuration::$BASE_URL ?>images/loading_blue.gif" style="display:none; width:32px; height:32px; margin-top:10px">
						<?php
					}else{
						?>
						<h3>Login disabled.</h3>
						<?php
					}
					?>
				</div>

				<legend style="margin-top:4px"></legend>

				<p style="color:grey">
					<?php echo Core::getSiteName() ?> uses the <a href="https://developers.google.com/identity/">Google Sign-In API</a>
					authentication service.
				</p>

			</div>
		</div>
	</div>
	<p class="text-center muted" style="color:grey; margin-top:-10px">&copy; Copyright <?php echo date("Y"); ?> - <?php echo Core::getSiteName() ?></p>
</section>
