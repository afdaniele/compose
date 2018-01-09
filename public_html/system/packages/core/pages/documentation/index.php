<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Sunday, January 7th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 8th 2018

$file = \system\classes\Configuration::$ACTION;

if( strlen($file) < 5 ){ /* it has to contain at least .html */
	\system\classes\Core::redirectTo('documentation/index.html');
}
?>

<script>
function resizeIframe(obj) {
	var iframewindow= obj.contentWindow? obj.contentWindow : obj.contentDocument.defaultView;
	var sH = iframewindow.document.body.scrollHeight;
	obj.style.height = sH + 'px';
}
</script>

<style>
body > .container{
	width: 100% !important;
	padding: 0 8%;
}

iframe{
	border: 0;
	width: 100%;
}
</style>

<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:20px">
		<tr>
			<td style="width:100%">
				<h2 style="font-size:30px">Documentation</h2>
			</td>
		</tr>
	</table>

	<iframe id="doc_iframe" frameborder="0" scrolling="no" seamless="seamless" onload="resizeIframe(this)"
	src="<?php echo sprintf('%sdata/documentation/documentation.php?file=%s', \system\classes\Configuration::$BASE_URL, $file); ?>">
	</iframe>

	<script type="text/javascript">
		window.addEventListener('message', function(event) {
			if(event.data == 'ready') {
				sendHash();
			}
		});

		sendHash = function(){
			hash = window.location.hash.substring(1);
			$('iframe')[0].contentWindow.location.hash = window.location.hash;
			$('iframe')[0].contentWindow.postMessage({"findElement": hash}, '*');
		}

		$(window).on('hashchange', sendHash);

		window.addEventListener('message', function(event) {
			if(offset = event.data['offset']) {
				window.scrollTo(0, $('iframe').offset().top + offset)
			}
		});

		var minScroll = 160;
		$(window).scroll(function(){
			var scrollPos = window.pageYOffset;
			if( scrollPos <= minScroll ){
				$('#menu-box', $('#doc_iframe').contents()).css('margin-top', 0);
			}else{
				$('#menu-box', $('#doc_iframe').contents()).css('margin-top', scrollPos-minScroll);
			}
		})
	</script>

</div>
