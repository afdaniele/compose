<?php

if( isset($_GET['report']) ){
	if( isset($_POST['errorMsg']) && isset($_SESSION['_ERROR_OCCURRED']) ){
		// collect error message
		\system\classes\Core::collectErrorInformation( array( 'message' => urldecode($_POST['errorMsg']) ) );
		// clear the flag
		unset( $_SESSION['_ERROR_OCCURRED'] );
		// open an alert
		$_SESSION['_ALERT_INFO'] = 'Thanks for reporting the error!';
	}
	//
	\system\classes\Core::redirectTo('dashboard');
}else{
	// mark the ERROR flag
	$_SESSION['_ERROR_OCCURRED'] = true;
}

if( !isset($_SESSION['_ERROR_PAGE_MESSAGE']) ){
	\system\classes\Core::redirectTo('dashboard');
}

?>

<br/>
<br/>
<br/>

<div class="col-md-6 text-right">
	<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/error.jpg">
</div>

<div class="col-md-6">
	<h1 style="font-size:70px; margin-bottom:0">Oops!</h1>
	<h3 style="margin-top:0; padding-left:4px">Something broke here!</h3>

	<br/>

	<div class="col-md-12 text-center" style="padding-left:5px; padding-right:100px">
		- - - - - - - - - - - - - - - - - - -
	</div>

	<br/>
	<br/>

	<div class="col-md-12 text-justify" style="padding-left:5px; padding-right:80px">
		The box below contains a description of the error.
		<br/>
		You can either ignore the error and return to the website or report the error using the orange button in the box below.
		<br><br>
		<a href="<?php echo \system\classes\Configuration::$BASE ?>dashboard/" type="button" class="btn btn-info" role="button">Go back to the Dashboard</a>
	</div>

</div>


<div class="col-md-offset-1 col-md-10" style="margin-top:20px">
	<div class="panel panel-warning">

		<div class="panel-heading" style="padding:4px 15px 4px 15px">
			Error description
		</div>

		<div class="panel-footer" style="padding:18px">
			<code>
				<?php
				echo $_SESSION['_ERROR_PAGE_MESSAGE'];
				?>
			</code>

			<br/>


			<form method="post" action="<?php echo \system\classes\Configuration::$BASE ?>error?report=1">
				<input type="hidden" name="errorMsg" value="<?php echo urlencode($_SESSION['_ERROR_PAGE_MESSAGE']) ?>">
				<table style="width:100%">
					<tr>
						<td class="col-md-12 text-right" style="padding-right:0">
							<button type="submit" class="btn btn-warning pull-right" style="margin:0">
								<span class="glyphicon glyphicon-bullhorn" aria-hidden="true"></span> Report
							</button>
						</td>
					</tr>
				</table>
			</form>

		</div>
	</div>
</div>

<?php
// clear the error console
unset( $_SESSION['_ERROR_PAGE_MESSAGE'] );
?>
