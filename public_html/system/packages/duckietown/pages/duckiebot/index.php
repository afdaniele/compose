<?php

$duckiebotName = null;

if( \system\classes\Core::getUserRole() == 'user' ){
	$user = \system\classes\Core::getLoggedUser('username');
	$res = \system\classes\Core::getDuckiebotLinkedToUser( $user );
	if( !$res['success'] ){
		\system\classes\Core::throwError(
			sprintf('Error: "%s"', $res['data'])
		);
	}
	if( is_null($res['data']) ){
		\system\classes\Core::throwError('Your account is not linked to any Duckiebot');
	}
	$duckiebotName = $res['data'];
}else{
	$duckiebotName = \system\classes\Configuration::$ACTION;
	if( strlen($duckiebotName) < 1 ){
		\system\classes\Core::redirectTo("");
	}
}

if( !\system\classes\Core::duckiebotExists($duckiebotName) ){
	\system\classes\Core::throwError(
		sprintf('The Duckiebot `%s` does not exist.', $duckiebotName)
	);
}

?>


<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>Duckiebot - <?php echo $duckiebotName ?></h2>
			</td>
		</tr>

	</table>

	<?php
	if(isset($_GET['lst'])){
		$qs = ( (isset($_GET['lst']))? base64_decode(urldecode($_GET['lst'])) : '' );
		?>
		<a role="button"
			href="<?php echo \system\classes\Configuration::$BASE ?>duckiefleet<?php echo ( (strlen($qs) > 0)? '?'.$qs : '' ) ?>"
			class="btn btn-info" data-toggle="modal"
			style="margin-bottom:30px">
				<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
				&nbsp; Go back to the list
		</a>
		<?php
	}
	?>

	<?php
	$duckiebotOwner = \system\classes\Core::getDuckiebotOwner($duckiebotName);
	?>

	<br/>
	<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
		<div class="container-fluid" style="padding-left:0; padding-right:0">

			<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

				<table style="width:100%">
					<tr>
						<td class="text-center" width="160px" style="border-right:1px solid lightgray">
							<h3 style="margin:18px; margin-left:10px">
								<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/duckiebot.gif" style="width:100px" />
							</h3>
						</td>
						<td style="padding-left:20px; width:340px; border-right:1px solid lightgray">
							<table id="duckiebot_general_info">
								<tr>
									<td style="width:100px">
										 <bold>Duckiebot:<bold>
									</td>
									<td>
										 <?php echo $duckiebotName ?>
									</td>
								</tr>
								<tr>
									<td>
										 <bold>Owner ID:<bold>
									</td>
									<td>
										 <?php echo $duckiebotOwner ?>
									</td>
								</tr>
								<tr>
									<td>
										 <bold>Owner Name:<bold>
									</td>
									<td id="duckiebot_owner">
										<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:18px; height:18px;">
									</td>
								</tr>
								<tr>
									<td>
										 <bold>Status:<bold>
									</td>
									<td id="duckiebot_status">
										<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:18px; height:18px;">
									</td>
								</tr>
							</table>
						</td>
						<td class="text-center" style="padding:0 30px 0 56px">
							<h4 style="margin:0 26px 8px 0">Configuration</h4>

							<div id="configuration-section-placeholder">
								<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px; margin-top:28px; margin-bottom:20px">
							</div>
							<div id="configuration-section-container" style="display:none">
								<ul class="nav nav-pills" role="tablist">
									<li role="presentation" class="configuration-indicator">
										<a id="configuration-w" class="color-default" style="height:80px">
											<h3 style="margin:4px 0 6px 0"><i class="fa fa-wifi" aria-hidden="true"></i></h3>
											DB-17w <span class="badge">NO</span>
										</a>
									</li>
									<li role="presentation" class="configuration-indicator">
										<a id="configuration-j" class="color-default" style="height:80px">
											<h3 style="margin:4px 0 6px 0"><i class="fa fa-gamepad" aria-hidden="true"></i></h3>
											DB-17j <span class="badge">NO</span>
										</a>
									</li>
									<li role="presentation"  class="configuration-indicator">
										<a id="configuration-d" class="color-default" style="height:80px">
											<h3 style="margin:4px 0 6px 0"><i class="fa fa-hdd-o" aria-hidden="true"></i></h3>
											DB-17d <span class="badge">NO</span>
										</a>
									</li>
								</ul>
							</div>

						</td>
					</tr>
				</table>
			</div>
		</div>
	</nav>




	<h3 style="margin:80px 0 20px 0; border-bottom:1px solid #ddd;"><i class="fa fa-exchange" aria-hidden="true"></i>&nbsp; Network</h3>

	<div id="network-section-placeholder" class="text-center">
		<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px; margin-bottom:20px">
	</div>
	<div id="network-section-container" class="text-center" style="display:none">
		<!-- Here we will have a list of network interfaces -->
	</div>



	<h3 style="margin:60px 0 20px 0; border-bottom:1px solid #ddd;"><span class="glyphicon glyphicon-hdd" aria-hidden="true"></span>&nbsp; Storage</h3>

	<div id="storage-section-placeholder" class="text-center">
		<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px; margin-bottom:20px">
	</div>
	<div id="storage-section-container" class="text-center" style="display:none">
		<!-- Here we will have a list of mountpoints -->
	</div>


	<h3 style="margin:60px 0 20px 0; border-bottom:1px solid #ddd;"><i class="fa fa-th" aria-hidden="true"></i>&nbsp; ROS</h3>

	<div id="ros-section-placeholder" class="text-center">
		<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px; margin-bottom:20px">
	</div>
	<div id="ros-section-container" class="text-center" style="display:none">
		<nav class="navbar navbar-default" role="navigation" style="width:100%">
			<div class="container-fluid" style="padding-left:0; padding-right:0">
				<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
					<table style="width:100%; height:50px">
						<tr>
							<td class="text-center" width="160px" style="border-right:1px solid lightgray">
								<h5 class="text-bold">ROS Core</h5>
							</td>
							<td id="ros_core_status" class="text-left" style="padding:0 10px"></td>
						</tr>
					</table>
				</div>
			</div>
		</nav>

		<table id="ros_details" style="width:100%; display:none">
			<tr>
				<td class="col-md-6" style="padding:0; vertical-align:top">
					<nav class="navbar navbar-default" role="navigation" style="width:100%; padding:0">
						<div class="container-fluid" style="padding-left:0; padding-right:0">
							<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

								<table style="width:100%; border-bottom:1px solid lightgray">
									<tr>
										<td class="text-center" style="width:160px; border-right:1px solid lightgray; padding:2px 4px">
											<h5 class="text-bold">ROS Nodes</h5>
										</td>
										<td class="text-right" style="width:auto; padding:0 20px"></td>
									</tr>
								</table>

								<div class="text-left" style="padding:8px 20px">
									<table id="ros_nodes_table" class="table-condensed">
										<tbody></tbody>
									</table>
								</div>

							</div>
						</div>
					</nav>
				</td>
				<td>&nbsp;</td>
				<td class="col-md-6" style="padding:0; vertical-align:top">
					<nav class="navbar navbar-default" role="navigation" style="width:100%; padding:0">
						<div class="container-fluid" style="padding-left:0; padding-right:0">

							<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

								<table style="width:100%; border-bottom:1px solid lightgray">
									<tr>
										<td class="text-center" style="width:160px; border-right:1px solid lightgray; padding:2px 4px">
											<h5 class="text-bold">ROS Topics</h5>
										</td>
										<td class="text-right" style="width:auto; padding:0 20px"></td>
									</tr>
								</table>

								<div class="text-left" style="padding:8px 20px">
									<table id="ros_topics_table" class="table-condensed">
										<tbody></tbody>
									</table>
								</div>

							</div>
						</div>
					</nav>
				</td>
			</tr>
		</table>
	</div>



	<br/>

	<h3 style="margin:60px 0 20px 0; border-bottom:1px solid #ddd;"><i class="fa fa-bug" aria-hidden="true"></i>&nbsp; Debug</h3>
	<?php
	$wtd_status_color_map = array(
		'passed' => 'success',
		'skipped' => 'warning',
		'failed' => 'danger'
	);

	$what_the_duck = \system\classes\Core::getDuckiebotLatestWhatTheDuck($duckiebotName);

	$wtd = $what_the_duck['duckiebot']; //TODO: add laptops

	$tests_stats = array(
		'passed' => 0,
		'skipped' => 0,
		'failed' => 0
	);
	foreach ($wtd as $test) {
		$tests_stats[$test['status']] += 1;
	}
	?>
	<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
		<div class="container-fluid" style="padding-left:0; padding-right:0">

			<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

				<table style="width:100%; border-bottom:1px solid lightgray">
					<tr>
						<td class="text-center" style="width:160px; border-right:1px solid lightgray; padding:2px 4px">
							<h5 class="text-bold">What The Duck</h5>
						</td>
						<td class="text-right" style="width:auto; padding:0 20px">
							<span style="color:green">
								<span class="text-bold">
									<?php echo $tests_stats['passed'] ?>
								</span>
								Test<?php echo ($tests_stats['passed'] == 1)? '' : 's' ?> passed
							</span>
							&nbsp;|&nbsp;
							<span style="color:#cece44">
								<span class="text-bold">
									<?php echo $tests_stats['skipped'] ?>
								</span>
								Test<?php echo ($tests_stats['skipped'] == 1)? '' : 's' ?> skipped
							</span>
							&nbsp;|&nbsp;
							<span style="color:red">
								<span class="text-bold">
									<?php echo $tests_stats['failed'] ?>
								</span>
								Test<?php echo ($tests_stats['failed'] == 1)? '' : 's' ?> failed
							</span>
						</td>
					</tr>
				</table>

				<div style="padding:20px">

					<table class="table">
						<tr>
							<td class="col-md-4 text-bold">
								Test
							</td>
							<td class="col-md-6 text-bold">
								Result
							</td>
							<td class="col-md-2 text-bold">
								Executed
							</td>
						</tr>
						<?php
						$test_num = 0;
						foreach ($wtd as $test) {
							?>
							<tr>
								<td class="col-md-4">
									<?php echo $test['test_name'] ?>
								</td>
								<td class="col-md-6 <?php echo $wtd_status_color_map[$test['status']]; ?>">
									<?php
									switch( $test['status'] ){
										case 'passed':
											echo 'Passed';
											break;
										case 'skipped':
										case 'failed':
											echo $test['out_short'];
											break;
										default:
											break;
									}
									?>
								</td>
								<td class="col-md-2">
									<span id="<?php echo $test['upload_event_id']; ?>_last_execution_<?php echo $test_num; ?>">
										...
									</span>
								</td>
							</tr>
							<?php
							$test_num += 1;
						}
						?>
					</table>

				</div>

			</div>
		</div>
	</nav>
</div>


<script type="text/javascript">

	var _datetime_format = 'YYYY-MM-DD HH:mm:ss.SSS';

	var _last_execution_divs = [
		<?php
		$test_num = 0;
		$wtd = $what_the_duck['duckiebot']; //TODO: add laptops
		foreach ($wtd as $test) {
			echo '{ "id" : "'.$test['upload_event_id'].'_last_execution_'.$test_num.'", "datetime" : "'.$test['upload_event_date'].'"}, ';
			$test_num += 1;
		}
		?>
	];



	function duckiebot_configuration_callback(result){
		container = $('#configuration-section-container');
		placeholder = $('#configuration-section-placeholder');
		// turn on the indicators
		$.each(result.data.configuration, function(c) {
			active = result.data.configuration[c];
			if( active ){
				$('#configuration-section-container #configuration-'+c).removeClass('color-default');
				$('#configuration-section-container #configuration-'+c).addClass('color-warning');
				$('#configuration-section-container #configuration-'+c+' .badge').html('YES');
			}
		});
		// hide the placeholder and show the indicators
		placeholder.css('display', 'none');
		container.css('display', '');
	}



	var _network_interface_template =
	`<nav class="navbar navbar-default" role="navigation" style="width:310px; margin:0 {5} 36px 0; display:inline-block">
		<div class="container-fluid" style="padding-left:0; padding-right:0">
			<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
				<table style="width:100%">
					<tr>
						<td class="text-center" width="50px" style="border-right:1px solid lightgray">
							<h2 style="margin:44px 6px">
								<i class="fa fa-sitemap" aria-hidden="true"></i>
							</h2>
						</td>
						<td class="text-left" style="padding:0 10px">
							<table>
								<tr>
									<td><bold>Interface:<bold></td>
									<td>&nbsp;&nbsp;<span>{0}</span></td>
								</tr>
								<tr>
									<td><bold>Connected:<bold></td>
									<td>&nbsp;&nbsp;<span>{1}</span></td>
								</tr>
								<tr>
									<td><bold>MAC address:<bold></td>
									<td>&nbsp;&nbsp;<span>{2}</span></td>
								</tr>
								<tr>
									<td><bold>IP address:<bold></td>
									<td>&nbsp;&nbsp;<span>{3}</span></td>
								</tr>
								<tr>
									<td><bold>Subnet mask:<bold></td>
									<td>&nbsp;&nbsp;<span>{4}</span></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</nav>`;

	function duckiebot_network_callback(result){
		container = $('#network-section-container');
		placeholder = $('#network-section-placeholder');
		// create network interfaces descriptors
		$.each(result.data.interfaces, function(i) {
			iface = result.data.interfaces[i];
			if( iface.name == 'lo' ){
				// do not show the loopback interface
				return;
			}
			html = _network_interface_template.format(
				iface.name,
				( iface.connected )? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true" style="color:green; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Online"></span>' : '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color:red; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Offline"></span>',
				iface.mac,
				iface.ip,
				iface.mask,
				( (i+1)%3==0 ||  i == result.data.interfaces.length-1 )? '0' : '16px'
			);
			container.html( container.html() + html );
		});
		// hide the placeholder and show the container
		placeholder.css('display', 'none');
		container.css('display', '');
	}



	var _storage_mountpoint_template =
	`<nav class="navbar navbar-default" role="navigation" style="width:310px; margin:0 {6} 36px 0; display:inline-block">
		<div class="container-fluid" style="padding-left:0; padding-right:0">
			<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
				<table class="duckiebot_storage_info" style="width:100%">
					<tr>
						<td class="text-center" width="50px" style="border-right:1px solid lightgray">
							<h2 style="margin:44px 6px">
								<i class="fa fa-hdd-o" aria-hidden="true"></i>
							</h2>
						</td>
						<td class="text-left" style="padding:0 18px">
							<table>
								<tr>
									<td><bold>Mount point:<bold></td>
									<td>&nbsp;&nbsp;<span>{0}</span></td>
								</tr>
								<tr>
									<td><bold>Device:<bold></td>
									<td>&nbsp;&nbsp;<span>{1}</span></td>
								</tr>
							</table>

							<div class="progress" style="margin:10px 0 0 0">
								<div class="progress-bar progress-bar-danger" style="width:{2}">{3}</div>
								<div class="progress-bar progress-bar-success" style="width:{4}">{5}</div>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</nav>`;

	function duckiebot_storage_callback(result){
		container = $('#storage-section-container');
		placeholder = $('#storage-section-placeholder');
		// create mountpoints descriptors
		$.each(result.data.mountpoints, function(i) {
			mount = result.data.mountpoints[i];
			var used = Math.floor( mount.used*100 );
			var free = Math.floor( mount.free*100 );
			html = _storage_mountpoint_template.format(
				mount.mountpoint,
				mount.device,
				'{0}%'.format( used ),
				'{0}%{1}'.format( used, (used > 34)? ' used' : '' ),
				'{0}%'.format( free ),
				'{0}%{1}'.format( free, (free > 34)? ' free' : '' ),
				( (i+1)%3==0 ||  i == result.data.mountpoints.length-1 )? '0' : '16px'
			);
			container.html( container.html() + html );
		});
		// hide the placeholder and show the container
		placeholder.css('display', 'none');
		container.css('display', '');
	}



	function duckiebot_ros_callback(result){
		container = $('#ros-section-container');
		container_details = $('#ros_details');
		placeholder = $('#ros-section-placeholder');
		// get ROS status
		if( result.data.core.is_running ){
			$('#ros_core_status').html( '<span style="color:green; font-weight:bold">Running</span> with PID <bold>'+result.data.core.pid+'</bold>' );
			// add nodes
			$.each(result.data.nodes, function(i) {
				node = result.data.nodes[i];
				$('#ros_nodes_table > tbody').html( $('#ros_nodes_table > tbody').html() + '<tr><td>'+node+'</td></tr>' );
			});
			// add topics
			$.each(result.data.topics, function(i) {
				topic = result.data.topics[i];
				$('#ros_topics_table > tbody').html( $('#ros_topics_table > tbody').html() + '<tr><td>'+topic+'</td></tr>' );
			});
			// show details
			container_details.css('display', '');
		}else{
			$('#ros_core_status').html( '<span style="color:red; font-weight:bold">Not running</span>' );
		}
		// hide the placeholder and show the container
		placeholder.css('display', 'none');
		container.css('display', '');
	}



	function duckiebot_status_callback(result){
		$('#duckiebot_status').html(
			( result.data.online )? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true" style="color:green; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Online"></span> Online' : '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color:red; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Offline"></span> Offline'
		);
		//
		if( result.data.online ){
			// configuration call
			var url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/duckiebot/configuration/json?name=<?php echo $duckiebotName ?>&token=<?php echo $_SESSION["TOKEN"] ?>';
			callAPI( url, false, false, duckiebot_configuration_callback, true );
			// network call
			var url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/duckiebot/network/json?name=<?php echo $duckiebotName ?>&token=<?php echo $_SESSION["TOKEN"] ?>';
			callAPI( url, false, false, duckiebot_network_callback, true );
			// storage call
			var url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/duckiebot/storage/json?name=<?php echo $duckiebotName ?>&token=<?php echo $_SESSION["TOKEN"] ?>';
			callAPI( url, false, false, duckiebot_storage_callback, true );
			// ROS call
			var url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/duckiebot/ros/json?name=<?php echo $duckiebotName ?>&token=<?php echo $_SESSION["TOKEN"] ?>';
			callAPI( url, false, false, duckiebot_ros_callback, true );
		}else{
			$('#storage-section-container').html('<h4 class="text-center">The Duckiebot is offline.</h4>');
			$('#network-section-container').html('<h4 class="text-center">The Duckiebot is offline.</h4>');
		}
	}



	function github_user_info_callback( result ){
		$('#duckiebot_owner').html(
			(result.name == null)? result.login : result.name
		);
	}



	$(document).ready( function(){
		$.each(_last_execution_divs, function(i) {
			test_div = _last_execution_divs[i];
			document.getElementById(test_div.id).innerHTML = moment( test_div.datetime, _datetime_format ).fromNow();
		});
		// is online check
		var url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/duckiebot/status/json?name=<?php echo $duckiebotName ?>&token=<?php echo $_SESSION["TOKEN"] ?>';
		callAPI( url, false, false, duckiebot_status_callback, true );
		// owner name call
		url = 'https://api.github.com/users/<?php echo $duckiebotOwner ?>';
		callExternalAPI( url, 'GET', 'json', false, false, github_user_info_callback, true, true, null, '<?php echo $duckiebotOwner ?>' );
	} );

</script>
