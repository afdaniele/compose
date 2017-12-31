# Duckieboard - A Remote Dashboard for Duckietown

The Duckietown Remote Dashboard (duckieboard) is a web
platform that provides high level fleet management and
monitoring capabilities for Duckietown.


## Setup

### Fast setup

You can setup the platform using the script provided
by running

```bash
sh ./setup.sh
```

**Note:** The setup script needs sudo permissions to give
the ownership of certain configuration files to the Apache
server.


### Step-by-Step setup

- Give Apache write access to the configuration file
```bash
chmod 664 public_html/system/config/configuration.json
sudo chgrp www-data public_html/system/config/configuration.json
```


- Give Apache write access to the users files
```bash
chmod 664 public_html/system/users/*
sudo chgrp www-data public_html/system/users/*
```
