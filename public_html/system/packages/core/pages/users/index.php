<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;

$modes = [
    "/" => [
        "main" => "users",
        "section" => "users"
    ],
    "groups/" => [
        "main" => "groups",
        "section" => "groups"
    ],
    "groups/user" => [
        "main" => "users",
        "section" => "groups"
    ],
    "groups/link" => [
        "main" => "groups",
        "section" => "users"
    ],
    "groups/members" => [
        "main" => "groups",
        "section" => "users"
    ]
];
$section_sel = sprintf('%s/%s', Configuration::$ACTION, Configuration::$ARG1);

// redirect to /users if the given arguments do not identify a mode
if (!array_key_exists($section_sel, $modes)) {
    Core::redirectTo('users');
    return;
}

// get main and section file to load
$main = $modes[$section_sel]['main'];
$section = $modes[$section_sel]['section'];

// create title
$title_fmt = '<%s href="%s" class="%s" style="float:%s">%s</%s>';
$mains = [
    'users' => ['position' => 'left', 'url' => Core::getURL('users')],
    '/' => null,
    'groups' => ['position' => 'right', 'url' => Core::getURL('users', 'groups')]
];
?>
<h2 class="page-title-static text-center" style="display: block">
    <?php
    foreach ($mains as $mkey => $mdata) {
        $type = ($main == $mkey || is_null($mdata)) ? 'span' : 'a';
        $class = ($main == $mkey) ? 'text-bold' : '';
        $url = $mdata['url'] ?? "";
        $position = $mdata['position'] ?? "";
        $key = ucfirst($mkey);
        printf($title_fmt, $type, $url, $class, $position, $key, $type);
    }
    ?>
</h2>


<?php
include_once sprintf('%s/sections/%s.php', __DIR__, $section);
?>