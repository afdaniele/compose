<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use system\classes\Configuration;
use system\classes\Core;

function settings_pages_tab() {
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
            $pages = Core::getPagesList('by-package');
            
            $packages = array_keys($pages);
            sort($packages);
            
            $i = 1;
            foreach ($packages as $package) {
                foreach ($pages[$package] as $page) {
                    ?>
                    <tr>
                        <td class="col-md-1"><?php echo $i ?></td>
                        <td class="col-md-2"><?php echo $page['id'] ?></td>
                        <td class="col-md-3"><?php echo $page['name'] ?></td>
                        <td class="col-md-3"><?php echo $package ?></td>
                        <td class="col-md-1" style="padding: 0.7rem"><?php echo format($page['enabled'], 'boolean') ?></td>
                        <td class="col-md-2" style="padding: 0.3rem">
                            <?php
                            if ($package !== 'core') {
                                if ($page['enabled']) {
                                    ?>
                                    <button type="button"
                                            class="btn btn-sm btn-warning page-disable-button"
                                            data-package="<?php echo $package ?>"
                                            data-page="<?php echo $page['id'] ?>">
                                        <i class="bi bi-pause-fill"></i>&nbsp;Disable
                                    </button>
                                    <?php
                                } else {
                                    ?>
                                    <button type="button"
                                            class="btn btn-sm btn-success page-enable-button"
                                            data-package="<?php echo $package ?>"
                                            data-page="<?php echo $page['id'] ?>">
                                        <i class="bi bi-play-fill"></i>&nbsp;Enable
                                    </button>
                                    <?php
                                }
                            } else {
                                echo '<i class="bi bi-slash-circle" style="margin-top:2px; color:grey;"></i>';
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

        $('.page-disable-button').on('click', function () {
            let pkg_id = $(this).data('package');
            let page_id = $(this).data('page');
            //
            let url = "<?php echo Configuration::$BASE ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/page/disable/json?package=" + pkg_id + "&id=" + page_id + "&token=<?php echo $_SESSION["TOKEN"] ?>";
            //
            callAPI(url, true, true);
        });

        $('.page-enable-button').on('click', function () {
            let pkg_id = $(this).data('package');
            let page_id = $(this).data('page');
            //
            let url = "<?php echo Configuration::$BASE ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/page/enable/json?package=" + pkg_id + "&id=" + page_id + "&token=<?php echo $_SESSION["TOKEN"] ?>";
            //
            callAPI(url, true, true);
        });

    </script>
    
    <?php
}

?>
