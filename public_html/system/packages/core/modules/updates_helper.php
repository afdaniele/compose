<?php
use \system\classes\Core;

$codebase_info = Core::getCodebaseInfo();
$url_to_codebase = Core::getURL('settings', null, null, null, ['check_updates' => 1], 'sel:codebase_collapse');
?>

<style type="text/css">
  .updates_helper{
    font-weight: bold;
    position: fixed;
    top: 64px;
    right: 10px;
    text-align: center;
    background-color: #e38d13dd;
    background-image: none;
    z-index: 900;
  }
</style>

<a role="button" id="_updates_helper_btn" class="btn btn-warning updates_helper" style="display: none" href="<?php echo $url_to_codebase ?>">
  <i class="bi bi-cloud-download" aria-hidden="true"></i>
  Updates available
</a>

<script type="text/javascript">
  $(document).ready(function(){
    function on_success_fcn(needs_update){
      if (needs_update) {
        $('#_updates_helper_btn').css('display', 'block');
      }
    }//on_success_fcn

    checkForUpdates(
      "<?php echo $codebase_info['git_host'] ?>",
      "<?php echo $codebase_info['git_owner'] ?>",
      "<?php echo $codebase_info['git_repo'] ?>",
      "<?php echo $codebase_info['head_full_hash'] ?>",
      <?php echo Core::getSetting('allow_devel_updates')? 'true' : 'false' ?>,
      on_success_fcn
    );
  });
</script>
