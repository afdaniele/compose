#!/usr/bin/env python3
import sys
import json
from pprint import pformat
from os.path import join, realpath, dirname, exists, isfile

DEBUG = False

def main():
  # get arguments
  arguments = sys.argv[1:] + ['--END']
  # get package to configure (if passed, default: `core`)
  package_name = 'core'
  if not arguments[0].startswith('--'):
    package_name = arguments[0]
  # get metadata and configuration files
  public_html = join(dirname(realpath(__file__)), 'public_html')
  config_metadata = join(public_html, 'system', 'packages', package_name, 'configuration', 'metadata.json')
  config_file = join(public_html, 'system', 'user-data', 'databases', package_name, '__configuration__', 'content.json')
  # make sure the metadata file exists
  if not exists(config_metadata) or not isfile(config_metadata):
    print('The file `%s` does not exist, check and try again' % config_metadata)
    exit()
  # get the most recent config
  config = {}
  with open(config_metadata) as f:
    metadata = json.load(f)
    for k,d in metadata['configuration_content'].items():
      config[k] = d['default']
  if DEBUG:
    print('Metadata loaded.\nConfig so far:\n%s\n' % pformat(config))
  config_metadata = []
  if exists(config_file) and isfile(config_file):
    with open(config_file) as f:
      config_data_in = json.load(f)
      config_data = config_data_in['_data']
      config_metadata = config_data_in['_metadata']
      if DEBUG:
        change = {
          e[0]: config_data[e[0]]
          for e in set(config_data.items()).symmetric_difference(set(config.items()))
        }
        print('Stored configuration loaded.\nChange:\n%s\n' % pformat(change))
      config.update(config_data)
    if DEBUG: print('Stored configuration loaded.\nConfig so far:\n%s\n' % pformat(config))
  # update config
  key_indices = [i for i in range(len(arguments)) if arguments[i].startswith('--')]
  config_update = {}
  cur_key = key_indices[0]
  for next_key in key_indices:
    if next_key-cur_key == 2:
      key = arguments[cur_key][2:].strip()
      if key in metadata['configuration_content'].keys():
        config_update[key] = arguments[cur_key+1]
    cur_key = next_key
  if DEBUG:
    print('Configuration update received:\n%s\n' % pformat(config_update))
  # update config
  config.update(config_update)
  # dump config
  if DEBUG:
    print('Final configuration:\n%s' % pformat(config))
  json.dump(
    {
      '_data': config,
      '_metadata': config_metadata
    },
    open(config_file, 'wt'),
    sort_keys=True,
    indent=2
  )
  print('Done!')


if __name__ == '__main__':
    main()
