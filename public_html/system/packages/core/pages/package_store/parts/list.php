<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Formatter;

$installed_packages = Core::getPackagesList();
?>

<style type="text/css">
    #packages-table > thead > tr {
        font-weight: bold;
    }

    #packages-table > thead td:nth-child(1),
    #packages-table > thead td:nth-child(3),
    #packages-table > thead td:nth-child(4) {
        text-align: center;
    }

    #packages-table > tbody .compose-package > td:nth-child(1),
    #packages-table > tbody .compose-package > td:nth-child(3),
    #packages-table > tbody .compose-package > td:nth-child(4) {
        text-align: center;
        vertical-align: middle;
    }

    #packages-table > tbody .compose-package > td:nth-child(1) {
        font-weight: bold;
    }

    #packages-table > tbody .compose-package > td:nth-child(1),
    #packages-table > tbody .compose-package > td:nth-child(2),
    #packages-table > tbody .compose-package > td:nth-child(3) {
        border-right: none;
    }

    #packages-table > tbody .compose-package > td:nth-child(2),
    #packages-table > tbody .compose-package > td:nth-child(3),
    #packages-table > tbody .compose-package > td:nth-child(4) {
        border-left: none;
    }

    #packages-table > tbody .compose-package > td:nth-child(4) {
        padding-left: 0;
        padding-right: 0;
    }

    #packages-table > tbody .compose-package .main-button {
        width: 100px;
    }

    #packages-table > tbody .compose-package .update-button {
        width: 96px;
        margin: 0 4px;
    }

    #packages-table > tbody .compose-package .package-icon {
        width: 42px;
    }

    #packages-table > tbody .compose-package > td:nth-child(4) .disabled-button {
        background-image: none;
        background-color: grey;
        border: 1px solid lightgray;
    }

    #packages-table > tbody .compose-package.to-be-installed {
        background-color: rgba(0, 255, 0, 0.1);
    }

    #packages-table > tbody .compose-package.to-be-updated {
        background-color: rgba(0, 0, 255, 0.1);
    }

    #packages-table > tbody .compose-package.to-be-uninstalled {
        background-color: rgba(255, 0, 0, 0.1);
    }
</style>

<h2 class="page-title"></h2>

<div class="col-md-12" style="margin-bottom: 20px; padding: 0">
    <a id="apply_changes_btn" class="btn btn-success btn-sm" role="button"
       style="float:right" onclick="apply_changes()" href="javascript:void(0);">
        Apply changes
    </a>
    <span id="status_label"
          style="font-size:12pt; float:right; padding-top:4px; font-weight:normal;"></span>
</div>

<?php
$assets_index_url = sanitize_url(
    sprintf(
        '%s/%s/index',
        Configuration::$ASSETS_STORE_URL,
        Configuration::$ASSETS_STORE_BRANCH
    )
);
?>

<div class="input-group" style="margin-top:28px">
    <span class="input-group-addon" id="packages-search-addon">Search package</span>
    <input type="text" class="form-control" id="packages-search-field" style="height:42px"
           aria-describedby="packages-search-addon">
</div>

<table class="table table-striped table-bordered table-hover" id="packages-table"
       style="margin-top:20px">
    <thead>
    <tr>
        <td class="col-md-1"></td>
        <td class="col-md-6">
            Package
        </td>
        <td class="col-md-1">
            Installed
        </td>
        <td class="col-md-4">
            Actions
        </td>
    </tr>
    </thead>
    <tbody id="packages-table-body">
    </tbody>
</table>


<script type="text/javascript">

    let packages_to_install = [];
    let packages_to_update = [];
    let packages_to_uninstall = [];

    let providers = {
        'github.com': 'https://raw.githubusercontent.com/{0}/{1}/{2}',
        'bitbucket.org': 'https://bitbucket.org/{0}/{1}/raw/{2}'
    };

    let providers_repo = {
        'github.com': 'https://github.com/{0}/{1}',
        'bitbucket.org': 'https://bitbucket.org/{0}/{1}'
    };

    let providers_source = {
        'github.com': 'https://github.com/{0}/{1}/tree/{2}',
        'bitbucket.org': 'https://bitbucket.org/{0}/{1}/src/{2}'
    };
    
    <?php
    // this fixes nested quotes
    $json_str = str_replace("\u0022", "\\\\\"", json_encode($installed_packages, JSON_HEX_QUOT));
    ?>
    let installed_packages = JSON.parse('<?php echo $json_str ?>');

    let installed_packages_ids = Object.keys(installed_packages);

    let packages_table_body_row_template = `
    <tr class="compose-package" id="{5}" data-search="{4}">
      <td>
        {0}
      </td>
      <td>
        {1}
      </td>
      <td>
        {2}
      </td>
      <td>
        {3}
      </td>
    </tr>`;

    let package_template = `
    <strong>
      {0}
      <br/>
    </strong>
    ID: <span class="mono" style="color:grey">{1}</span><br/>
    Maintainer: <span class="mono" style="color:grey">{2}</span><br/>
    {4}<br/>
    <div style="margin-top:4px">
      {3}
    </div>`;

    let package_version_line_template = `
    {0}: <span class="mono" style="color:{1}">{2}</span>
  `;

    function render_changes() {
        // show info about how many packages will be installed/removed
        let install = '{0} package(s) will be installed'.format(packages_to_install.length);
        let updated = '{0} updated'.format(packages_to_update.length);
        let uninstall = '{0} removed'.format(packages_to_uninstall.length);
        let labels = [
            install,
            updated,
            uninstall
        ];
        // show/hide apply changes button
        $('#status_label').html(labels.join(', ') + '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;');
        // colorize the choices
        $('#packages-table > tbody .compose-package').each(function (index) {
            $(this).removeClass('to-be-installed');
            $(this).removeClass('to-be-updated');
            $(this).removeClass('to-be-uninstalled');
        });
        $('#packages-table > tbody .compose-package .main-button.undo-button').each(function (index) {
            $(this).css('display', 'none');
        });
        $('#packages-table > tbody .compose-package .main-button.action-button').each(function (index) {
            $(this).css('display', 'inline-block');
        });
        $('#packages-table > tbody .compose-package .update-button.undo-button').each(function (index) {
            $(this).css('display', 'none');
        });
        $('#packages-table > tbody .compose-package .update-button.action-button').each(function (index) {
            $(this).css('display', 'inline-block');
        });
        $.each(packages_to_install, function (i, package_name) {
            $('.compose-package#compose-package-{0} .main-button.undo-button'.format(package_name)).css('display', 'inline-block');
            $('.compose-package#compose-package-{0} .main-button.action-button'.format(package_name)).css('display', 'none');
            $('.compose-package#compose-package-{0}'.format(package_name)).addClass('to-be-installed');
        });
        $.each(packages_to_update, function (i, package_name) {
            $('.compose-package#compose-package-{0} .update-button.undo-button'.format(package_name)).css('display', 'inline-block');
            $('.compose-package#compose-package-{0} .update-button.action-button'.format(package_name)).css('display', 'none');
            $('.compose-package#compose-package-{0}'.format(package_name)).addClass('to-be-updated');
        });
        $.each(packages_to_uninstall, function (i, package_name) {
            $('.compose-package#compose-package-{0} .main-button.undo-button'.format(package_name)).css('display', 'inline-block');
            $('.compose-package#compose-package-{0} .main-button.action-button'.format(package_name)).css('display', 'none');
            $('.compose-package#compose-package-{0}'.format(package_name)).addClass('to-be-uninstalled');
        });
    }//render_changes

    function mark_to_install(package_name) {
        // if marked to update, remove it from the list
        let idx = packages_to_update.indexOf(package_name);
        if (idx >= 0) {
            packages_to_update.splice(idx, 1);
            render_changes();
            return;
        }
        // if already marked, do nothing
        idx = packages_to_install.indexOf(package_name);
        if (idx >= 0)
            return;
        // if marked to uninstall, just remove it from the list
        idx = packages_to_uninstall.indexOf(package_name);
        if (idx >= 0) {
            packages_to_uninstall.splice(idx, 1);
        }
        // if not installed, mark to install
        idx = installed_packages_ids.indexOf(package_name);
        if (idx < 0) {
            packages_to_install.push(package_name);
        }
        // render new status
        render_changes();
    }//mark_to_install

    function mark_to_update(package_name) {
        // if already marked, do nothing
        let idx = packages_to_update.indexOf(package_name);
        if (idx >= 0)
            return;
        // if not installed, do nothing
        idx = installed_packages_ids.indexOf(package_name);
        if (idx < 0) {
            return;
        } else {
            packages_to_update.push(package_name);
        }
        // if marked to uninstall, remove it from the list
        idx = packages_to_uninstall.indexOf(package_name);
        if (idx >= 0) {
            packages_to_uninstall.splice(idx, 1);
        }
        // render new status
        render_changes();
    }//mark_to_update

    function mark_to_uninstall(package_name) {
        // if already marked, do nothing
        let idx = packages_to_uninstall.indexOf(package_name);
        if (idx >= 0)
            return;
        // remove from packages marked to install
        idx = packages_to_install.indexOf(package_name);
        if (idx >= 0) {
            packages_to_install.splice(idx, 1);
        }
        // if installed, mark to uninstall
        idx = installed_packages_ids.indexOf(package_name);
        if (idx >= 0) {
            packages_to_uninstall.push(package_name);
        }
        // render new status
        render_changes();
    }//mark_to_uninstall

    function apply_changes() {
        let install = packages_to_install.join(',');
        let update = packages_to_update.join(',');
        let uninstall = packages_to_uninstall.join(',');
        let qs = 'install={0}&update={1}&uninstall={2}'.format(install, update, uninstall);
        location.href = 'package_store/install?{0}'.format(qs);
    }//apply_changes

    function add_package_to_list(num, pack) {
        let is_installed = (installed_packages_ids.indexOf(pack.id) >= 0);
        let version_str_fmt = '{0}{1}{2}';
        let installed_version_str = '';
        let version_sep_str = '';
        let available_version_str = '';
        let installed_version = (is_installed) ? installed_packages[pack.id].codebase.head_tag : null;
        if (is_installed) {
            // show installed version
            installed_version = (installed_version === 'ND') ? 'devel' : installed_version;
            installed_version_str = package_version_line_template.format(
                'Installed version',
                'grey',
                installed_version
            );
        }
        // ---
        // show available version (if any)
        let latest_version = pack.git_branch;
        let needs_update = is_installed && latest_version !== 'master' && latest_version !== installed_version;
        if (!is_installed || needs_update) {
            // show available version
            var color = 'grey';
            if (is_installed && installed_version !== 'devel' && latest_version !== installed_version) {
                color = 'darkgreen';
            }
            version_sep_str = (installed_version_str.length > 0) ? '&nbsp;  |  &nbsp;' : '';
            available_version_str = package_version_line_template.format(
                'Available version',
                color,
                (latest_version === 'master') ? 'devel' : latest_version
            );
        }
        let version_str = version_str_fmt.format(
            installed_version_str,
            version_sep_str,
            available_version_str
        );
        // ---
        let col1 = package_template.format(
            pack.name,
            pack.id,
            pack.git_owner,
            pack.description,
            version_str
        );
        // ---
        let installed = (is_installed) ?
            '<?php echo Formatter::format(1, Formatter::BOOLEAN) ?>' : '<?php echo Formatter::format(0, Formatter::BOOLEAN) ?>';
        // ---
        let git_action_url = providers_source[pack.git_provider].format(pack.git_owner, pack.git_repository, pack.git_branch);
        let source_action = `
      <a class="btn btn-default" href="{0}" role="button" target="_blank">
        <i class="fa fa-code" aria-hidden="true"></i>&nbsp;
        Code
      </a>`.format(git_action_url);
        let git_repo_url = providers_repo[pack.git_provider].format(pack.git_owner, pack.git_repository);
        let install_action = `
      <a role="button" class="btn btn-success main-button action-button" onclick="mark_to_install('{0}')" href="javascript:void(0);">
        <i class="fa fa-download" aria-hidden="true"></i>&nbsp;
        Install
      </a>
      <a role="button" class="btn btn-warning main-button undo-button" style="display:none" onclick="mark_to_uninstall('{0}')" href="javascript:void(0);">
        <i class="fa fa-times" aria-hidden="true"></i>&nbsp;
        Cancel
      </a>`;
        let uninstall_action = `
      <a role="button" class="btn btn-danger main-button action-button" onclick="mark_to_uninstall('{0}')" href="javascript:void(0);">
        <i class="fa fa-trash" aria-hidden="true"></i>&nbsp;
        Uninstall
      </a>
      <a role="button" class="btn btn-warning main-button undo-button" style="display:none" onclick="mark_to_install('{0}')" href="javascript:void(0);">
        <i class="fa fa-times" aria-hidden="true"></i>&nbsp;
        Cancel
      </a>`;
        let update_action = `
      <a role="button" class="btn btn-info update-button action-button {1}" onclick="mark_to_update('{0}')" href="javascript:void(0);">
        <i class="fa fa-cloud-download" aria-hidden="true"></i>&nbsp;
        Update
      </a>
      <a role="button" class="btn btn-warning update-button undo-button" style="display:none" onclick="mark_to_install('{0}')" href="javascript:void(0);">
        <i class="fa fa-times" aria-hidden="true"></i>&nbsp;
        Cancel
      </a>`;
        let main_action = (is_installed) ? uninstall_action : install_action;
        main_action = main_action.format(pack.id);
        let update_btn = update_action.format(
            pack.id,
            (is_installed && needs_update) ? '' : 'disabled disabled-button'
        );
        let icon_url = pack.icon;
        if (!icon_url.startsWith('http')) {
            icon_url = '{0}/{1}'.format(
                '<?php
                    echo sprintf(
                        '%s/%s',
                        Configuration::$ASSETS_STORE_URL,
                        Configuration::$ASSETS_STORE_BRANCH
                    )
                    ?>',
                icon_url
            );
        }
        // ---
        $('#packages-table-body').html(
            $('#packages-table-body').html() +
            packages_table_body_row_template.format(
                '<img class="package-icon" src="{0}"></img>'.format(icon_url),
                col1,
                installed,
                "{0}{1}{2}".format(source_action, update_btn, main_action),
                "{0},{1},{2}".format(pack.id, pack.name, pack.description),
                "compose-package-{0}".format(pack.id)
            )
        )
    }

    function fetch_package_list_success_fcn(result) {
        let doc = JSON.parse(result);
        // add packages to the list
        ProgressBar.set(100);
        for (let i = 0; i < doc.packages.length; i++) {
            add_package_to_list(i + 1, doc.packages[i]);
        }
        render_changes();
    }

    // filter by keyword
    $('#packages-search-field').keyup(function () {
        let valThis = $(this).val().toLowerCase();
        $('.compose-package').each(function () {
            let text = $(this).data('search').toLowerCase();
            let parent = $(this);
            (text.indexOf(valThis) !== -1) ? parent.show() : parent.hide();
        });
    });

    $(document).ready(function () {
        $('#status_label').html('Downloading packages list...');
        ProgressBar.set(10);
        // ---
        let url = "<?php echo $assets_index_url ?>";
        callExternalAPI(
            url,
            'GET',
            'text',
            false,
            false,
            fetch_package_list_success_fcn,
            true
        );
        // ---
        render_changes();
    });

</script>
