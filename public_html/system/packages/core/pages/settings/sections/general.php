<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Saturday, January 13th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Saturday, January 13th 2018

?>


<h5 style="font-weight:bold">
    <?php
    //TODO: check all configuration files (maybe via API?)
    $writable = is_writable( __DIR__.'/../../config/configuration.json' );
    if( !$writable ){
        ?>
        <div class="alert alert-warning" role="alert">
            <span class="glyphicon glyphicon-file" aria-hidden="true" style="color:#ff9818"></span>
            <span style="color:#ff9818">WARNING!</span>&nbsp; The server does not have the rights to modify the configuration file. Any change will be lost.
        </div>
        <?php
    }
    ?>
</h5>


<form id="settings-form">

    <table style="width:100%; margin-top:20px">
        <tr>
            <td>
                <div style="width:700px; margin:auto">

                    <div style="margin-bottom:4px">
                        <label class="col-md-5 text-right">Maintenance mode</label>
                        <p class="col-md-6" style="margin-bottom:20px">
                            <input type="checkbox" class="switch" data-size="mini" name="maintenance_mode" id="maintenance-switch" <?php echo ( ( \system\classes\Configuration::$MAINTEINANCE_MODE )? 'checked' : '' ) ?>>
                        </p>
                    </div>

                    <div style="margin-bottom:4px">
                        <label class="col-md-5 text-right">HTML and App title</label>
                        <p class="col-md-6" style="margin-bottom:20px">
                            <input type="text" name="main_page_title" style="width:100%" placeholder="es. Welcome!" value="<?php echo \system\classes\Configuration::$MAIN_PAGE_TITLE ?>">
                        </p>
                    </div>

                    <div style="margin-bottom:4px">
                        <label class="col-md-5 text-right">Administrator e-mail address</label>
                        <p class="col-md-6" style="margin-bottom:20px">
                            <input type="text" name="admin_contact_mail_address" style="width:100%" placeholder="es. admin@example.com" value="<?php echo \system\classes\Configuration::$ADMIN_CONTACT_MAIL_ADDRESS ?>">
                        </p>
                    </div>

                    <div style="margin-bottom:4px">
                        <label class="col-md-5 text-right">Use cache</label>
                        <div class="col-md-7" style="margin-bottom:20px">
                            <table style="width:100%">
                                <tr>
                                    <td>
                                        <input type="checkbox" class="switch" data-size="mini" name="cache_enabled" id="cache-switch" <?php echo ( ( \system\classes\Configuration::$CACHE_ENABLED )? 'checked' : '' ) ?>>
                                    </td>
                                    <td>
                                        <?php
                                        if( \system\classes\Configuration::$CACHE_ENABLED ){
                                            $stats = \system\classes\enum\Statistics::cache_utilization();
                                            ?>
                                            | &nbsp;<strong>Cache usage:</strong> &nbsp;<?php echo round( floatval( ($stats['STATS_CACHED_SELECT_REQS'] / $stats['STATS_TOTAL_SELECT_REQS']) * 100 ), 2 ) ?>% &nbsp;<small style="font-family:monospace; font-size:7pt">(<?php echo $stats['STATS_CACHED_SELECT_REQS'].'/'.$stats['STATS_TOTAL_SELECT_REQS'] ?>)</small>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>

            </td>
        </tr>

        <tr>
            <td>
                <button type="button" class="btn btn-success" id="settings-save-button" style="float:right"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> &nbsp; Save and Apply</button>
            </td>
        </tr>
    </table>

</form>


<script type="text/javascript">

	$('#settings-save-button').on('click', function(){
		var qs = serializeForm( '#settings-form' );
		//
		var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/configuration/set/json?"+qs+"&token=<?php echo $_SESSION["TOKEN"] ?>";
		//
		callAPI( url, true, false );
	});

</script>
