<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Saturday, January 13th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, January 14th 2018

use \system\classes\Core;

function settings_user_roles_tab(){
?>

    <p>
        The following table shows all the user roles registered on the platform.
    </p>
    <div class="text-center" style="padding:10px 0">
        <table class="table table-bordered table-striped" style="margin:auto">
            <tr style="font-weight:bold">
                <td class="col-md-1">#</td>
                <td class="col-md-3">Package</td>
                <td class="col-md-4">Role</td>
                <td class="col-md-4">Default page</td>
            </tr>
            <?php
            $packages = array_keys( Core::getPackagesList() );
            sort($packages);

            $i = 1;
            foreach($packages as $package) {
                $roles = Core::getUserRolesList( $package );
                foreach($roles as $role) {
                    $default_page = Core::getDefaultPagePerRole($role, $package);
                    ?>
                    <tr>
                        <td><?php echo $i ?></td>
                        <td><?php echo $package ?></td>
                        <td><?php echo $role ?></td>
                        <td><?php echo $default_page ?></td>
                    </tr>
                    <?php
                    $i += 1;
                }
            }
            ?>
        </table>
    </div>

<?php
}
?>
