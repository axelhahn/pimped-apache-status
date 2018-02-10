<?php

global $aLangTxt;
$aLangTxt = array(
    
    'id'=>'english',
    
    'menuHeaderMonitoring'=>'Monitoring',
    'menuHeaderConfig'=>'Configuration',
    'menuAdmin'=>'Setup ',
    'menuGroup'=>'Group: ',
    'menuLang'=>'Language: ',
    'menuSkin'=>'Skin: ',
    'menuReload'=>'Reload interval: ',
    
    // ------------------------------------------------------------
    // basic errors
    // ------------------------------------------------------------
    'error-wrong-group'=>'The group [%s] does not exist.',
    'error-no-group'=>'No group of servers was found. Please create one in the setup.',
    'error-server-not-in-group'=>'Server [%s] does not exist in group [%s].',
    'error-no-server-in-group'=>'No server was defined in the selected group [%s]. Add one in the setup.',
    'error-no-server'=>'No server was given to analyze. After adding servers in the config select a server or server group on top.',

    // ------------------------------------------------------------
    // version check
    // ------------------------------------------------------------
    'versionUptodate'=>'OK (up to date)',
    'versionError'=>'??',
    'versionUpdateAvailable'=>'Version %s is available',
    'versionManualCheck'=>'check for a new version',
    
    'authAccessDenied'=>'<h1>Access denied.</h1>User and password are required.',
    
    // ------------------------------------------------------------
    // for menu of views:
    // label for menu and h2
    // ------------------------------------------------------------
 
        'view_allrequests.php_label'=>'All requests',
        'view_original.php_label'=>'Original server-status',
        'view_performance-check.php_label'=>'Performance checks',
        'view_serverinfos.php_label'=>'Server info',
        'view_help.php_label'=>'Help',
        'view_dump.php_label'=>'Dumps',
        'view_setup.php_label'=>'Setup',
        'view_update.php_label'=>'Update',
        'view_selectserver.php_label'=>'List of groups and servers',

    // ------------------------------------------------------------
    // for all tables in the views
    // ------------------------------------------------------------
        // ............................................................
        'lblInitialSetup'=>'Initial Setup',
        'lblInitialSetupAbort'=>'Sorry, the initial setup was executed already.',
        'lblHelplblInitialSetup'=>'
            Welcome to the Pimped Apache status.<br>
            First of all: protect the tool and setup an admin user.
        ',
        'lblUsername'=>'Username',
        'lblPassword'=>'Password',
        'lblRepeatPassword'=>'Repeat password',
        'lblInitialSetupSaved'=>'OK, data were saved.',
        'lblInitialSetupSaveFailed'=>'Something went wrong. Please enter a username and twice the same password.',

        // ............................................................
        'lblTable_status_workers'=>'Worker status',
        'lblTableHint_status_workers'=>'
            The table shows the status of apache worker processes of marked server or all marked servers of a group.<br>
            <ul>
                <li>"total" is the total count of worker processes</li>
                <li>"busy" count of active worker processes (status M is not equal "_" and not eqal ".").</li>
                <li>"idle" is count of processes with status "_".</li>
            </ul>',

        // ............................................................
        'lblTable_status'=>'Server status',
        'lblTableHint_status'=>'The table shows status information of the webserver(s)',

        // ............................................................
        'lblTile_server_responsetime'=>'Response time',
        'lblTileHint_server_responsetime'=>'Response time to fetch status from all servers',
        'lblTile_server_count'=>'Servers',
        'lblTileHint_server_count'=>'Count of requested webservers',

        // ............................................................
        'lblTile_requests_all'=>'Requests',
        'lblTileHint_requests_all'=>'Total count of (active and inactive) requests on all servers',
        'lblTable_requests_all'=>'List of all requests',
        'lblTableHint_requests_all'=>'The table shows all active and inactive requests.',

        // ............................................................
        'lblTile_requests_running'=>'Active requ.',
        'lblTileHint_requests_running'=>'Active requests',
        'lblTable_requests_running'=>'Active requests',
        'lblTableHint_requests_running'=>'The table shows requests that are currently processed on the selected webserver(s).',

        // ............................................................
        'lblTile_requests_mostrequested'=>'Most requested',
        'lblTileHint_requests_mostrequested'=>'Most often requested query',
        'lblTable_requests_mostrequested'=>'Most often processed requests',
        'lblTableHint_requests_mostrequested'=>'
            The table shows the most often processed requests.<br>
            Remarks:<br>
            <ul>
                <li>The table is sorted by coloumn "Count".</li>
                <li>The table contains active and already finished requests.</li>
            </ul>',

        // ............................................................
        'lblTable_requests_hostlist'=>'Most often requested vhosts',
        'lblTableHint_requests_hostlist'=>'
            The table shows the most often requested virtual hosts.<br>
            Remarks:<br>
            <ul>
                <li>The table is sorted by coloumn "Count".</li>
                <li>The table contains active and already finished requests.</li>
            </ul>',

        // ............................................................
        'lblTable_requests_methods' => 'Request methods',
        'lblTableHint_requests_methods' => 'Name and count of HTTP-request methods',
        
        // ............................................................
        'lblTile_requests_clients' => 'Max. from IP',
        'lblTileHint_requests_clients' => 'Maximum count of requests coming from a single IP',
        'lblTable_requests_clients' => 'Requests per ip address',
        'lblTableHint_requests_clients' => 'List of Clients and their count of (current and finished) requests.<br>
			Remark: An ip can be a gateway ip that masks several devices of an enterprise.',

        // ............................................................
        'lblTile_requests_longest'=>'Slowest request',
        'lblTileHint_requests_longest'=>'Slowest request',
        'lblTable_requests_longest'=>'Top 25 of slowest requests',
        'lblTableHint_requests_longest'=>'The table shows all requests ordered by response time.<br>
            Remarks:<br>
            <ul>
                <li>The response time in the Apache status is available, if the request is finished.
                    It is not available for currently procesed requests (its value is always "0").
                </li>
                <li>The table is ordered by columns "Req" (value is in ms)</li>
            </ul>',
    
        // ............................................................
        'lblTable_explanation'=>'Explanation',
        'lblTableHint_expalanation'=>'Colours in the tables',

    // ------------------------------------------------------------
    // description for tables
    // ------------------------------------------------------------
    
    
        'thWorkerServer' => 'webserver',
        'thWorkerTotal' => 'total',
        'thWorkerActive' => 'busy',
        'thWorkerWait' => 'idle',
        'thWorkerBar' => 'visual',
        'thWorkerActions' => 'actions',
        'thCount'=>'Count',
    
        'bartitleFreeWorkers' => 'free workers',
        'bartitleBusyWorkers' => 'busy workers',
        'bartitleIdleWorkers' => 'idle workers',
  
        'lblLink2Top' => 'top',
        'lblHintFilter' => 'Filter table by',
        'lblReload' => 'Refresh now',
        'lblExportLinks' => 'Export (unfiltered) table',

    // ------------------------------------------------------------
    // help page
    // ------------------------------------------------------------
        'lblHelpDoc'=>'Documentation',
        'lblHintHelpDoc'=>'small hints and links to the documentation',
        'lblHelpDocContent'=>'
            <br>
            <strong>Tables - sort</strong><br>
            <br>
            You can sort the table by any coloumn by clicking the name in the 
            table head with the left mousebutton. Reverse order by clicking 
            again.<br>
            Multi-coloumn sorting is available too: hold the SHIFT key while 
            clicking in the table head.<br>
            <br>
            <strong>Tables - filter</strong><br>
            <br>
            Use the search field to filter the table.<br>
            on some colums you can filter by their entries.<br>
            A click to the [X] icon removes the filter.<br>
            
            <br>
            <strong>Links</strong><br>
            <br>
            More detailed information you get here:
            ',
    
        'lblHelpBookmarklet'=>'<strong>Bookmarklet</strong><br>
            <br>
            If a server-status page is available in the public internet you can
            use a bookmarklet to view it here. Without any configuration.<br>
            Drag and drop the following link to your bookmarks: ',
        
        'lblHelpColors'=>'Colors of the request rows',
        'lblHintHelpColors'=>'
            The rows with the requests are colored. The color of each row
            depends on the selected skin.<br>
            In general the color depends on the criteria below.<br>
            The color properties of each group will be added.<br>
            ',
    
        'lblHelpThanks'=>'Thanks!',
        'lblHintHelpThanks'=>'The following helpers and and tools were used.',
        'lblHelpThanksContent'=>'
            <p>
                I say &quot;thank you&quot; to the developers of different
                tools I use here in my product:
            </p>
            <ul>
                <li>Admin LTE - Control panel template: <a href="https://adminlte.io/">https://adminlte.io/</a></li>
                <li>jQuery: <a href="http://jquery.com/">http://jquery.com/</a></li>
                <li>Datatables - sortable tables: <a href="https://datatables.net/">https://datatables.net/</a></li>
                <li>array2xml.class - XML export: <a href="http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes">http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes</a></li>
                <li>Bootstrap - Html-Framework: <a href="http://getbootstrap.com/">http://getbootstrap.com/</a></li>
                <li>Font-awesome - Icons: <a href="https://fontawesome.io/">https://fontawesome.io/</a></li>
                <li>jQuery Knob - dials: <a href="https://github.com/aterrien/jQuery-Knob">https://github.com/aterrien/jQuery-Knob</a></li>
                <li>Morris JS - charts: <a href="http://morrisjs.github.io/morris.js/index.html">http://morrisjs.github.io/morris.js/index.html</a></li>
            </ul>
            ',
    
    
        // column "comment" by column "M"
        'lblStatus' => 'Status',
        'cmtLegendM' => 'Mode of operation (column "M")',
        'cmtStatus_' => '_ Request finished. Waiting for new Connection',
        'cmtStatusS' => 'S Starting up',
        'cmtStatusR' => 'R Reading Request',
        'cmtStatusW' => 'W Sending Reply',
        'cmtStatusK' => 'K Keepalive (read)',
        'cmtStatusD' => 'D DNS Lookup',
        'cmtStatusC' => 'C Closing connection',
        'cmtStatusL' => 'L Logging',
        'cmtStatusG' => 'G Gracefully finishing',
        'cmtStatusI' => 'I Idle cleanup of worker',
        'cmtStatus.' => '. Request finished. Open slot with no current process',
        // 'cmtRequest'=>'',
    
        'cmtLegendRequest' => 'HTTP Request method',
        'cmtRequestGET' =>'GET',
        'cmtRequestHEAD' =>'HEAD',
        'cmtRequestPOST' =>'POST',
        'cmtRequestPUT' =>'PUT',
        'cmtRequestDELETE' =>'DELETE',
        'cmtRequestTRACE' =>'TRACE',
        'cmtRequestCONNECT' =>'CONNECT',
        'cmtRequestNULL' =>'NULL',
        'cmtRequestOPTIONS' =>'OPTIONS',
        'cmtRequestPROPFIND' =>'PROPFIND',
    
    
        'cmtLegendexectime' => 'Execution time of a request',
        'cmtexectimewarning' =>'warning',
        'cmtexectimecritical' =>'critcal',
    
    
    // ------------------------------------------------------------
    // description for debug
    // ------------------------------------------------------------
    
        'lblDumpsaUserCfg'=>'$aUserCfg - user configuration',
        'lblHintDumpsaUserCfg'=>'The user configuration array is a merge of
            the default config and the user specific configuration.',
    
        'lblDumpsaEnv'=>'$aEnv - environent of this current request',
        'lblHintDumpsaEnv'=>'It contains information to render output.<br>
            Here are name and version of the project, active values
            (like current selected server group, laguage or skin).<br>
            Below the key "links" are arrays that can be rendered
            i.e. as a dropdown or a tablist',
    
        'lblDumpsaSrvStatus'=>'$aSrvStatus - array of server status',
        'lblHintDumpsaSrvStatus'=>'Each server has an array key "status" and "request".
            These are parsed data from server-status pages.',
    
        'lblDumpsaLang'=>'$aLang - array of language specific texts',
        'lblHintDumpsaLang'=>'This table compares the language text arrays of
            the activated languages.',
        'lblDumpsMiss'=>'!!! This key has no value !!!',
    
    // ------------------------------------------------------------
    // software update
    // ------------------------------------------------------------
        'lblUpdate'=>'Update of the web application',
        'lblUpdateNewerVerionAvailable'=>'OK, a newer version is available.',
        'lblUpdateNoNewerVerionAvailable'=>'Remark: There is no newer version available. The execution of the updater ist not necessary.',
        'lblUpdateHints'=>'
            The update will be done in 2 steps:
            <ol>
                <li>Download of the zip file of the current version<br>%s</li>
                <li>uncompress zip file</li>
            </ol>
            ',
        'lblUpdateDonwloadDone'=>'OK, the file was downloaded.<br>In the next step it will be extracted.',
        'lblUpdateDonwloadFailed'=>'Error: unable to download the zip file.',
        'lblUpdateInstalldir'=>'Local installation directory: %s',
        'lblUpdateContinue'=>'Continue &raquo;',
        'lblUpdateUnzipFile'=>'Extract file: %s<br>To: %s',
        'lblUpdateUnzipOK'=>'OK: the new version was extracted. Have fun!<br>If you like the software you can support it and make me happy if you go to the docs page (see the footer below) and share it or donate a few bugs.<br>Helper for translations into other languages are welcome too...',
        'lblUpdateUnzipFailed'=>'Error: unable to open the zip file.',
    
    // ------------------------------------------------------------
    // javascript
    // ------------------------------------------------------------
        'js::statsCurrent'=>'Current',
        'js::statsAvg'=>'Avg.',
        'js::statsMax'=>'Maximum',
        'js::statsMin'=>'Minimum',
        'js::srvFilterPlaceholder'=>'find a server',
    
    // ------------------------------------------------------------
    // admin
    // ------------------------------------------------------------
        'ActionAdd'=>'Add',
        'ActionAddServer'=>'Add server',
        'ActionAddServergroup'=>'Add server group',
        'ActionEdit'=>'Edit',
        'ActionContinue'=>'Continue',
        'ActionClose'=>'Close',
        'ActionDelete'=>'Delete',
        'ActionDeleteHint'=>'Delete this entry. If you are sure.',
        'ActionDownload'=>'Download',
        'ActionDownloadHint'=>'Download this library.',
        'ActionOK'=>'OK',
        'ActionOKHint'=>'Save changes',
    
        'AdminMenusettings'=>'Settings',
        'AdminMenuservers'=>'Servers',
        'AdminMenulang'=>'Languages',
        'AdminMenuvendor'=>'Vendor-Libs',
        'AdminMenuupdate'=>'Update',
    
        'AdminMenuSettingsCompare'=>'Compare',    
        'AdminHintSettingsCompare'=>'You see all default variables and which of them the user config overrides.<br>',
        'AdminMenuSettings-var'=>'variable name',
        'AdminMenuSettings-description'=>'description',
        'AdminMenuSettings-default'=>'default value',
        'AdminMenuSettings-uservalue'=>'user value',    
        'AdminHintRaw-internal-config_default'=>'View to raw data of all default values.<br>',
        'AdminHintRaw-config_user'=>'Edit user config as raw data.<br>',
    
        'AdminHintServers'=>'Create groups.<br>'
            . 'In each group you can add your apache servers you want to monitor. All status pages of servers in the same group will be requested simultanously.<br>'
            . 'So you can monitor a loadbalanced website. Nice, isn\'t it?<br>'
            . 'Or just group a few servers to keep an eye to the traffic of a single project, i.e. static server, application server, caching server.<br>',
        'AdminServersLblAddGroup'=>'Add a new group of servers.',
        'AdminLblGroup-label'=>'Name of the group',
    
        'AdminServersLblAddServer'=>'Add an Apache http server to this group.',
    
        'AdminLblServers'=>'Configure apache servers you want to monitor',
        'AdminLblServers-ConfirmDelete'=>'Are you sure to delete the server\n%s?',
        'AdminLblServers-label'=>'Server name',
        'AdminLblServers-label-Hint'=>'Enter the server name of the apache webserver.',
        'AdminLblServers-status-url'=>'url of status page',
        'AdminLblServers-status-url-Hint'=>'Url of status page. If you leave it empty the default status url <em>http://[servername]/server-status/</em> will be used. You can change it, if you use https instead of http. If you use another port, enter <em>http://[servername]:[port]/server-status/</em>',
        'AdminLblServers-userpwd'=>'user and password',
        'AdminLblServers-userpwd-Hint'=>'optional value; Fill in this field if the server status page is password protected.<br>Syntax is <em>[username]:[password]</em>',
    
        'AdminMessageServer-add-defaults-ok' => 'OK: Default group and server have been created.',
        'AdminMessageServer-add-defaults-error' => 'ERROR: Unable to create default group and server.',
        'AdminMessageServer-addgroup-error' => 'ERROR: group %s cannot be added.',
        'AdminMessageServer-addgroup-ok' => 'OK: group %s was added.',
        'AdminMessageServer-deletegroup-error' => 'ERROR: group %s was not deleted.',
        'AdminMessageServer-deletegroup-ok' => 'OK: group %s was deleted',
        'AdminMessageServer-updategroup-error' => 'ERROR: group %s cannot be updated.',
        'AdminMessageServer-updategroup-ok' => 'OK: group %s was updated.',
        'AdminMessageServer-addserver-error' => 'ERROR: server %s cannot be added.',
        'AdminMessageServer-addserver-ok' => 'OK: server %s was added.',
        'AdminMessageServer-deleteserver-error' => 'ERROR: server %s cannot be deleted.',
        'AdminMessageServer-deleteserver-ok' => 'OK: server %s was deleted.',
        'AdminMessageServer-updateserver-error' => 'ERROR: server %s cannot be updated.',
        'AdminMessageServer-updateserver-ok' => 'OK: server %s was updated.',
    
        'AdminMessageSettings-update-error-no-json' => 'SKIP: sent data are not valid JSON. Keeping current config. You can try to go back to fix it.',
        'AdminMessageSettings-update-error' => 'ERROR: user config file was not saved :-/',
        'AdminMessageSettings-update-ok' => 'OK: user config file was saved.',
        'AdminMessageSettings-wrong-key' => 'WARNING: user config key [%s] is not a valid setting. This information is useless: ',
        
        'AdminHintVendor'=>'Used vendor libraries and the place where they are loaded from. Using remote or local libs has no functional impact.<br>Download the libaries to increase the speed of this web app and/ or to run it without additional internet access.',
        'AdminVendorLib'=>'Library',
        'AdminVendorVersion'=>'Version',
        'AdminVendorLocal'=>'Local',
        'AdminVendorRemote'=>'Remote',
        'AdminVendorLibLocalinstallations'=>'<strong>%s</strong> libs are in use and <strong>%s</strong> of them are local. Download all libs for best performance.',
        'AdminVendorLibAllLocal'=>'All <strong>%s</strong> used libs are local.',
    
        'AdminHintUpdates'=>'Update this web application.<br>',
    
    // ------------------------------------------------------------
    // cfg values
    // ------------------------------------------------------------
        'cfg-auth'=>'Authentication to protect access to the Apache status data',
            'AdminLblVar-user'=>'username',
            'AdminLblVar-password'=>'password',
        'cfg-autoreload'=>'Time to auto reload page. The array contains values in seconds that will be visible as a dropdown.',
            'AdminLblVar-autoreload'=>'time in sec',
        'cfg-checkupdate'=>'How often to check for an update. The value is in [s]. The value 0 turns off the check. Default is 1 day.',
        'cfg-datatableOptions'=>'Javascript object for datatable. Do not override this.',
        'cfg-defaultTemplate'=>'Do not override this.',
        'cfg-defaultView'=>'Default view is Server info page.',
        'cfg-debug'=>'Enable client debugging infos. Default is false.',
        'cfg-tmpdir'=>'Temp directory. This directory needs write permissions. Default is tmp (= [Appdir]/tmp]).',
        'cfg-execTimeRequest'=>'An array to define values in [ms] for long requests: one for warning and one for critical long requests.',
        'cfg-hideRows'=>'Array of rows to hide. You can save some space not to display all colums. Warning: do not hide important rows!',
        'cfg-icons'=>'Do not override this.',
        'cfg-lang'=>'Currently active default language.',
        'cfg-selectLang'=>'comma seperated list of selectable languages',
        'cfg-selectSkin'=>'comma seperated list of selectable skins',
        'cfg-showHint'=>'show hints (true/ false)',
        'cfg-skin'=>'Currently active default skin.',
        'cfg-tdbars'=>'Table rows that show a bar.',
        'cfg-tdlink'=>'special links for table rows.',
        'cfg-views'=>'list of the visible menu items',
        'cfg-viewsadmin'=>'list of the visible admin menu items',
        'cfg-wrongitem'=>'!!wrong key!!',
        'cfg-'=>'',
);
