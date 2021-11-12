<?php

use \system\classes\Core;

// load libraries
require_once join_path(Core::getPackageDetails('core', 'root'), 'modules', 'modals', 'record_editor_modal.php');
require_once $GLOBALS['__SYSTEM__DIR__'].'/templates/tableviewers/TableViewer.php';
require_once $GLOBALS['__SYSTEM__DIR__'].'/classes/RESTfulAPI.php';

use \system\templates\tableviewers\TableViewer;
use \system\classes\Configuration;
use \system\classes\RESTfulAPI;
?>

<div class="api-breadcrumb">
    <table style="width:100%">
        <tr>
            <td>
                <h3 class="text-left" style="margin:0">
                    <span class="bi bi-key" aria-hidden="true"></span>&nbsp;
                    <span class="mono">API Keys</span>
                    <button type="button" class="btn btn-warning" id="api-page-new-key-button" style="float:right"
                        data-toggle="tooltip dialog" data-target="#record-editor-modal-insert-form"
                        data-url="<?php echo sprintf(
                            '%sweb-api/%s/api/app_create/json?',
                            Configuration::$BASE,
                            Configuration::$WEBAPI_VERSION) ?>"
                        >
                        <span class="bi bi-plus" aria-hidden="true"></span>
                        &nbsp;
                        Create new Application
                    </button>
                </h3>
            </td>
        </tr>
    </table>
</div>
<br/>

<?php

// declare table structure
$table = array(
	'style' => 'table-striped table-hover',
	'layout' => array(
		'id' => array(
			'type' => 'text',
			'show' => false,
            'name' => 'Application ID',
			'editable' => false
		),
		'name' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-5',
			'align' => 'left',
            'name' => 'Application name',
			'translation' => 'Application name',
			'editable' => false
		),
		'num_endpoints' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-2',
			'align' => 'center',
            'name' => 'End-points',
			'translation' => 'End-points',
            'editable' => false,
			'hidden' => true
		),
		'enabled' => array(
			'type' => 'boolean',
			'show' => true,
			'width' => 'md-1',
			'align' => 'center',
            'name' => 'Enabled',
			'translation' => 'Enabled',
			'editable' => true
		)
	),
	'actions' => array(
		'_width' => 'md-3',
		'edit' => array(
			'type' => 'default',
			'glyphicon' => 'open',
			'tooltip' => 'Open',
			'text' => 'Open',
			'function' => array(
                'type' => '_toggle_modal',
				'class' => 'record-editor-modal',
                'static_data' => ['modal-mode' => 'edit'],
                'API_resource' => 'api',
                'API_action' => 'app_update'
			)
		),
        'separator' => array(
            'type' => 'separator'
        ),
        'delete' => array(
			'type' => 'danger',
			'glyphicon' => 'trash',
			'tooltip' => 'Delete application',
            'function' => array(
                'type' => '_toggle_modal',
				'class' => 'yes-no-modal',
                'API_resource' => 'api',
                'API_action' => 'app_delete',
                'arguments' => [
                    'id'
                ],
                'static_data' => [
                    'question' => 'Are you sure you want to delete this application?'
                ]
			)
		)
	),
	'features' => array(
		'_counter_column',
		'_actions_column'
	)
);

$form_insert = [
    'name' => array(
        'name' => 'Application name',
        'editable' => true,
    	'type' => 'text'
    ),
    'enabled' => array(
        'name' => 'Enabled',
        'editable' => true,
    	'type' => 'boolean'
    )
];

$form_edit = [
    'name' => array(
        'name' => 'Application name',
        'editable' => false,
    	'type' => 'text'
    ),
    'id' => array(
        'name' => 'Application ID',
        'editable' => false,
        'type' => 'text'
    ),
    'secret' => array(
        'name' => 'Application Secret Key',
        'editable' => false,
    	'type' => 'text'
    ),
    'enabled' => array(
        'name' => 'Enabled',
        'editable' => true,
    	'type' => 'boolean'
    )
];

// add one option for each service/action pair
$user_role = Core::getUserLogged('role');
foreach( RESTfulAPI::getConfiguration() as $pkg_id => &$pkg_api ){
    foreach( $pkg_api['services'] as $service_id => &$service_config ){
        foreach( $service_config['actions'] as $action_id => &$action_config ){
            if( !in_array('app', $action_config['authentication']) ) continue;
            if( in_array($user_role, $action_config['access_level']) ){
                $pair = sprintf('%s__%s', $service_id, $action_id);
                $form_edit[$pair] = array(
                    'name' => '<span class="bi bi-plug" aria-hidden="true"></span>&nbsp;API <span class="mono" style="font-weight:normal">'.sprintf('%s/%s', $service_id, $action_id).'</span>',
                    'editable' => true,
                    'type' => 'boolean'
                );
            }
        }
    }
}

// get list of applications
$res = RESTfulAPI::getUserApplications( Core::getUserLogged('username') );
if( !$res['success'] ){
    Core::throwError( $res['data'] );
}
$applications = $res['data'];

// add num_endpoints to the entries, convert endpoints from `service/action` to `service__action`
foreach( $applications as &$app ){
    $app['num_endpoints'] = count($app['endpoints']);
    foreach($app['endpoints'] as $act) {
        $pair = str_replace('/', '__',$act);
        $app[$pair] = true;
    }
}

// compute total number of applications for pagination purposes
$total_apps = count( $applications );

// prepare data for the table viewer
$res = [
	'size' => count($applications),
	'total' => $total_apps,
	'data' => $applications
];
?>

The following is a list of all your API Applications. An API Application
authorizes third-party applications to interact with <strong>\compose\</strong>
on your behalf.
<br/><br/>

<strong>IMPORTANT:</strong> Do not share the <strong>Secret key</strong> of your
applications with others or write it in plain in shared files.
<br/>

<?php

// <== Here is the Magic Call!
TableViewer::generateTableViewer( Configuration::$PAGE, $res, null, $table );

// generate record editor modal
generateRecordEditorModal( $form_edit, $formID='the-form', $method='POST' );
generateRecordEditorModal( $form_insert, $formID='insert-form', $method='POST' );
?>
