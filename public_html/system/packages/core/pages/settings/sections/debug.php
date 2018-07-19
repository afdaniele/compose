<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Saturday, January 13th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

use \system\classes\Core as Core;
use \system\classes\Formatter as Formatter;

function settings_debug_tab(){
    $debugger_data = Core::getDebugInfo();
    ?>

    <p>
        This tab shows results of the debug tests.
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

<?php
}
?>
