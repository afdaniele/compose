<?php /** @noinspection PhpIncludeInspection */
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

// load the error handler module
require_once 'system/packages/core/modules/error_handler.php';

// load constants
require_once 'system/environment.php';
require_once 'system/exceptions.php';

// load core libraries
require_once 'system/utils/utils.php';
require_once 'system/classes/Core.php';
require_once 'system/classes/Configuration.php';

// simplify namespaces
use system\classes\Configuration;
use system\classes\Core;
use exceptions\BaseRuntimeException;
use exceptions\FileNotFoundException;
use system\classes\Utils;


try {
    // compute how far this page is from the root
    $__arg__ = strtolower($_GET['__arg__'] ?? "");
    $depth = substr_count($__arg__, '/');
    $to_root = implode('/', array_fill(0, $depth, '..'));
    $to_root .= strlen($to_root) ? '/' : '';
    
    // set the $BASE (Experimental)
    Configuration::$BASE = $to_root;
    
    // parse arguments
    $args = explode('/', $__arg__);
    $requested_page = $args[0];
    $requested_action = (count($args) > 1 && $args[1] !== '') ? $args[1] : ($_GET['action'] ?? "");
    $requested_action = ($requested_action !== '') ? $requested_action : null;
    
    // set configuration
    Configuration::$PAGE = $requested_page;
    Configuration::$ACTION = $requested_action;
    Configuration::$ARG1 = (count($args) > 2 && $args[2] !== '') ? $args[2] : null;
    Configuration::$ARG2 = (count($args) > 3 && $args[3] !== '') ? $args[3] : null;
    
    // create a Session
    Core::startSession();
    
    // init configuration
    try {
        Configuration::init();
    } catch (FileNotFoundException $e) {
        array_push($errors, $e);
    }
    
    // TODO: load core/settings database and get language out
    Configuration::$LANG = "en";
    ?>
    
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo Configuration::$LANG ?>">
    <head>
        <?php
        require_once 'system/templates/tableviewers/commons.php';
        
    //    require_once 'system/utils/URLrewrite.php';
    //    require_once 'system/classes/Database.php';
    //    require_once 'system/classes/BlockRenderer.php';
    //    require_once 'system/classes/MissionControl.php';
        // TODO: remove what is not needed
    //    require_once 'system/templates/sections/sections.php';
        
        // simplify namespaces
//        use system\classes\Utils;
    //    use system\utils\URLrewrite;
        
        // init Core
        $safe_mode = in_array($requested_page, ['error', 'maintenance']);
        Core::init($safe_mode);
        
        // get info about the current user
        $main_user_role = Core::getUserRole();
        $user_roles = Core::getUserRolesList();
        
        // nice to haves
        $is_admin = $main_user_role == 'administrator';
        $is_maintenance = Core::getSetting('maintenance_mode', 'core', false);
        
        // redirect user to the setup page (if necessary)
        $allow_before_setup = ['error', 'setup', 'maintenance'];
        if (!Core::isComposeConfigured() && !in_array($requested_page, $allow_before_setup)) {
            Core::redirectTo('setup');
        }
        
        // redirect user to maintenance mode (if necessary)
        $allow_during_maintenance = ['login', 'setup', 'error', 'maintenance'];
        if (!$is_admin && $is_maintenance && !in_array($requested_page, $allow_during_maintenance)) {
            Core::redirectTo('maintenance');
        }
        
        // get the list of pages the current user has access to
        $pages_list = Core::getFilteredPagesList('list', true, $user_roles);
        $available_pages = array_map(function ($p) {
            return $p['id'];
        }, $pages_list);
        
        // get factory default page
        $factory_default_page = Core::getFactoryDefaultPagePerRole($main_user_role);
        if (strcmp($factory_default_page, "NO_DEFAULT_PAGE") == 0) {
            if ($main_user_role == 'guest') {
                $factory_default_page = 'login';
            } else {
                $factory_default_page = 'profile';
            }
        }
        
        // get default page
        $default_page = Core::getDefaultPagePerRole($main_user_role);
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
    //    try {
    //        URLrewrite::match();
    //    } catch (URLRewriteException $e) {
    //        // collect error
    //        array_push($errors, $e);
    //    }
        
        // get theme
        $theme_id = Core::getSetting('theme');
        $theme_parts = explode(':', $theme_id);
        $theme_name = (count($theme_parts) == 1) ? $theme_parts[0] : $theme_parts[1];
        $theme_name = (strlen($theme_name) <= 0) ? 'default' : $theme_name;
        $theme_package = (count($theme_parts) == 1) ? 'core' : $theme_parts[0];
        $theme_file = Core::getThemeFile($theme_name, $theme_package);
        if (is_null($theme_file)) {
            $theme_file = Core::getThemeFile('default');
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
        
        <?php
        $site_name = Core::getAppName();
        $page_name = Core::getPageDetails(Configuration::$PAGE, 'name');
        $title = "{$site_name} - {$page_name}";
        ?>
        <title><?php echo $title ?></title>
    
        <script type="text/javascript">
            window.COMPOSE_BASE = "<?php echo Configuration::$BASE ?>";
            window.COMPOSE_TOKEN = "<?php echo Configuration::$TOKEN ?>";
        </script>
    
        <!-- Bootstrap v5.1.3 by getbootstrap.com -->
        <link href="<?php echo Configuration::$BASE ?>css/bootstrap.5.1.3.min.css" rel="stylesheet"
              type="text/css">
    
        <!-- OLD Bootstrap v3.3.1 by getbootstrap.com -->
<!--        <link href="--><?php //echo Configuration::$BASE ?><!--css/bootstrap-toggle.min.css" rel="stylesheet"-->
<!--              type="text/css">-->
<!--        <link href="--><?php //echo Configuration::$BASE ?><!--css/bootstrap-theme.min.css" rel="stylesheet"-->
<!--              type="text/css">-->
<!--        <link href="--><?php //echo Configuration::$BASE ?><!--css/bootstrap-callout.css" rel="stylesheet"-->
<!--              type="text/css">-->
    
        <!-- Bootstrap Icons v1.7.0 by getbootstrap.com -->
        <link rel="stylesheet"
              href="<?php echo Configuration::$BASE ?>css/bootstrap-icons.1.8.1.css">
    
        <!-- JQuery UI v1.13.0 by Google -->
        <link rel="stylesheet"
              href="<?php echo Configuration::$BASE ?>css/jquery-ui-1.13.0.min.css">
    
        <!-- JSONForm v2.2.5 by https://github.com/jsonform/jsonform -->
        <link rel="stylesheet"
              href="<?php echo Configuration::$BASE ?>css/jsonform.css">
    
        <!-- OLD Bootstrap Select v1.13.9 by developer.snapappointments.com/bootstrap-select/ -->
        <!--    <link rel="stylesheet" href="-->
        <?php //echo Configuration::$BASE ?><!--css/bootstrap-select.min.css">-->
    
        <!-- OLD Custom CSS -->
        <link href="<?php echo Configuration::$BASE ?>css/compose.css" rel="stylesheet" media="all">

        <?php
        if ($theme_package == "core") {
            ?>
            <!-- Theme CSS -->
            <link href="<?php echo Configuration::$BASE ?>css/themes/<?php echo $theme_name ?>.css" rel="stylesheet" media="all">
            <?php
        }
        ?>
    
        <!-- JQuery v3.6.0 by Google -->
        <script src="<?php echo Configuration::$BASE ?>js/jquery-3.6.0.min.js" type="application/javascript"></script>
    
        <!-- ChartJS v2.7.0 by chartjs.org  -->
        <script src="<?php echo Configuration::$BASE ?>js/Chart.min.js" type="application/javascript"></script>
        <script src="<?php echo Configuration::$BASE ?>js/Chart.plugins.js" type="application/javascript"></script>
    
        <!-- JSONForm v2.2.5 by https://github.com/jsonform/jsonform -->
        <script src="<?php echo Configuration::$BASE ?>js/jsonform/deps/underscore.js" type="application/javascript"></script>
        <script src="<?php echo Configuration::$BASE ?>js/jsonform/deps/opt/jsv.js" type="application/javascript"></script>
        <script src="<?php echo Configuration::$BASE ?>js/jsonform/jsonform.js" type="application/javascript"></script>
    
        <!-- Custom JS -->
        <script src="<?php echo Configuration::$BASE ?>js/compose.js" type="application/javascript"></script>
        <script src="<?php echo Configuration::$BASE ?>js/compose_colors.js" type="application/javascript"></script>
    
        <!-- Utility JS -->
        <script src="<?php echo Configuration::$BASE ?>js/md5.js" type="application/javascript"></script>
        <script src="<?php echo Configuration::$BASE ?>js/hmac-sha256.js" type="application/javascript"></script>
        <script src="<?php echo Configuration::$BASE ?>js/enc-base64-min.js" type="application/javascript"></script>
        <script src="<?php echo Configuration::$BASE ?>js/string.format.js" type="application/javascript"></script>
        <script src="<?php echo Configuration::$BASE ?>js/string.capitalize.js" type="application/javascript"></script>
        <script src="<?php echo Configuration::$BASE ?>js/string.strip.js" type="application/javascript"></script>
        <script src="<?php echo Configuration::$BASE ?>js/semaphore.js" type="application/javascript"></script>
    
        <!-- Google API Library -->
        <script src="https://apis.google.com/js/platform.js" async defer type="application/javascript"></script>
        <?php
        $login_enabled = Core::getSetting('login_enabled');
        $google_client_id = Core::getSetting('google_client_id');
        if ($login_enabled) {
            ?>
            <meta name="google-signin-client_id" content="<?php echo $google_client_id ?>">
            <?php
        }
        ?>
    
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js" type="application/javascript"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js" type="application/javascript"></script>
        <![endif]-->
    </head>
    
    <body <?php echo((Configuration::$PAGE == 'error') ? 'style="background-color:white"' : '') ?>>
    <?php
    
    // Load JS Configuration class
    include('js/compose-configuration.js.php');
    $CORE_PKG_DIR = $GLOBALS['__CORE__PACKAGE__DIR__'];
    
    // Load login system
    include(join_path($CORE_PKG_DIR, 'modules/login.php'));
    
    // Updates helper
    if (Core::getUserRole() == 'administrator' && Core::getSetting('check_updates')) {
        include(join_path($CORE_PKG_DIR, 'modules/updates_helper.php'));
    }
    
    // Load theme
    include($theme_file);
    
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
    <script src="<?php echo Configuration::$BASE ?>js/bootstrap.5.1.3.bundle.min.js" type="application/javascript"></script>
    
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo Configuration::$BASE ?>js/ie10-viewport-bug-workaround.js" type="application/javascript"></script>
    
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

} catch (Exception $e) {
    print_exception_plain($e);
}
?>
