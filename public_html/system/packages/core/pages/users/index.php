<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;

// load libraries
require_once join_path(Core::getPackageDetails('core', 'root'), 'modules', 'modals', 'record_editor_modal.php');
require_once $GLOBALS['__SYSTEM__DIR__'].'templates/tableviewers/TableViewer.php';

use \system\templates\tableviewers\TableViewer;


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
		'placeholder' => 'e.g., Andrea'
	)
);

$table = array(
	'style' => 'table-striped table-hover',
	'layout' => array(
		'userid' => array(
			'type' => 'text',
			'show' => false
		),
		'avatar' => array(
			'type' => 'avatar_image_small',
			'show' => true,
			'width' => 'md-1',
			'align' => 'center',
			'translation' => '',
			'editable' => false
		),
		'name' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-4',
			'align' => 'left',
			'translation' => 'Name',
			'editable' => false
		),
		'role' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-2',
			'align' => 'center',
			'translation' => 'Role',
			'editable' => false
		),
		'active' => array(
			'type' => 'boolean',
			'show' => true,
			'width' => 'md-1',
			'align' => 'center',
			'translation' => 'Enabled',
			'editable' => true
		)
	),
	'actions' => array(
		'_width' => 'md-3',
		'edit' => array(
			'type' => 'default',
			'glyphicon' => 'pencil',
			'tooltip' => 'Edit user account',
			'text' => 'Edit',
			'function' => array(
                'type' => '_toggle_modal',
				'class' => 'record-editor-modal',
                'static_data' => ['modal-mode' => 'edit'],
                'API_resource' => 'userprofile',
                'API_action' => 'edit',
				'arguments' => [
                    'userid'
                ]
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
				<h2>Users</h2>
			</td>
		</tr>

	</table>


	<?php

	// parse the arguments
	TableViewer::parseFeatures( $features, $_GET );

	$users = Core::getUsersList();

	$tmp = [];
	for( $i = 0; $i < sizeof( $users ); $i++ ){
		$user_id = $users[$i];
		$res = Core::getUserInfo( $user_id );
		if( !$res['success'] ){
			Core::throwError( $res['data'] );
		}
		$user_info = $res['data'];
		//
		$user_record = [
			'userid' => $user_id,
			'avatar' => $user_info['picture'],
			'name' => $user_info['name'],
			'role' => ucfirst($user_info['role']),
			'active' => $user_info['active']
		];
		array_push( $tmp, $user_record );
	}
	$users = $tmp;

	// filter based on keywords (if needed)
	if( $features['keywords']['value'] != null ){
		$tmp = array();
		foreach( $users as $user ){
			if (strpos( strtolower($user['name']), strtolower($features['keywords']['value']) ) !== false) {
				array_push($tmp, $user);
			}
		}
		$users = $tmp;
	}

	// compute total number of users for pagination purposes
	$total_users = sizeof( $users );

	// take the slice corresponding to the selected page
	$users = array_slice(
		$users,
		($features['page']['value']-1)*$features['results']['value'],
		$features['results']['value']
	);

	// prepare data for the table viewer
	$res = array(
		'size' => sizeof( $users ),
		'total' => $total_users,
		'data' => $users
	);

	// <== Here is the Magic Call!
	TableViewer::generateTableViewer( \system\classes\Configuration::$PAGE, $res, $features, $table );

	$user_edit_form = [
		'name' => [
			'name' => 'Name',
			'type' => 'text',
			'editable' => false
		],
		'active' => [
			'name' => 'Enabled',
			'type' => 'boolean',
			'editable' => true
		]
	];
	generateRecordEditorModal( $user_edit_form, $formID='the-form', $method='POST' );
	?>

</div>
