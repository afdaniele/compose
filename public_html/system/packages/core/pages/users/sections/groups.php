<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use system\classes\Core;
use system\classes\Configuration;

// load libraries
require_once join_path(Core::getPackageDetails('core', 'root'), 'modules', 'modals', 'record_editor_modal.php');
require_once $GLOBALS['__SYSTEM__DIR__'] . 'templates/tableviewers/TableViewer.php';

use system\templates\tableviewers\TableViewer;


?>

<style>
    #new-group-btn {
        margin-top: -10px;
    }
</style>

<?php

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
    $user_info = Core::getUserInfo($user);
    $mode = sprintf('%s/%s', Configuration::$ACTION, Configuration::$ARG1);
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
    <div class="col-12 text-right">
        <button type="button" class="btn btn-warning btn-sm"
                id="new-group-btn"
                data-bs-toggle="modal"
                data-bs-target="#record-editor-modal-new-group"
                data-placement="bottom"
                data-original-title="Edit user account"
                data-modal-mode="new"
                data-record=""
                data-url="<?php echo $new_group_api_epoint_url ?>">
            <i class="bi bi-asterisk"></i>
            New Group
        </button>
    </div>
    <br>
    <?php
}

// different modes will show different actions
$mode_to_actions = [
    "groups/" => [
        'link' => [
            'type' => 'success',
            'icon' => 'plus',
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
            'icon' => 'trash',
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

$features = [
    'page' => [
        'type' => 'integer',
        'default' => 1,
        'values' => null,
        'minvalue' => 1,
        'maxvalue' => PHP_INT_MAX
    ],
    'results' => [
        'type' => 'integer',
        'default' => 10,
        'values' => null,
        'minvalue' => 1,
        'maxvalue' => PHP_INT_MAX
    ],
    'keywords' => [
        'type' => 'text',
        'default' => null,
        'placeholder' => 'e.g., My Group'
    ]
];

$table = [
    'style' => 'table-striped table-hover',
    'layout' => [
        'group' => [
            'type' => 'text',
            'show' => false
        ],
        'name' => [
            'type' => 'text',
            'show' => true,
            'width' => 'md-3',
            'align' => 'left',
            'translation' => 'Name',
            'editable' => false
        ],
        'creation-time' => [
            'type' => 'text',
            'show' => true,
            'width' => 'md-3',
            'align' => 'center',
            'translation' => 'Creation Time',
            'editable' => false
        ]
    ],
    'actions' => array_merge([
        '_width' => 'md-5',
        'members' => [
            'type' => 'secondary',
            'icon' => 'list',
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
    'features' => [
        '_counter_column',
        (Core::getUserLogged('role') == 'administrator') ? '_actions_column' : ''
    ]
];

// parse the arguments
TableViewer::parseFeatures($features, $_GET);

$groups = Core::getGroupsList();

$tmp = [];
foreach ($groups as $group_id) {
    // get group info
    $group_info = Core::getGroupInfo($group_id);
    // compile group records
    $group_record = [
        'group' => $group_id,
        'name' => $group_info['name'],
        'creation-time' => date("Y-m-d H:i:s", $group_info['creation-time'])
    ];
    // ---
    $tmp[] = $group_record;
}
$groups = $tmp;

// filter based on keywords (if needed)
if ($features['keywords']['value'] != null) {
    $tmp = [];
    foreach ($groups as $group) {
        if (str_contains(strtolower($group['name']), strtolower($features['keywords']['value']))) {
            $tmp[] = $group;
        }
    }
    $groups = $tmp;
}

// filter if this is the list of groups a specific user belongs to
if (!is_null($user)) {
    $tmp = [];
    foreach ($groups as $group) {
        $members = Core::getGroupMembers($group['group']);
        if (in_array($user, $members)) {
            // this user IS a member of this group, retain the group
            $tmp[] = $group;
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
$res = [
    'size' => sizeof($groups),
    'total' => $total_users,
    'data' => $groups
];

// <== Here is the Magic Call!
TableViewer::generateTableViewer($current_resource, $res, $features, $table);


if ($mode == 'groups/') {
    $new_group_form_schema = [
        'name' => [
            'description' => 'Name of the group',
            'type' => 'text'
        ],
        'description' => [
            'description' => 'Description of the group',
            'type' => 'text'
        ]
    ];
    $new_group_form_ui = [
        [
            'key' => 'name',
            'title' => 'Name'
        ],
        [
            'key' => 'description',
            'title' => 'Description',
        ]
    ];
    // generate modal
    generateRecordEditorModal($new_group_form_schema, id: 'new-group', method: 'POST', ui: $new_group_form_ui);
}
?>

<script type="text/javascript">
    let args = "<?php echo base64_encode(toQueryString(array_keys($features), $_GET)) ?>";
    let url = "<?php echo Core::getURL('users', 'groups', '{0}', '{1}', ['lst' => '{2}']) ?>";

    function _open_group(target) {
        let group = $(target).data('group');
        // open tool
        _open_group_tool('members', group);
    }

    function _open_link(target) {
        let group = $(target).data('group');
        // open tool
        _open_group_tool('link', group);
    }

    function _open_group_tool(tool, group) {
        location.href = url.format(tool, group, args);
    }
</script>
