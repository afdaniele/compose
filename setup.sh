#!/bin/bash

# get the absolute path to the Dashboard repo
DASHBOARD_PATH="$( cd "$(dirname "$0")" ; pwd -P )"

# make the configuration file editable by Apache
configuration_file="$DASHBOARD_PATH/public_html/system/config/configuration.json"
chmod 664 $configuration_file
sudo chgrp www-data $configuration_file

# make the users files editable by Apache
users_files="$DASHBOARD_PATH/public_html/system/users/*"
chmod 664 $users_files
sudo chgrp www-data $users_files
