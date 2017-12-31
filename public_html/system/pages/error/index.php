<?php

if( isset($_GET['report']) ){
	if( isset($_POST['errorMsg']) && isset($_SESSION['ADMIN_BACKEND_ERROR_OCCURRED']) ){
		// collect error message
		\system\classes\Core::collectErrorInformation( array( 'message' => urldecode($_POST['errorMsg']) ) );
		// clear the flag
		unset( $_SESSION['ADMIN_BACKEND_ERROR_OCCURRED'] );
		// open an alert
		$_SESSION['ADMIN_BACKEND_ALERT_INFO'] = 'Grazie per aver segnalato l\'errore!';
	}
	//
	\system\classes\Core::redirectTo('dashboard');
}else{
	// mark the ERROR flag
	$_SESSION['ADMIN_BACKEND_ERROR_OCCURRED'] = true;
}

if( !isset($_SESSION['ADMIN_BACKEND_ERROR_PAGE_MESSAGE']) ){
	\system\classes\Core::redirectTo('dashboard');
}

?>


<br/>
<br/>
<br/>
<br/>

<div class="col-md-6 text-right">
	<img src="<?php echo \system\classes\Configuration::$BASE_URL ?>images/error.jpg">
</div>

<div class="col-md-6">
	<h1 style="font-size:70px; margin-bottom:0">Oops!</h1>
	<h3 style="margin-top:0; padding-left:4px">Qualcosa qui è andato storto!</h3>

	<br/>
	<p style="padding-left:70px">- - - - - - - - - - - - - - - - - - -</p>

	<br/>

	<div class="col-md-11" style="padding-left:5px">
		Se è la prima volta che vedi questa pagina, torna al sito e non preoccuparti.
		<br/>
		Se ti capita spesso di ricevere questo errore ti preghiamo di segnalarcelo, provvederemo a sistemarlo.
		<br><br>
		<a href="<?php echo \system\classes\Configuration::$BASE ?>amministrazione/" type="button" class="btn btn-info" role="button">Torna al sito</a>
	</div>

</div>


<div class="col-md-offset-1 col-md-10" style="margin-top:30px">
	<div class="panel panel-warning">

		<div class="panel-heading" style="padding:4px 15px 4px 15px">
			Descrizione dell'errore
		</div>

		<div class="panel-footer" style="padding:18px">
			<code>
				<?php
				echo $_SESSION['ADMIN_BACKEND_ERROR_PAGE_MESSAGE'];
				?>
			</code>

			<br/>


			<form method="post" action="<?php echo \system\classes\Configuration::$BASE ?>errore?report=1">
				<input type="hidden" name="errorMsg" value="<?php echo urlencode($_SESSION['ADMIN_BACKEND_ERROR_PAGE_MESSAGE']) ?>">
				<table style="width:100%">
					<tr>
						<td class="col-md-8"></td>
						<td class="col-md-4" style="padding-right:0">
							<button type="submit" class="btn btn-warning pull-right" style="margin:0"><span class="glyphicon glyphicon-bullhorn" aria-hidden="true"></span> Segnala</button>
						</td>
					</tr>
				</table>
			</form>

		</div>
	</div>
</div>

<?php
// clear the error console
unset( $_SESSION['ADMIN_BACKEND_ERROR_PAGE_MESSAGE'] );
?>
