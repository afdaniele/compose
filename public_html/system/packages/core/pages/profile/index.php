<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use \system\classes\Core;
use \system\classes\Configuration;

?>

<h2 class="page-title"></h2>

<?php

// get core info
$user = Core::getUserLogged();

// prepare labels and values
$labelName = array('Name', 'E-mail address', 'Account type');
$fieldValue = array($user['name'], $user['email'], ucfirst($user['role']));

// get roles in packages
$packages = array_keys(Core::getPackagesList());
foreach ($packages as $package) {
    if ($package == 'core') {
        continue;
    }
    $role = Core::getUserRole($package);
    if (!is_null($role)) {
        array_push($labelName, sprintf('Role (%s)', $package));
        array_push($fieldValue, $role);
    }
}

$user_name = $user['name'];
$roles = ["{$user['role']}"];
foreach ($user['pkg_role'] as $pkg => $role) {
    array_push($roles, "{$role} ($pkg)");
}
$user_roles = implode(", ", $roles);
?>

<div class="card" style="width: 100%">
    <div class="card-body">
        <h5 class="card-title"><?php echo $user_name ?></h5>
        <h6 class="card-subtitle mb-2 text-muted"><?php echo $user_roles ?></h6>
        <hr/>
        
        <div class="container">
            <div class="row">
                <div class="col-2">
                    <?php
                    $picture_url = $user['picture'];
                    if (preg_match('#^https?://#i', $picture_url) !== 1) {
                        $picture_url = sanitize_url(sprintf(
                            "%s%s", Configuration::$BASE, $picture_url
                        ));
                    }
                    ?>
                    <img class="img-fluid" src="<?php echo $picture_url; ?>" id="avatar" alt="">
                </div>
                <div class="col-1"></div>
                <div class="col-9" style="margin: auto">
                    <?php
                    generateView($labelName, $fieldValue, 3, 9);
                    ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
// get list of profile plugins files
$profile_addon_files_per_pkg = Core::getPackagesModules('profile');

// render separator
$num_addons = array_sum(array_map("count", array_values($profile_addon_files_per_pkg)));
if ($num_addons > 0) {
    echo '<hr/>';
}

// render add-ons
foreach ($profile_addon_files_per_pkg as $pkg_id => $profile_addon_files) {
    foreach ($profile_addon_files as $profile_addon_file) {
        require_once $profile_addon_file;
    }
}
?>

