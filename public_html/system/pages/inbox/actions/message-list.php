<?php

require_once __DIR__.'/../../../../../system/templates/tableviewers/TableViewer.php';


$base = 'amministrazione';
$action = 'messaggi';

// <========================================  CONFIG ==========================



$subject = array('problem', 'question', 'info');

$baseurl = $base.'/'.$action;


// Define Constants

$order_feature = array(
	'newest' => array(
		'orderBy'=>'creationTime',
		'orderWay'=>'DESC',
		'translation' => 'Prima il più recente'
	),
	'oldest' => array(
		'orderBy'=>'creationTime',
		'orderWay'=>'ASC',
		'translation' => 'Prima il più vecchio'
	),
	'unread' => array(
		'orderBy'=>'read',
		'orderWay'=>'ASC',
		'translation' => 'Prima quelli non letti'
	),
	'read' => array(
		'orderBy'=>'read',
		'orderWay'=>'DESC',
		'translation' => 'Prima quelli già letti'
	)
);


$features = array(
	'page' => array(
		'type' => 'integer',
		'default' => 1,
		'values' => null,
		'minvalue' => 1,
		'maxvalue' => PHP_INT_MAX
	),
	'results' => array(
		'type' => 'integer',
		'default' => 10,
		'values' => null,
		'minvalue' => 1,
		'maxvalue' => PHP_INT_MAX
	),
	'order' => array(
		'type' => 'alpha',
		'default' => 'unread',
		'values' => array_keys($order_feature),
		'details' => $order_feature
	),
	'tag' => array(
		'type' => 'alpha',
		'default' => null,
		'values' => $subject,
		'translation' => 'Tipo'
	)
);

$table = array(
	'style' => 'table-striped table-hover',
	'layout' => array(
		'messageID' => array(
			'type' => 'key',
			'show' => true,
			'width' => 'md-2',
			'align' => 'center',
			'translation' => 'Chiave',
			'editable' => false
		),
		'creationTime' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-3',
			'align' => 'left',
			'translation' => 'Ricevuto il',
			'editable' => false
		),
		'subject' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-3',
			'align' => 'center',
			'translation' => 'Oggetto',
			'meaning' => array( 'problem' => 'Segnalazione Problema', 'question' => 'Domanda Generica', 'info' => 'Messaggio di Informazione' ),
			'editable' => false
		),
		'read' => array(
			'type' => 'message-status',
			'show' => true,
			'width' => 'md-1',
			'align' => 'center',
			'translation' => 'Stato',
			'editable' => false
		)
	),
	'actions' => array(
		'_width' => 'md-2',
		'read' => array(
			'type' => 'default',
			'glyphicon' => 'fullscreen',
			'tooltip' => 'Leggi il messaggio',
			'text' => 'Apri',
			'function' => array(
				'type' => 'custom',
				'custom_html' => 'onclick="_go_to_link(this)"',
				'arguments' => array('messageID')
			)
		)
	),
	'features' => array(
		'_counter_column',
		'_actions_column'
	)
);

?>


<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>I tuoi Messaggi</h2>
			</td>
		</tr>

	</table>


	<?php

	// parse the arguments
	\system\templates\tableviewers\TableViewer::parseFeatures( $features, $_GET );


	// get the message list
	$res = \system\classes\Core::getAdministratorMessageList( $features['tag']['value'], null, $order_feature[$features['order']['value']]['orderBy'], $order_feature[$features['order']['value']]['orderWay'], $features['offset']['value'], $features['limit']['value'] );

	if( !$res['success'] ){
		//error
		\system\classes\Core::throwError( $res['data'] );
	}

	// <== Here the Magic Call!

	\system\templates\tableviewers\TableViewer::generateTableViewer( $baseurl, $res, $features, $table );

	?>

</div>


<script type="text/javascript">

	function _go_to_link( target ){
		var message = $(target).data('messageid');
		<?php
		$qs = urlencode( base64_encode( toQueryString( array_keys($features), $_GET ) ) );
		?>
		var url = "<?php echo \system\classes\Configuration::$PLATFORM_BASE ?>messaggi?<?php echo ( (strlen($qs) > 0)? 'lst='.$qs.'&' : '' ) ?>message="+message;
		window.location = url;
	}

</script>
