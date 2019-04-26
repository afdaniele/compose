<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

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
  _compose_first_setup_step_in_progress();

  // confirm step
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

    <div id="g-signin"></div>

  </form>

</div>

<script type="text/javascript">
  $(window).on('COMPOSE_LOGGED_IN', function(evt){
    location.href = 'setup?step=<?php echo $step_no ?>&confirm=1';
  });
</script>
