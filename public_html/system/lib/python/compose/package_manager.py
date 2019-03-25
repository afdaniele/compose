import json
import yaml
import requests
import subprocess
from glob import glob
from os.path import join, abspath, dirname, isdir, isfile, basename, exists
import shutil
import git

class PackageManager(object):

  def __init__(self):
    self._compose_dir = abspath(join(
      dirname(abspath(__file__)),
      '..', '..', '..', '..'
    ))
    self._packages_dir = join(self._compose_dir, 'system', 'packages')
    # check if the directory system/packages exists
    if not isdir(self._packages_dir):
      self.error(
        'init',
        None,
        'init',
        'The directory "%s" does not exist' % self._packages_dir,
        None,
        1
      )
    # read remote index url
    self._assets_store_url = None
    config_file = join(self._compose_dir, 'system', 'config', 'configuration.php')
    if not isfile(config_file):
      config_file = join(self._compose_dir, 'system', 'config', 'configuration.default.php')
    if not isfile(config_file):
      files = join(self._compose_dir, 'system', 'config', 'configuration(.default).php')
      self.error(
        'init',
        None,
        'init',
        'Configuration files "%s" not found!' % files,
        None,
        2
      )
    with open(config_file, 'rt') as fp:
      content = fp.readlines()
      line = [l for l in content if 'ASSETS_STORE_URL' in l][0]
      self._assets_store_url = line.split('=')[1].replace("'", '').replace(';', '').strip()
    # retrieve index
    self._index = self.get_available_packages()

  def list_installed_packages(self):
    dirs = [d for d in glob(join(self._packages_dir, '*')) if isdir(d)]
    packages = [basename(d) for d in dirs if isfile(join(d, 'metadata.json'))]
    return packages

  def get_package(self, package_name):
    if package_name in self.list_installed_packages():
      return Package(join(self._packages_dir, package_name))
    self.error(
      'init',
      None,
      'get_package',
      'Package "%s" not found' % package_name,
      None,
      11
    )

  def get_available_packages(self):
    index_url = '%s/master/index' % self._assets_store_url
    response = requests.get(index_url)
    data = yaml.load(response.text, Loader=yaml.BaseLoader)
    packages = {
      p['id'] : p for p in data['packages']
    }
    return packages

  def install(self, package_name):
    # make sure that the package is available
    if package_name not in self._index:
      self.error(
        'install',
        package_name,
        'get_package',
        'Package "%s" not found' % package_name,
        None,
        12
      )
    # make sure that the package is not present already
    if package_name in self.list_installed_packages():
      self.error(
        'install',
        package_name,
        'install',
        'The package "%s" is already installed!' % package_name,
        None,
        13
      )
    # make sure that the destination directory is not taken
    package_path = join(self._packages_dir, package_name)
    if exists(package_path):
      self.error(
        'install',
        package_name,
        'install',
        'The directory/file "system/packages/%s" already exists' % package_name,
        None,
        14
      )
    # clone
    package_info = self._index[package_name]
    package_git_url = 'https://%s/%s/%s' % (
      package_info['git_provider'],
      package_info['git_owner'],
      package_info['git_repository']
    )
    repo = git.Repo.clone_from(
      package_git_url,
      package_path,
      branch=package_info['git_branch'],
      depth=1
    )
    # repo.submodule_update(
    #   init=True,
    #   recursive=True
    # )

  def post_install(self, package_name):
    # exec post_install if available
    post_install_file = join(self._packages_dir, package_name, 'post_install')
    if isfile(post_install_file):
      post_install_process = subprocess.Popen(post_install_file, shell=True, stdout=subprocess.PIPE)
      post_install_process.wait()
      _, err = post_install_process.communicate()
      if post_install_process.returncode != 0:
        self.error(
          'install',
          package_name,
          'post_install',
          err,
          post_install_process.returncode,
          15
        )

  def uninstall(self, package_name):
    # make sure that the package is installed
    if package_name not in self.list_installed_packages():
      self.error(
        'uninstall',
        package_name,
        'uninstall',
        'The package "%s" is not installed!' % package_name,
        None,
        21
      )
    # ---
    shutil.rmtree(join(self._packages_dir, package_name))

  def error(self, task, package_name, step, error_msg, source_error_code, return_code):
    print('An error occurred while %sing the package "%s".' % (task, package_name))
    print('    - Step: %s' % step)
    print('    - Source error code: %s' % source_error_code)
    print('    - Error: %s' % error_msg)
    # ---
    exit(return_code)


class Package(object):

  def __init__(self, path):
    self._name = basename(path)
    self._path = abspath(path)
    with open(join(self._path, 'metadata.json'), 'rt') as fp:
      self._metadata = json.load(fp)

  @property
  def name(self):
    return self._name

  @property
  def path(self):
    return self._path

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


if __name__ == '__main__':

  import argparse

  parser = argparse.ArgumentParser(description='Manage compose packages')
  parser.add_argument('--install', metavar='N', type=str, nargs='+',
                      help='a comma-separated list of packages to install')
  parser.add_argument('--uninstall', metavar='N', type=str, nargs='+',
                      help='a comma-separated list of packages to uninstall')
  args = parser.parse_args()

  # ---

  pm = PackageManager()

  # perform uninstall
  for package_name in args.uninstall or []:
    pm.uninstall(package_name)

  # perform install
  for package_name in args.install or []:
    pm.install(package_name)

  # perform post_install
  for package_name in args.install or []:
    pm.post_install(package_name)
