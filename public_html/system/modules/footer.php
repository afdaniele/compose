<footer id="footer" class="footer navbar-inverse" style="height:50px; color:#c3c3c3">

	<div class="col-md-6 text-left">

		<ul class="nav navbar-nav">

			<li class="dropup">

				<a class="dropdown-toggle cursor-pointer" data-toggle="dropdown" style="padding-top:12px; padding-bottom:0">
					<span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span>
					</span><span class="glyphicon glyphicon-user" aria-hidden="true" style="font-size:30px"></span>
				</a>

				<ul class="dropdown-menu dropup" role="menu" style="background-color:#3c3c3c; color:white">
					<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo \system\classes\Configuration::$BASE ?>dashboard" style="color:white" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#FFF'"><span class="glyphicon glyphicon-th-large" aria-hidden="true"></span> &nbsp;Dashboard</a></li>
					<li class="divider"></li>
					<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo \system\classes\Configuration::$BASE ?>duckiebots" style="color:white" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#FFF'"><i class="icon-automobile-car"></i> &nbsp;Duckiebots</a></li>
					<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo \system\classes\Configuration::$BASE ?>live" style="color:white" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#FFF'"><span class="glyphicon glyphicon-facetime-video" aria-hidden="true"></span> &nbsp;Live</a></li>
					<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo \system\classes\Configuration::$BASE ?>surveillance" style="color:white" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#FFF'"><span class="glyphicon glyphicon-hdd" aria-hidden="true"></span> &nbsp;Surveillance</a></li>
					<!-- <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo \system\classes\Configuration::$BASE ?>inbox" style="color:white" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#FFF'"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> &nbsp;Inbox</a></li> -->
					<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo \system\classes\Configuration::$BASE ?>settings" style="color:white" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#FFF'"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> &nbsp;Settings</a></li>
					<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo \system\classes\Configuration::$BASE ?>profile" style="color:white" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#FFF'"><span class="glyphicon glyphicon-align-justify" aria-hidden="true"></span> &nbsp;Profile</a></li>
					<li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo \system\classes\Configuration::$BASE ?>api" style="color:white" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#FFF'"><span class="glyphicon glyphicon-book" aria-hidden="true"></span> &nbsp;API</a></li>
					<li class="divider"></li>
					<li role="presentation"><a role="menuitem" tabindex="-1" href="#" onclick="administratorLogOut('<?php echo \system\classes\Configuration::$BASE ?>', '<?php echo \system\classes\Configuration::$BASE_URL ?>', '<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>', '<?php echo $_SESSION['TOKEN'] ?>');" style="color:#ffc864" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#ffc864'"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> &nbsp;Log out</a></li>
				</ul>

			</li>


		</ul>

		<div style="float:left">
			<?php
			$administrator = \system\classes\Core::getAdministratorLogged();
			?>
			<p style="margin:0; font-size:16px; margin-top:8px"><strong><?php echo $administrator['name'].' '.$administrator['surname'] ?></strong></p>
			<p style="margin:0; font-size:12px; margin-top:-4px"><?php echo $administrator['username'].' | '.$administrator['email'] ?></p>
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
