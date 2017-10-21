<?php

$duckiebotName = $_GET['bot'];

?>


<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>Duckiebot - <?php echo $duckiebotName ?></h2>
			</td>
		</tr>

	</table>


	<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px; <?php echo $nav_size ?>">
		<div class="container-fluid" style="padding-left:0; padding-right:0">

			<div class="collapse navbar-collapse navbar-left" style="padding-left:10px; padding-right:0">

				<table style="width:100%">
					<tr>
						<td class="text-center" width="100px" style="border-right:1px solid lightgray">
							<h3 style="margin:18px; margin-left:10px">
								<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/duckiebot.gif" style="width:100px" />
							</h3>
						</td>
						<td style="padding-left:20px; width:280px">
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
										 <?php echo \system\classes\Core::getDuckiebotOwner($duckiebotName) ?>
									</td>
								</tr>
								<tr>
									<td>
										 <bold>Owner Name:<bold>
									</td>
									<td>
										<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:18px; height:18px;">
										 <!-- <?php echo \system\classes\Core::getDuckiebotOwner($duckiebotName) ?> -->
									</td>
								</tr>
								<tr>
									<td>
										 <bold>IP Address:<bold>
									</td>
									<td>
										<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:18px; height:18px;">
										 <!-- <?php echo \system\classes\Core::getDuckiebotIPAddress($duckiebotName) ?> -->
									</td>
								</tr>
							</table>
						</td>
						<td class="text-center" style="padding:0 5px" width="100px">
							<img id="duckiebot-disk-chart-placeholder" src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px">
							<canvas id="duckiebot-disk-chart-canvas" width="90px" height="90px" style="display:none"></canvas>
						</td>
					</tr>




				</table>
			</div>
		</div>
	</nav>

</div>


<script type="text/javascript">

	function disk_status( disk_status ){
		var config = {
			type: 'pie',
			data: {
				datasets: [{
					data: [
						disk_status.used,
						disk_status.free
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
					"({0}%) Used".format( (disk_status.used * 100.0).toPrecision(2) ),
					"({0}%) Free".format( (disk_status.free * 100.0).toPrecision(2) )
				]
			},
			options: {
				responsive: true,
				cutoutPercentage: 50,
				legend: {
					display: false,
					position: "right"
				},
				animation: {
					easing: 'easeOutBounce'
				}
			}
		};

		// $('#duckiebot-disk-chart-placeholder').css('display', 'none');
		// $('#duckiebot-disk-chart-canvas').css('display', '');

		var ctx = document.getElementById("duckiebot-disk-chart-canvas").getContext("2d");
		new Chart(ctx, config);
	}



	$(document).ready( function(){
		// configure Chart.js
		Chart.defaults.global.animationEasing = "easeOutBounce";
		Chart.defaults.global.responsive = true;
		Chart.defaults.global.scaleBeginAtZero = true;
		Chart.defaults.global.maintainAspectRatio = false;

		disk_status( {used:0.5, free:0.5} );
	} );

</script>
