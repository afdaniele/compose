<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018

?>
<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:30%">
				<h2>Settings</h2>
			</td>
			<td style="width:70%; text-align:right">
				<h5 style="margin-top:38px; margin-bottom:0; font-weight:bold">
					<?php
					//TODO: check all configuration files (maybe via API?)
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

		<?php
		Section::begin('General Settings');
		?>
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
		</table>
		<?php
		Section::end();
		?>

		<br/><br/>

		<?php
		Section::begin('Packages');
		?>
		<p>
			The following table shows all the packages installed on the platform.
		</p>
		<div class="text-center" style="padding:10px 0">
			<table class="table table-bordered table-striped" style="margin:auto">
				<tr style="font-weight:bold">
					<td class="col-md-1">#</td>
					<td class="col-md-2">ID</td>
					<td class="col-md-3">Name</td>
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
						<td class="col-md-1"><?php echo $i ?></td>
						<td class="col-md-2"><?php echo $pkg_id ?></td>
						<td class="col-md-3"><?php echo $pkg['name'] ?></td>
						<td class="col-md-1"><?php echo format($pkg['enabled'], 'boolean') ?></td>
						<td class="col-md-2">
							<?php
							if( $pkg_id !== 'core' ){
								if( $pkg['enabled'] ){
									?>
									<button type="button" class="btn btn-xs btn-warning page-disable-button" data-page="<?php echo $pkg_id ?>">
										<span class="glyphicon glyphicon-pause" aria-hidden="true"></span>&nbsp;Disable
									</button>
									<?php
								}else{
									?>
									<button type="button" class="btn btn-xs btn-success page-enable-button" data-page="<?php echo $pkg_id ?>">
										<span class="glyphicon glyphicon-play" aria-hidden="true"></span>&nbsp;Enable
									</button>
									<?php
								}
								//TODO: complete this
								echo "//TODO";
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
		<?php
		Section::end();
		?>





		<br/><br/>

		<?php
		Section::begin('Pages');
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
										<button type="button" class="btn btn-xs btn-warning page-disable-button" data-page="<?php echo $page['id'] ?>">
											<span class="glyphicon glyphicon-pause" aria-hidden="true"></span>&nbsp;Disable
										</button>
										<?php
									}else{
										?>
										<button type="button" class="btn btn-xs btn-success page-enable-button" data-page="<?php echo $page['id'] ?>">
											<span class="glyphicon glyphicon-play" aria-hidden="true"></span>&nbsp;Enable
										</button>
										<?php
									}
									//TODO: complete this
									echo "//TODO";
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
		<?php
		Section::end();
		?>



		<br/><br/>

		<?php
		Section::begin('API');
		?>
		<p>
			The following table reports all the API Services and Actions available on the platform.
		</p>
		<div style="padding:10px 0">

			<?php
			// echoArray(  );
			// \system\classes\Core::_load_API_setup();
			?>

		</div>
		<?php
		Section::end();
		?>



		<br/><br/>

		<button type="button" class="btn btn-success" id="settings-save-button" style="float:right"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> &nbsp; Save and Apply</button>

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

	$('.page-disable-button').on('click', function(){
		var page_id = $(this).data('page');
		//
		var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/configuration/disable_page/json?id="+page_id+"&token=<?php echo $_SESSION["TOKEN"] ?>";
		//
		// callAPI( url, true, false );
	});

	$('.page-enable-button').on('click', function(){
		var page_id = $(this).data('page');
		//
		var url = "<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/configuration/enable_page/json?id="+page_id+"&token=<?php echo $_SESSION["TOKEN"] ?>";
		//
		// callAPI( url, true, false );
	});
</script>
