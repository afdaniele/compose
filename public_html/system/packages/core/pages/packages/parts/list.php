<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Formatter;
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
        Packages
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
$branch = 'master';
$assets_index_url = sprintf('%s/%s/index', Configuration::$ASSETS_STORE_URL, $branch);
?>

<div class="input-group" style="margin-top:28px">
  <span class="input-group-addon" id="packages-search-addon">Search package</span>
  <input type="text" class="form-control" id="packages-search-field" style="height:42px" aria-describedby="packages-search-addon">
</div>

<table class="table table-striped table-bordered table-hover" id="packages-table" style="margin-top:20px">
  <thead>
    <tr>
      <td class="col-md-1">
        #
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

  var installed_packages = [
    <?php echo '"'.implode('", "', array_keys(Core::getPackagesList())).'"' ?>
  ];

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
    <strong id="compose-package-field-name">
      <div class="progress" style="height:10px; width:200px">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; background-color:lightgray;">
        </div>
      </div>
    </strong>
    ID: <span class="mono" style="color:grey">{0}</span><br/>
    Maintainer: <span class="mono" style="color:grey">{1}</span>
    <div id="compose-package-field-description" style="margin-top:4px">
      <div class="progress" style="height:10px">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; background-color:lightgray;">
        </div>
      </div>
    </div>`;

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
    idx = installed_packages.indexOf(package_name);
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
    idx = installed_packages.indexOf(package_name);
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
    var url = 'packages/install?{0}'.format(qs);
    location.href = url;
  }//apply_changes

  function add_package_placeholder_to_list(num, id, provider, owner, repository, branch){
    var col1 = package_template.format(id, owner);
    // ---
    var is_installed = (installed_packages.indexOf(id) >= 0);
    var installed = (is_installed)?
      '<?php echo Formatter::format(1, Formatter::BOOLEAN) ?>' : '<?php echo Formatter::format(0, Formatter::BOOLEAN) ?>';
    // ---
    var git_action_url = providers_source[provider].format(owner, repository, branch);
    var source_action = `
      <a class="btn btn-default" href="{0}" role="button" target="_blank">
        <i class="fa fa-code" aria-hidden="true"></i>&nbsp;
        Code
      </a>`.format(git_action_url);
    var git_repo_url = providers_repo[provider].format(owner, repository);
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
    var main_action = (is_installed)? uninstall_action : install_action;
    main_action = main_action.format(id);
    // ---
    $('#packages-table-body').html(
      $('#packages-table-body').html() +
      packages_table_body_row_template.format(
        num,
        col1,
        installed,
        '{0}&nbsp;{1}'.format(source_action, main_action),
        "{0},{1}".format(id, '{0}'),
        "compose-package-"+id
      )
    )
  }

  function fetch_package_info_success_fcn(id, result){
    $("#compose-package-{0} #compose-package-field-name".format(id)).html(result.name+'<br/>');
    $("#compose-package-{0} #compose-package-field-description".format(id)).html(result.description);
    window.packages_loaded_no += 1;
    // update status label
    if (window.packages_loaded_no == window.packages_total_no){
      $('#status_label').html('');
      setTimeout(function(){
        $('#loading_status_bar').parent().remove();
        $('#apply_changes_btn').css('display', 'default');
      }, 800);
    }else{
      $('#status_label').html('Remaining {0}/{1}'.format(
        window.packages_total_no-window.packages_loaded_no,
        window.packages_total_no
      ));
    }
    // update progress bar
    progress = Math.ceil(100.0 * (window.packages_loaded_no / window.packages_total_no));
    $('#loading_status_bar').css('width', '{0}%'.format(progress));
  }

  function fetch_package_info(id, provider, owner, repository, branch){
    // make sure we can handle this provider
    if (!(provider in providers))
      return;
    // ---
    var provider_base_url = providers[provider].format(owner, repository, branch);
    var url = "{0}/{1}".format(provider_base_url, 'metadata.json');
    callExternalAPI(
      url,
      'GET',
      'text',
      false,
      false,
      function(result_json){
        var result = JSON.parse(result_json);
        fetch_package_info_success_fcn(id, result);
      },
      true
    );
  }

  function fetch_package_list_success_fcn(result){
    $('#status_label').html('Downloading packages list...');
    var doc = jsyaml.load(result);
    // add packages to the list
    window.packages_total_no = doc.packages.length;
    window.packages_loaded_no = 0;
    $('#loading_status_bar').removeClass('progress-bar-striped progress-bar-default');
    $('#loading_status_bar').addClass('progress-bar-success');
    $('#loading_status_bar').css('width', '0%');
    for (var i = 0; i < doc.packages.length; i++) {
      var pack = doc.packages[i];
      // ---
      add_package_placeholder_to_list(i+1, pack.id, pack.git_provider, pack.git_owner, pack.git_repository, pack.git_branch);
      fetch_package_info(pack.id, pack.git_provider, pack.git_owner, pack.git_repository, pack.git_branch);
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
