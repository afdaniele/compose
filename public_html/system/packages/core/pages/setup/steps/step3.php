<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Database;
use \system\classes\enum\StringType;

require_once __DIR__."/../../settings/sections/package_specific.php";


$step_no = 3;

if(
    (
      (isset($_GET['step']) && $_GET['step'] == $step_no) ||
      (isset($_GET['force_step']) && $_GET['force_step'] == $step_no)
    ) &&
    (
      isset($_GET['confirm']) && $_GET['confirm'] == '1'
    )
  ){
  _compose_first_setup_step_in_progress();
  // confirm step
  $first_setup_db = new Database('core', 'first_setup');
  $first_setup_db->write('step'.$step_no, null);

  // redirect to setup page
  Core::redirectTo('setup');
}

// get configuration for core package
$res = Core::getPackageSettings( 'core' );
if( !$res['success'] )
  Core::throwError($res['data']);
$core_pkg_setts = $res['data'];

$step_keys = [
  "website_name",
  "navbar_title",
  "timezone",
  "admin_contact_email_address"
];
?>

<form id="step-form">
  <input type="text" name="package" style="display:none" value="core">

  <table style="width:100%; margin-top:20px">
    <tr>
      <td>
        <div style="width:700px; margin:auto">
          <?php
          $settings_values = $core_pkg_setts->asArray();
          $metadata = $core_pkg_setts->getMetadata();
          $metadata = $metadata['configuration_content'];
          // ---
          foreach( $step_keys as $key ){
            $settings_entry = $metadata[$key];
            $settings_entry_value =
              array_key_exists($key, $settings_values)?
                $settings_values[$key] :
                ( is_null($settings_entry['default'])? '' : $settings_entry['default'] );
            create_row( $key, $settings_entry, $settings_entry_value );
          }
          ?>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <button type="button" class="btn btn-success" id="confirm-step-button" style="float:right">
          <span class="fa fa-arrow-down" aria-hidden="true"></span>
          &nbsp;
          Next
        </button>
      </td>
    </tr>
  </table>
</form>

<script type="text/javascript">
  $('#confirm-step-button').on('click', function(){
    qs = serializeForm( '#step-form' );
    //
    url = "{0}web-api/{1}/configuration/set/json?{2}&token={3}".format(
      "<?php echo Configuration::$BASE_URL ?>",
      "<?php echo Configuration::$WEBAPI_VERSION ?>",
      qs,
      "<?php echo $_SESSION["TOKEN"] ?>"
    );

    succ_fcn = function(r){
      location.href = 'setup?step=<?php echo $step_no ?>&confirm=1';
    }

    callAPI(url, true, false, succ_fcn);
  });
</script>
