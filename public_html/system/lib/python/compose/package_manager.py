import json
import yaml
import requests
import subprocess
from enum import Enum
from glob import glob
from collections import defaultdict
from os.path import join, abspath, dirname, isdir, isfile, basename, exists
from toposort import toposort_flatten
import shutil
import git


def exit_with_code(exit_code, message, data):
  exit_str = json.dumps({
    'exit_code': exit_code.value,
    'message': message,
    'data': data
  })
  print(exit_str)
  exit(exit_code.value)

def error(task, step, package_name, error_msg, source_error_code, exit_code):
  message_str = 'Error: %s[%d] :: %s' % (
    exit_code.name,
    exit_code.value,
    error_msg
  )
  data = {
    'task': task.name,
    'step': step.name,
    'package_name': package_name,
    'error_msg': error_msg,
    'source_error_code': source_error_code
  }
  exit_with_code(exit_code, message_str, data)


class PackageManager(object):

  class Task(Enum):
    INIT = 1
    DEPENDENCIES_SOLVER = 2
    INSTALL = 3
    UPDATE = 4
    UNINSTALL = 5
    INIT_PACKAGE = 6

  class InitStep(Enum):
    INIT = 1
    GET_PACKAGE = 2

  class InitPackageStep(Enum):
    INIT = 1

  class DependenciesSolverStep(Enum):
    INIT = 1
    GET_PACKAGE = 2

  class InstallStep(Enum):
    INIT = 1
    GET_PACKAGE = 2
    PRE_INSTALL = 3
    INSTALL = 4
    POST_INSTALL = 5

  class UpdateStep(Enum):
    INIT = 1
    GET_PACKAGE = 2
    PRE_UPDATE = 3
    UPDATE = 4
    POST_UPDATE = 5

  class UninstallStep(Enum):
    INIT = 1
    GET_PACKAGE = 2
    PRE_UNINSTALL = 3
    UNINSTALL = 4
    POST_UNINSTALL = 5

  class Error(Enum):
    NO_PACKAGES_DIR = 1
    NO_CONFIG_FILE = 2
    PACKAGE_NOT_INSTALLED = 11
    PACKAGE_NOT_FOUND = 12
    PACKAGE_ALREADY_INSTALLED = 13
    POST_INSTALL = 15
    POST_UPDATE = 25

  class Success(Enum):
    OK = 0

  def __init__(self):
    self._compose_dir = abspath(join(
      dirname(abspath(__file__)),
      '..', '..', '..', '..'
    ))
    self._packages_dir = join(self._compose_dir, 'system', 'packages')
    # check if the directory system/packages exists
    if not isdir(self._packages_dir):
      error(
        PackageManager.Task.INIT,
        PackageManager.InitStep.INIT,
        None,
        'The directory "%s" does not exist' % self._packages_dir,
        None,
        PackageManager.Error.NO_PACKAGES_DIR
      )
    # read remote index url
    self._assets_store_url = None
    config_file = join(self._compose_dir, 'system', 'config', 'configuration.php')
    if not isfile(config_file):
      config_file = join(self._compose_dir, 'system', 'config', 'configuration.default.php')
    if not isfile(config_file):
      files = join(self._compose_dir, 'system', 'config', 'configuration(.default).php')
      error(
        PackageManager.Task.INIT,
        PackageManager.InitStep.INIT,
        None,
        'Configuration files "%s" not found!' % files,
        None,
        PackageManager.Error.NO_CONFIG_FILE
      )
    with open(config_file, 'rt') as fp:
      content = fp.readlines()
      # read ASSETS_STORE_URL from config file
      line = [l for l in content if 'ASSETS_STORE_URL' in l][0].split('=')[1]
      assets_store_url = line.replace("'", '').replace('"', '').replace(';', '').strip()
      self._assets_store_url = assets_store_url
      # ---
      # read ASSETS_STORE_BRANCH from config file
      line = [l for l in content if 'ASSETS_STORE_BRANCH' in l][0].split('=')[1]
      assets_store_branch = line.replace("'", '').replace('"', '').replace(';', '').strip()
      self._assets_store_branch = assets_store_branch
    # retrieve index
    self._index = self.get_available_packages()

  def list_installed_packages(self):
    dirs = [d for d in glob(join(self._packages_dir, '*')) if isdir(d)]
    packages = [basename(d) for d in dirs if isfile(join(d, 'metadata.json'))]
    return packages

  def get_package(self, package_name):
    package_path = join(self._packages_dir, package_name)
    if package_name in self.list_installed_packages():
      return Package(package_name, package_path)
    if package_name in self._index:
      package_info = self._index[package_name]
      package_git_url = 'https://%s/%s/%s' % (
        package_info['git_provider'],
        package_info['git_owner'],
        package_info['git_repository']
      )
      return Package(package_name, package_path, package_git_url, package_info['git_branch'])
    # ---
    error(
      PackageManager.Task.INIT,
      PackageManager.InitStep.GET_PACKAGE,
      None,
      'Package "%s" not found' % package_name,
      None,
      PackageManager.Error.PACKAGE_NOT_INSTALLED
    )

  def get_available_packages(self):
    index_url = '%s/%s/index' % (self._assets_store_url, self._assets_store_branch)
    response = requests.get(index_url)
    data = yaml.load(response.text, Loader=yaml.BaseLoader)
    packages = {
      p['id'] : p for p in data['packages']
    }
    return packages

  def solve_dependencies_graph(self, packages_to_install):
    dep_graph = {}
    # ---
    def extend_dep_graph(package_name):
      dep_sub_graph = {}
      # make sure that the package is available
      if package_name not in self._index:
        error(
          PackageManager.Task.DEPENDENCIES_SOLVER,
          PackageManager.DependenciesSolverStep.GET_PACKAGE,
          package_name,
          'Package "%s" not found' % package_name,
          None,
          PackageManager.Error.PACKAGE_NOT_FOUND
        )
      # get deps
      deps = self._index[package_name]['dependencies']
      dep_sub_graph[package_name] = set(deps)
      # extend deps
      for d in deps:
        dep_sub_graph.update(extend_dep_graph(d))
      return dep_sub_graph
    # ---
    # build graph
    for package_name in packages_to_install:
      dep_graph.update(extend_dep_graph(package_name))
    # get flatten list of packages to install
    return toposort_flatten(dep_graph)

  def install(self, package_name, dryrun=False):
    # nothing to do if the package is already installed
    if package_name in self.list_installed_packages():
      return
    # make sure that the package is available
    if package_name not in self._index:
      error(
        PackageManager.Task.INSTALL,
        PackageManager.InstallStep.GET_PACKAGE,
        package_name,
        'Package "%s" not found' % package_name,
        None,
        PackageManager.Error.PACKAGE_NOT_FOUND
      )
    # create package
    package = self.get_package(package_name)
    package.install(dryrun=dryrun)

  def post_install(self, package_name, dryrun=False):
    package = self.get_package(package_name)
    package.post_install(dryrun=dryrun)

  def update(self, package_name, version=None, dryrun=False):
    package = self.get_package(package_name)
    if not version:
      # assume latest
      version = self._index[package_name]['git_branch']
    package.update(version, dryrun=dryrun)

  def post_update(self, package_name, dryrun=False):
    package = self.get_package(package_name)
    package.post_update(dryrun=dryrun)

  def uninstall(self, package_name, dryrun=False):
    package = self.get_package(package_name)
    package.uninstall(dryrun=dryrun)

  def post_uninstall(self, package_name, dryrun=False):
    package = self.get_package(package_name)
    package.post_uninstall(dryrun=dryrun)


class Package(object):

  def __init__(self, package_name, path, remote_url=None, remote_version=None):
    self._name = str(package_name)
    self._path = abspath(path)
    self._metadata = defaultdict(lambda: None)
    self._remote_version = remote_version
    # check if the package is installed
    self._is_installed = isdir(self._path)
    # set remte URL
    if self._is_installed and not remote_url:
      repo = git.Repo(self._path)
      remote_url = repo.remotes.origin.url
    self._remote_url = remote_url
    # load metadata
    if self._is_installed:
      with open(join(self._path, 'metadata.json'), 'rt') as fp:
        self._metadata = json.load(fp)
    # handle case: not_installed and remote_url=None
    if not self._is_installed and not self._remote_url:
      error(
        PackageManager.Task.INIT_PACKAGE,
        PackageManager.InitPackageStep.INIT,
        self._name,
        'The package "%s" is not installed!' % self._name,
        None,
        PackageManager.Error.PACKAGE_NOT_INSTALLED
      )

  @property
  def name(self):
    return self._name

  @property
  def path(self):
    return self._path

  @property
  def remote_url(self):
    return self._remote_url

  @property
  def is_installed(self):
    return self._is_installed

  @property
  def description(self):
    return self._metadata['description']

  @property
  def dependencies(self):
    return self._metadata['dependencies']

  def get_pages_list(self):
    dirs = [d for d in glob(join(self._path, 'pages', '*')) if isdir(d)]
    pages = [basename(d) for d in dirs if isfile(join(d, 'metadata.json'))]
    return pages

  def install(self, version=None, dryrun=False):
    if self.is_installed:
      error(
        PackageManager.Task.INSTALL,
        PackageManager.InstallStep.PRE_INSTALL,
        self.name,
        'The package "%s" is already installed' % self.name,
        None,
        PackageManager.Error.PACKAGE_ALREADY_INSTALLED
      )
    # ---
    if dryrun:
      return
    if not version:
      version = self._remote_version
    # clone git repository
    git.Repo.clone_from(
      self.remote_url,
      self.path,
      branch=version,
      depth=1
    )

  def post_install(self, dryrun=False):
    self._perform_aux_action(
      PackageManager.Task.INSTALL,
      PackageManager.InstallStep.POST_INSTALL,
      PackageManager.Error.POST_INSTALL,
      dryrun=dryrun
    )

  def update(self, version=None, dryrun=False):
    # make sure that the package is installed
    if not self.is_installed:
      error(
        PackageManager.Task.UPDATE,
        PackageManager.UpdateStep.PRE_UPDATE,
        self.name,
        'The package "%s" is not installed!' % self.name,
        None,
        PackageManager.Error.PACKAGE_NOT_INSTALLED
      )
    # ---
    if dryrun:
      return
    if not version:
      version = self._remote_version
    # perform git checkout
    repo = git.Repo(self._path)
    repo.git.fetch('origin', '+refs/tags/%s:refs/tags/%s' % (version, version))
    repo.git.checkout(version)

  def post_update(self, dryrun=False):
    self._perform_aux_action(
      PackageManager.Task.UPDATE,
      PackageManager.UpdateStep.POST_UPDATE,
      PackageManager.Error.POST_UPDATE,
      dryrun=dryrun
    )

  def uninstall(self, dryrun=False):
    # make sure that the package is installed
    if not self.is_installed:
      error(
        PackageManager.Task.UNINSTALL,
        PackageManager.UninstallStep.PRE_UNINSTALL,
        self.name,
        'The package "%s" is not installed!' % self.name,
        None,
        PackageManager.Error.PACKAGE_NOT_INSTALLED
      )
    # ---
    if dryrun:
      return
    shutil.rmtree(self.path)

  def post_uninstall(self, dryrun=False):
    self._perform_aux_action(
      PackageManager.Task.UNINSTALL,
      PackageManager.UpdateStep.POST_UNINSTALL,
      PackageManager.Error.POST_UNINSTALL,
      dryrun=dryrun
    )

  def _perform_aux_action(self, task, step, error_code, dryrun=False):
    if dryrun:
      return
    # exec aux file script (if available)
    action_file = join(self.path, step.name.lower())
    if isfile(action_file):
      action_command = '%s > /dev/null 2>&1' % action_file
      action_process = subprocess.Popen(action_command, shell=True, stdout=None, stderr=None)
      action_process.wait()
      _, error_str = action_process.communicate()
      if action_process.returncode != 0:
        error(
          task,
          step,
          self.name,
          error_str,
          action_process.returncode,
          error_code
        )


if __name__ == '__main__':

  import argparse

  parser = argparse.ArgumentParser(description='Manage compose packages')
  parser.add_argument('--install', metavar='N', type=str, nargs='+',
                      help='a comma-separated list of packages to install')
  parser.add_argument('--uninstall', metavar='N', type=str, nargs='+',
                      help='a comma-separated list of packages to uninstall')
  parser.add_argument('--update', metavar='N', type=str, nargs='+',
                      help='a comma-separated list of packages to update')
  parser.add_argument('--dry-run', action='store_true', default=False,
                      help='do not commit changes')
  args = parser.parse_args()

  # ---

  pm = PackageManager()

  # output
  out_data = {
    'installed': [],
    'updated': [],
    'uninstalled': []
  }

  # solve dependency graph
  to_install = set(args.install or [])
  to_update = set(args.update or [])
  to_install = to_install.union(to_update)
  to_install = pm.solve_dependencies_graph(to_install)

  # perform uninstall
  for package_name in args.uninstall or []:
    pm.uninstall(package_name, dryrun=args.dry_run)
    out_data['uninstalled'].append(package_name)

  # perform update
  requires_post_update = []
  for package_name in to_update:
    if package_name in pm.list_installed_packages():
      pm.update(package_name, dryrun=args.dry_run)
      requires_post_update.append(package_name)
      out_data['updated'].append(package_name)

  # perform install
  requires_post_install = []
  for package_name in to_install:
    if package_name not in pm.list_installed_packages():
      pm.install(package_name, dryrun=args.dry_run)
      requires_post_install.append(package_name)
      out_data['installed'].append(package_name)

  # perform post_update
  for package_name in requires_post_update:
    pm.post_update(package_name, dryrun=args.dry_run)

  # perform post_install
  for package_name in requires_post_install:
    pm.post_install(package_name, dryrun=args.dry_run)

  # exit
  exit_with_code(
    pm.Success.OK,
    'Done!',
    out_data
  )
