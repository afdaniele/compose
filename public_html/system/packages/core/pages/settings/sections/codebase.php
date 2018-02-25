<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Saturday, January 13th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

use \system\classes\Core as Core;

function settings_codebase_tab(){
    $codebase_info = Core::getCodebaseInfo();
    ?>

    <p>
        This tab shows the version of <strong>\compose\</strong> currently installed.
    </p>
    <div style="padding:10px 0">



        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-code-fork" aria-hidden="true"></i>
                &nbsp;
                Provider
            </div>
            <div class="form-control">
                <a href="<?php echo $codebase_info['git_remote_url']; ?>" target="_blank">
                    <?php echo $codebase_info['git_remote_url']; ?>
                </a>
            </div>
            <span class="input-group-btn">
                <a class="btn btn-default" type="button"
                    href="<?php echo $codebase_info['git_remote_url']; ?>"
                    target="_blank">
                    <i class="fa fa-external-link" aria-hidden="true"></i>
                    &nbsp;
                    Go to the repository
                </a>
            </span>
        </div>

        <br/>

        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-hashtag" aria-hidden="true"></i>
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
            <span class="input-group-btn">
                <a class="btn btn-default" type="button"
                    href="<?php
                        echo sprintf( '%s/tree/%s',
                            str_replace( '.git', '', $codebase_info['git_remote_url'] ),
                            Core::getCodebaseHash( true )
                        )?>"
                    target="_blank">
                    <i class="fa fa-external-link" aria-hidden="true"></i>
                    &nbsp;
                    Browse this version
                </a>
            </span>
        </div>


        <!-- <br/>
        TODO: re-enable once we define how to check for COMPATIBLE upgrades

        <div class="input-group">
            <div class="input-group-addon">
                <i class="glyphicon glyphicon-cloud-download" aria-hidden="true"></i>
                &nbsp;
                Updates
            </div>
            <div class="form-control" id="settings_codebase_update_result">
                Press "Check updates" to check whether an updated version of <b>\compose\</b> is available.
            </div>
            <span class="input-group-btn">
                <a class="btn btn-default" type="button" id="settings_codebase_update_check_button">
                    <i class="glyphicon glyphicon-refresh" aria-hidden="true"></i>
                    &nbsp;
                    Check updates
                </a>
            </span>
        </div> -->


    </div>


    <script type="text/javascript">

        function github_repo_latest_release_callback( result ){
            if( result.object.sha != "<?php echo Core::getCodebaseHash( true ) ?>" ){
                $('#settings_codebase_update_result').html(
                    'A new version (<b>#{0}</b>) is available!'.format( result.object.sha )
                );
                $('#settings_codebase_update_result').css('background-color', 'aliceblue');
            }else{
                $('#settings_codebase_update_result').html(
                    'Your copy of <b>\\compose\\</b> is up to date!'
                );
                $('#settings_codebase_update_result').css('background-color', 'palegreen');
            }
        }

    	$('#settings_codebase_update_check_button').on('click', function(){
            $('#settings_codebase_update_result').html(
                '<div class="progress"> \
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%"></div> \
                </div>'
            );
            //
            var url = "https://api.github.com/repos/afdaniele/compose/releases/latest";
            //
            callExternalAPI( url, 'GET', 'json', false, false, github_repo_latest_release_callback, true, true );
    	});

    </script>

<?php
}
?>
