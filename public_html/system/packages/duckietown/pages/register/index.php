<?php

# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Tuesday, January 9th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018



require_once $GLOBALS['__PACKAGES__DIR__'].'duckietown/Duckietown.php';
use \system\packages\duckietown\Duckietown as Duckietown;

?>

<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:50%">
				<h2>Find your Duckiebot</h2>
			</td>
		</tr>

	</table>

	<p style="font-size:16px">
		<?php
		if( !\system\classes\Core::userExists( \system\classes\Core::getUserLogged('username') ) ){
			echo "This is the first time you access Duckieboard.<br>";
		}else{
			$full_name = \system\classes\Core::getUserLogged('name');
			$parts = explode(' ', $full_name);
			echo sprintf("Welcome back, %s.<br>", $parts[0]);
		}
		?>
		Insert the information about your Duckiebot below to link it to your new account.
	</p>

	<br/>

	<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
		<div class="container-fluid" style="padding-left:0; padding-right:0">

			<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

				<table style="width:100%">
					<tr>
						<td class="col-md-2 text-center" style="border-right:1px solid lightgray">
							<h3 style="margin:18px; margin-left:10px">
								<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/duckiebot.gif" style="width:100px" />
							</h3>
						</td>
						<td class="col-md-7" style="padding:20px">

							<?php
							$labelName = array('Branch', 'Hostname', 'SSH Username', 'SSH Password');
							$inputName = array('branch', 'hostname', 'username', 'password');
							$inputPlaceholder = array('none', 'e.g., robot', 'SSH username', 'SSH password');
							$inputValue = array( ucfirst(\system\classes\Configuration::$DUCKIEFLEET_BRANCH), '', '', '' );
							$inputType = array('text', 'text', 'text', 'password');
							$inputEditable = array(false, true, true, true);
							$inputAddOn = array(null, '.local', null, null);
							//
							generateForm( null, null, 'associate-duckiebot-form', $labelName, $inputName, $inputPlaceholder, $inputValue, $inputType, $inputEditable, 'md-3', 'md-8', null, null, $inputAddOn );
							?>

						</td>
						<td class="col-md-2" style="padding:20px">
							<button type="button" class="btn btn-primary btn-block" style="height:140px" onclick="associate_duckiebot();">
								<h4>
									Associate
									<br>
			                        <span class="glyphicon glyphicon-link" aria-hidden="true" style="margin-top:10px"></span>
								</h4>
		                    </button>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</nav>




	<div id="associate-status-container" style="display:none">
		<h4>Status:</h4>

		<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
			<div class="container-fluid" style="padding-left:0; padding-right:0">

				<div class="collapse navbar-collapse navbar-left" style="padding:30px 40px 10px 40px; width:100%">

					<table class="table table-bordered" id="associate-status-table">
						<tr class="text-bold active">
							<td class="col-md-1 text-center">#</td>
							<td class="col-md-3">Step</td>
							<td class="col-md-6">Description</td>
							<td class="col-md-2 text-center">Status</td>
						</tr>
						<?php
						$steps = [
							1 => [
								'id' => 'locate',
								'name' => 'Locate',
								'description' => 'Check whether the Duckiebot exists'
							],
							2 => [
								'id' => 'ping',
								'name' => 'Ping',
								'description' => 'Check whether the Duckiebot is reachable'
							],
							3 => [
								'id' => 'authenticate',
								'name' => 'Authenticate',
								'description' => 'Access the Duckiebot via SSH'
							],
							4 => [
								'id' => 'associate',
								'name' => 'Associate',
								'description' => 'Link the Duckiebot to your account'
							]
						];

						$status_row_class = [
							'clear' => '',
							'progress' => 'info',
							'success' => 'success',
							'error' => 'danger'
						];

						$status_map = [
							'clear' => '<span class="fa fa-hourglass-half" aria-hidden="true" style="margin-top:2px" data-toggle="tooltip" data-placement="bottom" title="Queued"></span>',
							'progress' =>
								'<div class="progress" style="margin:0">
									<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%"></div>
								</div>',
							'success' => '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true" style="color:green; margin-top:2px" data-toggle="tooltip" data-placement="bottom" title="Passed"></span>',
							'error' => '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color:red; margin-top:2px" data-toggle="tooltip" data-placement="bottom" title="Failed"></span>'
						];

						for($i=1; $i<5; $i++) {
							$step = $steps[$i];
							foreach(['clear', 'progress', 'success', 'error'] as $status) {
								$row_id = sprintf("%s_%s", $step['id'], $status);
								$display = ( $status == 'clear' )? '' : 'none';
								?>
								<tr id="<?php echo $row_id ?>" class="<?php echo $status_row_class[$status]; ?>" style="display:<?php echo $display ?>">
									<td class="text-bold text-center"><?php echo $i ?></td>
									<td><?php echo $step['name'] ?></td>
									<td><?php echo $step['description'] ?></td>
									<td class="text-center"><?php echo $status_map[$status] ?></td>
								</tr>
								<?php
								if( $status == 'error' ){
									?>
									<tr class="danger" id="<?php echo $row_id ?>_details" style="display:none">
										<td></td>
										<td colspan="3">
											<pre style="margin:0"></pre>
										</td>
									</tr>
									<?php
								}
							}
						}
						?>

					</table>

				</div>
			</div>
		</nav>
	</div>

</div>



<script type="text/javascript">

var baseurl = '<?php echo \system\classes\Configuration::$BASE_URL ?>';

var steps = [
	{
		'id' : 'locate',
		'action' : 'exists',
		'bool_key' : 'exists'
	},
	{
		'id' : 'ping',
		'action' : 'status',
		'bool_key' : 'online'
	},
	{
		'id' : 'authenticate',
		'action' : 'authenticate',
		'bool_key' : 'success'
	},
	{
		'id' : 'associate',
		'action' : 'associate',
		'bool_key' : 'success'
	}
];

var global_api_uri_template = "";
var global_table_id = "associate-status-table";

function associate_duckiebot(){
	closeAlert();
	var form_data = $('#associate-duckiebot-form').toAssociativeArray();
	// check arguments
	var errors = [];
	for(const key in form_data){
		if( key == 'branch' ) continue;
		if( form_data[key].length <= 0 ){
			errors.push( 'The "{0}" field cannot be empty.'.format( key ) );
		}
	}
	if( errors.length > 0 ){
		var messageHTML = "<ul>";
		for(const i in errors){
			msg = errors[i];
			messageHTML += "<li>{0}</li>".format( msg );
		}
		messageHTML += "</ul>";
		//
		openAlert( 'danger', messageHTML );
	}else{ // no error, we can proceed
		// clear the table and show it
		for(const i in steps){
			step_id = steps[i]['id'];
			$('#{0} #{1}_clear'.format(global_table_id, step_id)).css('display', '');
			$('#{0} #{1}_progress'.format(global_table_id, step_id)).css('display', 'none');
			$('#{0} #{1}_success'.format(global_table_id, step_id)).css('display', 'none');
			$('#{0} #{1}_error'.format(global_table_id, step_id)).css('display', 'none');
			$('#{0} #{1}_error_details'.format(global_table_id, step_id)).css('display', 'none');
		}
		$('#associate-status-container').css( 'display', '' );
		// map fields from the form to arguments expected by the API
		var password_wordArray = CryptoJS.enc.Utf8.parse(form_data['password']);
		var password_base64 = CryptoJS.enc.Base64.stringify(password_wordArray);
		var api_args = {
			'name' : form_data['hostname'],
			'username' : form_data['username'],
			'password' : password_base64
		}
		// create URL template
		var apiversion = '<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>';
		var token = '<?php echo $_SESSION['TOKEN'] ?>';
		var api_uri = "web-api/{1}/duckiebot/{0}/json?{2}&token={3}".format(
			'{0}',
			apiversion,
			$.param(api_args),
			token
		);
		global_api_uri_template = api_uri;
		// roll first step
		single_step_fcn( 0 );
	}

}

var single_step_fcn = function(step_no){
	step = steps[step_no];
	step_id = step['id'];
	step_action = step['action'];
	step_bool_key = step['bool_key'];
	// clear table for this step
	$('#{0} #{1}_clear'.format(global_table_id, step_id)).css('display', '');
	$('#{0} #{1}_progress'.format(global_table_id, step_id)).css('display', 'none');
	$('#{0} #{1}_success'.format(global_table_id, step_id)).css('display', 'none');
	$('#{0} #{1}_error'.format(global_table_id, step_id)).css('display', 'none');
	$('#{0} #{1}_error_details'.format(global_table_id, step_id)).css('display', 'none');
	// create url
	var url = baseurl + encodeURI( global_api_uri_template.format( step_action ) );
	// show the current step as in progress
	$('#{0} #{1}_clear'.format(global_table_id, step_id)).css('display', 'none');
	$('#{0} #{1}_progress'.format(global_table_id, step_id)).css('display', '');
	// prepare success and error functions
	var step_error_fcn = function( _ ){
		// show the current step as failed
		$('#{0} #{1}_progress'.format(global_table_id, step_id)).css('display', 'none');
		$('#{0} #{1}_error'.format(global_table_id, step_id)).css('display', '');
	}
	//
	var step_success_fcn = function( res ){
		if( res.data[step_bool_key] ){
			// show the current step as completed
			$('#{0} #{1}_progress'.format(global_table_id, step_id)).css('display', 'none');
			$('#{0} #{1}_success'.format(global_table_id, step_id)).css('display', '');
			// roll the next function (if present)
			if( step_no < steps.length-1 ){
				single_step_fcn( step_no+1 );
			}else{
				// last step reached, association completed
				onAssociationCompleted();
			}
		}else{
			step_error_fcn();
			// show error details
			$('#{0} #{1}_error_details'.format(global_table_id, step_id)).css('display', '');
			$('#{0} #{1}_error_details pre'.format(global_table_id, step_id)).html( res.data.message );
		}
	}
	//
	callAPI( url, false, false, step_success_fcn, true, false, step_error_fcn );
}

function onAssociationCompleted(){
	// show the success dialog for 2 secs and then reload the page
	showSuccessDialog( 2000, function(){ window.location.reload(true); } );
}


</script>
