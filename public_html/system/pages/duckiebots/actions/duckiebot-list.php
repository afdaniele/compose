<?php

require_once __DIR__.'/../../../templates/tableviewers/TableViewer.php';


$base = '';
$action = 'duckiebots';

// <========================================  CONFIG ==========================


$baseurl = $base.'/'.$action;


// Define Constants

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
	'keywords' => array(
		'type' => 'text',
		'default' => null,
		'placeholder' => 'es. Quack'
	)
);

$table = array(
	'style' => 'table-striped table-hover',
	'layout' => array(
		'name' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-3',
			'align' => 'center',
			'translation' => 'Name',
			'editable' => false
		),
		'owner' => array(
			'type' => 'placeholder',
			'show' => true,
			'width' => 'md-4',
			'align' => 'center',
			'translation' => 'Owner',
			'editable' => false
		),
		'online' => array(
			'type' => 'placeholder',
			'show' => true,
			'width' => 'md-2',
			'align' => 'center',
			'translation' => 'Status',
			'editable' => false
		)
	),
	'actions' => array(
		'_width' => 'md-3',
		'activity' => array(
			'type' => 'default',
			'glyphicon' => 'stats',
			'tooltip' => 'Show Duckiebot activity',
			'text' => 'Activity',
			'function' => array(
				'type' => 'custom',
				'custom_html' => 'onclick="_go_to_link(this)"',
				'arguments' => array('name')
			)
		)
	),
	'features' => array(
		'_actions_column'
	)
);

?>


<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>Duckiebots</h2>
			</td>
		</tr>

	</table>


	<?php

	// parse the arguments
	\system\templates\tableviewers\TableViewer::parseFeatures( $features, $_GET );

	$duckiebots = \system\classes\Core::getDuckiebotsCurrentBranch();

	if( $features['keywords']['value'] != null ){
		$tmp = array();
		foreach( $duckiebots as $b ){
			if (strpos($b, $features['keywords']['value']) !== false) {
				array_push($tmp, $b);
			}
		}
		$duckiebots = $tmp;
	}

	$total_duckiebots = sizeof( $duckiebots );

	$duckiebots = array_slice(
		$duckiebots,
		($features['page']['value']-1)*$features['results']['value'],
		$features['results']['value']
	);

	$res = array(
		'size' => sizeof( $duckiebots ),
		'total' => $total_duckiebots,
		'data' => array()
	);

	$duckiebot_owners = array();

	for( $i = 0; $i < sizeof( $duckiebots ); $i++ ){
		$duckiebot_owner = \system\classes\Core::getDuckiebotOwner( $duckiebots[$i] );
		$duckiebot_owner = strtolower( preg_replace('/ /', '', $duckiebot_owner) );
		$bot_record = array(
			'name' => $duckiebots[$i],
			'owner' => 'owner_'.$duckiebot_owner,
			'online' => 'online_'.$duckiebots[$i]
		);
		array_push( $res['data'], $bot_record );
		// get the owner
		$duckiebot_owners[$duckiebots[$i]] = $duckiebot_owner;
	}


	// <== Here is the Magic Call!

	\system\templates\tableviewers\TableViewer::generateTableViewer( $baseurl, $res, $features, $table );

	?>

</div>


<script type="text/javascript">

	var duckiebots = [
		<?php
		foreach ($duckiebots as $b) {
			echo '{ name: "'.$b.'", owner: "'.$duckiebot_owners[$b].'"},';
		}
		?>
	];

	// create callback function
	function duckiebot_status_callback( result ){
		$('#_format_placeholder_online_'+result.data.name).html(
			( result.data.online )? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true" style="color:green; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Online"></span>' : '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color:red; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Offline"></span>'
		);
	}

	function github_user_info_callback( result ){
		$('#_format_placeholder_owner_'+result.login.toLowerCase()).html(
			(result.name == null)? result.login : result.name
		);
	}

	function error_fcn(owner){
		$('#_format_placeholder_owner_'+owner).html(
			'<span class="glyphicon glyphicon-warning-sign" aria-hidden="true" style="color:red"></span>'
		);
	}

	$(document).ready( function(){
		$.each(duckiebots, function(i) {
			duckiebot = duckiebots[i];
			// is online check
			var url = '<?php echo \system\classes\Configuration::$BASE_URL ?>web-api/<?php echo \system\classes\Configuration::$WEBAPI_VERSION ?>/duckiebot/status/json?name='+duckiebot.name+'&token=<?php echo $_SESSION["TOKEN"] ?>';
			callAPI( url, false, false, duckiebot_status_callback, true );
			// owner name call
			url = 'https://api.github.com/users/'+duckiebot.owner;
			callExternalAPI( url, 'GET', 'json', false, false, github_user_info_callback, true, true, error_fcn, duckiebot.owner );
		});
	} );

	function _go_to_link( target ){
		var duckiebot = $(target).data('name');
		<?php
		$qs = urlencode( base64_encode( toQueryString( array_keys($features), $_GET ) ) );
		?>
		var url = "<?php echo \system\classes\Configuration::$PLATFORM_BASE ?>duckiebots?<?php echo ( (strlen($qs) > 0)? 'lst='.$qs.'&' : '' ) ?>bot="+duckiebot;
		window.location = url;
	}

</script>
