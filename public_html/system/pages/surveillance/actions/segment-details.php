<?php

// Get the argument `segment`
$segment = $_GET['segment'];

// Get the query string from the `list` page
$qs = array();
parse_str( base64_decode($_GET['lst']), $qs );
$camera_num = $qs['camera_num'];

$segment_is_valid = ( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}/', $segment) === 1 );
$segment_exists = \system\classes\Core::isSurveillanceSegmentPresent( $camera_num, $segment );

if( !$segment_is_valid || !$segment_exists ){
	?>
	<script type="text/javascript">
		var url = "<?php echo \system\classes\Configuration::$PLATFORM_BASE ?>surveillance";
		window.location = url;
	</script>
	<?php
	die();
	exit();
}

$segment_parts = explode( '_', $segment );
$date = $segment_parts[0];
$date_parts = explode('-', $date);
$year = $date_parts[0];
$month = $date_parts[1];
$day = $date_parts[2];
$chunk = $segment_parts[1];
$chunk_parts = explode('.', $chunk);
$hour = $chunk_parts[0];
$min = $chunk_parts[1];
?>

<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>Segment - <?php echo date('F jS Y - g:iA', mktime($hour, $min, 0, $month, $day, $year)); ?></h2>
			</td>
		</tr>

	</table>

	<div class="alert alert-info text-center" role="alert" style="padding:10px">
		<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span> &nbsp;
		<bold>Note:</bold> This video was recorded in high resolution. You need a high-speed connection to watch it.
	</div>
	<div style="text-align:center">
		<video width="100%" poster="<?php echo \system\classes\Configuration::$BASE_URL ?>images/video_privacy_placeholder.jpg" style="border: 1px solid lightgray" controls>
		  <source src="http://box0.afdaniele.com/surveillance_data_1/<?php echo $date ?>/<?php echo $segment ?>.mp4?start=1" type="video/mp4">
		</video>
	</div>

</div>
