<?php

require_once __DIR__.'/../../../../classes/Core.php';
use system\classes\Core as Core;

require_once __DIR__ . '/../utils/utils.php';


function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'send':
			$sender = '';
			$sender_enum = '';
			if( $arguments['sender'] == 'user' ){
				if( !Core::isUserLoggedIn() && (!isset($arguments['sender_name']) || strlen($arguments['sender_name']) <= 0) ) return array( 'code' => 400, 'status' => 'Bad Request', 'message' => 'E\' necessario fornire un valore per il campo \'Nominativo di Contatto\' (\'sender_name\') se si invia un messaggio senza essere loggato' );
				//
				$sender = 'Utente' . ( (Core::isUserLoggedIn())? ' | '.Core::getUserLogged('name').' '.Core::getUserLogged('surname') : ( (isset($arguments['sender_name']))? ' | '.$arguments['sender_name'] : '' ) ) . ( (Core::isUserLoggedIn())? ' | ID:'.Core::getUserLogged('phone') : '' );
				$sender_enum = 'Utente';
			} else {
				if( !Core::isMerchantLoggedIn() && (!isset($arguments['sender_name']) || strlen($arguments['sender_name']) <= 0) ) return array( 'code' => 400, 'status' => 'Bad Request', 'message' => 'E\' necessario fornire un valore per il campo \'Nominativo di Contatto\' (\'sender_name\') se si invia un messaggio senza essere loggato' );
				//
				$sender = 'Esercente' . ( (Core::isMerchantLoggedIn())? ' | '.Core::getMerchantLogged('name') : ( (isset($arguments['sender_name']))? ' | '.$arguments['sender_name'] : '' ) ) . ( (Core::isMerchantLoggedIn())? ' | ID:'.Core::getMerchantLogged('merchID') : '' );
				$sender_enum = 'Esercente';
			}
			//
			$arguments['sender'] = $sender;
			//
			$subject_tran = array( 'problem' => 'Segnalazione Problema', 'question' => 'Domanda Generica', 'info' => 'Messaggio di Informazione' );
			$emaildata = array( /*title*/ 'Hai ricevuto un nuovo messaggio!',  /*sender_enum*/ $sender_enum,  /*sender*/ $arguments['sender'],  /*subject*/ $subject_tran[$arguments['subject']],  /*creation_time*/ date('d/m/Y', time()), /*email*/ $arguments['email'], /*phone*/ $arguments['phone'], /*msg*/ $arguments['message']);
			Core::sendEMail( \system\classes\Configuration::$ADMIN_CONTACT_MAIL_ADDRESS, 'Richiesta di contatto su '.\system\classes\Configuration::$SHORT_SITE_LINK, \system\classes\enum\EmailTemplates::$ADMIN_NEW_MESSAGE, $emaildata );
			//
			$res = Core::collectContactRequest( $arguments );
			//
			if( !$res['success'] ){
				return array( 'code' => 500, 'status' => 'Internal Server Error', 'message' => $res['data'] );
			}
			//
			return array( 'code' => 200, 'status' => 'OK' );
			break;
		//
		default:
			return array( 'code' => 404, 'status' => 'Not Found', 'message' => "Comando '".$actionName."' non trovato" );
			break;
	}
}//execute

?>