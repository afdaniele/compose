<script src="<?php echo \system\classes\Configuration::$BASE_URL ?>js/videojs-contrib-hls.min.js"></script>

<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">
		<tr>
			<td style="width:100%">
				<h2>Live</h2>
			</td>
		</tr>
	</table>

	<video id="live-video" width=970 height=450 class="video-js vjs-default-skin" controls preload="auto">
		<source src="<?php echo \system\classes\Configuration::$BASE_URL ?>/ts/live.m3u8" type="application/x-mpegURL">
	</video>

	<script>
		$(document).ready( function(){
			var player = videojs('live-video');
			player.play();
		});
	</script>

</div>
