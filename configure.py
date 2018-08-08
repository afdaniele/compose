#!/usr/bin/env python

import sys
import json
from pprint import pformat
from os.path import join, realpath, dirname, exists, isfile

DEBUG = True

# def prettyprint(obj):
#     str, _, _ = pprint.format( obj )
#     return str

def main():
    # get metadata and configuration files
    public_html = join(dirname(realpath(__file__)), 'public_html')
    config_metadata = join(public_html, 'system', 'packages', 'core', 'configuration', 'metadata.json')
    config_file = join(public_html, 'system', 'packages', 'core', 'configuration', 'configuration.json')
    # make sure the metadata file exists
    if not exists(config_metadata) or not isfile(config_metadata):
        print 'The file `%s` does not exist, check and try again' % config_metadata
        exit()
    # get the most recent config
    config = {}
    with open(config_metadata) as f:
        metadata = json.load(f)
        for k,d in metadata['configuration_content'].items():
            config[k] = d['default']
    if DEBUG: print 'Metadata loaded.\nConfig so far:\n%s\n' % pformat(config)
    if exists(config_file) and isfile(config_file):
        with open(config_file) as f:
            config_data = json.load(f)
            if DEBUG:
                change = { e[0]:e[1] for e in set(config.items()).symmetric_difference(set(config_data.items())) }
                print 'Stored configuration loaded.\nChange:\n%s\n' % pformat(change)
            config.update( config_data )
        if DEBUG: print 'Stored configuration loaded.\nConfig so far:\n%s\n' % pformat(config)
    # update config
    arguments = sys.argv + ['--END']
    key_indices = [ i for i in range(len(arguments)) if arguments[i].startswith('--') ]
    config_update = {}
    cur_key = key_indices[0]
    for next_key in key_indices:
        if next_key-cur_key == 2:
            key = sys.argv[cur_key][2:].strip()
            if key in metadata['configuration_content'].keys():
                config_update[key] = sys.argv[cur_key+1]
    if DEBUG: print 'Configuration update received:\n%s\n' % pformat(config_update)
    # update config
    config.update( config_update )
    # dump config
    if DEBUG: print 'Final configuration:\n%s' % pformat(config)
    json.dump( config, file(config_file, 'w') )
    print 'Done!'

if __name__ == '__main__':
    main()
