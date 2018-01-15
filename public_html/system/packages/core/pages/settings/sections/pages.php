<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Saturday, January 13th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, January 14th 2018


function settings_pages_tab(){
?>

    <p>
        The following table reports all the pages available on the platform.
    </p>
    <div class="text-center" style="padding:10px 0">
        <table class="table table-bordered table-striped" style="margin:auto">
            <tr style="font-weight:bold">
                <td class="col-md-1">#</td>
                <td class="col-md-2">ID</td>
                <td class="col-md-3">Title</td>
                <td class="col-md-3">Package</td>
                <td class="col-md-1">Enabled</td>
                <td class="col-md-2">Actions</td>
            </tr>
            <?php
            $pages = \system\classes\Core::getPagesList('by-package');

            $packages = array_keys( $pages );
            sort($packages);

            $i = 1;
            foreach($packages as $package) {
                foreach($pages[$package] as $page) {
                    ?>
                    <tr>
                        <td class="col-md-1"><?php echo $i ?></td>
                        <td class="col-md-2"><?php echo $page['id'] ?></td>
                        <td class="col-md-3"><?php echo $page['name'] ?></td>
                        <td class="col-md-3"><?php echo $package ?></td>
                        <td class="col-md-1"><?php echo format($page['enabled'], 'boolean') ?></td>
                        <td class="col-md-2">
                            <?php
                            if( $package !== 'core' ){
                                if( $page['enabled'] ){
                                    ?>
                                    <button type="button" class="btn btn-xs btn-warning page-disable-button" data-package="<?php echo $package ?>" data-page="<?php echo $page['id'] ?>">
                                        <span class="glyphicon glyphicon-pause" aria-hidden="true"></span>&nbsp;Disable
                                    </button>
                                    <?php
                                }else{
                                    ?>
                                    <button type="button" class="btn btn-xs btn-success page-enable-button" data-package="<?php echo $package ?>" data-page="<?php echo $page['id'] ?>">
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
            }
            ?>
        </table>
    </div>


    <script type="text/javascript">

    	$('.page-disable-button').on('click', function(){
    		var pkg_id = $(this).data('package');
    		var page_id = $(this).data('page');
    		//
    		var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/page/disable/json?package="+pkg_id+"&id="+page_id+"&token=<?php echo $_SESSION["TOKEN"] ?>";
    		//
    		callAPI( url, true, true );
    	});

    	$('.page-enable-button').on('click', function(){
    		var pkg_id = $(this).data('package');
    		var page_id = $(this).data('page');
    		//
    		var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/page/enable/json?package="+pkg_id+"&id="+page_id+"&token=<?php echo $_SESSION["TOKEN"] ?>";
    		//
    		callAPI( url, true, true );
    	});

    </script>

<?php
}
?>
