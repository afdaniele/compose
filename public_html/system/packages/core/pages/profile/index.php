<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use \system\classes\Core;
use \system\classes\Configuration;

?>

<div style="width:100%; margin:auto">

  <table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

    <tr>
      <td style="width:50%">
        <h2>Your account</h2>
      </td>
    </tr>

  </table>

  <?php

  // get core info
  $user = Core::getUserLogged();

  // prepare labels and values
  $labelName = array('Name', 'E-mail address', 'Account type' );
  $fieldValue = array( $user['name'], $user['email'], ucfirst($user['role']) );

  // get roles in packages
  $packages = array_keys( Core::getPackagesList() );
  foreach($packages as $package) {
    if( $package == 'core' ) continue;
    $role = Core::getUserRole( $package );
    if( !is_null($role) ){
      array_push($labelName, sprintf('Role (%s)',$package));
      array_push($fieldValue, $role);
    }
  }
  ?>

  <h4>Personal Information</h4>
  <nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
    <div class="container-fluid" style="padding-left:0; padding-right:0">

      <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

        <table style="width:100%">
          <tr>
            <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
              <h3 style="margin:0">
                <div class="text-center col-md-12" id="profile_page_avatar">
                  <?php
                  $picture_url = $user['picture'];
                  if (preg_match('#^https?://#i', $picture_url) !== 1) {
                    $picture_url = sanitize_url(sprintf(
                      "%s%s", Configuration::$BASE, $picture_url
                    ));
                  }
                  ?>
                  <img src="<?php echo $picture_url; ?>" id="avatar">
                </div>
              </h3>
            </td>
            <td class="col-md-9" style="padding:20px">
              <?php
              generateView( $labelName, $fieldValue, 'md-3', 'md-9' );
              ?>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </nav>

  <?php
  // get list of profile plugins files
  $profile_addon_files_per_pkg = Core::getPackagesModules('profile');

  // render separator
  $num_addons = array_sum(array_map(count, array_values($profile_addon_files_per_pkg)));
  if($num_addons > 0){
    echo '<legend style="width: 100px; margin: 20px auto"></legend>';
  }

  // render add-ons
  foreach ($profile_addon_files_per_pkg as $pkg_id => $profile_addon_files) {
    foreach ($profile_addon_files as $profile_addon_file) {
      require_once $profile_addon_file;
    }
  }
  ?>

</div>
