<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

use \system\classes\Core;
use \system\classes\Configuration;

if( !Core::getSetting('maintenance_mode', 'core') ){
	\system\classes\Core::redirectTo('');
}

?>

<br/>
<br/>
<br/>


<div class="col-md-3 text-center">
	<br/>
	<br/>
	<img src="<?php echo Configuration::$BASE ?>images/work_in_progress.png" style="width:100%; max-width:220px">
</div>

<div class="col-md-9 text-left">
	<h2 style="font-size:70px; margin-bottom:10px">We're working here!</h2>
	<h3 style="margin-top:0; padding-left:4px">The website is currently under maintenance, check again later.</h3>

	<br/>
	<br/>

	<div class="col-md-12 text-center" style="padding-left:5px; padding-right:100px">
		- - - - - - - - - - - - - - - - - - -
	</div>

	<br/><br/><br/>

	<div class="col-md-12 text-justify">
		We apologize for the inconvenience, we are working so that you can experience
		the website at its best.
	</div>

</div>
