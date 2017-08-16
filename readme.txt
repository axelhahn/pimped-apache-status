----------------------------------------------------------------------

  Axel pimped the Apache-status

  https://www.axel-hahn.de/apachestatus
  http://sourceforge.net/projects/pimpapachestat/

  GNU GPL v 3.0
----------------------------------------------------------------------


ABOUT
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

  
LICENSE
  GNU GPL v 3.0


REQUIREMENTS
  * On the system you want to install the pimped Apache status:
    - any webserver with php 5 (with curl; no database is needed)
  * On all webservers you want to monitor:
    - apache 2.x
    - installed module mod_status and ExtendedStatus On
    - permission for the monitoring server to request the the 
      alias /server-status (see below)


INSTALL
  1. Extract the files somewhere below webroot on your webserver. You 
     can put to any subdirectory. It is not a must to have it in the 
     webroot.
     -- OR --
     checkout sources with svn client:
     cd [webroot-directory]
     svn checkout http://svn.code.sf.net/p/pimpapachestat/code/trunk apachestatus
  2. Allow your server to access the server-status page on the systems 
     you want to monitor
     <Location /server-status> 
       SetHandler server-status 
       order deny, allow 
       allow from 127.0.0.1
       allow from 192.168.123.4 # enter ip of your monitoring system
       deny from all 
     </Location> 
  3. Open http://localhost/apachestatus/ in your webbrowser.
  4. In ./conf/ directory: open config_user.php and setup the systems you want 
     to monitor. See config_default.php to see other thing you could override


CUSTOMIZATION
  * change or add language: see readme in subdirectory ./lang/
  * change theme: 
    - make copy ./templates/default/
    - make changes in style.css and out_html.php in your copy
    - set skin in ./conf/config_user.php:
      $aUserCfg = array(
        'skin' => 'name_of_subdir',
      );

  If you have a language or a theme to share then send it to me.


----------------------------------------------------------------------