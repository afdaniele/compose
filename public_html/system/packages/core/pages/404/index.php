<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;

?>

<br/>
<br/>
<br/>

<div class="col-md-6 text-right">
	<br/><br/><br/><br/>
	<h1 class="text-center" style="font-size:120px; color:#28a4c9">
		<span class="fa fa-bug" aria-hidden="true"></span> 404
	</h1>
</div>

<div class="col-md-6" style="border-left:1px solid lightgray; padding-left:60px">
	<h1 style="font-size:70px; margin-bottom:0">Oops!</h1>
	<h3 style="margin-top:0; padding-left:4px">Something is missing here!</h3>

	<br/>

	<div class="col-md-12 text-center" style="padding-left:5px; padding-right:100px">
		- - - - - - - - - - - - - - - - - - -
	</div>

	<br/>
	<br/>

	<div class="col-md-12 text-justify" style="padding-left:5px; padding-right:80px; margin-bottom:50px">
		The file you were looking for does not exist or was moved.
		<br/>
		Please check your URL again and retry. We are sorry for the inconvenience.
		<br><br>
		<a href="<?php echo Core::getURL('') ?>/" type="button" class="btn btn-info" role="button">
      Go back to the Main page
    </a>
	</div>
</div>
