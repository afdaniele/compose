<script type="text/javascript">
	var _datetime_format = 'YYYY-MM-DD HH:mm:ss';
	var server_disk_status_last_update = null;
	var server_surveillance_status_last_update = null;
	var server_surveillance_recording_history_last_update = null;
	var server_surveillance_postprocessing_history_last_update = null;

	function update_last_update_strs(){
		if( server_disk_status_last_update != null ){
			server_disk_status_last_update_str = moment( server_disk_status_last_update, _datetime_format ).fromNow();
			$('#server-disk-last-update-elem').html( server_disk_status_last_update_str );
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


<div style="width:100%; margin:auto">

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
							array( 'name' => "Operating System", 'icon' => "software", 'value' => $server_status['os_release'] ),
							array( 'name' => "Processor", 'icon' => "cpu-processor", 'value' => $server_status['cpu_model'] ),
							array( 'name' => "System memory", 'icon' => "ram", 'value' => $server_status['ram_total'] )
						);

						foreach( $server_details as $elem ){
							?>
							<p>
								<bold><i class="icon-<?php echo $elem['icon']; ?>"></i> <?php echo $elem['name']; ?>:</bold>
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
					<div class="text-center duckiebots-counter-chart-placeholder" style="height:180px">
						<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px; margin-top:76px">
					</div>

					<table style="width:100%">
						<tr>
							<td style="height:130px">
								<canvas id="duckiebots-counter-chart-canvas-0" style="width:150px; height:130px; padding:8px"></canvas>
							</td>
						</tr>
						<tr>
							<td style="height:20px">
								<div class="col-md-6 text-right" style="border-right:1px solid #ddd; padding-right:8px">
									Online (<span id="duckiebots-counter-chart-text-span-1"></span>)
									<div style="width:16px; height:16px; margin-top:2px; margin-left:8px; background-color:rgba(70,191,189,0.7); float:right"></div>
								</div>
								<div class="col-md-6 text-left" style="padding-left:8px">
									<div style="width:16px; height:16px; margin-top:2px; margin-right:8px; background-color:rgba(151,187,205,0.7); float:left"></div>
									(<span id="duckiebots-counter-chart-text-span-2"></span>) Offline
								</div>
							</td>
						</tr>
					</table>

				</div>
			</div>

			<div class="panel-footer" style="background-color:cornsilk; padding-top:4px; padding-bottom:0; height:30px">
				<div class="col-md-12 text-right" style="padding-right:0">
					<span class="glyphicon glyphicon-time" aria-hidden="true"></span>&nbsp;
					<span id="duckiebots-counter-last-update-elem">...</span>
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
					<div id="surveillance-status-container" style="margin:auto">
						<span style="color:green; display:none" id="surveillance-status-true">
							<h1 style="margin-top:55px">
								<i class="icon-record"></i>
							</h1>
							<h4>RECORDING</h4>
						</span>
						<span style="color:brown; display:none" id="surveillance-status-false">
							<h1 style="margin-top:55px">
								<i class="icon-erroralt"></i>
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
							<bold><i class="icon-film"></i> Total:</bold>
							<span id="surveillance-recording-total-time-span"></span>
						</p>
						<br/>
						<bold><i class="icon-history"></i> History:</bold>
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
							<bold><i class="icon-film"></i> Total:</bold>
							<span id="surveillance-postprocessing-total-time-span"></span>
						</p>
						<br/>
						<bold><i class="icon-history"></i> History:</bold>
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
		var is_recording_url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/server/surveillance_status/json?camera_num=1&token=<?php echo $_SESSION["TOKEN"] ?>';
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
		var recording_history_url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/server/surveillance_history/json?camera_num=1&type=recording&size=3&token=<?php echo $_SESSION["TOKEN"] ?>';
		function recording_history_callback( result ){
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
			//
			server_surveillance_recording_history_last_update = moment().format( _datetime_format );
			update_last_update_strs();
		}
		callAPI( recording_history_url, false, false, recording_history_callback, true );
	}

	function post_processing_history(){
		var post_processing_history_url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/server/surveillance_history/json?camera_num=1&type=post-processing&size=3&token=<?php echo $_SESSION["TOKEN"] ?>';
		function post_processing_history_callback( result ){
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
			//
			server_surveillance_postprocessing_history_last_update = moment().format( _datetime_format );
			update_last_update_strs();
		}
		callAPI( post_processing_history_url, false, false, post_processing_history_callback, true );
	}


	function duckiebots_status(){
		// compose the url
		var url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/duckiebot/status/json?name=afduck&token=<?php echo $_SESSION["TOKEN"] ?>';
		//
		function duckiebot_status_callback( result ){
			console.log( result );
		}
		//
		callAPI( url, false, false, duckiebot_status_callback, true );
	}


	function disk_status(){
		// compose the url
		var url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/server/disk_usage/json?camera_num=1&token=<?php echo $_SESSION["TOKEN"] ?>';
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
		                    "rgba(151,187,205,0.7)",
		                    "rgba(70,191,189,0.7)"
		                ],
						hoverBackgroundColor: [
							"rgba(151,187,205,0.5)",
							"rgba(70,191,189,0.5)"
						]
		            }],
		            labels: [
		                "({0}%) Used".format( (result.data.used * 100.0).toPrecision(2) ),
		                "({0}%) Free".format( (result.data.free * 100.0).toPrecision(2) )
		            ]
		        },
		        options: {
		            responsive: true,
					cutoutPercentage: 50,
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

		duckiebots_status();

		disk_status();
	} );
</script>
