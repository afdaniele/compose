<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

// simplify namespaces
use system\classes\Configuration;
use system\classes\Core;

include_once join_path(__DIR__, 'constants.php');

$CORE_PKG_DIR = $GLOBALS['__CORE__PACKAGE__DIR__'];
?>

<style>
    body {
        margin-bottom: 0;
        background-image: url("<?php echo Core::getImageURL('bground.jpg') ?>");
        background-repeat: repeat;
        background-size: 300px;
    }

    ._ctheme_body {
        height: 100vh;
        padding: <?php echo $THEME_DIM_PAGE_PADDING ?>px;
    }

    ._ctheme_page {
        height: 100%;
        border-radius: <?php echo $THEME_DIM_PAGE_RADIUS ?>px;
        border: 1px solid black;
        background-color: white;
        overflow: hidden;
    }

    ._ctheme_container {
        position: absolute;
        top: <?php echo $THEME_DIM_TOPBAR_HEIGHT ?>px;
        bottom: 0;
        left: <?php echo $THEME_DIM_SIDEBAR_FULL_WIDTH ?>px;
        right: 0;
        border-left: 1px solid lightgrey;
    }

    ._ctheme_content {
        position: absolute;
        overflow: auto;
        top: 10px;
        bottom: <?php echo max(10, $THEME_DIM_PAGE_RADIUS * 0.75) ?>px;
        left: 0;
        right: 6px;
        border-left: 1px solid lightgrey;
    }

    .page-title {
        display: none;
    }

    #page_container {
        margin-top: 0;
    }

    /* width */
    ::-webkit-scrollbar {
        width: 10px;
    }

    /* Track */
    ::-webkit-scrollbar-track {
        box-shadow: inset 0 0 5px grey;
        border-radius: 10px;
    }

    /* Handle */
    ::-webkit-scrollbar-thumb {
      background: <?php echo $THEME_COLOR_3->get_hex() ?>;
      border-radius: 10px;
    }

</style>


<div class="_ctheme_body col-md-12">
    <div class="_ctheme_page col-md-12">
        <div class="_ctheme_side_bar">
            <?php
            // load top bar
            include join_path(__DIR__, 'components/side_bar.php')
            ?>
        </div>

        <div class="_ctheme_top_bar">
            <?php
            // load top bar
            include join_path(__DIR__, 'components/top_bar.php')
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
                <div id="page_container" class="container">
                    <div id="page_container" class="container">
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
        </div>
    </div>
