<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;

// load libraries
require_once join_path(Core::getPackageDetails('core', 'root'), 'modules', 'modals', 'record_editor_modal.php');
require_once $GLOBALS['__SYSTEM__DIR__'] . 'templates/tableviewers/TableViewer.php';

use \system\templates\tableviewers\TableViewer;


// this sections supports multiple modes
//
//  - groups/: we show the list of groups and allow for group edit actions
//  - groups/user: we show the list of groups that a given user is a member of
//
$modes = [
    "groups/" => [
        "resource" => "users/groups",
        "description" => null
    ],
    "groups/user" => [
        "resource" => "users/groups/user/%s",
        "description" => "This is the list of groups the user <strong>%s</strong> is a member of."
    ]
];

// define current mode
$mode = 'groups/';
$user = null;
$user_info = null;
if (Configuration::$ARG1 == 'user') {
    $user = Configuration::$ARG2;
    // try to load info about given group
    $res = Core::getUserInfo($user);
    if (!$res['success']) {
        Core::throwError($res['data']);
        return;
    }
    $mode = sprintf('%s/%s', Configuration::$ACTION, Configuration::$ARG1);
    $user_info = $res['data'];
}
$current_resource = sprintf($modes[$mode]['resource'], $user);

// show return to link and mode description
if (!is_null($user)) {
    $lst_args = isset($_GET['lst']) ? base64_decode($_GET['lst']) : '';
    ?>
    <p style="margin-top:-30px; margin-bottom:30px">
        <a href="<?php echo Core::getURL(Configuration::$PAGE, null, null, null, $lst_args) ?>">
            &larr; Back to Users
        </a>
    </p>
    
    <p style="margin:40px 0;">
        <?php printf($modes[$mode]['description'], $user_info['name'], $user) ?>
    </p>
    <?php
} else {
    $new_group_api_epoint_url = Core::getAPIurl('usergroup', 'create');
    ?>
    <button class="btn btn-warning" type="button" data-toggle="tooltip dialog"
            data-placement="bottom" data-original-title="Edit user account"
            data-modal-mode="new" data-target="#record-editor-modal-new-group-form"
            data-record=""
            data-url="<?php echo $new_group_api_epoint_url ?>"
            style="margin-bottom: 30px; float: right">
        <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
        &nbsp;New Group
    </button>
    <?php
}

// different modes will show different actions
$mode_to_actions = [
    "groups/" => [
        'link' => [
            'type' => 'success',
            'glyphicon' => 'plus',
            'tooltip' => 'Add users to group',
            'text' => 'Add member',
            'function' => [
                'type' => 'custom',
                'custom_html' => 'onclick="_open_link(this)"',
                'arguments' => [
                    'group'
                ]
            ]
        ],
        'delete' => [
            'type' => 'danger',
            'glyphicon' => 'trash',
            'tooltip' => 'Remove group',
            'text' => 'Delete',
            'function' => [
                'type' => '_toggle_modal',
                'class' => 'yes-no-modal',
                'API_resource' => 'usergroup',
                'API_action' => 'delete',
                'arguments' => [
                    'group'
                ],
                'static_data' => [
                    'question' => 'Are you sure you want to <strong>DELETE</strong> this group? This cannot be undone.'
                ]
            ]
        ]
    ],
    "groups/user" => []
];

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
        'placeholder' => 'e.g., My Group'
    )
);

$table = array(
    'style' => 'table-striped table-hover',
    'layout' => array(
        'group' => array(
            'type' => 'text',
            'show' => false
        ),
        'name' => array(
            'type' => 'text',
            'show' => true,
            'width' => 'md-3',
            'align' => 'left',
            'translation' => 'Name',
            'editable' => false
        ),
        'creation-time' => array(
            'type' => 'text',
            'show' => true,
            'width' => 'md-3',
            'align' => 'center',
            'translation' => 'Creation Time',
            'editable' => false
        )
    ),
    'actions' => array_merge([
        '_width' => 'md-5',
        'members' => [
            'type' => 'default',
            'glyphicon' => 'list-alt',
            'tooltip' => 'Show list of members of this group',
            'text' => 'Members',
            'function' => [
                'type' => 'custom',
                'custom_html' => 'onclick="_open_group(this)"',
                'arguments' => [
                    'group'
                ]
            ]
        ]
    ], $mode_to_actions[$mode]),
    'features' => array(
        '_counter_column',
        (Core::getUserLogged('role') == 'administrator') ? '_actions_column' : ''
    )
);

// parse the arguments
TableViewer::parseFeatures($features, $_GET);

$groups = Core::getGroupsList();

$tmp = [];
foreach ($groups as $group_id) {
    // get group info
    $res = Core::getGroupInfo($group_id);
    if (!$res['success']) {
        Core::throwError($res['data']);
    }
    $group_info = $res['data'];
    // compile group records
    $group_record = [
        'group' => $group_id,
        'name' => $group_info['name'],
        'creation-time' => date("Y-m-d H:i:s", $group_info['creation-time'])
    ];
    // ---
    array_push($tmp, $group_record);
}
$groups = $tmp;

// filter based on keywords (if needed)
if ($features['keywords']['value'] != null) {
    $tmp = array();
    foreach ($groups as $group) {
        if (strpos(strtolower($group['name']), strtolower($features['keywords']['value'])) !== false) {
            array_push($tmp, $group);
        }
    }
    $groups = $tmp;
}

// filter if this is the list of groups a specific user belongs to
if (!is_null($user)) {
    $tmp = [];
    foreach ($groups as $group) {
        $res = Core::getGroupMembers($group['group']);
        if (!$res['success']) {
            Core::throwError($res['data']);
        }
        if (in_array($user, $res['data'])) {
            // this user IS a member of this group, retain the group
            array_push($tmp, $group);
        }
    }
    $groups = $tmp;
}

// compute total number of users for pagination purposes
$total_users = sizeof($groups);

// take the slice corresponding to the selected page
$groups = array_slice(
    $groups,
    ($features['page']['value'] - 1) * $features['results']['value'],
    $features['results']['value']
);

// prepare data for the table viewer
$res = array(
    'size' => sizeof($groups),
    'total' => $total_users,
    'data' => $groups
);

// <== Here is the Magic Call!
TableViewer::generateTableViewer($current_resource, $res, $features, $table);


if ($mode == 'groups/') {
    // load editor modal library
    require_once join_path(Core::getPackageDetails('core', 'root'), 'modules', 'modals', 'record_editor_modal.php');
    // ---
    $new_group_form = [
        'name' => [
            'name' => 'Name',
            'type' => 'text',
            'editable' => true
        ],
        'description' => [
            'name' => 'Description',
            'type' => 'text',
            'editable' => true
        ]
    ];
    generateRecordEditorModal($new_group_form, $formID='new-group-form', $method='POST');
}
?>

<script type="text/javascript">
	let args = "<?php echo base64_encode(toQueryString(array_keys($features), $_GET)) ?>";

	function _open_group(target){
		let group = $(target).data('group');
		// open tool
        _open_group_tool('members', group);
	}

	function _open_link(target){
		let group = $(target).data('group');
		// open tool
        _open_group_tool('link', group);
	}
	
	function _open_group_tool(tool, group) {
		let url = "<?php echo Core::getURL('users', 'groups', '{0}', '{1}', ['lst' => '{2}']) ?>".format(tool, group, args);
		location.href = url;
    }
</script>
