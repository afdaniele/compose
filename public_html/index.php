<?php

require_once 'system/classes/Core.php';
require_once 'system/classes/Configuration.php';
require_once 'system/classes/enum/EmailTemplates.php';
require_once 'system/utils/utils.php';
require_once 'system/templates/forms/forms.php';
require_once 'system/templates/sections/sections.php';
require_once 'system/templates/paginators/paginators.php';

use system\classes\Core as Core;
use system\classes\Configuration as Configuration;



// Init Core
Core::initCore();

// Create a Session
Core::startSession();

$availablePages = array();
$availablePages['guest'] = array( 'login', 'error' );
$availablePages['logged'] = array( 'dashboard', 'duckiebots', 'live', 'surveillance', 'inbox', 'settings', 'profile', 'api' );

$args = explode( '/', strtolower($_GET['arg']) );


if( ( !Core::isUserLoggedIn() && !in_array($args[0], $availablePages['guest']) ) || ( Core::isUserLoggedIn() && !in_array($args[0], $availablePages['guest']) && !in_array($args[0], $availablePages['logged']) )  ){
	if( Core::isUserLoggedIn() ){
		Core::redirectTo( 'dashboard' );
	}else{
		Core::redirectTo( 'login' );
	}
}

Configuration::$PAGE = ($args[0] == '')? ( ( Core::isUserLoggedIn() )? ( isset($_SESSION['USER_LOGGED_IN_RECOVERY_MODE'])? 'profile' : 'dashboard' ) : 'login' ) : $args[0];
Configuration::$ACTION = (isset($args[1]) && $args[1]!=='') ? $args[1] : $_GET['action'];

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


	<title><?php echo Configuration::$SHORT_SITE_LINK.' - Dashboard' ?></title>

	<!-- Bootstrap v3.3.1 by getboostrap.com -->
	<link href="<?php echo Configuration::$BASE_URL ?>css/bootstrap.min.css" rel="stylesheet" type="text/css" >
	<link href="<?php echo Configuration::$BASE_URL ?>css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" >
	<link href="<?php echo Configuration::$BASE_URL ?>css/bootstrap-theme.min.css" rel="stylesheet" type="text/css" >

	<!-- FontAwesome v4.7 by fontawesome.io -->
	<link rel="stylesheet" href="<?php echo Configuration::$BASE_URL ?>css/font-awesome/css/font-awesome.min.css">

	<!-- videoJS v5.19 by videojs.com -->
	<link href="<?php echo Configuration::$BASE_URL ?>css/video-js.min.css" rel="stylesheet">

	<!-- Utility CSS -->
	<link href="<?php echo Configuration::$BASE_URL ?>css/sticky-footer-navbar.css" rel="stylesheet" media="all">

	<!-- Custom CSS -->
	<link href="<?php echo Configuration::$BASE_URL ?>css/style.css" rel="stylesheet" media="all">




	<!-- JQuery v1.11.1 by Google -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

	<!-- videoJS v5.19 by videojs.com -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/video.min.js"></script>

	<!-- momentJS v2.18.1 by momentjs.com -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/moment.js"></script>

	<!-- ChartJS v2.7.0 by chartjs.org  -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/Chart.min.js"></script>

	<!-- Custom JS -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/document.js"></script>

	<!-- Utility JS -->
	<script src="<?php echo Configuration::$BASE_URL ?>js/md5.js"></script>
	<script src="<?php echo Configuration::$BASE_URL ?>js/hmac-sha256.js"></script>
	<script src="<?php echo Configuration::$BASE_URL ?>js/enc-base64-min.js"></script>
	<script src="<?php echo Configuration::$BASE_URL ?>js/string.format.js"></script>
	<script src="<?php echo Configuration::$BASE_URL ?>js/bcrypt.min.js"></script>

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>


<body <?php echo ( (Configuration::$PAGE == 'error')? 'style="background-color:white"' : '' ) ?>>


<?php require_once __DIR__.'/js/charts.js.php'; ?>

<!-- Fixed navbar -->
<?php
include( 'system/modules/navbar.php' );
?>


<!-- Begin page content -->
<div class="container" style="padding-bottom:15px; margin-top:42px">

	<?php include(__DIR__."/system/modules/alerts.php") ?>

	<br>


	<!-- Main Container -->
	<div>
		<?php include(__DIR__."/system/pages/".Configuration::$PAGE."/index.php"); ?>
	</div>
	<!-- Main Container End -->

	<br>

</div>


<?php
include( 'system/modules/modals/loading_modal.php' );
include( 'system/modules/modals/success_modal.php' );
include( 'system/modules/modals/yes_no_modal.php' );
?>

<?php
include( 'system/modules/footer' . ( (Core::isUserLoggedIn())? '' : '_guest' ) . '.php' );
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
