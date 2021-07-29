

# Axel pimped the Apache-status

Ressources:

  * Docs: [axel-hahn.de](https://www.axel-hahn.de/docs/apachestatus/index.htm)
  * Sources: [Github](https://github.com/axelhahn/pimped-apache-status/tree/master)
  * Download: [sourceforge](https://sourceforge.net/projects/pimpapachestat/files/latest/download)
  * Home: [axel-hahn.de](https://www.axel-hahn.de/apachestatus) (German)


---


## ABOUT

  The default apache status shows you information about current Apache 
  activity. But these server-status pages are difficult to read.

  The pimped Apache status makes the Apache server status readable,
  sortable and searchable. 
  The pimped Apache status can merge the status of several servers
  that opens the possibility to identify the troubleshooter in a
  load balanced website much more easily. 
  Btw: I use the script to fetch the status from 20+ servers.

  The output uses jQuery and the plugin datatable to search and
  sort the data.

  
## LICENSE
  GNU GPL v 3.0

![Screesnhot Pimped Apache Status](https://www.axel-hahn.de/assets/projects/pimped-apache-status/01-history-popup.png)

## REQUIREMENTS
  * On the system you want to install the pimped Apache status:
    - any webserver with php 5+ (PHP 8 is recommended; with php_curl; no database is needed)
  * On all webservers you want to monitor:
    - apache 2.x
    - installed module mod_status and ExtendedStatus On
    - permission for the monitoring server to request the the 
      alias /server-status (see below)


## INSTALL

### (1)
Download the software https://sourceforge.net/projects/pimpapachestat/files/latest/download
Extract the files somewhere below webroot on your webserver. You 
can put to any subdirectory. It is not a must to have it in the 
webroot.

### (2)
Allow your server to access the server-status page on the systems 
you want to monitor, i.e. in apache 2.4 syntax:

    <Location /server-status> 
      SetHandler server-status 
      Require ip 127.0.0.1
      Require ip 192.168.123.4 # enter ip of your monitoring system
    </Location>


### (3) 
Open http://localhost/apachestatus/ in your webbrowser.

### (4)
Go to the admin subdirectory: http://localhost/apachestatus/admin/
There you can add groups and servers.
(You can change the settings in the json files in ./config/ too).


## UPGRADE from version 1.x

A new local version will be detected in the webbrowser. Follow the
upgrade link or open directly
http://localhost/apachestatus/upgrade.php

On CLI or for automation execute 

    php [installdir]/upgrade.php

## CUSTOMIZATION

You can setup and group servers to monitor, define skins, templates and more.
See the [Docs::Customization](https://www.axel-hahn.de/docs/apachestatus/custom.htm).

You can disable the access to admin in your apache config with a deny rule.
All settings and configured servers you find in json files below the
config subdirectory:
 - config_servers.json
 - config_user.json


----------------------------------------------------------------------
