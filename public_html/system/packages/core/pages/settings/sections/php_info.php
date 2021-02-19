<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use system\classes\Core;


function settings_phpinfo_tab(){
    $php_info = Core::getURL('script.php', null, null, null, ['script' => 'php_info']);
    ?>
    <style type="text/css">
        #_compose_settings_phpinfo_tab_iframe{
            min-height: 200px;
            width: 100%;
            border: none;
            overflow: hidden;
        }
    </style>
    
    <iframe src="<?php echo $php_info ?>" id="_compose_settings_phpinfo_tab_iframe"></iframe>
    
    <script type="application/javascript">
        let iframe = $("#_compose_settings_phpinfo_tab_iframe");
        iframe.closest(".panel-body").css("padding", "0");
        
        function _on_phpinfo_iframe_ready() {
            let dom = iframe.get()[0];
            let content_h = dom.contentWindow.document.body.scrollHeight;
            content_h += 40;
            dom.style.height = "{0}px".format(content_h);
        }
        
        iframe.load(_on_phpinfo_iframe_ready);
        $("#php_collapse").on('shown.bs.collapse', _on_phpinfo_iframe_ready);
    </script>
    <?php
}
?>
