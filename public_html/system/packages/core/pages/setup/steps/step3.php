<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Database;
use system\classes\Utils;

require_once $GLOBALS['__SYSTEM__DIR__'] . 'templates/forms/SmartForm.php';

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
$res = Core::getPackageSettings('core');
if( !$res['success'] )
  Core::throwError($res['data']);
$core_pkg_setts = $res['data'];

$step_keys = [
  "website_name",
  "navbar_title",
  "timezone",
  "admin_contact_email_address"
];

// get settings schema
$schema = $core_pkg_setts->getSchema();
$schema_arr = $schema->asArray();

// keep only settings we want to change at first setup
foreach ($schema_arr['_data'] as $key => &$_) {
    if (!in_array($key, $step_keys)) {
        unset($schema_arr['_data'][$key]);
    }
}

// merge defaults and actual configuration
$core_pkg_setts_full = Utils::arrayMergeAssocRecursive(
    $schema->defaults(), $core_pkg_setts->asArray(), false
);

// create form
$form = new SmartForm($schema_arr, $core_pkg_setts_full);
?>

<div style="margin: 40px 60px">
    <?php
    $form->render();
    ?>
</div>

<button type="button" class="btn btn-success" id="confirm-step-button" style="float:right">
  <span class="fa fa-arrow-down" aria-hidden="true"></span>&nbsp; Next
</button>

<script type="text/javascript">
    
    $('#confirm-step-button').on('click', function(){
        let form = ComposeForm.get("<?php echo $form->formID ?>");
        // define success function
        let succ_fcn = function(r){
            location.href = 'setup?step=<?php echo $step_no ?>&confirm=1';
        };
        // call API
        smartAPI('configuration', 'set', {
            method: 'POST',
            arguments: {
                package: "core"
            },
            data: {
                configuration: form.serialize()
            },
            block: true,
            confirm: true,
            reload: false,
            on_success: succ_fcn
        });
    });
    
</script>



