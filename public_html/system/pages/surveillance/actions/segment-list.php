<?php

require_once __DIR__.'/../../../templates/tableviewers/TableViewer.php';


// Define constants
$start_hour = 8; // 8AM
$end_hour = 23; // 11PM

// Define months available
$available_month_year = array(
	'10-2017',
	'11-2017',
	'12-2017',
	'01-2018'
);

// Get available cameras
$available_cameras = array();
foreach ( \system\classes\Configuration::$SURVEILLANCE as $cam_num => $cam) {
	if( $cam['enabled'] ){
		array_push( $available_cameras, $cam_num );
	}
}

$features = array(
	'date' => array(
		'type' => 'text',
		'default' => '10-2017',
		'values' => $available_month_year
	),
	'camera_num' => array(
		'type' => 'enum',
		'values' => $available_cameras,
		'default' => '1'
	)
);

// parse the arguments
\system\templates\tableviewers\TableViewer::parseFeatures( $features, $_GET );
$camera_num = $features['camera_num']['value'];

// Add the arguments to the query string
$_GET['date'] = $features['date']['value'];
$_GET['camera_num'] = $features['camera_num']['value'];

// parse the argument `date`
$date_str = $features['date']['value'];
$date_parts = explode( '-', $date_str );
$month = $date_parts[0];
$year = $date_parts[1];

// compute the current chunk
$today_str = date('Y-m-d');
$now_hour_str = date('H');
$now_minute_int = (int)date('i');
$now_str = sprintf( "%s.%02d", $now_hour_str, 30*( ($now_minute_int >= 30)? 1 : 0 ) );

// get real-time camera status
$cameraStatus = \system\classes\Core::getSurveillanceStatus( $camera_num );
$current_chunk_str = $cameraStatus['chunk'];
?>


<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>Surveillance</h2>
			</td>
		</tr>

	</table>

	<h4>Choose a month:</h4>
	<div class="btn-group btn-group-justified" data-toggle="buttons">
		<?php
		foreach ($features['date']['values'] as $d) {
			$dp = explode( '-', $d );
			$m = $dp[0];
			$y = $dp[1];
			$m_str = date('F', mktime(0, 0, 0, $m, 10));
			$active = ( strcmp($m, $month) == 0 && strcmp($y, $year) == 0 );
			?>
			<label class="btn btn-default <?php echo ( $active )? 'active' : '' ?>" data-date="<?php echo $m.'-'.$y ?>" onclick="_go_to_month(this)">
	  	    	<input type="radio" name="dates" autocomplete="off" <?php echo ( $active )? 'checked' : '' ?>>
				<?php
				echo sprintf( '%s %s', $m_str, $y );
				?>
	  	  	</label>
		<?php
		}
		?>
	</div>

	<br/><br/>


	<div class="text-center">
		<?php
		$hours = $end_hour - $start_hour;

		$month_short = date('M', mktime(0, 0, 0, $month, 10));

		$rec_history = \system\classes\Core::getSurveillanceRecordingHistory( $camera_num, null, $month );

		for ($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $day++) {
			$date_str = sprintf("%d-%02d-%02d", $year, $month, $day);
			$rec_day_chunks = $rec_history['days'][$date_str]['chunks'];
			$pproc_day_chunks = array(); //TODO
			$current_chunk = false;

			$no_data = booleanval($rec_day_chunks == null);
			$nav_size = "";
			if( $no_data ){
				$nav_size = "width:175px; margin:0 7px 36px 7px; display:inline-block";
			}
			?>

			<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px; <?php echo $nav_size ?>">
				<div class="container-fluid" style="padding-left:0; padding-right:0">

					<div class="collapse navbar-collapse navbar-left" style="padding-left:10px; padding-right:0">

						<table style="width:100%">
							<tr>
								<td class="text-center" width="100px" style="border-right:1px solid lightgray">
									<h3 style="margin:18px; margin-left:10px">
										<bold>
											<?php echo $month_short ?><br/>
											<?php echo sprintf("%02d", $day) ?>
										</bold>
									</h3>
								</td>

								<td style="padding:3px 0 0 10px">

									<?php
									if( $no_data ){
										?>
										<div class="rotated90ccw" style="margin-left:-5px">No&nbsp;Data</div>
										<?php
									}else{
										?>

										<table style="width:840px">
											<tr>
												<td width="40px" style="font-size:14pt; padding-right:12px; text-align:right">
													<table style="width:100%">
														<tr height="30px">
															<td><i class="icon-videocamerathree"></i></td>
														</tr>
														<tr height="30px">
															<td><i class="icon-movieclapper"></i></td>
														</tr>
														<tr height="30px">
															<td><i class="icon-manalt"></i></td>
														</tr>
													</table>
												</td>
												<td width="780px">
													<table style="width:100%">
														<?php

														$day_chunks_per_type = array(
															'rec' => $rec_day_chunks,
															'post-proc' => $pproc_day_chunks
														);

														foreach( array('rec', 'post-proc') as $type ){
															$day_chunks = $day_chunks_per_type[$type];
															?>

															<tr height="30px">
																<td>
																	<table style="width:100%">
																		<tr height="30px">
																			<?php
																			$cur = 0;
																			$right_now_found = false;
																			for ($abs = 0; $abs < 2*$hours; $abs++) {
																				$bar_color = null;
																				$was_recorded = false;
																				$right_now = false;
																				$expected = sprintf("%02d.%02d", $start_hour+$abs/2, 30*($abs%2) );
																				if( strcmp($expected, $day_chunks[$cur]) == 0 ){
																					$was_recorded = true;
																					$bar_color = 'progress-bar-success';
																					$cur += 1;
																				}
																				if( strcmp($type, 'rec') == 0 && strcmp($today_str, $date_str)==0 && strcmp($expected, $current_chunk_str)==0 ){
																					$right_now = true;
																					$right_now_found = true;
																					$bar_color = 'progress-bar-warning progress-bar-striped active';
																				}
																				if( strcmp($type, 'rec') == 0 && strcmp($today_str, $date_str)==0 && strcmp($expected, $now_str)==0 ){
																					$right_now_found = true;
																				}
																				if( strcmp($type, 'rec') == 0 && !$was_recorded && !$right_now_found ){
																					$bar_color = 'progress-bar-danger';
																				}
																				$border_color = ($abs%2 == 0)? ( ($abs==0)? 'lightgray' : '#e5e5e5' ) : '';
																				?>
																				<td width="1px" <?php echo ($abs%2 == 0)? 'style="background-color:'.$border_color.'"' : '' ?>></td>
																				<td>
																					<?php $clickable = ($was_recorded && !$right_now); ?>
																					<div class="progress <?php echo ($clickable)? 'pointer-hand' : '' ?>" style="margin:0; height:16px"
																						data-segment="<?php echo sprintf('%04d-%02d-%02d_%s', $year, $month, $day, $expected ); ?>"
																						<?php echo ($clickable)? 'onclick="_go_to_segment(this)"' : '' ?> >
																						<?php
																						$bar_size = ($right_now)? (float)$now_minute_int%30 / 0.3 : 100;
																						$bar_size = 100; //TODO: real size feature disabled
																						?>
																						<div class="progress-bar <?php echo $bar_color ?>"
																						  role="progressbar"
																						  style="width:<?php echo ($bar_color == null)? 0 : $bar_size ?>%">
																						</div>
																					</div>
																				</td>
																				<?php
																			}
																			?>
																			<td width="1px" style="background-color:#e5e5e5"></td>
																		</tr>
																	</table>
																</td>
															</tr>
														<?php
														}
														?>
														<tr height="30px">
															<td>
																<canvas id="motion-detection-chart-<?php echo 'TODO' ?>" width="780" height="30" class="chartjs-render-monitor" style="display: block; max-height:30px; border-right:1px solid #e5e5e5"></canvas>
															</td>
														</tr>
													</table>
												</td>
												<td width="20px"></td>
											</tr>
											<tr height="20px">
												<td colspan=3 style="padding-left:16px">
													<table style="width:100%">
														<tr height="20px">
															<?php
															for ($i = 0; $i <= $hours; $i++) {
																?>
																	<td class="text-center" width="<?php echo 100.0/(float)$hours-1 ?>%">
																		<span style="font-size:12px">
																			<?php echo date('ga', mktime($start_hour*60, ($i+$start_hour)*60 )); ?>
																		</span>
																	</td>
																<?php
															}
															?>
														</tr>
													</table>
												</td>
											</tr>
										</table>

										<?php
									}
									?>

								</td>
							</tr>
						</table>

					</div>

				</div>
			</nav>
		<?php
		}
		?>
	</div>


	<script>

		var data = [1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 1, 0, 0, 0,    0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 0, 1];
		var labels = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16,    17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31];

		var ctx = document.getElementById("motion-detection-chart-TODO").getContext("2d");

		var cfg = {
			type: 'line',
			data: {
				labels: labels,
				datasets: [{
					data: data,
					type: 'line',
					pointRadius: 0,
					fill: true,
					lineTension: 0,
					borderWidth: 3
				}]
			},
			options: {
				scales: {
					xAxes: [{
						type: 'time',
						time: {
							stepSize: 2
		                },
						ticks: {
							display: false
						},
						gridLines: {
				            display: true,
							drawBorder: false,
							drawTicks: false
				        }
					}],
					yAxes: [{
						type: 'linear',
						ticks: {
							display: false,
							max: 1.2
						},
						gridLines: {
				            display: false,
							drawBorder: true,
							drawTicks: false
				        }
					}]
				},
				legend: {
		            display: false
		        },
				gridLines: {
		            display: true,
					drawBorder: false,
					drawTicks: false
		        }
			}
		};

		$( document ).ready( function(){
			new Chart(ctx, cfg);
		} );


		function _go_to_month( target ){
			var date = $(target).data('date');
			var url = "<?php echo \system\classes\Configuration::$PLATFORM_BASE ?>surveillance?date="+date;
			window.location = url;
		}

		function _go_to_segment( target ){
			var segment = $(target).data('segment');
			<?php
			$qs = urlencode( base64_encode( toQueryString( array_keys($features), $_GET ) ) );
			?>
			var url = "<?php echo \system\classes\Configuration::$PLATFORM_BASE ?>surveillance?<?php echo ( (strlen($qs) > 0)? 'lst='.$qs.'&' : '' ) ?>segment="+segment;
			window.location = url;
		}

	</script>

</div>
