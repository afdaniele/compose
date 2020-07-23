<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

// simplify namespaces
use system\classes\Core;
use system\classes\Configuration;

include_once join_path(__DIR__, 'constants.php');

$CORE_PKG_DIR = $GLOBALS['__CORE__PACKAGE__DIR__'];
?>


<style type="text/css">
    body {
        margin-bottom: 0;
        background-image: url("<?php echo Core::getImageURL('bground.jpg') ?>");
        background-repeat: repeat;
        background-size: 300px;
    }
    
    ._ctheme_body {
        height: 100vh;
        padding: <?php echo Configuration::$THEME_CONFIG['dimensions']['page_padding'] ?>px;
    }
    
    ._ctheme_page {
        height: 100%;
        border-radius: <?php echo Configuration::$THEME_CONFIG['dimensions']['page_radius'] ?>px;
        border: 1px solid black;
        background-color: white;
        overflow: hidden;
    }
    
    ._ctheme_container {
        position: absolute;
        top: <?php echo Configuration::$THEME_CONFIG['dimensions']['topbar_height'] ?>px;
        bottom: 0;
        left: <?php echo Configuration::$THEME_CONFIG['dimensions']['sidebar_full_width'] ?>px;
        right: 0;
        border-left: 1px solid lightgrey;
    }
    
    ._ctheme_content {
        position: absolute;
        overflow: auto;
        top: 10px;
        bottom: <?php echo max(10, Configuration::$THEME_CONFIG['dimensions']['page_radius'] * 0.75) ?>px;
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
      background: <?php echo $_THEME_COLOR_3->get_hex() ?>;
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
            
            <div class="_ctheme_content">

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
    
                </div>
                
            </div>


        </div>
        
    </div>
</div>


