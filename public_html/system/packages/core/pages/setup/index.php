<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Database;

// redirect user away from this page (if necessary)
if (Core::isComposeConfigured())
  Core::redirectTo('');

// define steps
$steps = [
  1 => 'Configure Google Sign-In',
  2 => 'Create an Administrator account',
  3 => 'Configure \\compose\\',
  4 => 'Complete'
];
$num_steps = count($steps);

// look for edit actions
$force_step = null;
if( isset($_GET['force_step']) && array_key_exists(intval($_GET['force_step']), $steps) )
$force_step = intval($_GET['force_step']);

// open first_setup DB
$first_setup_db = new Database('core', 'first_setup');

$cur_step = $num_steps;
for ($i = 1; $i <= $num_steps; $i++) {
  if( $force_step == $i || !$first_setup_db->key_exists('step'.$i) ){
    $cur_step = $i;
    break;
  }
}
?>

<style type="text/css">
.panel > .panel-heading a{
  color: inherit;
  text-decoration: none;
}

select.form-control{
  height: 26px !important;
}
</style>


<div style="width:100%; margin:auto">

  <table style="width:100%; border-bottom:1px solid #ddd; margin:20px 0 20px 0">
    <tr>
      <td style="width:100%">
        <h1 class="text-center">
          <img src="<?php echo Configuration::$BASE_URL ?>images/compose-black-logo.svg">
          <br/>
          Welcome!
        </h1>
      </td>
    </tr>
  </table>


  <?php
  function _compose_first_setup_step_in_progress(){
    ?>
    <div class="progress">
      <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
      </div>
    </div>
    <?php
  }//_compose_first_setup_step_in_progress

  for ($step_no = 1; $step_no <= $num_steps; $step_no++) {
    $collapse = $step_no == $cur_step? 'in' : '';
    $icon = ($step_no == $cur_step)? 'square-o' : (($step_no < $cur_step)? 'check-square' : 'square');
    $color = ($step_no < $cur_step)? 'green' : 'black';
    $panel_style = ($step_no < $cur_step)? 'panel-success' : 'panel-default';
    // ---
    ?>
    <div class="panel <?php echo $panel_style ?>">
      <div class="panel-heading" role="tab" id="<?php echo $header ?>">
        <a id="a_setup_step<?php echo $step_no ?>" role="button" aria-expanded="true" aria-controls="setup_step<?php echo $step_no ?>">
          <h4 class="panel-title">
            <span class="fa fa-<?php echo $icon ?>" style="color:<?php echo $color ?>" aria-hidden="true"></span>
            &nbsp;
            <strong>Step <?php echo $step_no ?>:</strong> <?php echo $steps[$step_no] ?>
            <?php
            if ($step_no < $cur_step){
              ?>
              <a role="button" class="btn btn-xs" href="setup?force_step=<?php echo $step_no ?>" style="float:right">
                <span class="fa fa-pencil" aria-hidden="true"></span>
                &nbsp;
                Edit
              </a>
              <?php
            }elseif($step_no > $cur_step && $first_setup_db->key_exists('step'.($step_no-1))){
              ?>
              <a role="button" class="btn btn-xs" href="setup?force_step=<?php echo $step_no ?>" style="float:right">
                <span class="fa fa-mail-forward" aria-hidden="true"></span>
                &nbsp;
                Return
              </a>
              <?php
            }
            ?>
          </h4>
        </a>
      </div>
      <div id="setup_step<?php echo $step_no ?>" class="panel-collapse collapse <?php echo $collapse ?>" role="tabpanel" aria-labelledby="setup_step<?php echo $step_no ?>">
        <div class="panel-body">
          <?php
          if ($step_no == $cur_step){
            include_once __DIR__.'/steps/step'.$step_no.'.php';
          }
          ?>
        </div>
      </div>
    </div>
    <?php
  }
  ?>

</div>
