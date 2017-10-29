#!/bin/bash

# get the absolute path to the Dashboard repo
DASHBOARD_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# make the configuration file editable
configuration_file="$DASHBOARD_PATH/config/configuration.json"
chmod +w $configuration_file
