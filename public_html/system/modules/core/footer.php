<?php
// get user info
$user = \system\classes\Core::getUserLogged();
?>

<footer id="footer" class="footer navbar-inverse" style="height:50px; color:#c3c3c3">

	<div class="col-md-6 text-left">

		<ul class="nav navbar-nav">

			<li class="dropup">

				<a class="dropdown-toggle cursor-pointer" data-toggle="dropdown" style="padding-top:12px; padding-bottom:0; z-index:999999">
					<span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span>
					<img src="<?php echo $user['picture']; ?>" id="avatar">
				</a>

				<ul class="dropdown-menu dropup" role="menu" style="background-color:#3c3c3c; color:white">
					<?php
					// get pages
					$user_role = \system\classes\Core::getUserRole();
					$pages = \system\classes\Core::getFilteredPagesList(
						'by-menuorder',
						true /* enabledOnly */,
						$user_role /* accessibleBy */
					);

					foreach($pages as &$elem) {
						$icon = sprintf('%s %s-%s', $elem['menu_entry']['icon']['type'], $elem['menu_entry']['icon']['type'], $elem['menu_entry']['icon']['name']);
						?>
						<li role="presentation">
							<a role="menuitem" tabindex="-1"
							   href="<?php echo \system\classes\Configuration::$BASE . $elem['id'] ?>"
							   style="color:white" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#FFF'">
								   	<span class="<?php echo $icon ?>" aria-hidden="true"></span> &nbsp;
									<?php echo $elem['name'] ?>
							</a>
						</li>
						<?php
					}
					?>
					<li class="divider"></li>
					<!-- Logout button -->
					<li role="presentation" style="margin-bottom:20px">
						<a role="menuitem" tabindex="-1" href="#"
						   onclick="userLogOut('<?php echo \system\classes\Configuration::$BASE ?>', '<?php echo \system\classes\Configuration::$BASE_URL ?>', '<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>', '<?php echo $_SESSION['TOKEN'] ?>');"
						   style="color:#ffc864" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#ffc864'">
						   		<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> &nbsp;Log out
						</a>
					</li>
				</ul>

			</li>


		</ul>

		<div style="float:left">
			<p style="margin:0; font-size:16px; margin-top:8px"><strong><?php echo $user['name'] ?></strong></p>
			<p style="margin:0; font-size:12px; margin-top:-4px"><?php echo $user['email'] ?></p>
		</div>

	</div>

	<div class="col-md-6 text-right" style="height:100%">
		<p style="margin:10px 10px 0 0; font-size:8pt">
			<strong>developed by</strong> &nbsp; <a href="http://www.afdaniele.com" style="color:white">Andrea F. Daniele</a>
			<br/>
			<strong>serial</strong> &nbsp; <span style="font-family:monospace">git|<?php echo \system\classes\Core::getCodebaseHash(); ?></span>
		</p>
	</div>


</footer>
