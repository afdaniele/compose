<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:30%">
				<h2>Settings</h2>
			</td>
			<td style="width:70%; text-align:right">
				<h5 style="margin-top:38px; margin-bottom:0; font-weight:bold">
					<?php
					$writable = is_writable( __DIR__.'/../../config/configuration.json' );
					if( !$writable ){
						?>
						<span class="glyphicon glyphicon-file" aria-hidden="true" style="color:#ff9818"></span>
						<span style="color:#ff9818">WARNING!</span>&nbsp; The server does not have the rights to modify the configuration file.
						<?php
					}
					?>
				</h5>
			</td>
		</tr>

	</table>


	<form id="settings-form">

		<div style="width:100%; margin:auto; display:table; clear:both; padding-left:45px">

			<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">
				<tr>
					<td style="width:100%">
						<h3>General</h3>
					</td>
				</tr>
			</table>


			<table style="width:100%; margin-top:10px; margin-bottom:20px">
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
			</table>

		</div>





		<?php
		foreach (\system\classes\Configuration::$SURVEILLANCE as $cam_num => $cam) {
			?>
			<div style="width:100%; margin:auto; display:table; clear:both; padding-left:45px">

				<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">
					<tr>
						<td style="width:100%">
							<h3>Surveillance system #<?php echo $cam_num ?></h3>
						</td>
					</tr>
				</table>

				<table style="width:100%; margin-top:10px; margin-bottom:20px">
					<tr>
						<td>
							<div style="width:700px; margin:auto">

								<div style="margin-bottom:4px">
									<label class="col-md-5 text-right">Enabled</label>
									<p class="col-md-6" style="margin-bottom:20px">
										<input type="checkbox" class="switch" data-size="mini" name="surveillance_<?php echo $cam_num ?>_enabled" id="maintenance-switch" <?php echo ( ( $cam['enabled'] )? 'checked' : '' ) ?>>
									</p>
								</div>

								<div style="margin-bottom:4px">
									<label class="col-md-5 text-right">Storage disk device</label>
									<p class="col-md-6" style="margin-bottom:20px">
										<input type="text" name="surveillance_<?php echo $cam_num ?>_disk_dev" style="width:100%" placeholder="es. /dev/sdb1" value="<?php echo $cam['disk_dev'] ?>">
									</p>
								</div>

								<div style="margin-bottom:4px">
									<label class="col-md-5 text-right">Raw videos path</label>
									<p class="col-md-6" style="margin-bottom:20px">
										<input type="text" name="surveillance_<?php echo $cam_num ?>_raw_data_path" style="width:100%" placeholder="es. /mnt/data/videos/" value="<?php echo $cam['raw_data_path'] ?>">
									</p>
								</div>

								<div style="margin-bottom:4px">
									<label class="col-md-5 text-right">Video logs path</label>
									<p class="col-md-6" style="margin-bottom:20px">
										<input type="text" name="surveillance_<?php echo $cam_num ?>_log_data_path" style="width:100%" placeholder="es. /mnt/data/logs/" value="<?php echo $cam['log_data_path'] ?>">
									</p>
								</div>

								<div style="margin-bottom:4px">
									<label class="col-md-5 text-right">Motion detection path</label>
									<p class="col-md-6" style="margin-bottom:20px">
										<input type="text" name="surveillance_<?php echo $cam_num ?>_activity_data_path" style="width:100%" placeholder="es. /mnt/data/activity/" value="<?php echo $cam['activity_data_path'] ?>">
									</p>
								</div>

							</div>

						</td>
					</tr>
				</table>

			</div>
		<?php
		}
		?>


		<div style="width:700px; margin:auto">
			<button type="button" class="btn btn-success" id="settings-save-button" style="float:right; margin-right:70px">Save and Apply</button>
		</div>

	</form>

</div>


<script type="text/javascript">
	$('#settings-save-button').on('click', function(){
		var qs = serializeForm( '#settings-form' );
		//
		var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/configuration/set/json?"+qs+"&token=<?php echo $_SESSION["TOKEN"] ?>";
		//
		callAPI( url, true, false );
	});
</script>
