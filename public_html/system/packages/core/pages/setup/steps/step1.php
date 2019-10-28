<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Cache;
use \system\classes\Configuration;
use \system\classes\Database;
use \system\classes\enum\StringType;

$step_no = 1;

if(
    (isset($_GET['step']) && $_GET['step'] == $step_no) ||
    (isset($_GET['force_step']) && $_GET['force_step'] == $step_no)
  ){
  $confirm_steps = [];
  $first_setup_db = new Database('core', 'first_setup');
  // ---
  if( isset($_GET['data']) ){
    // check data
    $google_client_id = $_GET['data'];
    if (!StringType::isValid($google_client_id, StringType::TEXT)) {
      Core::throwError('Invalid value for parameter "data" for page "/setup", step #'.$step_no.'.');
    }
    // store google client ID
    $res = Core::setSetting('core', 'google_client_id', $google_client_id);
    if (!$res['success']) {
      Core::throwError($res['data']);
    }
    // mark the step as completed
    array_push($confirm_steps, $step_no);
  }

  // ---
  if (isset($_GET['skip']) && $_GET['skip'] == '1') {
    $first_setup_db->write('no_admin', null);
    // enable developer mode
    $res = Core::setSetting('core', 'developer_mode', true);
    if (!$res['success']) {
      Core::throwError($res['data']);
    }
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
    // clear cache
    if (Cache::enabled()) {
      Cache::clearAll();
    }
    // redirect to setup page
    Core::redirectTo('setup');
  }
}

// get configuration for core package
$res = Core::getPackageSettings('core');
if( !$res['success'] )
  Core::throwError($res['data']);
$core_pkg_setts = $res['data'];

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
    <div class="input-group" style="width: 100%">
      <span class="input-group-addon" style="width: 180px">
        <i class="fa fa-google" aria-hidden="true"></i>&nbsp; |&nbsp;
        Google Client ID
      </span>
      <input
        name="data"
        type="text"
        class="form-control"
        placeholder="Your Google Client ID"
        aria-describedby="google-id"
        style="width: 100%"
        <?php echo is_null($client_id)? '' : sprintf('value="%s"', $client_id)?>
        >
    </div>

    <div style="float: right; margin-top: 20px">
      <a role="button" class="btn btn-default" onclick="_first_setup_skip_step1()">
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

  function _first_setup_skip_step1(){
    var url = "<?php echo Core::getURL('setup', null, null, null, ['force_step' => $step_no, 'skip' => 1]) ?>";
    var question = "If you skip this step, the <strong>Developer Mode</strong> will be enabled.";
    question += "<br/>The Developer Mode allows everybody to access the platform without logging in.";
    question += "<br/>Remember to turn it off before deploying your application.";
    question += "<br/>Learn more about <a href=\"http://compose.afdaniele.com/docs/latest/developer-mode\" target=\"_blank\">Developer Mode</a>.";
    question += "<br/><br/><strong>Do you want to continue?<strong>";
    openYesNoModal(
      question,
      function(){location.href = url;},
      true,
      'md'
    );
  }//_first_setup_skip_step1

  $('#confirm-step-button').on('click', function(){
    qs = serializeForm( '#step-form' );
    //
    location.href = 'setup?step=<?php echo $step_no ?>&'+qs;
  });

</script>
