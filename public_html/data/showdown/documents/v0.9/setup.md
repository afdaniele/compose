# Install natively

**\\compose\\** is designed to work on Linux machines. This part of the documentation
will guide through all the steps needed to prepare you Linux environment for hosting
**\\compose\\**.

## Table of Contents

[toc]


## Prepare environment

**\\compose\\** is designed to work on Linux servers. No official support is provided
for Microsoft Windows OS or Mac OS X.
This guide is based on [Ubuntu 16.04 LTS](http://releases.ubuntu.com/16.04/) but the
instructions should be valid for any Debian-based distribution. For other
Linux distributions, you may need to adapt the commands below to your Operating
System.


### Step 1: Prerequisites

Make sure that you have a Linux machine with internet access. You can install Ubuntu 16.04
by following the instructions in [this guide](https://help.ubuntu.com/16.04/installation-guide/index.html).

### Step 2: Install Apache Web Server

The [Apache web server](https://httpd.apache.org/) is among the most popular web servers in the world.
We can install Apache easily using Ubuntu's package manager, `apt`.

Install Apache by running

```plain
sudo apt update
sudo apt install apache2
```

### Step 3: Check Apache configuration

Run the following command to check your Apache configuration for syntax errors

```plain
sudo apache2ctl configtest
```

This should return something that looks like this:

<pre>
<span style="color:red">AH00558: apache2: Could not reliably determine the server's fully qualified domain name, using 127.0.1.1. Set the 'ServerName' directive globally to suppress this message</span>
Syntax OK
</pre>

If you don't see the red warning above you can skip this step and go directly to the next one.

If you do see the red warning message, then you need to manually specified the server's name. To
do so, run the following command to run the in-terminal text editor nano

```plain
sudo nano /etc/apache2/apache2.conf
```

Move to the very end of the configuration file and add the line

```plain
ServerName SERVER_HOSTNAME
```

where `SERVER_HOSTNAME` is the public hostname of your server. If you don't know what a public
hostname is, then it is likely that your server does not have one. In that case you can simply
specify the IP address of your Linux machine or `localhost`.
Once done, press **Ctrl-X**, then **Y** and **Enter** to save the changes and close the editor.

Check your Apache configuration again

```plain
sudo apache2ctl configtest
```

This time, it should return something that looks like this:

<pre>
Syntax OK
</pre>

If you don't see the red warning anymore, you are done with this step, go to the next one.

If you still see the red warning message, make sure you followed the instructions above correctly.


### Step 4: Restart Apache

Restart Apache by running the command

```plain
sudo systemctl restart apache2
```


### Step 5: Configure the Firewall

Next, we will make sure that your firewall allows HTTP and HTTPS traffic.
First of all, let's check that UFW (Uncomplicated FireWall) has an application profile for Apache
by running

```plain
sudo ufw app list
```

You show see something that looks like this

```plain
Available applications:
  Apache
  Apache Full
  Apache Secure
  OpenSSH
```

If you look at the *Apache Full* profile, it should show that it enables traffic to ports 80 and 443.
Running the command

```plain
sudo ufw app info "Apache Full"
```

will show you information about the *Apache Full* profile like so

```plain
Profile: Apache Full
Title: Web Server (HTTP,HTTPS)
Description: Apache v2 is the next generation of the omnipresent Apache web server.

Ports:
  80,443/tcp
```

We can now allow incoming traffic for this profile by running

```plain
sudo ufw allow in "Apache Full"
```


### Step 6: Test your installation of Apache

Open your browser and navigate to

```http
http://SERVER_HOSTNAME/
```

and you should see the Apache Default Page (similar to the one shown below).

![center](images/setup-apache-test-page.png =60%x100%)


### Step 7: Install dependencies

**\\compose\\** requires some dependencies to be installed on your system.
Install the dependencies by running the following command.

```plain
sudo apt install git php libapache2-mod-php php-mcrypt php-mysql php7.0-mbstring
```

NOTE: Make sure to install PHP-7.0 or newer. **\\compose\\** does not work with older versions of PHP.


### Step 8: Enable Apache modules

**\\compose\\** uses Apache modules that are no enabled by default.
Run the following command to enable them.

```plain
sudo a2enmod rewrite
```

Restart Apache to put these changes into effect.

```plain
sudo systemctl restart apache2
```


### Step 9: Configure website

**\\compose\\** uses the URL Rewrite engine provided by Apache to favor URL readability.
Moreover, by default, Apache stores the website under `/var/www/html/`. Run the following command
to change the configuration of the website and enable the URL Rewrite engine.

```plain
sudo nano /etc/apache2/sites-available/000-default.conf
```

You should be able to see something like the following.
Change the file so that it matches the one shown below.
You can decide where to install **\\compose\\**, it can be the default directory (i.e., `/var/www/html/`)
or a new one in your home folder (e.g., `~/www/`). Let's refer to this directory as
`COMPOSE_ROOT`.

Make sure you replace the parts in red with the correct information.
Once done, press **Ctrl-X**, then **Y** and **Enter**
to save the changes and close the editor.

<pre>
&lt;&#8203;VirtualHost &#42;:80&#8203;&gt;
    <span style="color:gray"># The ServerName directive sets the request scheme, hostname and port that
	...
	# However, you must set it for any further virtual host explicitly.</span>

	ServerName <span style="color:#c7254e">SERVER_HOSTNAME</span>

	ServerAdmin <span style="color:#c7254e">YOUR_EMAIL_ADDRESS</span>
	DocumentRoot <span style="color:#c7254e">COMPOSE_ROOT</span>/public_html/

	&lt;&#8203;Directory <span style="color:#c7254e">COMPOSE_ROOT</span>/public_html/ &#8203;&gt;
        	DirectoryIndex index.php index.html
        	AllowOverride All
        	Require all granted
    &lt;&#8203;/Directory&#8203;&gt;

	<span style="color:gray"># Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
	...
	#LogLevel info ssl:warn</span>

	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined

	<span style="color:gray"># For most configuration files from conf-available/, which are
	...
	#Include conf-available/serve-cgi-bin.conf</span>
&lt;&#8203;/VirtualHost&#8203;&gt;

&lt;&#8203;Directory "/"&#8203;&gt;
    Options FollowSymLinks
    AllowOverride None
    Order deny,allow
    Allow from all
&lt;&#8203;/Directory&#8203;&gt;
</pre>

NOTE: In the configuration file above, make sure that the suffix `/public_html/` is appended to the end of
your `COMPOSE_ROOT`.


Restart Apache to put these changes into effect.

```plain
sudo systemctl restart apache2
```


## Install \\compose\\

Move to the directory `COMPOSE_ROOT`. Let it be the default `/var/www/html/` for example.

```plain
cd /var/www/html/
```

Download the latest version of **\\compose\\** by running.

```plain
git clone https://github.com/afdaniele/compose.git ./
```


## Test

Open your browser and navigate to the page
[http://SERVER_HOSTNAME/](#).
You should be able to see a **Setup** page like the following.

<p style="text-align: center;">
  <img src="images/setup/first_setup_step1.jpg" width="80%" height="100%" style="border: 1px solid black">
</p>

You can now move to the step [First Setup](first-setup).

If you experience problems reaching your installation of **\\compose\\**, please check again
all the steps above. If you still have problems, take a look at the
[Troubleshooting](troubleshooting) section of our documentation. If the problem persists,
do not hesitate to open a new *Issue* on our [GitHub page](https://github.com/afdaniele/compose).
