<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Database;
use \system\classes\enum\StringType;

$step_no = 1;

// get configuration for core package
$res = Core::getPackageSettings('core');
if( !$res['success'] )
  Core::throwError($res['data']);
$core_pkg_setts = $res['data'];

if( (isset($_GET['step']) && $_GET['step'] == $step_no) ||
  (isset($_GET['force_step']) && $_GET['force_step'] == $step_no)
  ){
  $confirm_steps = [];
  $first_setup_db = new Database('core', 'first_setup');

  // ---
  if( isset($_GET['data']) ){
    // check data
    $google_client_id = $_GET['data'];
    if( !StringType::isValid($google_client_id, StringType::TEXT) )
      Core::throwError('Invalid value for parameter "data" for page "/setup", step #'.$step_no.'.');

    // store google client ID
    $res = $core_pkg_setts->set('google_client_id', $google_client_id);
    if( !$res['success'] )
      Core::throwError($res['data']);

    // commit configuration file to disk
    $res = $core_pkg_setts->commit();
    if( !$res['success'] )
      Core::throwError($res['data']);

    // mark the step as completed
    array_push($confirm_steps, $step_no);
  }

  // ---
  if (isset($_GET['skip']) && $_GET['skip'] == '1') {
    $first_setup_db->write('no_admin', null);
    // mark the steps as completed
    array_push($confirm_steps, 1);
    array_push($confirm_steps, 2);
  }

  // confirm steps
  foreach ($confirm_steps as $step_id) {
    $first_setup_db->write('step'.$step_id, null);
  }

  if (count($confirm_steps)) {
    _compose_first_setup_step_in_progress();
    // redirect to setup page
    Core::redirectTo('setup');
  }
}

$client_id = Core::getSetting('google_client_id');
$core_pkg_setts_meta = $core_pkg_setts->getMetadata();
$default_client_id = $core_pkg_setts_meta['configuration_content']['google_client_id']['default'];

$client_id = ($client_id != $default_client_id)? $client_id : null;
?>

<div style="margin: 10px 20px">
  <form id="step-form">

    <p>
      <strong>\compose\</strong> uses the
      <a href="https://developers.google.com/identity/" target="_blank">Google Sign-In</a>
      service to authenticate you and your guests on your website.<br/>

      Follow the instructions at
      <a href="https://developers.google.com/identity/protocols/OAuth2WebServer#enable-apis" target="_blank">
        this page
      </a> to enable <strong>Google Sign-In</strong>.<br/><br/>

      Once you have your <strong>Client ID</strong>, insert it in the area below and click <strong>Next</strong>.
    </p>
    <div class="input-group">
      <span class="input-group-addon">
        <i class="fa fa-google" aria-hidden="true"></i>&nbsp; |&nbsp;
        Google Client ID
      </span>
      <input
        name="data"
        type="text"
        class="form-control"
        placeholder="Your Google Client ID"
        aria-describedby="google-id"
        <?php echo is_null($client_id)? '' : sprintf('value="%s"', $client_id)?>
        >
    </div>

    <div style="float: right; margin-top: 20px">
      <a role="button" class="btn btn-default" href="setup?force_step=<?php echo $step_no ?>&skip=1">
        <span class="fa fa-fast-forward" aria-hidden="true"></span>
        &nbsp;
        Skip
      </a>
      &nbsp;
      <button type="button" class="btn btn-success" id="confirm-step-button">
        <span class="fa fa-arrow-down" aria-hidden="true"></span>
        &nbsp;
        Next
      </button>
    </div>
  </form>

</div>

<script type="text/javascript">
  $('#confirm-step-button').on('click', function(){
    qs = serializeForm( '#step-form' );
    //
    location.href = 'setup?step=<?php echo $step_no ?>&'+qs;
  });
</script>
