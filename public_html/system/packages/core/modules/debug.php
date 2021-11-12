<?php
use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Formatter;

$debugger_data = Core::getDebugInfo();
$something_to_show = count($debugger_data) > 0;
$is_admin = boolval(Core::getUserRole() == 'administrator');

if( Configuration::$DEBUG && $is_admin && $something_to_show ){
    ?>
    <div class="panel-group" role="tablist" aria-multiselectable="true">
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="debug_header">
                <a id="collapse_a_debug" class="collapsed collapse_a" role="button" data-toggle="collapse" data-parent="#accordion" href="#debug" aria-expanded="true" aria-controls="debug">
                    <h4 class="panel-title" style="color:orangered">
                        <span class="bi bi-bug" aria-hidden="true"></span>&nbsp;Debug
                    </h4>
                </a>
            </div>
            <div id="debug" class="panel-collapse collapse" role="tabpanel" aria-labelledby="debug_header">
                <div class="panel-body">
                    	<p>
                    	    This table shows debug info collected by \compose\.
                    	</p>
                    	<div class="text-center" style="padding:10px 0">
                    	    <table class="table table-bordered table-striped" style="margin:auto">
                    	        <tr style="font-weight:bold">
                    	            <td class="col-md-2">Package</td>
                    	            <td class="col-md-3">Test</td>
                    	            <td class="col-md-7">Result</td>
                    	        </tr>
                    	        <?php
                    	        foreach($debugger_data as $pkg_id => $pkg_debug) {
                    	            foreach($pkg_debug as $debug_test_id => $debug_outcome) {
                    	                ?>
                    	                <tr>
                    	                    <td><?php echo $pkg_id ?></td>
                    	                    <td><?php echo $debug_test_id ?></td>
                    	                    <td><?php echo Formatter::format( $debug_outcome[0], $debug_outcome[1] ) ?></td>
                    	                </tr>
                    	                <?php
                    	            }
                    	        }
                    	        ?>
                    	    </table>
                    	</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>
