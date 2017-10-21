<?php

$messageID = $_GET['message'];

// get the user info
$res = \system\classes\Core::getAdministratorMessage( $messageID );
if( !$res['success'] ){
	//error
	\system\classes\Core::throwError( $res['data'] );
}
if( $res['size'] <= 0 ){
	// no message found
	\system\classes\Core::redirectTo('messaggi');
}
$message = $res['data'][0];

?>


<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="text-align:left; vertical-align:bottom; padding-bottom:8px">
				<?php
				$qs = ( (isset($_GET['lst']))? base64_decode(urldecode($_GET['lst'])) : '' );
				?>
				<a role="button" href="<?php echo \system\classes\Configuration::$PLATFORM_BASE ?>messaggi<?php echo ( (strlen($qs) > 0)? '?'.$qs : '' ) ?>" class="btn btn-info"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span> &nbsp;Torna alla lista</a>
			</td>

			<td style="width:100%; padding-left:40px">
				<h3>Messaggio: <strong><?php echo \system\classes\Configuration::$TRAN[$message['subject']].' &nbsp;-&nbsp; <span style="font-size:20px; color:#5bc0de">ID: '.$message['messageID'].'</span>' ?></strong></h3>
			</td>
		</tr>

	</table>


	<table style="width:100%; margin-top:30px">

		<tr>
			<td colspan="4">
				<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:20px">

					<tr>
						<td style="width:50%">
							<h3>Dettagli del messaggio</h3>
						</td>
					</tr>

				</table>

				<table style="width:100%">
					<tr>
						<td style="width:25%" class="text-right">
							<strong>Data di Ricezione:</strong>
						</td>
						<td style="width:5%"></td>
						<td style="width:70%">
							<?php echo date( 'd/m/Y H:i', strtotime($message['creationTime']) ) ?>
						</td>
					</tr>

					<tr style="height:10px"></tr>

					<tr>
						<td style="width:25%" class="text-right">
							<strong>Mittente:</strong>
						</td>
						<td style="width:5%"></td>
						<td style="width:70%">
							<?php echo $message['sender'] ?>
						</td>
					</tr>

					<tr style="height:10px"></tr>

					<tr>
						<td style="width:25%" class="text-right">
							<strong>Recapito Telefonico:</strong>
						</td>
						<td style="width:5%"></td>
						<td style="width:70%">
							<?php echo $message['phone'] ?>
						</td>
					</tr>

					<tr style="height:10px"></tr>

					<tr>
						<td style="width:25%" class="text-right">
							<strong>Indirizzo e-mail:</strong>
						</td>
						<td style="width:5%"></td>
						<td style="width:70%">
							<?php echo $message['email'] ?>
						</td>
					</tr>

					<tr style="height:10px"></tr>

					<tr>
						<td style="width:25%" class="text-right">
							<strong>Oggetto:</strong>
						</td>
						<td style="width:5%"></td>
						<td style="width:70%">
							<?php echo \system\classes\Configuration::$TRAN[$message['subject']] ?>
						</td>
					</tr>

					<tr style="height:10px"></tr>

					<tr>
						<td style="width:25%" class="text-right">
							<strong>Contenuto del messaggio:</strong>
						</td>
						<td style="width:5%"></td>
						<td style="width:70%; padding-right:40px">
							<div style="background-color:white; border-radius:4px; border:1px solid #999999; padding:12px">
								<?php echo $message['message'] ?>
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>

			<td style="width:100%; vertical-align:top">

			</td>
		</tr>

	</table>

</div>

<?php

$res = \system\classes\Core::markContactRequestAsRead( $messageID );

if( !$res['success'] ){
	//error
	\system\classes\Core::throwError( $res['data'] );
}

?>
