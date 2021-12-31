<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use JetBrains\PhpStorm\Pure;
use \system\classes\Core;
use \system\classes\Configuration;

// load libraries
require_once $GLOBALS['__SYSTEM__DIR__'] . 'templates/tableviewers/TableViewer.php';

use \system\templates\tableviewers\TableViewer;


// this sections supports multiple modes
//
//  - /: we show the list of users and allow for user edit actions
//  - groups/link: we show the list of users that are not members of the group and allow for link
//  - groups/members: we show the list of members of a group and allow for unlink
//
$modes = [
    "/" => [
        "resource" => "users",
        "description" => null
    ],
    "groups/link" => [
        "resource" => "users/groups/link/%s",
        "description" => "Select the users that you want to add to the group <strong>%s</strong> (<span style='font-family: monospace'>%s</span>)."
    ],
    "groups/members" => [
        "resource" => "users/groups/members/%s",
        "description" => "Members of the group <strong>%s</strong> (<span style='font-family: monospace'>%s</span>) are shown in the list below."
    ]
];

// define utility functions
#[Pure] function _avatar_url($path) {
    return startsWith($path, 'http') ? $path : Configuration::$BASE . $path;
}

// define current mode
$mode = '/';
$group = null;
$group_info = null;
if (Configuration::$ACTION == 'groups' && in_array(Configuration::$ARG1, ['link', 'members'])) {
    $group = Configuration::$ARG2;
    // try to load info about given group
    $group_info = Core::getGroupInfo($group);
    $mode = sprintf('%s/%s', Configuration::$ACTION, Configuration::$ARG1);
}
$current_resource = sprintf($modes[$mode]['resource'], $group);

// show return to link and mode description
if (!is_null($group)) {
    $lst_args = isset($_GET['lst']) ? base64_decode($_GET['lst']) : '';
    ?>
    <p style="margin-top:-30px; margin-bottom:30px">
        <a href="<?php echo Core::getURL(Configuration::$PAGE, Configuration::$ACTION, null, null, $lst_args) ?>">
            &larr; Back to Groups
        </a>
    </p>

    <p style="margin:40px 0;">
        <?php printf($modes[$mode]['description'], $group_info['name'], $group) ?>
    </p>
    <?php
}

// different modes will show different actions
$mode_to_actions = [
    "/" => [
        'edit' => [
            'type' => 'primary',
            'icon' => 'pencil',
            'tooltip' => 'Edit user account',
            'text' => 'Edit',
            'function' => [
                'type' => '_toggle_modal',
                'target' => 'record-editor-modal',
                'static_data' => ['modal-mode' => 'edit'],
                'API_resource' => 'userprofile',
                'API_action' => 'edit',
                'arguments' => [
                    'user'
                ]
            ]
        ],
        'groups' => [
            'type' => 'secondary',
            'icon' => 'list',
            'tooltip' => 'Groups this user is a member of',
            'text' => 'Groups',
            'function' => [
                'type' => 'custom',
                'custom_html' => 'onclick="_open_groups(this)"',
                'arguments' => [
                    'user'
                ]
            ]
        ]
    ],
    "groups/link" => [
        'link' => [
            'type' => 'success',
            'icon' => 'plus',
            'tooltip' => 'Add user to group',
            'text' => 'Add to group',
            'function' => [
                'type' => '_toggle_modal',
                'target' => 'yes-no-modal',
                'API_resource' => 'usergroup',
                'API_action' => 'link',
                'arguments' => [
                    'group',
                    'user'
                ],
                'static_data' => [
                    'question' => 'Do you confirm <strong>adding</strong> this user to the group?'
                ]
            ]
        ]
    ],
    "groups/members" => [
        'unlink' => [
            'type' => 'danger',
            'icon' => 'dash',
            'tooltip' => 'Remove user from group',
            'text' => 'Remove from group',
            'function' => [
                'type' => '_toggle_modal',
                'class' => 'yes-no-modal',
                'API_resource' => 'usergroup',
                'API_action' => 'unlink',
                'arguments' => [
                    'group',
                    'user'
                ],
                'static_data' => [
                    'question' => 'Do you confirm <strong>removing</strong> this user from the group?'
                ]
            ]
        ]
    ]
];

// define table features
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
        'placeholder' => 'e.g., Andrea'
    ]
];

$table = [
    'style' => 'table-striped table-hover',
    'layout' => [
        'group' => [
            'type' => 'text',
            'show' => false
        ],
        'user' => [
            'type' => 'text',
            'show' => false
        ],
        'avatar' => [
            'type' => 'avatar_image_small',
            'show' => true,
            'width' => 'md-1',
            'align' => 'center',
            'translation' => '',
            'editable' => false
        ],
        'name' => [
            'type' => 'text',
            'show' => true,
            'width' => 'md-4',
            'align' => 'left',
            'translation' => 'Name',
            'editable' => false
        ],
        'shown_role' => [
            'type' => 'text',
            'show' => true,
            'width' => 'md-2',
            'align' => 'center',
            'translation' => 'Role',
            'editable' => false
        ],
        'role' => [
            'type' => 'text',
            'show' => false,
            'editable' => true
        ],
        'active' => [
            'type' => 'boolean',
            'show' => true,
            'width' => 'md-1',
            'align' => 'center',
            'translation' => 'Enabled',
            'editable' => true
        ]
    ],
    'actions' => array_merge([
        '_width' => 'md-3',
    ], $mode_to_actions[$mode]),
    'features' => [
        '_counter_column',
        (Core::getUserLogged('role') == 'administrator') ? '_actions_column' : ''
    ]
];

// parse the arguments
TableViewer::parseFeatures($features, $_GET);

$users = [];
switch ($mode) {
    case '/':
        $users = Core::getUsersList();
        break;
    case 'groups/link':
        $users = Core::getUsersList();
        // get group members
        $members = Core::getGroupMembers($group);
        // retain only users that are not members yet
        $users = array_diff($users, $members);
        break;
    case 'groups/members':
        // get group members
        $users = Core::getGroupMembers($group);
        break;
}


$tmp = [];
foreach ($users as $user_id) {
    $user_info = Core::getUserInfo($user_id);
    // compile user record
    $user_record = [
        'user' => $user_id,
        'avatar' => _avatar_url($user_info['picture']),
        'name' => $user_info['name'],
        'shown_role' => ucfirst($user_info['role']),
        'role' => $user_info['role'],
        'active' => $user_info['active'],
        'group' => $group
    ];
    $tmp[] = $user_record;
}
$users = $tmp;

// filter based on keywords (if needed)
if ($features['keywords']['value'] != null) {
    $tmp = array();
    foreach ($users as $user) {
        if (str_contains(strtolower($user['name']), strtolower($features['keywords']['value']))) {
            $tmp[] = $user;
        }
    }
    $users = $tmp;
}

// compute total number of users for pagination purposes
$total_users = sizeof($users);

// take the slice corresponding to the selected page
$users = array_slice(
    $users,
    ($features['page']['value'] - 1) * $features['results']['value'],
    $features['results']['value']
);

// prepare data for the table viewer
$res = [
    'size' => sizeof($users),
    'total' => $total_users,
    'data' => $users
];

// <== Here is the Magic Call!
TableViewer::generateTableViewer($current_resource, $res, $features, $table);


if ($mode == '/') {
    // load editor modal library
    require_once join_path(Core::getPackageDetails('core', 'root'), 'modules', 'modals', 'record_editor_modal.php');
    // ---
    $roles = array_values(array_diff(Core::getPackageRegisteredUserRoles(), ['guest']));
    $user_edit_form_schema = [
        'name' => [
            'description' => 'Full name of the user',
            'type' => 'text'
        ],
        'active' => [
            'description' => 'Whether the user is enabled',
            'type' => 'boolean'
        ],
        'role' => [
            'description' => 'User\'s role',
            'type' => 'text',
            'enum' => $roles
        ]
    ];
    $user_edit_form_ui = [
        [
            'key' => 'name',
            'title' => 'Name',
            'readOnly' => true
        ],
        [
            'key' => 'active',
            'title' => 'Active',
        ],
        [
            'key' => 'role',
            'title' => 'Role',
            "titleMap" => array_combine($roles, array_map("ucfirst", $roles))
        ]
    ];
    // generate modal
    generateRecordEditorModal($user_edit_form_schema, method: 'POST', ui: $user_edit_form_ui);
}
?>

<script type="text/javascript">
    let args = "<?php echo base64_encode(toQueryString(array_keys($features), $_GET)) ?>";
    let url = "<?php echo Core::getURL('users', 'groups', 'user', '{0}', ['lst' => '{1}']) ?>";

    function _open_groups(target) {
        let user = $(target).data('user');
        // open groups
        location.href = url.format(user, args);
    }
</script>