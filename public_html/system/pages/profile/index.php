<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:50%">
				<h2>Your account</h2>
			</td>
		</tr>

	</table>


	<?php

	$administratorData = \system\classes\Core::getAdministratorLogged();

	$labelName = array('First name', 'Last name', 'E-mail address' );
	$fieldValue = array( $administratorData['name'], $administratorData['surname'], $administratorData['email'] );


	Section::begin('Personal information', null, null, null, array('Edit'), array('glyphicon-wrench'), array('data-toggle="modal" data-target="#edit-personal-info-modal"') );
	generateView( $labelName, $fieldValue, 'md-4', 'md-8' );
	Section::end();

	?>

	<br>
	<br>

	<?php

	$labelName = array('Username', 'Password');
	$fieldValue = array( $administratorData['username'], '********' );


	Section::begin('Access information', null, null, null, array('Edit'), array('glyphicon-wrench'), array('data-toggle="modal" data-target="#edit-security-info-modal"') );
	generateView( $labelName, $fieldValue, 'md-4', 'md-8' );
	Section::end();

	?>

	<?php

	require_once __DIR__.'/dialogs/edit_personal_info.php';
	require_once __DIR__.'/dialogs/edit_security_info.php';

	?>

</div>
