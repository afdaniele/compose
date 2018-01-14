<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Friday, January 12th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Friday, January 12th 2018

?>



<?php

$username = \system\classes\Core::getUserLogged('username');

if( \system\classes\Core::getUserRole() == 'user' ){

	$res = \system\packages\duckietown\Duckietown::getDuckiebotLinkedToUser( $username );

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
