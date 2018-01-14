<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Saturday, January 13th 2018

?>

<style type="text/css">

	#settings .tab-content{
		background-color: white;
		border-bottom: 1px solid #ddd;
		display: inline-block;
	    width: 100%;
	    padding: 30px;
	}

	.nav-tabs>li>a{
		border: 1px solid #ddd;
		color: inherit;
	}

</style>


<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>Settings</h2>
			</td>
		</tr>

	</table>


	<div id="settings">
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active">
				<a href="#general" aria-controls="general" role="tab" data-toggle="tab">General</a>
			</li>
			<li role="presentation">
				<a href="#packages" aria-controls="packages" role="tab" data-toggle="tab">Packages</a>
			</li>
			<li role="presentation">
				<a href="#pages" aria-controls="pages" role="tab" data-toggle="tab">Pages</a>
			</li>
			<li role="presentation">
				<a href="#api" aria-controls="api" role="tab" data-toggle="tab">API</a>
			</li>
			<?php
			//TODO: check which package exports settings and create a tab here
			?>
		</ul>

		<!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="general">
				<?php
				include_once "sections/general.php";
				?>
			</div>
			<div role="tabpanel" class="tab-pane" id="packages">
				<?php
				include_once "sections/packages.php";
				?>
			</div>
			<div role="tabpanel" class="tab-pane" id="pages">
				<?php
				include_once "sections/pages.php";
				?>
			</div>
			<div role="tabpanel" class="tab-pane" id="api">
				<?php
				include_once "sections/api.php";
				?>
			</div>
			<?php
			//TODO: check which package exports settings and create a tab-content here
			?>
		</div>

	</div>

</div>
