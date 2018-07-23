<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Tuesday, January 9th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 17th 2018


use \system\classes\Core;

?>

<style type="text/css">

	#credits_table_center {
		margin: auto;
		margin-top: 10px;
	}

	#credits_table_right {
		float: right;
		margin: 10px 10px 0 0;
	}

	#footer_developer_credit > p{
		font-size: 8pt;
		width: 190px;
		text-align: left;
		margin-bottom: 0;
		line-height: 11pt;
	}

	#footer_compose_credit{
		padding-left: 30px;
	}

	#footer_compose_credit > p{
		padding-right: 20px;
		margin-bottom: 0;
	}

	#footer_compose_credit > p > span{
		font-size: 8pt;
		font-weight: bold;
	}

	#footer_compose_credit > p > a > img{
		height: 24px;
	}

	#footer_compose_separator{
		font-size: 16pt;
		padding-right: 20px;
	}

</style>



<?php

function footer_user_menu(){
	// get user info
	$user = Core::getUserLogged();
	?>
	<ul class="nav navbar-nav" style="margin-left:10px;">

		<li class="dropup">

			<a class="dropdown-toggle cursor-pointer" data-toggle="dropdown" style="padding-top:12px; padding-bottom:0; z-index:999999">
				<span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span>
				<img src="<?php echo $user['picture']; ?>" id="avatar">
			</a>

			<ul class="dropdown-menu dropup" role="menu" style="background-color:#3c3c3c; color:white">
				<?php
				// get pages
				$user_role = Core::getUserRole();
				$pages = Core::getFilteredPagesList(
					'by-menuorder',
					true /* enabledOnly */,
					$user_role /* accessibleBy */
				);

				foreach($pages as &$elem) {
					// hide pages if maintenance mode is enabled
					if( $user_role!='administrator' && Core::getSetting('maintenance_mode','core',true) && $elem['id']!='login' )
						continue;
					// hide page if the current user' role is excluded
					if( in_array($user_role, $elem['menu_entry']['exclude_roles']) )
						continue;
					$icon = sprintf('%s %s-%s', $elem['menu_entry']['icon']['class'], $elem['menu_entry']['icon']['class'], $elem['menu_entry']['icon']['name']);
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
	<?php
}


function footer_credits( $float ){
	?>
	<table id="credits_table_<?php echo $float ?>">
		<tr>
			<td id="footer_compose_credit">
				<p>
					<span>
						powered by &nbsp;
					</span>
					<a href="https://github.com/afdaniele/compose" target="_blank">
						<img src="<?php echo \system\classes\Configuration::$BASE ?>images/compose-white-logo.svg"></img>
					</a>
				</p>
			</td>

			<td id="footer_compose_separator">
				&nbsp;|&nbsp;
			</td>

			<td id="footer_developer_credit">
				<?php
				$codebase_info = Core::getCodebaseInfo();
				$codebase_hash = $codebase_info['head_hash'];
				$codebase_tag = $codebase_info['head_tag'];
				$codebase_latest_tag = $codebase_info['latest_tag'];
				$codebase_tag = ( strcasecmp($codebase_tag, $codebase_latest_tag) === 0 )? $codebase_tag : 'devel';
				$codebase_str = ( in_array($codebase_tag, ['ND', 'GIT_ERROR', null]) )? '' : sprintf("%s | ", $codebase_tag);
				?>
				<p>
					<strong>developed by</strong> &nbsp; <a href="http://www.afdaniele.com" style="color:white">Andrea F. Daniele</a>
					<br/>
					<strong>serial</strong> &nbsp; <span style="font-family:monospace">git | <?php echo $codebase_str.$codebase_hash ?></span>
				</p>
			</td>
		</tr>
	</table>
	<?php
}

?>






<footer id="footer" class="footer navbar-inverse" style="height:50px; color:#c3c3c3">

	<table style="width:100%">
		<tr>

		<?php
		if( Core::isUserLoggedIn() ){
		?>
			<td class="text-left">
				<?php footer_user_menu(); ?>
			</td>

			<td class="text-right">
				<?php footer_credits('right'); ?>
			</td>
		<?php
		}else{
		?>
			<td>
				<?php footer_credits('center'); ?>
			</td>
		<?php
		}
		?>

		</tr>
	</table>


</footer>
