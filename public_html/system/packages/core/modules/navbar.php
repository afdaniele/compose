<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;

// get pages
$pages_list = Core::getPagesList();

// get the current user's role
$main_user_role = Core::getUserRole();
$user_roles = Core::getUserRolesList();

// get list of visible buttons
$buttons = Core::getFilteredPagesList(
	'by-responsive-priority',
	true /* enabledOnly */,
	$user_roles /* accessibleBy */
);

// create a whitelist/blacklist of pages
$pages_whitelist = null;
$pages_blacklist = null;

// check if compose was configured correctly
if (!Core::isComposeConfigured()){
  $pages_whitelist = ['setup'];
}else{
  $pages_blacklist = ['setup'];
}

// remove login if the functionality is not enabled
$login_enabled = Core::getSetting('login_enabled', 'core', False);

// count non-responsive buttons
$non_responsive_btns = 0;
foreach ($buttons as &$button) {
	if( !$login_enabled && $button['id']=='login' ) continue;
  if( !is_null($pages_whitelist) && !in_array($button['id'], $pages_whitelist) ) continue;
  if( !is_null($pages_blacklist) && in_array($button['id'], $pages_blacklist) ) continue;
	if( $button['menu_entry']['responsive']['priority'] < 0 ){
		$non_responsive_btns += 1;
	}
}

// define responsive parameters
$responsive_width_per_button = 140;
$responsive_min_width =
	400 + // logo and name
	$responsive_width_per_button * $non_responsive_btns + // non responsive buttons
	120; // logout button

// assign limit widths to the responsive buttons
$responsive_buttons = [];
$responsive_current_width = $responsive_min_width;
foreach ($buttons as &$button) {
	if( !$login_enabled && $button['id']=='login' ) continue;
  if( !is_null($pages_whitelist) && !in_array($button['id'], $pages_whitelist) ) continue;
  if( !is_null($pages_blacklist) && in_array($button['id'], $pages_blacklist) ) continue;
	if( $button['menu_entry']['responsive']['priority'] >= 0 ){
		$responsive_current_width += $responsive_width_per_button;
		$responsive_buttons[ $button['id'] ] = $responsive_current_width;
	}
}
?>

<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div style="padding-right:30px">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo Configuration::$BASE ?>" style="padding:10px 15px">
				<table>
					<tr>
						<td>
							<img id="navbarLogo" src="<?php echo Core::getSetting('logo_white') ?>"></img>
						</td>
						<td style="vertical-align:top">
							<h3 style="margin:0 0 0 15px">&nbsp;<?php echo Core::getSetting('navbar_title') ?></h3>
						</td>
					</tr>
				</table>

			</a>
		</div>

		<div id="navbar" class="collapse navbar-collapse">
			<ul class="nav navbar-nav navbar-right">
				<?php
				$pages = Core::getFilteredPagesList(
					'by-menuorder',
					true /* enabledOnly */,
					$user_roles /* accessibleBy */
				);

				// create buttons
				for ($i = 0; $i < count($pages); $i++) {
					$elem = $pages[$i];
					if( !$login_enabled && $elem['id']=='login' ) continue;
          if( !is_null($pages_whitelist) && !in_array($elem['id'], $pages_whitelist) ) continue;
          if( !is_null($pages_blacklist) && in_array($elem['id'], $pages_blacklist) ) continue;
					// hide pages if maintenance mode is enabled
					if( $main_user_role!='administrator' && Core::getSetting('maintenance_mode','core',true) && $elem['id']!='login' )
						continue;
					// hide page if the current user' role is excluded
					if( count( array_intersect($user_roles, $elem['menu_entry']['exclude_roles']) ) > 0 )
						continue;
					$is_last = boolval( $i == count($pages)-1 );
					$icon = sprintf('%s %s-%s', $elem['menu_entry']['icon']['class'], $elem['menu_entry']['icon']['class'], $elem['menu_entry']['icon']['name']);
					$active = (Configuration::$PAGE == $elem['id']) || in_array(Configuration::$PAGE, $elem['child_pages']);
					//
					?>
					<li class="<?php echo (isset($responsive_buttons[$elem['id']]))? 'navbar-'.$responsive_buttons[$elem['id']].'-full-button-component' : '' ?>
						<?php echo ($active)? 'active' : '' ?>" >
						<a href="<?php echo Configuration::$BASE . $elem['id'] ?>">
							<span class="<?php echo $icon ?>" aria-hidden="true" style="font-size:12pt"></span> &nbsp;
							<?php echo $elem['name'] ?>
						</a>
					</li>
					<?php
					if( !$is_last ){
						?>
						<li style="width:2px">&nbsp;</li>
						<?php
					}
				}
				?>

				<?php
				if( count($responsive_buttons) > 0 ){
					?>
					<!-- Responsive navbar -->
					<li class="dropdown navbar-<?php echo $responsive_current_width ?>-responsive-button-component">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
							<span class="glyphicon glyphicon-chevron-down" aria-hidden="true" style="margin-top:2px"></span>
						</a>
						<ul class="dropdown-menu" role="menu">
							<?php
							foreach ($responsive_buttons as $id => $width) {
								$elem = $pages_list['by-id'][$id];
								$icon = sprintf('%s %s-%s', $elem['menu_entry']['icon']['class'], $elem['menu_entry']['icon']['class'], $elem['menu_entry']['icon']['name']);
								?>
								<li class="navbar-<?php echo $width ?>-responsive-button-component">
									<a href="<?php echo Configuration::$BASE . $id ?>">
										<span class="<?php echo $icon ?>" aria-hidden="true"></span> &nbsp;
										<?php echo $elem['name'] ?>
									</a>
								</li>
								<?php
							}
							?>
						</ul>
					</li>
				<?php
				}
				?>

				<?php
				// Add LogOut button if the user is logged in
				if( Core::isUserLoggedIn() ){
					?>
					<li style="width:26px; text-align:center; color:white; margin-top:15px; margin-left: 15px">
						&nbsp;&bull;&nbsp;
					</li>

					<li>
						<a class="cursor-pointer"
						   onclick="logOutButtonClick();"
						   style="color:#ffc864; padding-right:0">
							<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> &nbsp;Log out
						</a>
					</li>
				<?php
				}
				?>

			</ul>
		</div><!--/.nav-collapse -->
	</div>
</nav>


<script type="text/javascript">
	$(window).resize(function () {
		_resize_navbar();
	});

	$(document).ready(function () {
		_resize_navbar();
	});

	function _resize_navbar(){
		<?php
		foreach ($responsive_buttons as $id => $width) {
			?>
			if( window.innerWidth < <?php echo $width ?> ){
				$('#navbar').find('.navbar-<?php echo $width ?>-full-button-component').each(function(){
					$(this).css('display', 'none');
				});
				//
				$('#navbar').find('.navbar-<?php echo $width ?>-responsive-button-component').each(function(){
					$(this).css('display', '');
				});
			}else{
				$('#navbar').find('.navbar-<?php echo $width ?>-full-button-component').each(function(){
					$(this).css('display', '');
				});
				//
				$('#navbar').find('.navbar-<?php echo $width ?>-responsive-button-component').each(function(){
					$(this).css('display', 'none');
				});
			}
			<?php
		}
		?>
	}//_resize_navbar

</script>
