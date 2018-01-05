<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:50%">
				<h2>Your account</h2>
			</td>
		</tr>

	</table>

	<?php

	$user = \system\classes\Core::getUserLogged();

	$labelName = array('Name', 'E-mail address', 'Account type' );
	$fieldValue = array( $user['name'], $user['email'], ucfirst($user['role']) );

	?>

	<h4>Personal Information</h4>
	<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
		<div class="container-fluid" style="padding-left:0; padding-right:0">

			<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

				<table style="width:100%">
					<tr>
						<td class="col-md-3 text-center" style="border-right:1px solid lightgray">
							<h3 style="margin:0">
								<div class="text-center col-md-12" id="profile_page_avatar">
									<img src="<?php echo $user['picture']; ?>" id="avatar">
								</div>
							</h3>
						</td>
						<td class="col-md-9" style="padding:20px">
							<?php
							generateView( $labelName, $fieldValue, 'md-3', 'md-9' );
							?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</nav>



	<?php

	if( \system\classes\Core::getUserRole() == 'user' ){

		$res = \system\classes\Core::getDuckiebotLinkedToUser( $user['username'] );

		if( !$res['success'] ){
			\system\classes\Core::throwError( $res['data'] );
		}

		$duckiebot_name = $res['data'];

		$labelName = array( 'Name' );
		$fieldValue = array( $duckiebot_name );

		?>
		<br/>
		<h4>Your Duckiebot</h4>
		<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
			<div class="container-fluid" style="padding-left:0; padding-right:0">

				<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

					<table style="width:100%">
						<tr>
							<td class="col-md-3 text-center" style="border-right:1px solid lightgray">
								<h3 style="margin:18px; margin-left:10px">
									<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/duckiebot.gif" style="width:96px" />
								</h3>
							</td>
							<td class="col-md-9" style="padding:20px">
								<?php
								generateView( $labelName, $fieldValue, 'md-3', 'md-9' );
								?>
								<button class="btn btn-danger" type="button" onclick="releaseDuckiebot();" style="margin: 8px 0 0 116px">
									<i class="fa fa-unlock" aria-hidden="true"></i> &nbsp; Release ownership of this Duckiebot
								</button>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</nav>

		<script type="text/javascript">
			var baseurl = '<?php echo \system\classes\Configuration::$BASE_URL ?>';
			var apiversion = '<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>';
			var token = '<?php echo $_SESSION['TOKEN'] ?>';
			var duckiebotName =  "<?php echo $duckiebot_name ?>";

			function releaseDuckiebot(){
				openYesNoModal('Are you sure you want to release the ownership of this Duckiebot?', function(){
					var api_uri = "web-api/{0}/duckiebot/release/json?name={1}&token={2}".format(
						apiversion,
						duckiebotName,
						token
					);
					var url = baseurl + encodeURI( api_uri );
					callAPI( url, true, true );
				});
			}
		</script>

		<?php
	}
	?>

</div>
