<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele



function settings_packages_tab(){
?>

    <p>
        The following table shows all the packages installed on the platform.
    </p>
    <div class="text-center" style="padding:10px 0">
        <table class="table table-bordered table-striped" style="margin:auto">
            <tr style="font-weight:bold">
                <td class="col-md-1">#</td>
                <td class="col-md-2">ID</td>
                <td class="col-md-2">Name</td>
                <td class="col-md-1">Version</td>
                <td class="col-md-1">Enabled</td>
                <td class="col-md-2">Actions</td>
            </tr>
            <?php
            $packages = \system\classes\Core::getPackagesList();
            $packages_ids = array_keys( $packages );

            sort($packages_ids);

            $i = 1;
            foreach($packages_ids as $pkg_id) {
                $pkg = $packages[$pkg_id];
                ?>
                <tr>
                    <td><?php echo $i ?></td>
                    <td><?php echo $pkg_id ?></td>
                    <td><?php echo $pkg['name'] ?></td>
                    <td><?php echo ($pkg['codebase']['head_tag'] == 'ND')? 'devel' : $pkg['codebase']['head_tag'] ?></td>
                    <td><?php echo format($pkg['enabled'], 'boolean') ?></td>
                    <td>
                        <?php
                        if( $pkg_id !== 'core' ){
                            if( $pkg['enabled'] ){
                                ?>
                                <button type="button" class="btn btn-sm btn-warning package-disable-button" data-package="<?php echo $pkg_id ?>">
                                    <span class="glyphicon glyphicon-pause" aria-hidden="true"></span>&nbsp;Disable
                                </button>
                                <?php
                            }else{
                                ?>
                                <button type="button" class="btn btn-sm btn-success package-enable-button" data-package="<?php echo $pkg_id ?>">
                                    <span class="glyphicon glyphicon-play" aria-hidden="true"></span>&nbsp;Enable
                                </button>
                                <?php
                            }
                        }else{
                            echo '<span class="glyphicon glyphicon-ban-circle" aria-hidden="true" style="margin-top:2px; color:grey;"></span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php
                $i += 1;
            }
            ?>
        </table>
    </div>


    <script type="text/javascript">

    	$('.package-disable-button').on('click', function(){
    		var pkg_id = $(this).data('package');
    		//
    		var url = "<?php echo \system\classes\Configuration::$BASE ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/package/disable/json?id="+pkg_id+"&token=<?php echo $_SESSION["TOKEN"] ?>";
    		//
    		callAPI( url, true, true );
    	});

    	$('.package-enable-button').on('click', function(){
    		var pkg_id = $(this).data('package');
    		//
    		var url = "<?php echo \system\classes\Configuration::$BASE ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/package/enable/json?id="+pkg_id+"&token=<?php echo $_SESSION["TOKEN"] ?>";
    		//
    		callAPI( url, true, true );
    	});

    </script>

<?php
}
?>
