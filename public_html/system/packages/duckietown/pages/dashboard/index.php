<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Tuesday, January 9th 2018

?>

<script type="text/javascript">
	var _datetime_format = 'YYYY-MM-DD HH:mm:ss';
	var server_disk_status_last_update = null;
	var duckiebots_last_update = null;
	var server_surveillance_status_last_update = null;
	var server_surveillance_recording_history_last_update = null;
	var server_surveillance_postprocessing_history_last_update = null;


	function update_last_update_strs(){
		if( server_disk_status_last_update != null ){
			server_disk_status_last_update_str = moment( server_disk_status_last_update, _datetime_format ).fromNow();
			$('#server-disk-last-update-elem').html( server_disk_status_last_update_str );
		}
		if( duckiebots_last_update != null ){
			duckiebots_last_update_str = moment( duckiebots_last_update, _datetime_format ).fromNow();
			$('#duckiebots-last-update-elem').html( duckiebots_last_update_str );
		}
		if( server_surveillance_status_last_update != null ){
			server_surveillance_status_last_update_str = moment( server_surveillance_status_last_update, _datetime_format ).fromNow();
			$('#surveillance-status-last-update-elem').html( server_surveillance_status_last_update_str );
		}
		if( server_surveillance_recording_history_last_update != null ){
			server_surveillance_recording_history_update_str = moment( server_surveillance_recording_history_last_update, _datetime_format ).fromNow();
			$('#surveillance-recording-last-update-elem').html( server_surveillance_recording_history_update_str );
		}
		if( server_surveillance_postprocessing_history_last_update != null ){
			server_surveillance_postprocessing_history_update_str = moment( server_surveillance_postprocessing_history_last_update, _datetime_format ).fromNow();
			$('#surveillance-postprocessing-last-update-elem').html( server_surveillance_postprocessing_history_update_str );
		}
	}

	window.setInterval(function(){
		update_last_update_strs();
	}, 1000);
</script>


<?php

$duckiebots = \system\classes\Core::getDuckiebotsCurrentBranch();
$total_duckiebots = sizeof( $duckiebots );

?>

<div style="width:100%; margin:auto">

	<br/>
	<br/>

	<!-- First Row ><-->

	<div class="col-md-4">
		<div class="panel panel-default">

			<div class="panel-heading" style="background-image:none; background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12" style="padding-left:0">
					<span class="glyphicon glyphicon-tasks" aria-hidden="true"></span>&nbsp;<strong>Server</strong> - Info
				</div>
			</div>

			<div class="panel-body dashboard-chart-body text-center">
				<div class="col-md-12 text-left" style="padding:6px 12px; height:180px">
					<div id="server-info-chart-container" style="margin:auto">
						<?php
						$server_status = \system\classes\Core::getServerStatus();

						$server_details = array(
							array( 'name' => "Operating System", 'icon' => "linux", 'value' => $server_status['os_release'] ),
							array( 'name' => "Processor", 'icon' => "tasks", 'value' => $server_status['cpu_model'] ),
							array( 'name' => "System memory", 'icon' => "microchip", 'value' => $server_status['ram_total'] )
						);

						foreach( $server_details as $elem ){
							?>
							<p>
								<bold><i class="fa fa-<?php echo $elem['icon']; ?>" aria-hidden="true"></i> <?php echo $elem['name']; ?>:</bold>
								<br/><?php echo $elem['value']; ?>
							</p>
							<?php
						}
						?>
					</div>
				</div>
			</div>

			<div class="panel-footer" style="background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px"></div>

		</div>
	</div>

	<div class="col-md-4">
		<div class="panel panel-default">

			<div class="panel-heading" style="background-image:none; background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12" style="padding-left:0">
					<span class="glyphicon glyphicon-tasks" aria-hidden="true"></span>&nbsp;<strong>Server</strong> - Disk
				</div>
			</div>

			<div class="panel-body dashboard-chart-body text-center">
				<div class="col-md-12" style="padding:0; height:180px">
					<div class="text-center" id="server-disk-chart-placeholder" style="height:180px">
						<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px; margin-top:76px">
					</div>
					<canvas id="server-disk-chart-canvas-0" style="width:273px; height:180px; padding:8px"></canvas>
				</div>
			</div>

			<div class="panel-footer" style="background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12 text-right" style="padding-right:0">
					<span class="glyphicon glyphicon-time" aria-hidden="true"></span>&nbsp;
					<span id="server-disk-last-update-elem">...</span>
				</div>
			</div>

		</div>
	</div>


	<div class="col-md-4">
		<div class="panel panel-default">

			<div class="panel-heading" style="background-image:none; background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12" style="padding-left:0">
					<span class="glyphicon glyphicon-stats" aria-hidden="true"></span>&nbsp;<strong>Duckiebots</strong>
				</div>
			</div>

			<div class="panel-body dashboard-chart-body" style="height:192px">
				<div class="col-md-12" style="padding:0; height:180px">
					<canvas id="duckiebots-counter-chart-canvas" style="width:273px; height:180px; padding:8px"></canvas>
				</div>
			</div>

			<div class="panel-footer" style="background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12 text-right" style="padding-right:0">
					<span class="glyphicon glyphicon-time" aria-hidden="true"></span>&nbsp;
					<span id="duckiebots-last-update-elem">...</span>
				</div>
			</div>

		</div>
	</div>



	<!-- Second Row ><-->

	<div class="col-md-4">
		<div class="panel panel-default">

			<div class="panel-heading" style="background-image:none; background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12" style="padding-left:0">
					<span class="glyphicon glyphicon-facetime-video" aria-hidden="true"></span>&nbsp;<strong>Surveillance</strong> - Status
				</div>
			</div>

			<div class="panel-body dashboard-chart-body text-center">
				<div class="col-md-12" style="padding:0; height:180px">
					<div class="text-center surveillance-status-placeholder" style="height:180px">
						<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px; margin-top:76px">
					</div>
					<div id="surveillance-status-container" style="margin:auto; transform: scale(1.2);">
						<span style="color:rgb(75, 192, 192); display:none" id="surveillance-status-true">
							<h1 style="margin-top:55px">
								<i class="fa fa-circle" aria-hidden="true"></i>
							</h1>
							<h4>RECORDING</h4>
						</span>
						<span style="color:rgb(255, 99, 132); display:none" id="surveillance-status-false">
							<h1 style="margin-top:55px">
								<i class="fa fa-stop" aria-hidden="true"></i>
							</h1>
							<h4>NOT RECORDING</h4>
						</span>
					</div>
				</div>
			</div>

			<div class="panel-footer" style="background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12 text-right" style="padding-right:0">
					<span class="glyphicon glyphicon-time" aria-hidden="true"></span>&nbsp;
					<span id="surveillance-status-last-update-elem">...</span>
				</div>
			</div>

		</div>
	</div>

	<div class="col-md-4">
		<div class="panel panel-default">

			<div class="panel-heading" style="background-image:none; background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12" style="padding-left:0">
					<span class="glyphicon glyphicon-facetime-video" aria-hidden="true"></span>&nbsp;<strong>Surveillance</strong> - Recording
				</div>
			</div>

			<div class="panel-body dashboard-chart-body">
				<div class="col-md-12" style="padding:6px 12px; height:180px">
					<div class="text-center surveillance-recording-chart-placeholder" style="height:180px">
						<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px; margin-top:76px">
					</div>
					<div id="surveillance-recording-chart-container" style="margin:auto; display:none">
						<p>
							<bold>Total:</bold>
							<span id="surveillance-recording-total-time-span"></span>
						</p>
						<br/>
						<bold>History:</bold>
						<table class="table table-condensed" id="surveillance-recording-history-records">
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="panel-footer" style="background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12 text-right" style="padding-right:0">
					<span class="glyphicon glyphicon-time" aria-hidden="true"></span>&nbsp;
					<span id="surveillance-recording-last-update-elem">...</span>
				</div>
			</div>

		</div>
	</div>


	<div class="col-md-4">
		<div class="panel panel-default">

			<div class="panel-heading" style="background-image:none; background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12" style="padding-left:0">
					<span class="glyphicon glyphicon-facetime-video" aria-hidden="true"></span>&nbsp;<strong>Surveillance</strong> - Post-Processing
				</div>
			</div>

			<div class="panel-body dashboard-chart-body">
				<div class="col-md-12" style="padding:6px 12px; height:180px">
					<div class="text-center surveillance-postprocessing-chart-placeholder" style="height:180px">
						<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px; margin-top:76px">
					</div>
					<div id="surveillance-postprocessing-chart-container" style="margin:auto">
						<p>
							<bold>Total:</bold>
							<span id="surveillance-postprocessing-total-time-span"></span>
						</p>
						<br/>
						<bold>History:</bold>
						<table class="table table-condensed" id="surveillance-postprocessing-history-records">
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="panel-footer" style="background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12 text-right" style="padding-right:0">
					<span class="glyphicon glyphicon-time" aria-hidden="true"></span>&nbsp;
					<span id="surveillance-postprocessing-last-update-elem">...</span>
				</div>
			</div>

		</div>
	</div>

</div>



<script type="text/javascript">

	function is_recording_check(){
		var is_recording_url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/surveillance/surveillance_status/json?camera_num=1&token=<?php echo $_SESSION["TOKEN"] ?>';
		function is_recording_callback( result ){
			// hide the placeholder and show the container
			$(document).find('.surveillance-status-placeholder').each(function(){
				$(this).css('display', 'none');
			});
			$('#surveillance-status-'+result.data.is_recording).css('display', '');
			$('#surveillance-status-'+(!result.data.is_recording)).css('display', 'none');
			server_surveillance_status_last_update = moment().format( _datetime_format );
			update_last_update_strs();
		}
		callAPI( is_recording_url, false, false, is_recording_callback, true );
	}

	function recording_history(){
		var recording_history_url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/surveillance/surveillance_history/json?camera_num=1&type=recording&size=3&token=<?php echo $_SESSION["TOKEN"] ?>';
		function recording_history_callback( result ){
			// create records rows
			$.each(result.data.days, function(day) {
				// create total time string
				var delta = result.data.days[day].total_minutes;
				// calculate (and subtract) whole hours
				var hours = Math.floor(delta / 60) % 24;
				delta -= hours * 60;
				// what's left is minutes
				var minutes = delta;
				var total_time_str = "";
				if( hours > 0 ){
					total_time_str += "{0} h".format( hours );
				}
				if( minutes > 0 ){
					if( hours > 0 ) total_time_str += ", ";
					total_time_str += "{0} m".format( minutes );
				}
				$('#surveillance-recording-history-records > tbody:last-child').append(
					'<tr><td>'+day+'</td><td style="width:50%; text-align:right"><bold>'+total_time_str+'</bold></td></tr>'
				);
			});
		}
		//
		var recording_history_unlimited_url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/surveillance/surveillance_history/json?camera_num=1&type=recording&token=<?php echo $_SESSION["TOKEN"] ?>';
		function recording_history_unlimited_callback( result ){
			// hide the placeholder and show the container
			$(document).find('.surveillance-recording-chart-placeholder').each(function(){
				$(this).css('display', 'none');
			});
			$('#surveillance-recording-chart-container').css('display', '');
			// create total time string
			var delta = result.data.total_minutes;
			// calculate (and subtract) whole days
			var days = Math.floor(delta / 1440);
			delta -= days * 1440;
			// calculate (and subtract) whole hours
			var hours = Math.floor(delta / 60) % 24;
			delta -= hours * 60;
			// what's left is minutes
			var minutes = delta;
			var total_time_str = "";
			if( days > 0 ){
				total_time_str += "{0} days, ".format( days );
			}
			if( hours > 0 ){
				total_time_str += "{0} hours, ".format( hours );
			}
			total_time_str += "{0} minutes".format( minutes );
			$('#surveillance-recording-total-time-span').html( total_time_str );
			//
			server_surveillance_recording_history_last_update = moment().format( _datetime_format );
			update_last_update_strs();
		}
		//
		callAPI( recording_history_url, false, false, recording_history_callback, true );
		callAPI( recording_history_unlimited_url, false, false, recording_history_unlimited_callback, true );
	}

	function post_processing_history(){
		var post_processing_history_url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/surveillance/surveillance_history/json?camera_num=1&type=post-processing&size=3&token=<?php echo $_SESSION["TOKEN"] ?>';
		function post_processing_history_callback( result ){
			// create records rows
			$.each(result.data.days, function(day) {
				// create total time string
				var delta = result.data.days[day].total_minutes;
				// calculate (and subtract) whole hours
				var hours = Math.floor(delta / 60) % 24;
				delta -= hours * 60;
				// what's left is minutes
				var minutes = delta;
				var total_time_str = "";
				if( hours > 0 ){
					total_time_str += "{0} h".format( hours );
				}
				if( minutes > 0 ){
					if( hours > 0 ) total_time_str += ", ";
					total_time_str += "{0} m".format( minutes );
				}
				$('#surveillance-postprocessing-history-records > tbody:last-child').append(
					'<tr><td>'+day+'</td><td style="width:50%; text-align:right"><bold>'+total_time_str+'</bold></td></tr>'
				);
			});
		}
		//
		var post_processing_history_unlimited_url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/surveillance/surveillance_history/json?camera_num=1&type=post-processing&token=<?php echo $_SESSION["TOKEN"] ?>';
		function post_processing_history_unlimited_callback( result ){
			// hide the placeholder and show the container
			$(document).find('.surveillance-postprocessing-chart-placeholder').each(function(){
				$(this).css('display', 'none');
			});
			$('#surveillance-postprocessing-chart-container').css('display', '');
			// create total time string
			var delta = result.data.total_minutes;
			// calculate (and subtract) whole days
			var days = Math.floor(delta / 1440);
			delta -= days * 1440;
			// calculate (and subtract) whole hours
			var hours = Math.floor(delta / 60) % 24;
			delta -= hours * 60;
			// what's left is minutes
			var minutes = delta;
			var total_time_str = "";
			if( days > 0 ){
				total_time_str += "{0} days, ".format( days );
			}
			if( hours > 0 ){
				total_time_str += "{0} hours, ".format( hours );
			}
			total_time_str += "{0} minutes".format( minutes );
			$('#surveillance-postprocessing-total-time-span').html( total_time_str );
			//
			server_surveillance_postprocessing_history_last_update = moment().format( _datetime_format );
			update_last_update_strs();
		}
		//
		callAPI( post_processing_history_url, false, false, post_processing_history_callback, true );
		callAPI( post_processing_history_unlimited_url, false, false, post_processing_history_unlimited_callback, true );
	}


	function disk_status(){
		// compose the url
		var url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/surveillance/disk_usage/json?camera_num=1&token=<?php echo $_SESSION["TOKEN"] ?>';
		//
		function disk_status_callback(result){
			var config = {
		        type: 'pie',
		        data: {
		            datasets: [{
		                data: [
							result.data.used,
							result.data.free
						],
		                backgroundColor: [
							window.chartColors.red,
							window.chartColors.green
		                ]
		            }],
		            labels: [
		                "({0}%) Used".format( (result.data.used * 100.0).toPrecision(2) ),
		                "({0}%) Free".format( (result.data.free * 100.0).toPrecision(2) )
		            ]
		        },
		        options: {
		            responsive: true,
					cutoutPercentage: 0,
					legend: {
						position: "bottom"
					},
					animation: {
						easing: 'easeOutBounce'
					}
		        }
		    };

			$('#server-disk-chart-placeholder').css('display', 'none');
			$('#server-disk-chart-canvas-0').css('display', '');

			var ctx = document.getElementById("server-disk-chart-canvas-0").getContext("2d");
	        new Chart(ctx, config);

			server_disk_status_last_update = moment().format( _datetime_format );
		}
		//
		callAPI( url, false, false, disk_status_callback, true );
	}

	var duckiebots_status_chart = null;

	function duckiebots_status(){
		// draw empty pie
		var config = {
			type: 'pie',
			data: {
				datasets: [{
					data: [
						0,
						<?php echo $total_duckiebots ?>,
						0
					],
					backgroundColor: [
						window.chartColors.green,
						window.chartColors.yellow,
						window.chartColors.red
					]
				}],
				labels: [
					"Online",
					"TBA",
					"Offline"
				]
			},
			options: {
				responsive: true,
				cutoutPercentage: 0,
				legend: {
					position: "bottom"
				},
				animation: {
					easing: 'easeOutBounce'
				}
			}
		};
		// draw empty chart
		var ctx = document.getElementById("duckiebots-counter-chart-canvas").getContext("2d");
		duckiebots_status_chart = new Chart(ctx, config);
		//
		function duckiebot_status_callback(result){
			if( result.data.online ){
				duckiebots_status_chart.config.data.datasets[0].data[0] += 1;
			}else{
				duckiebots_status_chart.config.data.datasets[0].data[2] += 1;
			}
			duckiebots_status_chart.config.data.datasets[0].data[1] -= 1;
			duckiebots_status_chart.update();
			duckiebots_last_update = moment().format( _datetime_format );
		}
		//
		var duckiebots = [
			<?php
			foreach ($duckiebots as $b) {
				echo sprintf('"%s", ', $b);
			}
			?>
		];
		$.each(duckiebots, function(i) {
			duckiebot = duckiebots[i];
			// is online check
			var url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/duckiebot/status/json?name='+duckiebot+'&token=<?php echo $_SESSION["TOKEN"] ?>';
			callAPI( url, false, false, duckiebot_status_callback, true );
		});
	}



	$(document).ready( function(){
		// configure Chart.js
		Chart.defaults.global.animationEasing = "easeOutBounce";
		Chart.defaults.global.responsive = true;
		Chart.defaults.global.scaleBeginAtZero = true;
		Chart.defaults.global.maintainAspectRatio = false;

		// Recording status
		is_recording_check();
		window.setInterval(function(){
			is_recording_check();
		}, 10000);

		recording_history();

		post_processing_history();

		disk_status();

		duckiebots_status();
	} );
</script>
