<?php
use system\classes\Core;

function _pages_avail_to_role ($args) {
    $user_role = $args[0];
    // get the list of pages the given user has (direct) access to
    $pagesList = Core::getFilteredPagesList(
        'by-menuorder', /* use menu-order here to get only pages that are directly accessible to the user
        (i.e., exclude special pages like 'error') */
        true /* enabledOnly */,
        $user_role /* accessibleBy */
    );
    $availablePages = array_map(
        function($p){
            return [
                'id' => $p['id'],
                'value' => $p['id'],
                'label' => $p['name'].' ('.$p['package'].')'
            ];
        },
        $pagesList
    );
    $factory_default = Core::getFactoryDefaultPagePerRole( $user_role );
    array_push(
        $availablePages,
        ['id' => '_not_found', 'value' => $factory_default, 'label' => 'PAGE NOT FOUND']
    );
    // return list
    return $availablePages;
}//_pages_avail_to_role


function _installed_themes($args){
    $res = Core::getPackagesModules('theme');
    $themes = [];
    foreach ($res as $pkg => $files) {
        foreach ($files as $file) {
            $name = basename(dirname($file));
            $id = sprintf("%s__%s", $pkg, $name);
            $value = sprintf("%s:%s", $pkg, $name);
            $label = sprintf("%s (%s)", ucfirst($name), $pkg);
            array_push($themes, ['id' => $id, 'value' => $value, 'label' => $label]);
        }
    }
    return $themes;
}//_installed_themes


function _available_favicons ($args) {
    $icon_pkgs = [
        ['id' => 'core', 'value' => 'core', 'label' => 'Default']
    ];
    foreach (array_keys(Core::getPackagesList()) as $pkg_id) {
        $pkg_root = Core::getPackageRootDir($pkg_id);
        $icon_path = join_path($pkg_root, 'images', 'favicon.ico');
        if (file_exists($icon_path)) {
            $label = Core::getPackageDetails($pkg_id, 'name');
            array_push($icon_pkgs, ['id' => $pkg_id, 'value' => $pkg_id, 'label' => $label]);
        }
    }
    return $icon_pkgs;
}//_available_favicons


function _static_enum ($args) {
    return array_map(
        function($a){
            return [
                'id' => $a,
                'value' => $a,
                'label' => $a
            ];
        },
        $args
    );
}//_static_enum

?>
