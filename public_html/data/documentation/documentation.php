<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

$page_file_name = $_GET['file'];

/* Use internal libxml errors -- turn on in production, off for debugging */
libxml_use_internal_errors(true);

/* Createa a new DomDocument object */
$dom = new DomDocument;
/* Load the HTML */
$dom->loadHTMLFile( $page_file_name );
/* Create a new XPath object */
$xpath = new DomXPath($dom);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=1000">
	<meta name="description" content="">
	<meta name="author" content="">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<base href="../../documentation/" target="_top">

	<!-- Bootstrap v3.3.1 by getboostrap.com -->
	<link href="../../css/compose.css" rel="stylesheet" type="text/css" >

	<!-- highlight.js 9.12.0 -->
	<link rel="stylesheet" href="../../css/highlight.js/arduino-light.css">
	<script src="../../js/highlight.min.js"></script>


	<?php
	// load CSS files
	$nodes = $xpath->query('/html/head/link[@type="text/css"]');
	foreach ($nodes as $node) {
		$css_file = $node->getAttribute('href');
		echo sprintf('<link type="text/css" rel="stylesheet" href="%s">', $css_file);
	}

	// load JS files
	$nodes = $xpath->query('/html/head/script[@type="text/javascript"]');
	foreach ($nodes as $node) {
        if( $node->hasAttribute('src') ){
			$js_file = $node->getAttribute('src');
			// if( $js_file == 'resize.js' ) continue;
			echo sprintf('<script type="text/javascript" src="%s"></script>', $js_file);
        }else{
            echo '<script type="text/javascript">'.$node->textContent.'</script>';
        }
	}
	?>
</head>
<body>

	<script>hljs.initHighlightingOnLoad();</script>

    <style type="text/css">
		body{
			background-color: inherit;
		}

    	body > .container{
    		width: 100% !important;
    		padding:0 8%;
    		min-width: 1000px !important;
    	}

    	h2, .h2{
    		font-size: 1.5em;
    	}

    	div.header{
    		padding: 0 20px;
    	}

    	div.summary{
    		width: 100%;
    		margin-top: 10px;
    	}

    	div.headertitle{
    		padding-left: 0;
    	}

    	a:empty{
    		display: block;
    	    position: relative;
    	    top: -54px;
    	    visibility: hidden;
    	}

    	.navpath li.footer{
    		display: none;
    	}

    	.sm{
    		z-index: inherit;
    	}

		#nav-tree{
			padding: 6px 8px;
			height: 480px;
			min-height: 480px;
			max-height: 480px;
		}

		#nav-tree-contents{
			overflow: scroll;
		}

		.memitem{
			margin-bottom: 30px;
		}

		.paramtype{
			font-weight: bold;
		}

		pre code.hljs{
			padding: 0;
		}
    </style>


    <div style="width:100%">

		<div style="margin-bottom:20px">
			<?php
			$nodes = $xpath->query('//*[@id="navrow1"]');
			if( !is_null($nodes->item(0)) ){
				echo( $dom->saveXml( $nodes->item(0) ) );
			}
			//
			$nodes = $xpath->query('//*[@id="navrow2"]');
			if( !is_null($nodes->item(0)) ){
				echo( $dom->saveXml( $nodes->item(0) ) );
			}
			?>
		</div>


    	<table class="documentation-box-container" style="width:100%">
    		<tr>
    			<td style="width:320px">
    				<div class="documentation-box documentation-menu-box" id="menu-box" style="background-color:#F9FAFC">
						<div class="header">
							<div class="headertitle">
								<div class="title">Navigator</div>
							</div>
						</div>
    					<div id="nav-tree">
    					  <div id="nav-tree-contents">
							  <div id="nav-sync" class="sync"></div>
						  </div>
    					</div>
    			</td>

    			<td>
    				<div class="documentation-box documentation-content-box">
    					<!-- Header -->
    					<?php
    					$nodes = $xpath->query('//*[@id="doc-content"]//div[@class="header"]');
						if( !is_null($nodes->item(0)) ){
							echo( $dom->saveXml( $nodes->item(0) ) );
						}
    					?>

    					<!-- NavBar -->
    					<?php
    					$nodes = $xpath->query('//*[@id="nav-path"]');
						if( !is_null($nodes->item(0)) ){
							echo( $dom->saveXml( $nodes->item(0) ) );
						}
    					?>

    					<!-- Content -->
    					<div style="padding: 0 26px">
    						<?php
    						$nodes = $xpath->query('//*[@id="doc-content"]/*[@class="contents"]');
							if( !is_null($nodes->item(0)) ){
								echo( $dom->saveXml( $nodes->item(0) ) );
							}
    						?>
    					</div>
    				</div>
    			</td>
    		</tr>
    	</table>

    </div>

    <?php
	// load JS files
	$nodes = $xpath->query('/html/body//script[@type="text/javascript"]');
	foreach ($nodes as $node) {
		$js_file = $node->getAttribute('src');
		if( $node->hasAttribute('src') ){
			echo sprintf('<script type="text/javascript" src="%s"></script>', $js_file);
		}else{
			echo '<script type="text/javascript">'.$node->textContent.'</script>';
		}
	}
	?>

	<script type="text/javascript">
		$(document).ready(function(){
			window.parent.postMessage("ready", "*");
		});

		window.addEventListener('message', function(event) {
			if(anchor = event.data['findElement']) {
				element = $('[id="' + anchor + '"]');
				window.parent.postMessage({"offset": element.offset().top}, "*");
			}
		});

		$(window).bind('hashchange', function(){
			console.log( window.location.hash );
	   });
	</script>

</body>
</html>
