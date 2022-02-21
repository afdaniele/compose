<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

// simplify namespaces
use system\classes\Core;
use system\classes\Configuration;

include_once join_path(__DIR__, 'constants.php');

$CORE_PKG_DIR = $GLOBALS['__CORE__PACKAGE__DIR__'];
?>


<style>
    body {
        margin-bottom: 0;
    }

    ._ctheme_body {
        height: 100vh;
        padding: 0;
    }

    ._ctheme_page {
        height: 100%;
        overflow: hidden;
    }

    ._ctheme_container {
        position: absolute;
        top: <?php echo $TOPBAR_HEIGHT ?? 70 ?>px;
        bottom: <?php echo $BOTTOMBAR_HEIGHT ?? 50 ?>px;
        left: 0;
        right: 0;
    }

    ._ctheme_content {
        position: absolute;
        overflow: auto;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 40px 0;
    }

    ._ctheme_progress_bar {
        position: absolute;
        top: <?php echo $TOPBAR_HEIGHT ?? 70 ?>px;
        bottom: 0;
        left: 0;
        right: 0;
    }
</style>


<div class="_ctheme_body col-md-12">
    <div class="_ctheme_page col-md-12">

        <div class="_ctheme_top_bar">
            <?php
            // load top bar
            include join_path(__DIR__, 'components/top_bar.php')
            ?>
        </div>

        <div class="_ctheme_progress_bar">
            <?php
            // load progress bar
            include(join_path($CORE_PKG_DIR, 'modules/progress_bar.php'));
            ?>
        </div>

        <div class="_ctheme_container">
            <?php
            // Developer mode watermark
            if (Core::getSetting('developer_mode')) {
                include(join_path($CORE_PKG_DIR, 'modules/devel_watermark.php'));
            }
            ?>

            <div class="_ctheme_content">
                <!-- Begin page content -->
                <div id="page_container" class="page-container">
                    <?php include(join_path($CORE_PKG_DIR, 'modules/alerts.php')); ?>

                    <!-- Main Container -->
                    <div id="page_canvas">
                        <?php
                        $page_dir = Core::getPageDetails(Configuration::$PAGE, 'path');
                        $php_index = join_path($page_dir, "index.php");
                        $css_index = join_path($page_dir, "index.css");
                        // page CSS
                        if (file_exists($css_index)) {
                            ?>
                            <style>
                                <?php include_once($css_index) ?>
                            </style>
                            <?php
                        }
                        // page source
                        include($php_index);
                        ?>
                    </div>
                    <!-- Main Container End -->
                </div>
            </div>
        </div>

        <div class="_ctheme_bottom_bar">
            <?php
            // load top bar
            include join_path(__DIR__, 'components/bottom_bar.php')
            ?>
        </div>

    </div>
</div>
