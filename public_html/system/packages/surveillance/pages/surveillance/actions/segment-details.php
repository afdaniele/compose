<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Thursday, October 12th 2017
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018



use \system\packages\surveillance\Surveillance as Surveillance;

// Get the argument `segment`
$segment = $_GET['segment'];

// Get the query string from the `list` page
$qs = array();
parse_str( base64_decode( urldecode($_GET['lst']) ), $qs );
$camera_num = $qs['camera_num'];

$segment_is_valid = ( preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}\.[0-9]{2}/', $segment) === 1 );
$segment_exists = Surveillance::isWebMSurveillanceSegmentPresent( $camera_num, $segment );

if( !$segment_is_valid || !$segment_exists ){
	$_SESSION['_ALERT_WARNING'] = "The segment has not yet been converted to the web-format. The video segments are accessible via web only after the post-processing step.";
	?>
	<script type="text/javascript">
		var url = "<?php echo \system\classes\Configuration::$BASE ?>surveillance";
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

	<div style="text-align:center">
		<video width="100%" poster="<?php echo \system\classes\Configuration::$BASE_URL ?>images/video_privacy_placeholder.jpg" style="border: 1px solid lightgray" controls>
		  <source src="<?php echo \system\classes\Configuration::$BASE_URL ?>surveillance_data_1_sd/<?php echo $date ?>/web_<?php echo $segment ?>.mp4?start=8" type="video/mp4">
		</video>
	</div>


	<div class="text-right" style="width:100%; margin:40px 0 20px 0">
		<h4 style="display:inline">Downloads:</h4>&nbsp;&nbsp;
		<a role="button" class="btn btn-primary" style="margin-right:10px" href="<?php echo \system\classes\Configuration::$BASE_URL ?>surveillance_data_1_hd/<?php echo $date ?>/<?php echo $segment ?>.mp4" Download>
			Download HD (<?php echo Surveillance::sizeOfSurveillanceSegment( $camera_num, $segment ) ?>)
		</a>
		<a role="button" class="btn btn-primary" href="<?php echo \system\classes\Configuration::$BASE_URL ?>surveillance_data_1_sd/<?php echo $date ?>/web_<?php echo $segment ?>.mp4" download>
			Download SD (<?php echo Surveillance::sizeOfWebMSurveillanceSegment( $camera_num, $segment ) ?>)
		</a>

	</div>

</div>
