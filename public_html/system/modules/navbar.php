<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div style="padding-right:30px">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo \system\classes\Configuration::$BASE ?>admin/" style="padding-right:0"><b><?php echo \system\classes\Configuration::$SHORT_SITE_LINK ?> - Dashboard</b></a>
		</div>
		<div id="navbar" class="collapse navbar-collapse">

			<ul class="nav navbar-nav navbar-right">

				<?php
				if( \system\classes\Core::isAdministratorLoggedIn() ){
					?>
					<li <?php if(\system\classes\Configuration::$PAGE == 'dashboard') echo 'class="active"'?> >
						<a href="<?php echo \system\classes\Configuration::$BASE ?>dashboard">
							<span class="glyphicon glyphicon-th-large" aria-hidden="true"></span> &nbsp;Dashboard
						</a>
					</li>

					<li style="width:2px">&nbsp;</li>

					<li <?php if(\system\classes\Configuration::$PAGE == 'duckiebots') echo 'class="active"'?> >
						<a href="<?php echo \system\classes\Configuration::$BASE ?>duckiebots">
							<i class="icon-automobile-car"></i> &nbsp;Duckiebots
						</a>
					</li>

					<li style="width:2px">&nbsp;</li>

					<li class="navbar-1220-full-button-component <?php if(\system\classes\Configuration::$PAGE == 'live') echo 'active'?>">
						<a href="<?php echo \system\classes\Configuration::$BASE ?>live">
							<span class="glyphicon glyphicon-facetime-video" aria-hidden="true"></span> &nbsp;Live
						</a>
					</li>

					<li style="width:2px" class="navbar-1220-full-button-component">&nbsp;</li>

					<li class="navbar-1220-full-button-component <?php if(\system\classes\Configuration::$PAGE == 'surveillance') echo 'active'?>">
						<a href="<?php echo \system\classes\Configuration::$BASE ?>surveillance">
							<span class="glyphicon glyphicon-hdd" aria-hidden="true"></span> &nbsp;Surveillance
						</a>
					</li>

					<li style="width:2px">&nbsp;</li>

					<!-- <?php
					$res = \system\classes\Core::getAdministratorMessageList( null, false );
					$count = ( ($res['success'])? $res['size'] : 0 );
					?>

					<li class="<?php if(\system\classes\Configuration::$PAGE == 'inbox') echo 'active'?>">
						<a href="<?php echo \system\classes\Configuration::$BASE ?>inbox">
							<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> &nbsp;Inbox
						<span class="badge" style="background-color:#ffad00; margin-left:4px; <?php echo ( ( $count > 0 )? '' : 'display:none' ) ?>">
							<?php echo $count ?>
						</span>
						</a>
					</li> -->

					<li style="width:2px" class="navbar-1340-full-button-component">&nbsp;</li>

					<li class="navbar-1340-full-button-component <?php if(\system\classes\Configuration::$PAGE == 'settings') echo 'active'?>">
						<a href="<?php echo \system\classes\Configuration::$BASE ?>settings">
							<span class="glyphicon glyphicon-cog" aria-hidden="true"></span> &nbsp;Settings
						</a>
					</li>

					<li style="width:2px" class="navbar-1400-full-button-component">&nbsp;</li>

					<li class="navbar-1400-full-button-component <?php if(\system\classes\Configuration::$PAGE == 'profile') echo 'active'?>">
						<a href="<?php echo \system\classes\Configuration::$BASE ?>profile">
							<span class="glyphicon glyphicon-align-justify" aria-hidden="true"></span> &nbsp;Profile
						</a>
					</li>

					<li style="width:2px" class="navbar-1460-full-button-component">&nbsp;</li>

					<li class="navbar-1460-full-button-component <?php if(\system\classes\Configuration::$PAGE == 'api') echo 'active'?>">
						<a href="<?php echo \system\classes\Configuration::$BASE ?>api">
							<span class="glyphicon glyphicon-book" aria-hidden="true"></span> &nbsp;API
						</a>
					</li>

					<li class="dropdown navbar-1460-responsive-button-component">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-chevron-down" aria-hidden="true" style="margin-top:2px"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li class="navbar-1220-responsive-button-component">
								<a href="<?php echo \system\classes\Configuration::$BASE ?>live">
									<span class="glyphicon glyphicon-facetime-video" aria-hidden="true"></span> &nbsp;Live
								</a>
							</li>
							<li class="navbar-1220-responsive-button-component">
								<a href="<?php echo \system\classes\Configuration::$BASE ?>surveillance">
									<span class="glyphicon glyphicon-hdd" aria-hidden="true"></span> &nbsp;Surveillance
								</a>
							</li>
							<li class="navbar-1340-responsive-button-component">
								<a href="<?php echo \system\classes\Configuration::$BASE ?>settings">
									<span class="glyphicon glyphicon-cog" aria-hidden="true"></span> &nbsp;Settings
								</a>
							</li>
							<li class="navbar-1400-responsive-button-component">
								<a href="<?php echo \system\classes\Configuration::$BASE ?>profile">
									<span class="glyphicon glyphicon-align-justify" aria-hidden="true"></span> &nbsp;Profile
								</a>
							</li>
							<li class="navbar-1460-responsive-button-component">
								<a href="<?php echo \system\classes\Configuration::$BASE ?>api">
									<span class="glyphicon glyphicon-book" aria-hidden="true"></span> &nbsp;API
								</a>
							</li>
						</ul>
					</li>

					<li style="width:26px; text-align:center; color:white; margin-top:15px">
						&nbsp;&bull;&nbsp;
					</li>

					<li><a class="cursor-pointer" onclick="administratorLogOut('<?php echo \system\classes\Configuration::$BASE ?>', '<?php echo \system\classes\Configuration::$BASE_URL ?>', '<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>', '<?php echo $_SESSION['TOKEN'] ?>');" style="color:#ffc864; padding-right:0"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> &nbsp;Log out</a></li>
				<?php
				}else{
					?>
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
		if( window.innerWidth < 1460 ){
			$('#navbar').find('.navbar-1460-full-button-component').each(function(){
				$(this).css('display', 'none');
			});
			//
			$('#navbar').find('.navbar-1460-responsive-button-component').each(function(){
				$(this).css('display', '');
			});
		}else{
			$('#navbar').find('.navbar-1460-full-button-component').each(function(){
				$(this).css('display', '');
			});
			//
			$('#navbar').find('.navbar-1460-responsive-button-component').each(function(){
				$(this).css('display', 'none');
			});
		}
		//
		if( window.innerWidth < 1400 ){
			$('#navbar').find('.navbar-1400-full-button-component').each(function(){
				$(this).css('display', 'none');
			});
			//
			$('#navbar').find('.navbar-1400-responsive-button-component').each(function(){
				$(this).css('display', '');
			});
		}else{
			$('#navbar').find('.navbar-1400-full-button-component').each(function(){
				$(this).css('display', '');
			});
			//
			$('#navbar').find('.navbar-1400-responsive-button-component').each(function(){
				$(this).css('display', 'none');
			});
		}
		//
		if( window.innerWidth < 1340 ){
			$('#navbar').find('.navbar-1340-full-button-component').each(function(){
				$(this).css('display', 'none');
			});
			//
			$('#navbar').find('.navbar-1340-responsive-button-component').each(function(){
				$(this).css('display', '');
			});
		}else{
			$('#navbar').find('.navbar-1340-full-button-component').each(function(){
				$(this).css('display', '');
			});
			//
			$('#navbar').find('.navbar-1340-responsive-button-component').each(function(){
				$(this).css('display', 'none');
			});
		}
		//
		if( window.innerWidth < 1220 ){
			$('#navbar').find('.navbar-1220-full-button-component').each(function(){
				$(this).css('display', 'none');
			});
			//
			$('#navbar').find('.navbar-1220-responsive-button-component').each(function(){
				$(this).css('display', '');
			});
		}else{
			$('#navbar').find('.navbar-1220-full-button-component').each(function(){
				$(this).css('display', '');
			});
			//
			$('#navbar').find('.navbar-1220-responsive-button-component').each(function(){
				$(this).css('display', 'none');
			});
		}
		//
		if( window.innerWidth < 1110 ){
			$('#navbar').find('.navbar-1110-full-button-component').each(function(){
				$(this).css('display', 'none');
			});
			//
			$('#navbar').find('.navbar-1110-responsive-button-component').each(function(){
				$(this).css('display', '');
			});
		}else{
			$('#navbar').find('.navbar-1110-full-button-component').each(function(){
				$(this).css('display', '');
			});
			//
			$('#navbar').find('.navbar-1110-responsive-button-component').each(function(){
				$(this).css('display', 'none');
			});
		}
	}//_resize_navbar

</script>
