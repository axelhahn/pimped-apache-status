

# Axel pimped the Apache-status v2.x BETA

## BETA

This is a real beta version. A lot of things are not ready yet.
It is based on version 1. All results work fine. There are things to do like

  * finish the (new) admin backend
  * translation to German (development is in english)
  * update documentation


Ressources for version 1.x:

  * Docs: [axel-hahn.de](http://www.axel-hahn.de/docs/apachestatus/index.htm) (English)
  * Sources+Download: [sourceforge](http://sourceforge.net/projects/pimpapachestat/)
  * Home: [axel-hahn.de](http://www.axel-hahn.de/apachestatus) (German)


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


## REQUIREMENTS
  * On the system you want to install the pimped Apache status:
    - any webserver with php 5 (with curl; no database is needed)
  * On all webservers you want to monitor:
    - apache 2.x
    - installed module mod_status and ExtendedStatus On
    - permission for the monitoring server to request the the 
      alias /server-status (see below)


## INSTALL

### (1)
Extract the files somewhere below webroot on your webserver. You 
can put to any subdirectory. It is not a must to have it in the 
webroot.

### (2)
Allow your server to access the server-status page on the systems 
you want to monitor, i.e. in apache 2.2 syntax:

    <Location /server-status>
        SetHandler server-status
        order deny, allow
        allow from 127.0.0.1
        allow from 192.168.123.4 # <--- enter ip of your monitoring system
        deny from all
    </Location>

### (3) 
Open http://localhost/apachestatus/ in your webbrowser.

### (4)
Go to the admin subdirectory: http://localhost/apachestatus/admin/
There you can add groups and servers.
(You can change the settings in the json files below ./config/ too).


## UPGRADE from version 1.x

Make a backup of version 1 (as zip, tgz, ...).
Extract version 2 over the existing version 1.
Then execute the upgrade script:
http://localhost/apachestatus/upgrade.php

This converts the config files (array in php files) to json.


## CUSTOMIZATION

You can setup and group servers to monitor, define skins, templates and more.
See the [Docs::Customization](http://www.axel-hahn.de/docs/apachestatus/custom.htm).

You can disable the access to admin in your apache config with a deny rule.
All settings and configured servers you find in json files below the
config subdirectory:
 - config_servers.json
 - config_user.json


----------------------------------------------------------------------
