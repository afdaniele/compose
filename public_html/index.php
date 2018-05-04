<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 15th 2018


// load constants
require_once 'system/environment.php';

// load core libraries
require_once 'system/classes/Core.php';
require_once 'system/classes/Configuration.php';
require_once 'system/classes/Database.php';
require_once 'system/classes/BlockRenderer.php';
require_once 'system/classes/MissionControl.php';
require_once 'system/classes/enum/EmailTemplates.php';
require_once 'system/utils/utils.php';
require_once 'system/utils/URLrewrite.php';
require_once 'system/templates/forms/forms.php';
require_once 'system/templates/sections/sections.php';
require_once 'system/templates/paginators/paginators.php';

// load the error handler module
require_once 'system/packages/core/modules/error_handler.php';

// simplify namespaces
use system\classes\Core as Core;
use system\classes\Configuration as Configuration;
use system\utils\URLrewrite as URLrewrite;

// create a Session
Core::startSession();

// init Core
Core::initCore();

// get info about the current user
$user_role = Core::getUserRole();

// get the list of pages the current user has access to
$pages_list = Core::getFilteredPagesList( 'list', true, $user_role );
$available_pages = array_map( function($p){ return $p['id']; }, $pages_list );
$factory_default_page = Core::getFactoryDefaultPagePerRole( $user_role );
if( strcmp($factory_default_page, "NO_DEFAULT_PAGE_FOR_USER_ROLE") == 0 )
	if( $user_role == 'guest' ){
		$factory_default_page = 'login';
	}else{
		$factory_default_page = 'profile';
	}

// get default page
$default_page = Core::getSetting( $user_role.'_default_page', 'core', $factory_default_page );
if( !in_array($default_page, $available_pages) )
	$default_page = $factory_default_page;

// parse arguments
$args = explode( '/', strtolower($_GET['arg']) );
$requested_page = $args[0];
$requested_action = (count($args) > 1 && $args[1]!=='') ? $args[1] : $_GET['action'];
$requested_action = ($requested_action !== '')? $requested_action : NULL;

// redirect to default page if the page is invalid
if( $requested_page == '' || !in_array($requested_page, $available_pages) ){
	// invalid page
	$redirect_page = $default_page;
	Core::redirectTo( $redirect_page );
}

Configuration::$PAGE = $requested_page;
Configuration::$ACTION = $requested_action;
Configuration::$ARG1 = (count($args) > 2 && $args[2] !== '') ? $args[2] : null;
Configuration::$ARG2 = (count($args) > 3 && $args[3] !== '') ? $args[3] : null;

URLrewrite::match();

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
	<link rel="icon" href="<?php echo Configuration::$BASE_URL ?>images/favicon.ico">

	<title><?php echo Configuration::$SHORT_SITE_NAME.' - '.Core::getPageDetails(Configuration::$PAGE, 'name') ?></title>

	<!-- Bootstrap v3.3.1 by getbootstrap.com -->
	<link href="<?php echo Configuration::$BASE_URL ?>css/bootstrap.min.css" rel="stylesheet" type="text/css" >
	<link href="<?php echo Configuration::$BASE_URL ?>css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" >
	<link href="<?php echo Configuration::$BASE_URL ?>css/bootstrap-theme.min.css" rel="stylesheet" type="text/css" >
	<link href="<?php echo Configuration::$BASE_URL ?>css/bootstrap-callout.css" rel="stylesheet" type="text/css" >

	<!-- FontAwesome v4.7 by fontawesome.io -->
	<link rel="stylesheet" href="<?php echo Configuration::$BASE_URL ?>css/font-awesome/css/font-awesome.min.css">

	<!-- videoJS v5.19 by videojs.com -->
	<link href="<?php echo Configuration::$BASE_URL ?>css/video-js.min.css" rel="stylesheet">

	<!-- Utility CSS -->
	<link href="<?php echo Configuration::$BASE_URL ?>css/sticky-footer-navbar.css" rel="stylesheet" media="all">

	<!-- HighlightJS Arduino-light CSS v9.12.0 -->
	<link href="<?php echo Configuration::$BASE_URL ?>css/highlight.js/arduino-light.css" rel="stylesheet" media="all">

	<!-- Custom CSS -->
	<link href="<?php echo Configuration::$BASE_URL ?>css/compose.css" rel="stylesheet" media="all">


	<!-- JQuery v1.11.1 by Google -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

	<!-- videoJS v5.19 by videojs.com -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/video.min.js"></script>

	<!-- HighlightJS v9.12.0 by highlightjs.org -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/highlight.min.js"></script>

	<!-- momentJS v2.18.1 by momentjs.com -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/moment.js"></script>

	<!-- ChartJS v2.7.0 by chartjs.org  -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/Chart.min.js"></script>

	<!-- Custom JS -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/compose.js"></script>

	<!-- Utility JS -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/md5.js"></script>
	<script src="<?php echo Configuration::$BASE_URL ?>js/hmac-sha256.js"></script>
	<script src="<?php echo Configuration::$BASE_URL ?>js/enc-base64-min.js"></script>
	<script src="<?php echo Configuration::$BASE_URL ?>js/string.format.js"></script>

	<!-- Google API Library -->
	<script src="https://apis.google.com/js/platform.js"></script>
	<meta name="google-signin-client_id" content="<?php echo Configuration::$GOOGLE_CLIENT_ID ?>">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body <?php echo ( (Configuration::$PAGE == 'error')? 'style="background-color:white"' : '' ) ?>>

	<!-- Fixed navbar -->
	<?php
	include( 'system/packages/core/modules/navbar.php' );
	?>

	<!-- Begin page content -->
	<div id="page_container" class="container" style="padding-bottom:15px; margin-top:42px">

		<?php include( 'system/packages/core/modules/alerts.php' ); ?>

		<br>

		<!-- Main Container -->
		<div id="page_canvas">
			<?php
			include( Core::getPageDetails(Configuration::$PAGE, 'path')."/index.php" );
			?>
		</div>
		<!-- Main Container End -->

		<br>

	</div>

	<?php
	include( 'system/packages/core/modules/modals/loading_modal.php' );
	include( 'system/packages/core/modules/modals/success_modal.php' );
	include( 'system/packages/core/modules/modals/yes_no_modal.php' );
	?>

	<!-- Fixed footer -->
	<?php
	include( 'system/packages/core/modules/footer.php' );
	?>


	<!-- Bootstrap core JavaScript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/bootstrap.min.js"></script>
	<script src="<?php echo Configuration::$BASE_URL ?>js/bootstrap-switch.min.js"></script>

	<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/ie10-viewport-bug-workaround.js"></script>

	<script type="text/javascript">
		$.fn.bootstrapSwitch.defaults.size = 'small';
		$.fn.bootstrapSwitch.defaults.offColor = 'warning';
		$("[class='switch']").bootstrapSwitch();

		// configure button groups
		$(".btn-group > .btn").click(function(){
			$(this).addClass("active").siblings().removeClass("active");
		});
	</script>

</body>
</html>

<?php
// IMPORTANT
Core::close();
?>
