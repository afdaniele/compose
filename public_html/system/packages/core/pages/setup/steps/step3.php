<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Database;
use system\classes\Utils;


$step_no = 3;

if (
    (
        (isset($_GET['step']) && $_GET['step'] == $step_no) ||
        (isset($_GET['force_step']) && $_GET['force_step'] == $step_no)
    ) &&
    (
        isset($_GET['confirm']) && $_GET['confirm'] == '1'
    )
) {
    _compose_first_setup_step_in_progress();
    // confirm step
    $first_setup_db = new Database('core', 'first_setup');
    $first_setup_db->write('step' . $step_no, null);
    
    // redirect to setup page
    Core::redirectTo('setup');
}

// get configuration for core package
$core_pkg_setts = Core::getPackageSettings('core');

$step_keys = [
    "website_name",
    "navbar_title",
    "timezone",
    "admin_contact_email_address"
];

// get settings schema
$schema = $core_pkg_setts->getSchemaAsArray();
$values = $core_pkg_setts->asArray();


// keep only settings we want to change at first setup
foreach ($schema["properties"] as $key => &$_) {
    if (!in_array($key, $step_keys)) {
        unset($schema["properties"][$key]);
    }
}
?>

<div style="margin: 40px 60px">
    <form id="step3_form"></form>
</div>


<button type="button" class="btn btn-success" id="confirm-step-button" style="float:right">
    <span class="bi bi-arrow-down" aria-hidden="true"></span>&nbsp; Next
</button>

<script type="text/javascript">

    let _SETUP_STEP3_FORM_SCHEMA = <?php print json_encode($schema) ?>;
    let _SETUP_STEP3_FORM_DATA = <?php print json_encode($values) ?>;

    $('#step3_form').jsonForm({
        schema: _SETUP_STEP3_FORM_SCHEMA,
        value: _SETUP_STEP3_FORM_DATA,
        form: ["*"]
    });

    $('#confirm-step-button').on('click', function () {
        let values = $('#step3_form').jsonFormValue();
        // define success function
        let succ_fcn = function (r) {
            location.href = 'setup?step=<?php echo $step_no ?>&confirm=1';
        };
        // call API
        smartAPI('configuration', 'set', {
            method: 'POST',
            arguments: {
                package: "core"
            },
            data: {
                configuration: values
            },
            block: true,
            confirm: true,
            reload: false,
            on_success: succ_fcn
        });
    });

</script>



