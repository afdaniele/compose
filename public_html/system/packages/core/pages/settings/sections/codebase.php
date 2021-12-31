<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;

function settings_codebase_tab(){
  $codebase_info = Core::getCodebaseInfo();
  ?>

  <p>
    This tab shows the version of <strong>\compose\</strong> currently installed.
  </p>
  <div style="padding:10px 0">

    <div class="input-group">
      <div class="input-group-addon">
        <i class="bi bi-code-fork" aria-hidden="true"></i>
        &nbsp;
        Provider
      </div>
      <div class="form-control">
        <a href="<?php echo $codebase_info['git_remote_url']; ?>" target="_blank">
          <?php echo $codebase_info['git_remote_url']; ?>
        </a>
      </div>
      <span class="input-group-btn" style="width:180px">
        <a class="btn btn-primary" type="button" style="width:100%; text-align:left"
          href="<?php echo $codebase_info['git_remote_url']; ?>"
          target="_blank">
          <i class="bi bi-external-link" aria-hidden="true"></i>
          &nbsp;
          Go to the repository
        </a>
      </span>
    </div>

    <br/>

    <div class="input-group">
      <div class="input-group-addon">
        <i class="bi bi-hashtag" aria-hidden="true"></i>
        &nbsp;
        Codebase Hash
      </div>
      <div class="form-control">
        <?php echo Core::getCodebaseHash( true ); ?>
        &nbsp;
        (
        <?php
        echo is_null($codebase_info['head_tag'])?
          (
            is_null($codebase_info['latest_tag'])?
            '<strong style="color:orange">devel</strong>'
            :
            $codebase_info['latest_tag'].' -> <strong style="color:orange">devel</strong>'
          )
          :
          $codebase_info['head_tag'];
        ?>
        )
      </div>
      <span class="input-group-btn" style="width:180px">
        <a class="btn btn-primary" type="button"
          style="width:100%; text-align:left"
          href="<?php
          echo sprintf( '%s/tree/%s',
            str_replace( '.git', '', $codebase_info['git_remote_url'] ),
            Core::getCodebaseHash(true)
          )?>"
          target="_blank">
          <i class="bi bi-external-link" aria-hidden="true"></i>
          &nbsp;
          Browse this version
        </a>
      </span>
    </div>

    <br/>

    <div class="input-group">
      <div class="input-group-addon">
        <i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>
        &nbsp;
        Updates
      </div>
      <div class="form-control" id="settings_codebase_update_result">
        Press "Check updates" to check whether an updated version of <b>\compose\</b> is available.
      </div>
      <span class="input-group-btn" style="width:180px">
        <a class="btn btn-info" type="button" id="settings_codebase_update_check_button" data-action="check" style="width:100%; text-align:left">
          <i class="glyphicon glyphicon-refresh" aria-hidden="true"></i>&nbsp; Check for updates
        </a>
      </span>
    </div>

  </div>


  <script type="text/javascript">
    $('#settings_codebase_update_check_button').on('click', function(){
      if ($(this).data('action') == 'check') {
        check_for_updates_action();
      }else if ($(this).data('action') == 'update') {
        redirectTo('settings', null, null, null, {'base_update': 1});
      }
    });

    function on_success_fcn(update_available, version){
      if (update_available) {
        $('#settings_codebase_update_result').html(
          'Version ' + version + ' of <b>\\compose\\</b> is available!'
        );
        $('#settings_codebase_update_result').css('background-color', 'lightgreen');
        $('#settings_codebase_update_check_button').attr('disabled', false);
        $('#settings_codebase_update_check_button').html(
          '<i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>&nbsp; Update <b>\\compose\\</b>'
        );
        $('#settings_codebase_update_check_button').data('action', 'update');
      }else{
        $('#settings_codebase_update_result').html(
          'Your copy of <b>\\compose\\</b> is up to date!'
        );
        $('#settings_codebase_update_result').css('background-color', 'aliceblue');
        $('#settings_codebase_update_check_button').html(
          '<i class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></i>&nbsp; Nothing to do!'
        );
        $('#settings_codebase_update_check_button').data('action', 'none');
      }
      $('#settings_codebase_update_check_button').removeClass('btn-info');
      $('#settings_codebase_update_check_button').addClass('btn-success');
    }//on_success_fcn

    function on_error_fcn(){
      $('#settings_codebase_update_result').html(
        'An error occurred while checking for updates. Try again later!'
      );
      $('#settings_codebase_update_result').css('background-color', 'gold');
      $('#settings_codebase_update_check_button').attr('disabled', false);
    }//on_error_fcn

    function check_for_updates_action(){
      $('#settings_codebase_update_check_button').attr('disabled', true);
      $('#settings_codebase_update_result').html(
        '<div class="progress"> \
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%"></div> \
        </div>'
      );
      // ---
      checkForUpdates(
        "<?php echo $codebase_info['git_host'] ?>",
        "<?php echo $codebase_info['git_owner'] ?>",
        "<?php echo $codebase_info['git_repo'] ?>",
        "<?php echo $codebase_info['head_full_hash'] ?>",
        <?php echo Core::getSetting('allow_devel_updates')? 'true' : 'false' ?>,
        on_success_fcn,
        on_error_fcn
      );
    }//check_for_updates_action

    <?php
    if (isset($_GET['check_updates']) && boolval($_GET['check_updates'])) {
      ?>
      $(document).ready(function(){
        check_for_updates_action();
      });
      <?php
    }
    ?>
  </script>

<?php
}
?>
