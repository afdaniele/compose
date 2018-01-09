<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Sunday, January 7th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, January 7th 2018


/* Use internal libxml errors -- turn on in production, off for debugging */
libxml_use_internal_errors(true);

// get page name
$page_name = \system\classes\Configuration::$ACTION;
$page_file_name = sprintf("%s%s/%s",
	__DIR__,
	"/../../../data/documentation",
	$page_name
);
if( !file_exists($page_file_name) ){
	\system\classes\Core::throwError(
		sprintf('The page "%s" does not exist. Please check with the administrator.', $page_file_name)
	);
}

/* Createa a new DomDocument object */
$dom = new DomDocument;
/* Load the HTML */
$dom->loadHTMLFile( $page_file_name );
/* Create a new XPath object */
$xpath = new DomXPath($dom);

?>

<iframe>
	<html>
	<head>
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
			$js_file = $node->getAttribute('src');
			echo sprintf('<script type="text/javascript" src="%s"></script>', $js_file);
		}
		?>
	</head>
	<body>

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

<?php
// // load CSS files
// $nodes = $xpath->query('/html/head/link[@type="text/css"]');
// foreach ($nodes as $node) {
// 	$css_file = $node->getAttribute('href');
// 	echo sprintf('<link type="text/css" rel="stylesheet" href="%s">', $css_file);
// }

// // load JS files
// $nodes = [];
// foreach ($xpath->query('/html/head/script[@type="text/javascript"]') as $node) {
// 	array_push( $nodes, $node );
// }
// foreach ($xpath->query('/html/body//script[@type="text/javascript"]') as $node) {
// 	array_push( $nodes, $node );
// }
//
// // $noConflictScripts = ['resizes.js'];
//
// foreach ($nodes as $node) {
// 	$js_file = $node->getAttribute('src');
// 	if( $js_file == 'jquery.js' ){
// 		// echo sprintf('<script type="text/javascript" src="%s"></script>', $js_file);
// 		// echo "<script>jQueryDocumentation = jQuery.noConflict(true);</script>";
// 	}else{
// 		if( $node->hasAttribute('src') ){
// 			?>
// 			<script type="text/javascript" origin="<?php echo $js_file ?>">
// 				// <?php
// 				// if( in_array($js_file, $noConflictScripts) ){
// 				// 	echo sprintf("(function($, jQuery) {
// 				// 		// doxygen code
// 				// 		%s
// 				// 		})(jQueryDocumentation, jQueryDocumentation);",
// 				// 		file_get_contents(
// 				// 			sprintf("%s%s/%s",
// 				// 				__DIR__,
// 				// 				"/../../../data/documentation",
// 				// 				$js_file
// 				// 			)
// 				// 		)
// 				// 	);
// 				// }else{
// 				// 	echo file_get_contents(
// 				// 		sprintf("%s%s/%s",
// 				// 			__DIR__,
// 				// 			"/../../../data/documentation",
// 				// 			$js_file
// 				// 		)
// 				// 	);
// 				// }
// 				// ?>
// 			</script>
// 			<?php
// 			echo sprintf('<script type="text/javascript" src="%s"></script>', $js_file);
// 		}else{
// 			?>
// 			<script type="text/javascript">
// 				// <?php
// 				// echo $node->textContent;
// 				// ?>
// 			</script>
// 			<?php
// 			echo '<script type="text/javascript">'.$node->textContent.'</script>';
// 		}
// 	}
// }



//
// $nodes = $xpath->query('/html/body//script[@type="text/javascript"]');
// foreach ($nodes as $node) {
// 	if( $node->hasAttribute('src') ){
// 		$js_file = $node->getAttribute('src');
// 		?>
// 		<script type="text/javascript" origin="<?php echo $js_file ?>">
// 			<?php
// 			// echo sprintf("(function($, jQuery) {
// 			// // doxygen code
// 			// %s
// 			// })(jQueryDocumentation, jQueryDocumentation);",
// 			// file_get_contents(__DIR__.sprintf("/data/%s", $js_file))
// 			// );
//
// 			echo file_get_contents(__DIR__.sprintf("/data/%s", $js_file));
// 			?>
// 		</script>
// 		<?php
// 	}else{
// 		?>
// 		<script type="text/javascript">
// 			<?php
// 			// echo sprintf("(function($, jQuery) {
// 			// // doxygen code
// 			// %s
// 			// })(jQueryDocumentation, jQueryDocumentation);",
// 			// // $dom->saveHtml( $node )
// 			// $node->textContent
// 			// );
//
// 			echo $node->textContent;
// 			?>
// 		</script>
// 		<?php
// 	}
// }







// // load JS files
// $nodes = $xpath->query('/html/head/script[@type="text/javascript"]');
// foreach ($nodes as $node) {
// 	$js_file = $node->getAttribute('src');
// 	echo sprintf('<script type="text/javascript" src="%s"></script>', $js_file);
// 	if( $js_file == 'jquery.js' ){
// 		echo "\n<script type='text/javascript'>jQueryDocumentation = jQuery.noConflict(true);</script>";
// 	}
//
// }
//
// $nodes = $xpath->query('/html/body//script[@type="text/javascript"]');
// foreach ($nodes as $node) {
// 	if( $node->hasAttribute('src') ){
// 		$js_file = $node->getAttribute('src');
// 		echo sprintf('<script type="text/javascript" src="%s"></script>', $js_file);
// 	}else{
// 		?>
// 		<script type="text/javascript">
// 			<?php
// 			echo sprintf("(function($, jQuery) {
// 			// doxygen code
// 			%s
// 			})(jQueryDocumentation, jQueryDocumentation);",
// 			// $dom->saveHtml( $node )
// 			$node->textContent
// 			);
//
// 			// echo $node->textContent;
// 			?>
// 		</script>
// 		<?php
// 	}
// }

?>

<style type="text/css">
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
	    top: -60px;
	    visibility: hidden;
	}

	.navpath li.footer{
		display: none;
	}

	#nav-tree .item .label{
		margin-left: 16px;
	}

	#nav-tree-contents > ul > li > .item > .label{
		margin-left: 0;
	}

	.sm{
		z-index: inherit;
	}
</style>


<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:20px">
		<tr>
			<td style="width:100%">
				<h2 style="font-size:30px">Documentation</h2>
			</td>
		</tr>
		<tr>
			<td>
				<?php
				$nodes = $xpath->query('//*[@id="main-nav"]');
				echo( $dom->saveXml( $nodes->item(0) ) );
				?>
			</td>
		</tr>
	</table>

	<table class="documentation-box-container" style="width:100%">
		<tr>
			<td style="width:320px">
				<div class="documentation-box" style="background-color:#F9FAFC">
					<p>
						<strong>
							Navigator
						</strong>
					</p>
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
					echo( $dom->saveXml( $nodes->item(0) ) );
					?>


					<!-- NavBar -->
					<?php
					$nodes = $xpath->query('//*[@id="nav-path"]');
					echo( $dom->saveXml( $nodes->item(0) ) );
					?>



					<!-- Content -->
					<div style="padding: 0 26px">
						<?php
						$nodes = $xpath->query('//*[@id="doc-content"]/*[@class="contents"]');
						echo( $dom->saveXml( $nodes->item(0) ) );
						?>
					</div>
				</div>
			</td>
		</tr>
	</table>

</div>
<!--
<script type="text/javascript">
$(document).ready(function(){
	window.jQuery = window.$ = jQueryDocumentation;
	initResizable();
	window.jQuery = window.$ = jQueryMaster;
});
</script>

<script type="text/javascript">
// var searchBox = new SearchBox("searchBox", "search",false,'Search');
</script>

<script type="text/javascript">
// $(function() {

initMenu('',true,false,'search.php','Search');
$(document).ready(function() {
	window.jQuery = window.$ = jQueryDocumentation;
	// init_search();
	window.jQuery = window.$ = jQueryMaster;
});

// });
</script>

<script type="text/javascript">
$(document).ready(function(){
	window.jQuery = window.$ = jQueryDocumentation;
	initNavTree('classsystem_1_1classes_1_1_core.html','');
	window.jQuery = window.$ = jQueryMaster;
});
</script>


<script type="text/javascript">
window.jQuery = window.$ = jQueryMaster;
</script> -->

</body>
</html>

</iframe>
