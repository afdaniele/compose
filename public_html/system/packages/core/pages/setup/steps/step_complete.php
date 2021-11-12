<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Database;
use \system\classes\enum\StringType;


$step_no = $_COMPOSE_SETUP_STEP_NO;

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
  $first_setup_db->write('configured', null);

  // redirect to default page
  Core::redirectTo('');
}
?>

<div style="margin: 10px 20px">
  <p>
    <strong>Congratulations!</strong><br/>
    Your <strong>\compose\</strong> website is ready to take off!<br/><br/>

    Check out the
    <a href="http://compose.afdaniele.com/docs/latest/" target="_blank">
      official \compose\ documentation
    </a>
    for further information about how to use <strong>\compose\</strong>.<br/>
    After you click <strong>Finish</strong>, you will be able to install packages
    from the <strong>Packages</strong> page.
  </p>

  <div style="float: right; margin-top: 20px">
    <button type="button" class="btn btn-success" id="confirm-step-button">
      <span class="bi bi-arrow-down" aria-hidden="true"></span>
      &nbsp;
      Finish
    </button>
  </div>
</div>

<script type="text/javascript">
  $('#confirm-step-button').on('click', function(){
    location.href = 'setup?step=<?php echo $step_no ?>&confirm=1';
  });
</script>
