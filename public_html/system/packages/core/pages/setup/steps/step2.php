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

$step_no = 2;

// make sure that the step1 is completed
$first_setup_db = new Database('core', 'first_setup');
if( !$first_setup_db->key_exists('step'.($step_no-1)) ){
  Core::redirectTo('setup');
}

if(
    (
      (isset($_GET['step']) && $_GET['step'] == $step_no) ||
      (isset($_GET['force_step']) && $_GET['force_step'] == $step_no)
    ) &&
    (
      isset($_GET['confirm']) && $_GET['confirm'] == '1' && Core::isUserLoggedIn()
    )
  ){
  // confirm step
  $first_setup_db = new Database('core', 'first_setup');
  $first_setup_db->write('step'.$step_no, null);

  // redirect to setup page
  Core::redirectTo('setup');
}
?>

<div style="margin: 10px 20px">
  <form id="step-form">
    <p>
      Use the button below to <strong>Sign in</strong> with your account Google.<br/>
      <strong>NOTE:</strong> The account you choose will become your administrator account.
    </p>
    <br/>

    <div id="g-signin2"></div>
  </form>

</div>

<script type="text/javascript">
  function onSuccess(googleUser) {
    location.href = 'setup?step=<?php echo $step_no ?>&confirm=1';
  }
  function onFailure(error) {
    openAlert('danger', error);
  }
  function renderButton() {
    gapi.signin2.render('g-signin2', {
      'scope': 'profile email',
      'width': 240,
      'height': 50,
      'longtitle': true,
      'onsuccess': onSuccess,
      'onfailure': onFailure
    });
  }
</script>

<script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>