import sys
import json
import uuid
import requests
import subprocess
from enum import Enum
from glob import glob
from collections import defaultdict
from os import mkdir, environ
from os.path import join, abspath, dirname, isdir, isfile, basename
from toposort import toposort_flatten
import shutil
import semver


def _version(ver):
    return ver[1:]


def version_lt(ver1, ver2):
    return semver.compare(_version(ver1), _version(ver2)) == -1


def version_gt(ver1, ver2):
    return semver.compare(_version(ver1), _version(ver2)) == 1


def version_lte(ver1, ver2):
    return semver.compare(_version(ver1), _version(ver2)) <= 0


def version_gte(ver1, ver2):
    return semver.compare(_version(ver1), _version(ver2)) >= 0


def exit_with_code(exit_code, message, data):
    exit_str = json.dumps({
        'exit_code': exit_code.value,
        'message': message,
        'data': data
    })
    log('------------------------------------')
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


def log(message):
    for line in message.split('\n'):
        print('# %s' % line)
    sys.stdout.flush()


def exec_cmd(command, retry_cleanup_command=None):
    num_trials = 3
    for i in range(1, num_trials + 1, 1):
        timeout = 30 * i
        log('   > Trial %d/%d (timeout %d secs)...' % (i, num_trials, timeout))
        # get remote url from repo
        try:
            log('   > $ %s' % str(command))
            pipe = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
            pipe.wait(timeout=timeout)
            output_str, error_str = pipe.communicate()
            log('   > Trial: Done!')
            return pipe.returncode, output_str, error_str
        except subprocess.TimeoutExpired:
            if retry_cleanup_command is not None:
                pipe = subprocess.Popen(retry_cleanup_command, stdout=subprocess.PIPE,
                                        stderr=subprocess.PIPE)
                pipe.wait()
            continue
    # raise an error
    error(
        PackageManager.Task.GENERIC,
        PackageManager.InitStep.INIT,
        None,
        'The command "%s" timed out after %d trials' % (' '.join(command), num_trials),
        None,
        PackageManager.Error.GIT_SUBPROCESS_TIMEOUT
    )


class PackageManager(object):
    class Task(Enum):
        GENERIC = 0
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
        # generic error codes: 1-9
        NO_PACKAGES_DIR = 1
        NO_CONFIG_FILE = 2
        GIT_SUBPROCESS_TIMEOUT = 3
        GIT_REMOTE_GET_URL_ERROR = 3
        # installation error codes: 10-19
        PACKAGE_NOT_INSTALLED = 11
        PACKAGE_NOT_FOUND = 12
        PACKAGE_ALREADY_INSTALLED = 13
        GIT_CLONE_ERROR = 14
        NO_COMPATIBLE_VERSION_FOUND = 15
        POST_INSTALL = 19
        # update error codes: 20-29
        PRE_UPDATE = 20
        GIT_CHECKOUT_TRACK_ERROR = 21
        POST_UPDATE = 29
        # update error codes: 30-39
        PRE_UNINSTALL = 30

    class Success(Enum):
        OK = 0

    def __init__(self):
        self._compose_dir = abspath(join(
            dirname(abspath(__file__)),
            '..', '..', '..', '..'
        ))
        # get compose version
        cmd = ['git', '-C', self._compose_dir, 'describe', '--abbrev=0', '--tags']
        compose_version = subprocess.check_output(cmd).decode('utf-8')
        self._compose_version = compose_version.strip('\n').split('-')[0]
        if len(self._compose_version.strip()) == 0:
            self._compose_version = None
        # find user-data
        userdata_dir = environ.get('COMPOSE_USERDATA_DIR',
                                   join(self._compose_dir, 'system', 'user-data'))
        log('user-data: `%s`' % userdata_dir)
        self._packages_dir = join(userdata_dir, 'packages')
        # try to create the directory if it does not exist
        if not isdir(self._packages_dir):
            mkdir(self._packages_dir, mode=0o775)
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
            line = [line for line in content if 'ASSETS_STORE_URL' in line][0].split('=')[1]
            assets_store_url = line.replace("'", '').replace('"', '').replace(';', '').strip()
            self._assets_store_url = assets_store_url
            # ---
            # read ASSETS_STORE_VERSION from config file
            line = [line for line in content if 'ASSETS_STORE_VERSION' in line][0].split('=')[1]
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
                package_info['git']['provider'],
                package_info['git']['owner'],
                package_info['git']['repository']
            )
            return Package(package_name, package_path, package_git_url)
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
        num_trials = 3
        packages = {}
        for i in range(num_trials):
            timeout = 5 * (i + 1)
            log('Retrieving index of packages from registry (%d/%d)...' % (i + 1, num_trials))
            random = str(uuid.uuid4())[0:8]
            index_url = '%s/%s/index.json?random=%s' % (
                self._assets_store_url, self._assets_store_branch, random
            )
            try:
                response = requests.get(index_url, timeout=timeout)
            except requests.exceptions.Timeout:
                continue
            # parse data
            data = json.loads(response.text)
            packages = data['packages']
            log('Done!')
            break
        return packages

    def get_package_latest_compatible_version(self, package):
        version = None
        for v, vinfo in self._index[package]['versions'].items():
            compatibility = vinfo['compatibility']['compose']
            # make sure this version is compatible with compose
            if (self._compose_version is not None and
                (version_lt(self._compose_version, compatibility['minimum']) or
                 version_gt(self._compose_version, compatibility['maximum']))):
                continue
            # ---
            if version is None:
                version = v
            if version_gt(v, version):
                version = v
        return version

    def solve_dependencies_graph(self, packages_to_install):
        dep_graph = {}
        dep_graph_versions = {}

        # ---
        def extend_dep_graph(_pkg_name, _pkg_version):
            dep_sub_graph = {}
            dep_sub_graph_versions = {}
            # make sure that the package is available
            if _pkg_name not in self._index:
                error(
                    PackageManager.Task.DEPENDENCIES_SOLVER,
                    PackageManager.DependenciesSolverStep.GET_PACKAGE,
                    _pkg_name,
                    'Package "%s" not found' % _pkg_name,
                    None,
                    PackageManager.Error.PACKAGE_NOT_FOUND
                )
            # get version
            if _pkg_version is None:
                _pkg_version = self.get_package_latest_compatible_version(_pkg_name)
                if _pkg_version is None:
                    error(
                        PackageManager.Task.DEPENDENCIES_SOLVER,
                        PackageManager.DependenciesSolverStep.GET_PACKAGE,
                        _pkg_name,
                        'No compatible versions for package "%s" were found' % _pkg_name,
                        None,
                        PackageManager.Error.NO_COMPATIBLE_VERSION_FOUND
                    )
            # get deps
            deps = self._index[_pkg_name]['versions'][_pkg_version]['dependencies']
            dep_sub_graph[_pkg_name] = set(deps)
            dep_sub_graph_versions[_pkg_name] = _pkg_version
            # extend deps
            for d in deps:
                p, v, *_ = d.split('==') + [None]
                sg, sgv = extend_dep_graph(p, v)
                dep_sub_graph.update(sg)
                dep_sub_graph_versions.update(sgv)
            return dep_sub_graph, dep_sub_graph_versions

        # ---
        # build graph
        for package_name, package_version in packages_to_install:
            subg, subg_v = extend_dep_graph(package_name, package_version)
            dep_graph.update(subg)
            dep_graph_versions.update(subg_v)
        # get flatten list of packages to install
        return toposort_flatten(dep_graph), dep_graph_versions

    def install(self, package_name, version, dryrun=False):
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
        package.install(version, dryrun=dryrun)

    def post_install(self, package_name, dryrun=False):
        package = self.get_package(package_name)
        package.post_install(dryrun=dryrun)

    def pre_update(self, package_name, dryrun=False):
        package = self.get_package(package_name)
        package.pre_update(dryrun=dryrun)

    def update(self, package_name, version=None, dryrun=False):
        package = self.get_package(package_name)
        if version is None:
            version = self.get_package_latest_compatible_version(package_name)
        package.update(version, dryrun=dryrun)

    def post_update(self, package_name, dryrun=False):
        package = self.get_package(package_name)
        package.post_update(dryrun=dryrun)

    def pre_uninstall(self, package_name, dryrun=False):
        package = self.get_package(package_name)
        package.pre_uninstall(dryrun=dryrun)

    def uninstall(self, package_name, dryrun=False):
        package = self.get_package(package_name)
        package.uninstall(dryrun=dryrun)


class Package(object):

    def __init__(self, package_name, path, remote_url=None):
        self._name = str(package_name)
        self._path = abspath(path)
        self._metadata = defaultdict(lambda: None)
        # check if the package is installed
        self._is_installed = isdir(self._path)
        # set remote URL
        if self._is_installed and not remote_url:
            # get remote url from repo
            cmd = ['git', '-C', self._path, 'remote', 'get-url', 'origin']
            returncode, _, error_str = exec_cmd(cmd)
            if returncode != 0:
                error(
                    PackageManager.Task.INIT_PACKAGE,
                    PackageManager.InitPackageStep.INIT,
                    self._name,
                    'Error retrieving the remote url from the package "%s":\n'
                    '\tExit code: %s\n\t: %s' % (
                        self._name,
                        str(returncode),
                        error_str
                    ),
                    returncode,
                    PackageManager.Error.GIT_REMOTE_GET_URL_ERROR
                )
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

    def install(self, version, dryrun=False):
        log(' > INSTALLING package "%s"...' % self.name)
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
            log(' < INSTALLING: Done!')
            return
        # clone git repository
        cmds = [
            ['git', 'clone', self.remote_url, self.path],
            ['git', '-C', self.path, 'checkout', version]
        ]
        cleanup_cmd = ['rm', '-rf', self.path]
        num_cmds = len(cmds)
        for i in range(num_cmds):
            cmd = cmds[i]
            log('  > Substep %d/%d...' % (i + 1, num_cmds))
            returncode, _, error_str = exec_cmd(cmd, cleanup_cmd)
            if returncode != 0:
                log('  < ERROR installing package "%s"...' % self.name)
                error(
                    PackageManager.Task.INSTALL,
                    PackageManager.InstallStep.INSTALL,
                    self.name,
                    'Error installing the package "%s":\n\tExit code: %s\n\t: %s' % (
                        self.name,
                        str(returncode),
                        error_str
                    ),
                    returncode,
                    PackageManager.Error.GIT_CLONE_ERROR
                )
            log('  < Substep: Done!')
        log(' < INSTALLING: Done!')

    def post_install(self, dryrun=False):
        self._perform_aux_action(
            PackageManager.Task.INSTALL,
            PackageManager.InstallStep.POST_INSTALL,
            PackageManager.Error.POST_INSTALL,
            dryrun=dryrun
        )

    def pre_update(self, dryrun=False):
        self._perform_aux_action(
            PackageManager.Task.UPDATE,
            PackageManager.UpdateStep.PRE_UPDATE,
            PackageManager.Error.PRE_UPDATE,
            dryrun=dryrun
        )

    def update(self, version, dryrun=False):
        log(' > UPDATING package "%s"...' % self.name)
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
            log(' < UPDATING: Done!')
            return
        # perform git fetch and checkout
        cmds = [
            ['git', '-C', self.path, 'fetch', '--tags'],
            ['git', '-C', self.path, 'checkout', version]
        ]
        num_cmds = len(cmds)
        for i in range(num_cmds):
            cmd = cmds[i]
            log('  > Substep %d/%d...' % (i + 1, num_cmds))
            returncode, _, error_str = exec_cmd(cmd)
            if returncode != 0:
                log('  < ERROR updating package "%s"...' % self.name)
                error(
                    PackageManager.Task.UPDATE,
                    PackageManager.UpdateStep.UPDATE,
                    self.name,
                    'Error checking out version "%s" of the package "%s":\n'
                    '\tExit code: %s\n\t: %s' % (
                        version,
                        self.name,
                        str(returncode),
                        error_str
                    ),
                    returncode,
                    PackageManager.Error.GIT_CHECKOUT_TRACK_ERROR
                )
            log('  < Substep: Done!')
        log(' < UPDATING: Done!')

    def post_update(self, dryrun=False):
        self._perform_aux_action(
            PackageManager.Task.UPDATE,
            PackageManager.UpdateStep.POST_UPDATE,
            PackageManager.Error.POST_UPDATE,
            dryrun=dryrun
        )

    def pre_uninstall(self, dryrun=False):
        self._perform_aux_action(
            PackageManager.Task.UNINSTALL,
            PackageManager.UninstallStep.PRE_UNINSTALL,
            PackageManager.Error.PRE_UNINSTALL,
            dryrun=dryrun
        )

    def uninstall(self, dryrun=False):
        log(' > UNINSTALLING package "%s"...' % self.name)
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
        if not dryrun:
            shutil.rmtree(self.path)
        log(' < UNINSTALLING: Done!')

    def _perform_aux_action(self, task, step, error_code, dryrun=False):
        log(' > Executing action %s.%s on package "%s"...' % (task.name, step.name, self.name))
        if dryrun:
            log(' < Done!')
            return
        # exec aux file script (if available)
        action_file = join(self.path, step.name.lower())
        if isfile(action_file):
            action_command = [action_file]
            returncode, _, error_str = exec_cmd(action_command)
            if returncode != 0:
                error(
                    task,
                    step,
                    self.name,
                    error_str,
                    returncode,
                    error_code
                )
        log(' < Done!')


if __name__ == '__main__':

    import argparse

    log('\\compose\\ Package Manager:')

    parser = argparse.ArgumentParser(description='Manage compose packages')
    parser.add_argument('--install', metavar='package', type=str, nargs='*',
                        help='a space-separated list of packages to install')
    parser.add_argument('--uninstall', metavar='package', type=str, nargs='*',
                        help='a space-separated list of packages to uninstall')
    parser.add_argument('--update', metavar='package', type=str, nargs='*',
                        help='a space-separated list of packages to update')
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

    def package_and_version(package_str):
        # split package==version  =>  (package, version)
        parts = package_str.split('==') + [None]
        return parts[0], parts[1]

    # get input
    to_install = set(args.install or [])
    to_update = set(args.update or [])
    to_install = to_install.union(to_update)

    # break package==version into (package, version)
    to_install = [package_and_version(p) for p in to_install]

    # solve dependency graph
    to_install, version_map = pm.solve_dependencies_graph(to_install)

    # perform uninstall
    for _package_name in args.uninstall or []:
        log('Performing UNINSTALL on package "%s"...' % _package_name)
        pm.uninstall(_package_name, dryrun=args.dry_run)
        out_data['uninstalled'].append(_package_name)
        log('Done!\n')

    # perform pre_update
    for _package_name in to_update:
        if _package_name in pm.list_installed_packages():
            log('Performing PRE_UPDATE on package "%s"...' % _package_name)
            pm.pre_update(_package_name, dryrun=args.dry_run)
            log('Done!\n')

    # perform update
    requires_post_update = []
    for _package_name in to_update:
        _package_name, _package_version = package_and_version(_package_name)
        if _package_name in pm.list_installed_packages():
            log('Performing UPDATE on package "%s"...' % _package_name)
            pm.update(_package_name, version=_package_version, dryrun=args.dry_run)
            requires_post_update.append(_package_name)
            out_data['updated'].append(_package_name)
            log('Done!\n')

    # perform install
    requires_post_install = []
    for _package_name in to_install:
        if _package_name not in pm.list_installed_packages():
            _package_version = version_map[_package_name]
            _version_info = " (version %s)" % _package_version
            log('Performing INSTALL on package "%s"%s...' % (_package_name, _version_info))
            pm.install(_package_name, _package_version, dryrun=args.dry_run)
            requires_post_install.append(_package_name)
            out_data['installed'].append(_package_name)
            log('Done!\n')

    # perform post_update
    for _package_name in requires_post_update:
        log('Performing POST_UPDATE on package "%s"...' % _package_name)
        pm.post_update(_package_name, dryrun=args.dry_run)
        log('Done!\n')

    # perform post_install
    for _package_name in requires_post_install:
        log('Performing POST_INSTALL on package "%s"...' % _package_name)
        pm.post_install(_package_name, dryrun=args.dry_run)
        log('Done!\n')

    # exit
    exit_with_code(
        pm.Success.OK,
        'Done!',
        out_data
    )
