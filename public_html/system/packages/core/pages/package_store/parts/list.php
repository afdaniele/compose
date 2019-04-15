<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Formatter;

$installed_packages = Core::getPackagesList();
?>

<!-- Load YAML library -->
<script type="text/javascript" src="<?php echo Configuration::$BASE_URL ?>js/js-yaml.min.js"></script>

<style type="text/css">
  #packages-table > thead > tr{
    font-weight: bold;
  }

  #packages-table > thead td:nth-child(1),
  #packages-table > thead td:nth-child(3),
  #packages-table > thead td:nth-child(4){
    text-align: center;
  }

  #packages-table > tbody .compose-package > td:nth-child(1),
  #packages-table > tbody .compose-package > td:nth-child(3),
  #packages-table > tbody .compose-package > td:nth-child(4){
    text-align: center;
    vertical-align: middle;
  }

  #packages-table > tbody .compose-package > td:nth-child(1){
    font-weight: bold;
  }

  #packages-table > tbody .compose-package > td:nth-child(1),
  #packages-table > tbody .compose-package > td:nth-child(2),
  #packages-table > tbody .compose-package > td:nth-child(3){
    border-right: none;
  }

  #packages-table > tbody .compose-package > td:nth-child(2),
  #packages-table > tbody .compose-package > td:nth-child(3),
  #packages-table > tbody .compose-package > td:nth-child(4){
    border-left: none;
  }

  #packages-table > tbody .compose-package .main-button{
    width: 100px;
  }

  #packages-table > tbody .compose-package .package-icon{
    width: 42px;
  }

  #packages-table > tbody .compose-package.to-be-installed{
    background-color: rgba(0,255,0,0.1);
  }

  #packages-table > tbody .compose-package.to-be-uninstalled{
    background-color: rgba(255,0,0,0.1);
  }
</style>

<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:2px">
	<tr>
		<td style="width:100%">
			<h2>
        Package Store
        <a id="apply_changes_btn" class="btn btn-success" role="button" style="display:none; float:right" onclick="apply_changes()" href="javascript:void(0);">Apply changes</a>
        <span id="status_label" style="font-size:12pt; float:right; padding-top:12px; font-weight:normal;"></span>
      </h2>
		</td>
	</tr>
</table>
<div class="progress" style="height:14px">
  <div id="loading_status_bar" class="progress-bar progress-bar-striped progress-bar-default active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:100%">
  </div>
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
  <input type="text" class="form-control" id="packages-search-field" style="height:42px" aria-describedby="packages-search-addon">
</div>

<table class="table table-striped table-bordered table-hover" id="packages-table" style="margin-top:20px">
  <thead>
    <tr>
      <td class="col-md-1">

      </td>
      <td class="col-md-7">
        Package
      </td>
      <td class="col-md-1">
        Installed
      </td>
      <td class="col-md-3">
        Actions
      </td>
    </tr>
  </thead>
  <tbody id="packages-table-body">
  </tbody>
</table>


<script type="text/javascript">

  packages_to_install = [];
  packages_to_uninstall = [];

  var providers = {
    'github.com': 'https://raw.githubusercontent.com/{0}/{1}/{2}',
    'bitbucket.org': 'https://bitbucket.org/{0}/{1}/raw/{2}'
  }

  var providers_repo = {
    'github.com': 'https://github.com/{0}/{1}',
    'bitbucket.org': 'https://bitbucket.org/{0}/{1}'
  }

  var providers_source = {
    'github.com': 'https://github.com/{0}/{1}/tree/{2}',
    'bitbucket.org': 'https://bitbucket.org/{0}/{1}/src/{2}'
  }

  var installed_packages = JSON.parse('<?php echo json_encode($installed_packages) ?>');

  var installed_packages_ids = Object.keys(installed_packages);

  var packages_table_body_row_template = `
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

  var package_template = `
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

  var package_version_line_template = `
    {0}: <span class="mono" style="color:{1}">{2}</span>
  `;

  function render_changes(){
    // show info about how many packages will be installed/removed
    var install = '{0} package(s) will be installed'.format(packages_to_install.length)
    var uninstall = '{0} package(s) will be removed'.format(packages_to_uninstall.length)
    var labels = [];
    if(packages_to_install.length > 0)
      labels.push(install);
    if(packages_to_uninstall.length > 0)
      labels.push(uninstall);
    $('#status_label').html(labels.join(', '));
    // show/hide apply changes button
    $('#apply_changes_btn').css('display', 'none');
    if (packages_to_install.length + packages_to_uninstall.length > 0){
      $('#status_label').html($('#status_label').html() + '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;');
      $('#apply_changes_btn').css('display', 'inline-block');
    }
    // colorize the choices
    $('#packages-table > tbody .compose-package').each(function(index){
      $(this).removeClass('to-be-installed');
      $(this).removeClass('to-be-uninstalled');
    });
    $('#packages-table > tbody .compose-package .main-button.undo-button').each(function(index){
      $(this).css('display', 'none');
    });
    $('#packages-table > tbody .compose-package .main-button.action-button').each(function(index){
      $(this).css('display', 'inline-block');
    });
    $.each(packages_to_install, function(i, package_name){
      $('.compose-package#compose-package-{0} .main-button.undo-button'.format(package_name)).css('display', 'inline-block');
      $('.compose-package#compose-package-{0} .main-button.action-button'.format(package_name)).css('display', 'none');
      $('.compose-package#compose-package-{0}'.format(package_name)).addClass('to-be-installed');
    });
    $.each(packages_to_uninstall, function(i, package_name){
      $('.compose-package#compose-package-{0} .main-button.undo-button'.format(package_name)).css('display', 'inline-block');
      $('.compose-package#compose-package-{0} .main-button.action-button'.format(package_name)).css('display', 'none');
      $('.compose-package#compose-package-{0}'.format(package_name)).addClass('to-be-uninstalled');
    });
  }//render_changes

  function mark_to_install(package_name){
    // if already marked, do nothing
    idx = packages_to_install.indexOf(package_name);
    if(idx >= 0)
      return;
    // if marked to uninstall, just remove it from the list
    idx = packages_to_uninstall.indexOf(package_name);
    if(idx >= 0){
      packages_to_uninstall.splice(idx, 1);
    }
    // if not installed, mark to install
    idx = installed_packages_ids.indexOf(package_name);
    if(idx < 0){
      packages_to_install.push(package_name)
    }
    // render new status
    render_changes();
  }//mark_to_install

  function mark_to_uninstall(package_name){
    // if already marked, do nothing
    idx = packages_to_uninstall.indexOf(package_name);
    if(idx >= 0)
      return;
    // remove from packages marked to install
    idx = packages_to_install.indexOf(package_name);
    if(idx >= 0){
      packages_to_install.splice(idx, 1);
    }
    // if installed, mark to uninstall
    idx = installed_packages_ids.indexOf(package_name);
    if(idx >= 0){
      packages_to_uninstall.push(package_name);
    }
    // render new status
    render_changes();
  }//mark_to_uninstall

  function apply_changes(){
    var install = packages_to_install.join(',');
    var uninstall = packages_to_uninstall.join(',');
    var qs = 'install={0}&uninstall={1}'.format(install, uninstall);
    var url = 'package_store/install?{0}'.format(qs);
    location.href = url;
  }//apply_changes

  function add_package_to_list(num, pack){
    var is_installed = (installed_packages_ids.indexOf(pack.id) >= 0);
    var version_str_fmt = '{0}{1}{2}';
    var installed_version_str = '';
    var version_sep_str = '';
    var available_version_str = '';
    var installed_version = (is_installed)? installed_packages[pack.id].codebase.head_tag : null;
    if(is_installed){
      // show installed version
      installed_version = (installed_version == 'ND')? 'devel' : installed_version;
      installed_version_str = package_version_line_template.format(
        'Installed version',
        'grey',
        installed_version
      );
    }
    // ---
    // show available version (if any)
    var latest_version = pack.git_branch;
    if(latest_version != 'master'){
      // show available version
      var color = 'grey';
      if(is_installed && installed_version != 'devel' && latest_version != installed_version){
        color = 'darkgreen';
      }
      version_sep_str = (installed_version_str.length > 0)? '  |  ' : '';
      available_version_str = package_version_line_template.format(
        'Available version',
        color,
        latest_version
      );
    }
    var version_str = version_str_fmt.format(
      installed_version_str,
      version_sep_str,
      available_version_str
    )
    // ---
    var col1 = package_template.format(
      pack.name,
      pack.id,
      pack.git_owner,
      pack.description,
      version_str
    );
    // ---
    var installed = (is_installed)?
      '<?php echo Formatter::format(1, Formatter::BOOLEAN) ?>' : '<?php echo Formatter::format(0, Formatter::BOOLEAN) ?>';
    // ---
    var git_action_url = providers_source[pack.git_provider].format(pack.git_owner, pack.git_repository, pack.git_branch);
    var source_action = `
      <a class="btn btn-default" href="{0}" role="button" target="_blank">
        <i class="fa fa-code" aria-hidden="true"></i>&nbsp;
        Code
      </a>`.format(git_action_url);
    var git_repo_url = providers_repo[pack.git_provider].format(pack.git_owner, pack.git_repository);
    var install_action = `
      <a role="button" class="btn btn-success main-button action-button" onclick="mark_to_install('{0}')" href="javascript:void(0);">
        <i class="fa fa-download" aria-hidden="true"></i>&nbsp;
        Install
      </a>
      <a role="button" class="btn btn-warning main-button undo-button" style="display:none" onclick="mark_to_uninstall('{0}')" href="javascript:void(0);">
        <i class="fa fa-times" aria-hidden="true"></i>&nbsp;
        Cancel
      </a>`;
    var uninstall_action = `
      <a role="button" class="btn btn-danger main-button action-button" onclick="mark_to_uninstall('{0}')" href="javascript:void(0);">
        <i class="fa fa-trash" aria-hidden="true"></i>&nbsp;
        Uninstall
      </a>
      <a role="button" class="btn btn-warning main-button undo-button" style="display:none" onclick="mark_to_install('{0}')" href="javascript:void(0);">
        <i class="fa fa-times" aria-hidden="true"></i>&nbsp;
        Cancel
      </a>`;
    var update_action = `
      <a role="button" class="btn btn-info main-button action-button" onclick="mark_to_update('{0}')" href="javascript:void(0);">
        <i class="fa fa-trash" aria-hidden="true"></i>&nbsp;
        Update
      </a>
      <a role="button" class="btn btn-warning main-button undo-button" style="display:none" onclick="mark_to_install('{0}')" href="javascript:void(0);">
        <i class="fa fa-times" aria-hidden="true"></i>&nbsp;
        Cancel
      </a>`;
    var main_action = (is_installed)? uninstall_action : install_action;
    main_action = main_action.format(pack.id);
    var icon_url = pack.icon;
    if (!icon_url.startsWith('http')){
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
        '{0}&nbsp;{1}'.format(source_action, main_action),
        "{0},{1},{2}".format(pack.id, pack.name, pack.description),
        "compose-package-{0}".format(pack.id)
      )
    )
  }

  function fetch_package_list_success_fcn(result){
    $('#status_label').html('Downloading packages list...');
    var doc = jsyaml.load(result);
    // add packages to the list
    $('#loading_status_bar').removeClass('progress-bar-striped progress-bar-default');
    $('#loading_status_bar').addClass('progress-bar-success');
    for (var i = 0; i < doc.packages.length; i++) {
      var pack = doc.packages[i];
      // ---
      add_package_to_list(i+1, pack);
      //
      $('#status_label').html('');
      setTimeout(function(){
        $('#loading_status_bar').parent().remove();
      }, 800);
    }
  }

  $(document).ready(function(){
    var url = "<?php echo $assets_index_url ?>";
    callExternalAPI(
      url,
      'GET',
      'text',
      false,
      false,
      fetch_package_list_success_fcn,
      true
    );
  });

  // filter by keyword
  $('#packages-search-field').keyup(function(){
      var valThis = $(this).val().toLowerCase();
      $('.compose-package').each(function(){
          var text = $(this).data('search').toLowerCase();
          var parent = $(this);
          (text.indexOf(valThis) != -1) ? parent.show() : parent.hide();
      });
  });

</script>
