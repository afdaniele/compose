<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

// simplify namespaces
use system\classes\Core;
use system\classes\Configuration;

$CORE_PKG_DIR = $GLOBALS['__CORE__PACKAGE__DIR__'];

// Fixed navbar
include(join_path(__DIR__, 'components/navbar.php'));
?>

    <!-- Begin page content -->
    <div id="page_container" class="container">
        
        <?php include(join_path($CORE_PKG_DIR, 'modules/alerts.php')); ?>

        <br>

        <!-- Main Container -->
        <div id="page_canvas">
            <?php
            include(join_path(Core::getPageDetails(Configuration::$PAGE, 'path'), "index.php"));
            ?>
        </div>
        <!-- Main Container End -->

        <br>

    </div>

<?php
// Fixed footer
include(join_path(__DIR__, 'components/footer.php'));
?>