<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use system\classes\Core;

function settings_codebase_tab() {
    $codebase_info = Core::getCodebaseInfo();
    ?>

    <p>
        This tab shows the version of <strong>\compose\</strong> currently installed.
    </p>
    <div style="padding:10px 0">

        <div class="input-group mb-3">
            <span class="input-group-text">
                <i class="bi bi-file-earmark-code" aria-hidden="true"></i>&nbsp;
                Provider
            </span>
            <div class="form-control" style="padding: 0.6rem">
                <a href="<?php echo $codebase_info['git_remote_url']; ?>" target="_blank">
                    <?php echo $codebase_info['git_remote_url']; ?>
                </a>
            </div>
            <span class="input-group-text">
                <a class="btn btn-primary btn-sm" type="button" style="width:100%; text-align:left"
                   href="<?php echo $codebase_info['git_remote_url']; ?>"
                   target="_blank">
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>&nbsp;
                    Go to the repository
                    </a>
            </span>
        </div>

        <br/>

        <div class="input-group mb-3">
            <span class="input-group-text">
                <i class="bi bi-hash" aria-hidden="true"></i>&nbsp;
                Codebase Hash
            </span>
            <div class="form-control" style="padding: 0.6rem">
                <?php echo Core::getCodebaseHash(true); ?>
                &nbsp;
                (
                <?php
                echo is_null($codebase_info['head_tag']) ?
                    (
                    is_null($codebase_info['latest_tag']) ?
                        '<strong style="color:orange">devel</strong>'
                        :
                        $codebase_info['latest_tag'] . ' -> <strong style="color:orange">devel</strong>'
                    )
                    :
                    $codebase_info['head_tag'];
                ?>
                )
            </div>
            <span class="input-group-text">
                <?php
                $url = sprintf('%s/tree/%s',
                    str_replace('.git', '', $codebase_info['git_remote_url']),
                    Core::getCodebaseHash(true)
                );
                ?>
                <a class="btn btn-primary btn-sm" type="button" style="width:100%; text-align:left"
                   href="<?php echo $url; ?>" target="_blank">
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>&nbsp;
                    Browse this version
                    </a>
            </span>
        </div>

        <br/>

        <div class="input-group mb-3">
            <span class="input-group-text">
                <i class="bi bi-cloud-download" aria-hidden="true"></i>&nbsp;
                Updates
            </span>
            <div class="form-control" style="padding: 0.6rem" id="settings_codebase_update_result">
                Press "Check updates" to check whether an updated version of <b>\compose\</b> is
                available.
            </div>
            <span class="input-group-text">
                <a class="btn btn-primary btn-sm" type="button"
                   id="settings_codebase_update_check_button"
                   data-action="check" style="width:100%; text-align:left">
                  <i class="bi bi-arrow-clockwise"></i>&nbsp; Check for updates
                </a>
            </span>
        </div>

    </div>


    <script type="text/javascript">
        let _codebase_update_check_btn = $('#settings_codebase_update_check_button');
        let _codebase_update_result = $('#settings_codebase_update_result');


        _codebase_update_check_btn.on('click', function () {
            if ($(this).data('action') === 'check') {
                check_for_updates_action();
            } else if ($(this).data('action') === 'update') {
                redirectTo('settings', null, null, null, {'base_update': 1});
            }
        });

        function on_success_fcn(update_available, version) {
            if (update_available) {
                _codebase_update_result.html(
                    'Version ' + version + ' of <b>\\compose\\</b> is available!'
                );
                _codebase_update_result.css('background-color', 'lightgreen');
                _codebase_update_check_btn.attr('disabled', false);
                _codebase_update_check_btn.html(
                    '<i class="bi bi-cloud-download"></i>&nbsp; Update <b>\\compose\\</b>'
                );
                _codebase_update_check_btn.data('action', 'update');
            } else {
                _codebase_update_result.html(
                    'Your copy of <b>\\compose\\</b> is up to date!'
                );
                _codebase_update_result.css('background-color', 'aliceblue');
                _codebase_update_check_btn.html(
                    '<i class="bi bi-hand-thumbs-up"></i>&nbsp; Nothing to do!'
                );
                _codebase_update_check_btn.data('action', 'none');
            }
            _codebase_update_check_btn.removeClass('btn-info');
            _codebase_update_check_btn.addClass('btn-success');
        }//on_success_fcn

        function on_error_fcn() {
            _codebase_update_result.html(
                'An error occurred while checking for updates. Try again later!'
            );
            _codebase_update_result.css('background-color', 'gold');
            _codebase_update_check_btn.attr('disabled', false);
        }//on_error_fcn

        function check_for_updates_action() {
            _codebase_update_check_btn.attr('disabled', true);
            _codebase_update_result.html(
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
                <?php echo Core::getSetting('allow_devel_updates') ? 'true' : 'false' ?>,
                on_success_fcn,
                on_error_fcn
            );
        }//check_for_updates_action
        
        <?php
        if (isset($_GET['check_updates']) && $_GET['check_updates']) {
        ?>
        $(document).ready(function () {
            check_for_updates_action();
        });
        <?php
        }
        ?>
    </script>
    
    <?php
}

?>
