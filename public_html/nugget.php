<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu


// load constants
require_once 'system/environment.php';

// load core libraries
require_once 'system/classes/Core.php';
require_once 'system/classes/Configuration.php';

// load the error handler module
require_once 'system/packages/core/modules/error_handler.php';

// simplify namespaces
use exceptions\BaseException;
use exceptions\PackageNotFoundException;
use exceptions\ThemeNotFoundException;
use system\classes\Core;
use system\classes\Configuration;

// create a Session
Core::startSession();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?php
    // load core libraries
    require_once 'system/classes/Database.php';
    require_once 'system/classes/BlockRenderer.php';
    require_once 'system/classes/MissionControl.php';
    require_once 'system/utils/utils.php';
    require_once 'system/utils/URLrewrite.php';
    // TODO: remove what is not needed
//    require_once 'system/templates/sections/sections.php';
//    require_once 'system/templates/paginators/paginators.php';
    
    // simplify namespaces
    use system\utils\URLrewrite;
    
    // compute how far this page is from the root
    $__arg__ = strtolower($_GET['__arg__']);
    $depth = substr_count($__arg__, '/');
    $to_root = implode('/', array_fill(0, $depth, '..'));
    $to_root .= strlen($to_root) ? '/' : '';
    
    // set the $BASE (Experimental)
    Configuration::$BASE = $to_root;
    
    // parse arguments
    $args = explode('/', $__arg__);
    $nugget_package = (count($args) > 0 && $args[0] !== '') ? $args[0] : null;
    $nugget_name = (count($args) > 1 && $args[1] !== '') ? $args[1] : null;
    
    // init Core
    try {
        Core::init();
    } catch (BaseException $e) {
        $msg = $e->getMessage();
        echoArray("Error: '$msg'.");
        return;
    }
    
    // get info about the current user
    $user_role = Core::getUserRole();
    $user_roles = Core::getUserRolesList();
    
    // redirect user to maintenance mode (if necessary)
    if ($user_role != 'administrator' && Core::getSetting('maintenance_mode') && !in_array($requested_page, ['login', 'setup', 'error', 'maintenance'])) {
        Core::redirectTo('maintenance');
    }
    
    // get the list of pages the current user has access to
    $pages_list = Core::getFilteredPagesList('list', TRUE, $user_roles);
    $available_pages = array_map(function ($p) {return $p['id'];}, $pages_list);
    
    // get factory default page
    $factory_default_page = Core::getFactoryDefaultPagePerRole($user_role);
    if (strcmp($factory_default_page, "NO_DEFAULT_PAGE") == 0) {
        if ($user_role == 'guest') {
            $factory_default_page = 'login';
        }
        else {
            $factory_default_page = 'profile';
        }
    }
    
    // get default page
    $default_page = Core::getDefaultPagePerRole($user_role);
    foreach (array_keys(Core::getPackagesList()) as $pkg_id) {
        if ($pkg_id == 'core') {
            continue;
        }
        $pkg_user_role = Core::getUserRole($pkg_id);
        if (!is_null($pkg_user_role)) {
            $default_page_per_pkg = Core::getDefaultPagePerRole($pkg_user_role, $pkg_id);
            if ($default_page_per_pkg != 'NO_DEFAULT_PAGE') {
                $default_page = $default_page_per_pkg;
                break;
            }
        }
    }
    if (!in_array($default_page, $available_pages)) {
        $default_page = $factory_default_page;
    }
    
    // redirect to default page if the page is invalid
    if ($requested_page == '' || !in_array($requested_page, $available_pages)) {
        // invalid page
        $redirect_page = $default_page;
        Core::redirectTo($redirect_page, $redirect_page == 'login');
    }
    
    // execute URL rewrite
    URLrewrite::match();
    
    // get theme
    $theme_id = Core::getSetting('theme');
    $theme_parts = explode(':', $theme_id);
    $theme_name = (count($theme_parts) == 1)? $theme_parts[0] : $theme_parts[1];
    $theme_name = (strlen($theme_name) <= 0)? 'default' : $theme_name;
    $theme_package = (count($theme_parts) == 1)? 'core' : $theme_parts[0];
    $theme_file = null;
    try {
        $theme_file = Core::getThemeFile($theme_name, $theme_package);
    } catch (BaseException $e) {
        $msg = $e->getMessage();
        Core::requestAlert("WARNING", "Error: $msg. Falling back to 'default' theme.");
    }
    if (is_null($theme_file)) {
        try {
            $theme_file = Core::getThemeFile('default');
        } catch (BaseException $e) {
            echo "ERROR: Failed to load default theme.";
            return;
        }
    }
    
    // get favicon
    $favicon = join_path(Configuration::$BASE, "images", "favicon.ico");
    $favicon_pkg_id = Core::getSetting('favicon');
    if ($favicon_pkg_id != 'core' && Core::packageExists($favicon_pkg_id)) {
        $pkg_root = Core::getPackageRootDir($favicon_pkg_id);
        $icon_path = join_path($pkg_root, 'images', 'favicon.ico');
        if (file_exists($icon_path)) {
            $favicon = Core::getImageURL('favicon.ico', $favicon_pkg_id);
        }
    }
    ?>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1000">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="icon" href="<?php echo $favicon ?>">

    <title><?php echo Core::getAppName() . ' - ' . Core::getPageDetails(Configuration::$PAGE, 'name') ?></title>

    <script type="text/javascript">
        window.COMPOSE_BASE = "<?php echo Configuration::$BASE ?>";
        window.COMPOSE_TOKEN = "<?php echo Configuration::$TOKEN ?>";
    </script>

    <!-- Bootstrap v5.1.3 by getbootstrap.com -->
    <link href="<?php echo Configuration::$BASE ?>css/bootstrap.5.1.3.min.css" rel="stylesheet"
          type="text/css">

    <!-- OLD Bootstrap v3.3.1 by getbootstrap.com -->
    <link href="<?php echo Configuration::$BASE ?>css/bootstrap-toggle.min.css" rel="stylesheet"
          type="text/css">
    <link href="<?php echo Configuration::$BASE ?>css/bootstrap-theme.min.css" rel="stylesheet"
          type="text/css">
    <link href="<?php echo Configuration::$BASE ?>css/bootstrap-callout.css" rel="stylesheet"
          type="text/css">

    <!-- Bootstrap Icons v1.7.0 by getbootstrap.com -->
    <link rel="stylesheet"
          href="<?php echo Configuration::$BASE ?>css/bootstrap-icons.1.7.0.css">

    <!-- JQuery UI v1.13.0 by Google -->
    <link rel="stylesheet"
          href="<?php echo Configuration::$BASE ?>css/jquery-ui-1.13.0.min.css">

    <!-- JSONForm v2.2.5 by https://github.com/jsonform/jsonform -->
    <link rel="stylesheet"
          href="<?php echo Configuration::$BASE ?>css/jsonform.css">
    
    <!-- OLD Bootstrap Select v1.13.9 by developer.snapappointments.com/bootstrap-select/ -->
<!--    <link rel="stylesheet" href="--><?php //echo Configuration::$BASE ?><!--css/bootstrap-select.min.css">-->

    <!-- OLD Custom CSS -->
    <link href="<?php echo Configuration::$BASE ?>css/compose.css" rel="stylesheet" media="all">


    <!-- JQuery v3.6.0 by Google -->
    <script src="<?php echo Configuration::$BASE ?>js/jquery-3.6.0.min.js"></script>

    <!-- ChartJS v2.7.0 by chartjs.org  -->
    <script src="<?php echo Configuration::$BASE ?>js/Chart.min.js"></script>
    <script src="<?php echo Configuration::$BASE ?>js/Chart.plugins.js"></script>

    <!-- Bootstrap Select v1.13.9 by developer.snapappointments.com/bootstrap-select/ -->
    <script src="<?php echo Configuration::$BASE ?>js/bootstrap-select.min.js"></script>

    <!-- JSONForm v2.2.5 by https://github.com/jsonform/jsonform -->
    <script src="<?php echo Configuration::$BASE ?>js/jsonform/deps/underscore.js"></script>
    <script src="<?php echo Configuration::$BASE ?>js/jsonform/deps/opt/jsv.js"></script>
    <script src="<?php echo Configuration::$BASE ?>js/jsonform/jsonform.js"></script>
    
<!--    <script type="text/javascript">-->
<!--        $.fn.selectpicker.Constructor.BootstrapVersion = '3';-->
<!--    </script>-->

    <!-- Custom JS -->
    <script src="<?php echo Configuration::$BASE ?>js/compose.js"></script>
    <script src="<?php echo Configuration::$BASE ?>js/compose_colors.js"></script>

    <!-- Utility JS -->
    <script src="<?php echo Configuration::$BASE ?>js/md5.js"></script>
    <script src="<?php echo Configuration::$BASE ?>js/hmac-sha256.js"></script>
    <script src="<?php echo Configuration::$BASE ?>js/enc-base64-min.js"></script>
    <script src="<?php echo Configuration::$BASE ?>js/string.format.js"></script>
    <script src="<?php echo Configuration::$BASE ?>js/string.capitalize.js"></script>
    <script src="<?php echo Configuration::$BASE ?>js/string.strip.js"></script>

    <!-- Google API Library -->
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <?php
    if (Core::getSetting('login_enabled', 'core')) {
        ?>
        <meta name="google-signin-client_id"
              content="<?php echo Core::getSetting('google_client_id') ?>">
        <?php
    }
    ?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body <?php echo((Configuration::$PAGE == 'error') ? 'style="background-color:white"' : '') ?>>

    <!-- Load JS Configuration class -->
    <?php
    include('js/compose-configuration.js.php');
    $CORE_PKG_DIR = $GLOBALS['__CORE__PACKAGE__DIR__'];
    
    // Load login system
    include(join_path($CORE_PKG_DIR, 'modules/login.php'));
    
    // Updates helper
    if (Core::getUserRole() == 'administrator' && Core::getSetting('check_updates')) {
        include(join_path($CORE_PKG_DIR, 'modules/updates_helper.php'));
    }
    ?>
    
    
    <!-- Load theme -->
    <?php
    include($theme_file);
    ?>
    
    
    <?php
    // Load modals
    include(join_path($CORE_PKG_DIR, 'modules/modals/loading_modal.php'));
    include(join_path($CORE_PKG_DIR, 'modules/modals/success_modal.php'));
    include(join_path($CORE_PKG_DIR, 'modules/modals/yes_no_modal.php'));
    
    // Debug section (Admin only)
    include(join_path($CORE_PKG_DIR, 'modules/debug.php'));

    // Global Background modules: get list of background/global module files
    $global_background_scripts_per_pkg = Core::getPackagesModules('background/global');
    foreach ($global_background_scripts_per_pkg as $pkg_id => $global_background_scripts) {
        foreach ($global_background_scripts as $global_background_script) {
            include($global_background_script);
        }
    }
    
    // Local Background modules: get list of background/local module files
    $page_package = Core::getPageDetails(Configuration::$PAGE, 'package');
    $local_background_scripts = Core::getPackagesModules('background/local', $page_package);
    foreach ($local_background_scripts as $local_background_script) {
        include($local_background_script);
    }
    
    // Package-specific CSS stylesheets
    foreach (Core::getRegisteredCSSstylesheets() as $css_file) {
        echo sprintf('<style type="text/css">%s</style>', file_get_contents($css_file));
    }
    ?>
    
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->

    <!-- Bootstrap v5.1.3 by getbootstrap.com -->
    <script type="text/javascript"
            src="<?php echo Configuration::$BASE ?>js/bootstrap.5.1.3.bundle.min.js"></script>
    
    <!-- OLD Bootstrap v3.3.1 by getbootstrap.com -->
    <script type="text/javascript"
            src="<?php echo Configuration::$BASE ?>js/bootstrap-toggle.min.js"></script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script type="text/javascript"
            src="<?php echo Configuration::$BASE ?>js/ie10-viewport-bug-workaround.js"
    ></script>

    <script type="text/javascript">
        // configure button groups
        $(".btn-group > .btn").click(function () {
            $(this).addClass("active").siblings().removeClass("active");
        });
        
        $(document).ready(function () {
            // set page title
            $('.page-title').html("<?php echo Core::getPageDetails(Configuration::$PAGE, 'name') ?>");
        });
    </script>

</body>
</html>

<?php
// IMPORTANT
Core::close();
?>