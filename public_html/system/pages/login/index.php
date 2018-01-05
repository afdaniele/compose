<section>
	<div class="container login">
		<div class="row" style="width:480px; margin:auto">
			<div class="center span4 well">
				<div class="col-md-4">
					<h3 style="margin-top:0"><strong><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> &nbsp;Sign in</strong></h3>
				</div>
				<div class="col-md-8">
					<a id="duckietownLogo" href="http://duckietown.org" target="_blank">
						<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/logo.png">
					</a>
				</div>
				<br>
				<br>
				<br>
				<legend></legend>

				<div class="text-center" style="height:140px; padding:35px 0 45px 0">
					<div class="g-signin2" id="google-signin-button" data-width="240" data-height="50" data-longtitle="true" style="margin-left:100px"></div>
					<!--  -->
					<img id="signin-loader" src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="display:none; width:32px; height:32px; margin-top:10px">
				</div>

				<legend style="margin-top:4px"></legend>

				<p style="color:grey">
					Duckieboard uses the <a href="https://developers.google.com/identity/">Google Sign-In API</a>
					authentication service.
				</p>

			</div>
		</div>
	</div>
	<p class="text-center muted" style="color:grey; margin-top:-10px">&copy; Copyright <?php echo date("Y"); ?> - <?php echo \system\classes\Configuration::$SHORT_SITE_LINK; ?></p>
</section>
