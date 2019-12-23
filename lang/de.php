<?php
$aLangTxt = array(

    'id'=>'deutsch',
    
    'menuHeaderMonitoring'=>'Monitoring',
    'menuHeaderConfig'=>'Konfiguration',
    'menuAdmin'=>'Admin',
    'menuGroup'=>'Gruppe: ',
    'menuGroupNone'=>'(keine)',
    'menuLang'=>'Sprache: ',
    'menuSkin'=>'Skin: ',
    'menuReload'=>'Aktualisierungs-Intervall: ',

    'gotop'=>'Seitenanfang',

    // ------------------------------------------------------------
    // basic errors
    // ------------------------------------------------------------
    'error-wrong-group'=>'Die Gruppe [%s] existiert nicht.',
    'error-no-group'=>'Es wurde keine Server-Gruppe gefunden. Bitte legen Sie eine unter Admin - Server an.',
    'error-server-not-in-group'=>'Server [%s] exisitert nicht in der Gruppe [%s].',
    'error-no-server-in-group'=>'Es wurde noch kein Server in der Gruppe [%s] angelegt. Bitte legen Sie einen unter Admin - Server an.',
    'error-no-server'=>'Es wurde kein Server gefunden.',
    'error-no-ssl'=>'Sicherheitswarnung: Diese Webseite verwendet kein SSL!<br>Der Webbrowser versendet die eingegebenen Daten inkl. Passwort unverschl&uuml;sselt / im Klartext.',
    
    // ------------------------------------------------------------
    // version check
    // ------------------------------------------------------------
    'versionUptodate'=>'OK (aktuell)',
    'versionError'=>'??',
    'versionUpdateAvailable'=>'Version %s verf&uuml;gbar',
    'versionManualCheck'=>'auf neue Version testen',
    'lblUpdateUnusedVendorlibs'=>'Noch ein Hinweis: Bei Gelegenheit kann man %s Vendor-Bibliotheken l&ouml;schen.',

    'authAccessDenied'=>'<h1>Zugriff verweigert</h1>Benutzername und Passwort sind erforderlich.',

    // ------------------------------------------------------------
    // for menu of views:
    // label for menu and h2
    // ------------------------------------------------------------
 
        'view_allrequests.php_label'=>'Alle Requests',
        'view_original.php_label'=>'Original Server-status',
        'view_performance-check.php_label'=>'Aktive Requests',
        'view_serverinfos.php_label'=>'Server-Infos',
        'view_help.php_label'=>'Hilfe',
        'view_dump.php_label'=>'Dumps',
        'view_utilization.php_label'=>'Auslastung',
        'view_setup.php_label'=>'Setup',
        'view_update.php_label'=>'Update',
        'view_selectserver.php_label'=>'Liste der Gruppen und Server',

    // ------------------------------------------------------------
    // for all tables in the views
    // ------------------------------------------------------------
        // ............................................................
        'lblInitialSetup'=>'Initial-Setup',
        'lblInitialSetupAbort'=>'Tut mir leid - das Initial-Setup wurde bereits ausgef&uuml;hrt.',
        'lblInitialSetupTab0'=>'Upgrade',
        'lblHelplblInitialSetupTab0'=>'
            Es wurde eine Konfigurationsdatei mit Version 1.x erkannt.<br>
            <br>
            Empfehlung: Starten Sie das das Upgrade - dieses
            <ul>
                <li>konvertiert die bestehenden Einstellungen einschl. Username und Passwort</li>
                <li>Transferiert die bestehenden Apache-Server
            </ul>
            <a href="upgrade.php" class="btn btn-primary">Upgrade starten</a><br>
            <br>
            ODER<br>
            W&auml;hlen Sie einen der weiteren Tabs, um mit einer neuen, leeren Konfiguration zu starten.
            
        ',
        'lblInitialSetupTab1'=>'Passwortschutz einrichten',
        'lblHelplblInitialSetupTab1'=>'
            Willkommen beim Pimped Apache Status.<br>
            Zuerst: Sch&uuml;tze das Werkzeug und setze einen Admin-user.
        ',
        'lblInitialSetupTab2'=>'Kein Schutz',
        'lblHelplblInitialSetupTab2'=>'
            Sie k&ouml;nnen dieses Tool auch selbst via Httpd Konfiguration auf eine IP zu beschr&auml;nken oder mit einer anderen Authentifzierung zu sch&uuml;tzen.<br>
            Hinweis: dieses Werkzeug niemals gegen das &ouml;ffentliche Internet offen lassen!<br>
            Sie haben hier die M&ouml;glichkeit, den internen Benutzer + Passwort zu &uuml;bergehen.<br>
        ',
        'lblUsername'=>'Username',
        'lblPassword'=>'Passwort',
        'lblRepeatPassword'=>'Passwort wiederholen',
        'lblInitialSetupSaved'=>'OK, Daten wurden gespeichert.',
        'lblInitialSetupSaveFailed'=>'Irgendwas ging schief. Bitte gib einen Benutzernamen ein und 2 x dasselbe Passwort.',

        // ............................................................
        'lblLogin'=>'Anmeldung',
        'lblLoginHint'=>'Du musst dich anmelden, um Zugriff auf die Monitoring-Daten zu erhalten.',
        'lblLoginIsAuthenticated'=>'Sie sind bereits angemeldet.',
        'lblLoginDoLogout'=>'Benutzer %s abmelden',

        // ............................................................
        'lblTable_status_workers'=>'Worker Status',
        'lblTableHint_status_workers'=>'Die Tabelle zeigt den Status der Apache Worker Prozesse vom markierten Server bzw. von allen 
            Servern der Gruppe.<br>
            <ul>
                <li>"Slots" ist das Maximum der konfigurierten Httpd-Prozesse</li>
                <li>"Aktiv" Anzahl aller aktiven Prozesse (Der Status M ist ungleich "_" und ungleich ".").</li>
                <li>"Idle" ist die Anzahl der Prozesse im Status "_".</li>
                <li>"Unbenutzt" ist die Anzahl der Prozesse, die noch gestartet werden k&ouml;nnen.</li>
            </ul>',

        // ............................................................
        'lblTable_status'=>'Serverstatus',
        'lblTableHint_status'=>'Die Tabelle zeigt die Status Informationen der Webserver',

        // ............................................................
        'lblTile_server_responsetime'=>'Antwortzeit',
        'lblTileHint_server_responsetime'=>'Antwortzeit der Anfrage aller Server',
        'lblTile_server_count'=>'Server',
        'lblTileHint_server_count'=>'Anzahl der abgefragten Webserver',

        // ............................................................
        'lblTile_requests_all'=>'Anz. Requests',
        'lblTileHint_requests_all'=>'Gesamt-Anzahl der Requests (aller angefragten Server)',
        'lblTable_requests_all'=>'Liste aller Requests',
        'lblTableHint_requests_all'=>'Die Liste zeigt alle aktiven und nicht aktiven Requests.<br>Bei Profilen mit mehreren Servern sehen Sie hier die Requests aller Server und k&ouml;nnen so Requests &uuml;ber alle Server hinweg vergleichen.',

        // ............................................................
        'lblTile_requests_running'=>'Aktiv',
        'lblTileHint_requests_running'=>'Aktive Requests',
        'lblTable_requests_running'=>'Aktive Requests',
        'lblTableHint_requests_running'=>'Die Tabelle zeigt die Requests, die derzeit auf den gew&auml;hlten Servern verarbeitet werden.',

        // ............................................................
        'lblTile_requests_mostrequested'=>'H&auml;ufigste Anfrage',
        'lblTileHint_requests_mostrequested'=>'H&auml;ufigste Http-Anfrage',
        'lblTable_requests_mostrequested'=>'H&auml;ufigste Anfragen',
        'lblTableHint_requests_mostrequested'=>'Die Tabelle zeigt die am h&auml;ufigsten abgerufenen Elemente an.<br>
            Anm.:<br>
            <ul>
                <li>Die Tabelle ist nach Spalte "Anzahl" sortiert.</li>
                <li>Die Auflistung enth&auml;lt sowohl aktive als auch bereits beendete Requests.</li>
            </ul>',

        // ............................................................
        'lblTable_requests_hostlist'=>'H&auml;ufigste VHosts',
        'lblTableHint_requests_hostlist'=>'Die Tabelle zeigt die am h&auml;ufigsten abgerufenen virtuellen Hosts an.<br>
            Anm.:<br>
            <ul>
                <li>Die Tabelle ist nach Spalte "Anzahl" sortiert.</li>
                <li>Die Auflistung enth&auml;lt sowohl aktive als auch bereits beendete Requests.</li>
            </ul>',

    
        // ............................................................
        'lblTable_requests_methods' => 'Verteilung der Methoden',
        'lblTableHint_requests_methods' => 'Name und H&auml;ufigkeit der HTTP-Request Methoden',
    
        // ............................................................
        'lblTile_requests_clients' => 'Max. von IP',
        'lblTileHint_requests_clients' => 'Die meisten Anfragen stammen von welcher IP',
        'lblTable_requests_clients' => 'Anzahl Requests pro IP',
        'lblTableHint_requests_clients' => 'Liste der Clients und Anzahl von dessen (aktiven und bereits beendeten) Requests.<br>
			Anm.: eine IP Adresse kann eine Gateway-IP sein, dahinter k&ouml;nnen sich auch mehrere Rechner eines Unternehmens verbergen.',
    
        // ............................................................
        'lblTile_requests_longest'=>'L&auml;ngster Request',
        'lblTileHint_requests_longest'=>'L&auml;ngster Request',
        'lblTable_requests_longest'=>'Top 25 der langsamsten Requests',
        'lblTableHint_requests_longest'=>'Die Tabelle zeigt alle Requests sortiert nach deren Requestdauer.<br>
            Anm.:<br>
            <ul>
                <li>Die Request-Zeit ist im Apache-Status erst dann verf&uuml;gbar, wenn
                    der Request beendet ist.
                    Sie ist nicht f&uuml;r den aktiven Request verf&uuml;gbar (Wert ist dann immer "0").
                </li>
                <li>Die Tabelle ist nach Spalte "Req" sortiert (Wert ist in ms)</li>
            </ul>',
    
        // ............................................................
        'lblTable_explanation'=>'Erl&auml;terungen',
        'lblTableHint_expalanation'=>'Wie die Farben in den Tabellen zustande kommen.',

    // ------------------------------------------------------------
    // description for tables
    // ------------------------------------------------------------

        // table for worker status
        'thWorkerServer' => 'Webserver',
        'thWorkerTotal' => 'Slots',
        'thWorkerActive' => 'Aktiv',
        'thWorkerWait' => 'Idle',
        'thWorkerUnused' => 'Unbenutzt',
        'thWorkerBar' => 'Grafik',
        'thWorkerActions' => 'Aktionen',
        'thCount'=>'Anzahl',
    
        'bartitleUnusedWorkers' => '%s unbenutzte, noch startbare Prozesse',
        'bartitleBusyWorkers' => '%s aktive Worker-Prozesse',
        'bartitleIdleWorkers' => '%s idle Worker-Prozesse',
    
        'lblLink2Top' => 'Seitenanfang',
        'lblHintFilter' => 'Filtere nach',
        'lblReload' => 'Jetzt Aktualisieren',
        'lblExportLinks' => 'Tabelle (ungefiltert) exportieren',

    // ------------------------------------------------------------
    // serverinfos
    // ------------------------------------------------------------
        'lblHintServerInfos'=>'Anzeige der gew&auml;hlten Gruppe und deren Server mit Angabe der derzeit aktiven Requests.',
        'lblServerInfosServercount'=>'Server: <strong>%s</strong>',
        'lblServerInfosRemove'=>'Filter [%s] entfernen',

    // ------------------------------------------------------------
    // original page
    // ------------------------------------------------------------
        'lblHelpOriginal'=>'Original server-status Ausgabe',
        'lblHintHelpOriginal'=>'Hier wird die  Original-Ausgabe des Httpd server-status angezeigt',
    
    // ------------------------------------------------------------
    // utilization page
    // ------------------------------------------------------------
        'lblHelpUtilization'=>'Auslastung',
        'lblHintHelpUtilization'=>'Anzeige der Auslastung des einzelnen Systems',

        'lblUtilizationLowActivityCritical'=>'HINWEIS: Der Wert unbenutzer Prozesse (%s von %s) ist extrem hoch. Vielleicht sind zu viele Prozesse konfiguriert oder es ist im Moment sehr geringer Traffic.',
        'lblUtilizationLowActivityWarning'=>'HINWEIS: Der Wert unbenutzer Prozesse (%s von %s) ist recht hoch. Vielleicht sind zu viele Prozesse konfiguriert oder es ist im Moment wenig Traffic.',
        'lblUtilizationHighActivityCritical'=>'HINWEIS: Die Anzahl der Prozesse (%s von %s) ist extrem hoch. Erh&ouml;he die Anzahl der Prozesse in der Konfiguration oder es braucht mehr Ressourcen/ Server, um den Traffic zu handhaben.',
        'lblUtilizationHighActivityWarning'=>'HINWEIS: Die Anzahl der Prozesse (%s von %s) ist recht hoch. Es braucht bald mehr Prozesse in der Konfiguration oder mehr Ressourcen/ Server, um den Traffic zu handhaben.',

        'lblUtilizationHighActivityWarning'=>'HINWEIS: Die Anzahl der Prozesse (%s von %s) ist recht hoch. Es braucht bald mehr Prozesse in der Konfiguration oder mehr Ressourcen/ Server, um den Traffic zu handhaben.',

        'lblUtilizationWorkerProcessesActiveTitle'=>'aktiv besch&auml;ftigte Prozesse',
        'lblUtilizationWorkerProcessesActiveTitleTotal'=>'aktiv besch&auml;ftigte Prozesse - im Verh&auml;ltnis zu %s verf&uuml;gbaren Slots',
        'lblUtilizationWorkerProcessesActive'=>'aktiv',
        'lblUtilizationWorkerProcessesRunningTitle'=>'laufende Prozesse (aktiv und idle)',
        'lblUtilizationWorkerProcessesRunningTitleTotal'=>'laufende Prozesse (aktiv und idle) - im Verh&auml;ltnis zu %s verf&uuml;gbaren Slots',
        'lblUtilizationWorkerProcessesRunning'=>'Prozesse',
    
        'lblUtilizationTraffic'=>'Durchsatz',
        'lblUtilizationTrafficTotalAccesses'=>'Zugriffe ges.',
        'lblUtilizationTrafficTotalTraffic'=>'Datenmenge ges.',
        'lblUtilizationTrafficAvgAccesses'=>'Zugriffe',
        'lblUtilizationTrafficAvgTraffic'=>'Daten-Durchsatz',
    
    // ------------------------------------------------------------
    // help page
    // ------------------------------------------------------------
        'lblHelpDoc'=>'Dokumentation',
        'lblHintHelpDoc'=>'Hinweise und Links zu Dokumentationen',
        'lblHelpDocContent'=>'
            <br>
            <strong>Tabellen - Sortieren</strong><br>
            <br>
            Man kann die Tabellen sortieren, indem man auf einen Spaltennamen
            klickt. Die Reihenfolge kehrt man um, wenn
            man erneut darauf klickt.<br>
            Eine Mehrfachsortierung ist ebenfalls m&ouml;glich: halte die SHIFT
            Taste gedr&uuml;ckt, wenn ein oder mehrere andere Spaltennamen
            angeklickt werden.<br>
            <br>
            <strong>Tabellen - Filtern</strong><br>
            <br>
            Verwende das Suchfeld, um die gesamte Tabelle nach einer Zeichenfolge
            zu filtern.<br>
            Du kannst per Klick auf die Eintr&auml;ge einiger Spalten nach deren
            Wert filtern.<br>
            Das [X] Symbol neben dem Suchfeld hebt den Filter wieder auf.<br>
            <br>
            <strong>Links</strong><br>
            <br>
            Mehr Informationen bekommt man hier:
            ',

        'lblHelpBookmarklet'=>'<strong>Bookmarklet</strong><br>
            <br>
            Eine Server-Status-Seite per Klick und ohne Konfiguration
            anschauen - das geht, wenn diese im Internet sichtbar ist.<br>
            Ziehe den nachfolgenden Button in deine Bookmarks: ',
        'lblHelpBookmarkletLabel'=>'Pimped Apache Status <strong>Bookmarklet</strong>',
        'lblHelpBookmarkletTitle'=>'Bitte in den Button in deine Bookmarks ziehen.',
    
        'lblHelpColors'=>'Einf&auml;rbung der Requests',
        'lblHintHelpColors'=>'
            Die Request-Zeilen sind eingef&auml;rbt. Die tats&auml;chliche Farbgebung h&auml;ngt von der 
            Unterst&uuml;tzung im jeweiligen Skin ab.<br>
            Generell h&auml;ngt die farbliche Darstellung von mehreren Faktoren 
            ab, wobei sich die Farbeigenschaften der einzelnen Gruppen addieren.<br>
            Nachfolgend sehen Sie die Faktoren und die Farbgebung des aktuellen
            Skins.<br>
            ',
    
        'lblHelpThanks'=>'Danke!',
        'lblHintHelpThanks'=>'Diese fremden Hilfsmittel und Tools werden verwendet.',
        'lblHelpThanksContent'=>'
            <p>
                Mein Dankesch&ouml;n geht an die Entwickler der in diesem Tool
                verwendeten Produkte:
            </p>
            <ul>
                <li>Admin LTE - Control Panel Template: <a href="https://adminlte.io/">https://adminlte.io/</a></li>
                <li>jQuery: <a href="https://jquery.com/">https://jquery.com/</a></li>
                <li>Chart.js: <a href="https://www.chartjs.org/">https://www.chartjs.org/</a></li>
                <li>Datatables - sortierbare Tabellen: <a href="https://datatables.net/">https://datatables.net/</a></li>
                <li>array2xml.class - XML Export: <a href="http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes">http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes</a></li>
                <li>Bootstrap - Html-Framework: <a href="http://getbootstrap.com/">http://getbootstrap.com/</a></li>
                <li>Font-awesome - Icons: <a href="https://fontawesome.io/">https://fontawesome.io/</a></li>
            </ul>
            ',
    
        // column "comment" by column "M"
        'lblStatus' => 'Modus',
        'cmtLegendM' => 'Modus des Requests (Spalte "M")',
        'cmtStatus_' => '_ Request ist beendet. Wartet auf neue Verbindung.',
        'cmtStatusS' => 'S Starte',
        'cmtStatusR' => 'R Lese Request',
        'cmtStatusW' => 'W Sende Antwort',
        'cmtStatusK' => 'K Keepalive (lesen)',
        'cmtStatusD' => 'D DNS Lookup',
        'cmtStatusC' => 'C Schliesse Verbindung',
        'cmtStatusL' => 'L Logging',
        'cmtStatusG' => 'G kontrolliertes Beenden',
        'cmtStatusI' => 'I Idle cleanup',
        'cmtStatus.' => '. Request beendet. Offener Slot ohne aktive Verbindung.',
        // 'cmtRequest'=>'',
    
        'cmtLegendRequest' => 'HTTP Request-Methode',
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
    
    
        'cmtLegendexectime' => 'Ausf&uuml;hrungszeit eines Requests',
        'cmtexectimewarning' =>'Warnung',
        'cmtexectimecritical' =>'Kritisch',
    
    // ------------------------------------------------------------
    // description for debug
    // ------------------------------------------------------------
    
        'lblDumpsaUserCfg'=>'$aUserCfg - benutzerspezifische Konfiguration',
        'lblHintDumpsaUserCfg'=>'Die Konfiguration ist zusammengef&uuml;hrt aus
            der Default-Konfiguration und der Benutzer-Config.',
    
        'lblDumpsaEnv'=>'$aEnv - Environent des aktuellen Requests',
        'lblHintDumpsaEnv'=>'Es beinhaltet Informationen, die beim Rendern der
            Ausgabe hilfreich sind.<br>
            So gibt es Name und Version des Projekts, aktuelle Werte
            (z.B. gew&auml;hlte Servergruppe, aktuelle Sprache, Skin).<br>
            Unterhalb des Keys "links" sind Arrays, die man f&uuml;r Men&uuml;s verwenden
            kann - sei es als Dropdown oder Tablist.',
    
        'lblDumpsaSrvStatus'=>'$aSrvStatus - Array des server status',
        'lblHintDumpsaSrvStatus'=>'Jeder Server hat ein einen Key "status" und "request".
            Hier sind die aus der HTML-Ausgabe des Apache server-status geparsten
            Daten abgelegt.',
    
        'lblDumpsaLang'=>'$aLang - array mit sprachabhh&auml;ngigen Texten',
        'lblHintDumpsaLang'=>'Die Tabelle vergleicht die Werte der 
            sprachabh&auml;ngigen Texte aller aktivierten Sprachen.',
        'lblDumpsMiss'=>'!!! Dieser Key hat keinen Eintrag !!!',
    
    // ------------------------------------------------------------
    // software update
    // ------------------------------------------------------------
        'lblUpdate'=>'Aktualisierung der Applikation',
        'lblUpdateNewerVerionAvailable'=>'OK, es ist eine neuere Version verf&uuml;gbar.',
        'lblUpdateNoNewerVerionAvailable'=>'Hinweis: Es gibt keine neuere Version. Das Ausf&uuml;hren des Updates ist eigentlich nicht notwendig.',
        'lblUpdateHints'=>'
            Die Aktualisierung erfolgt in 2 Schritten:
            <ol>
                <li>Download der aktuellsten ZIP-Datei<br>%s</li>
                <li>Entpacken der ZIP-Datei</li>
            </ol>
            ',
        'lblUpdateOutput'=>'Ausgabe',
        'lblUpdateDonwloadDone'=>'OK, die Datei wurde erfolgreich heruntergeladen.<br>Als N&auml;chstes wird sie entpackt.',
        'lblUpdateDonwloadFailed'=>'Fehler: die Datei konnte nicht heruntergeladen werden.',
        'lblUpdateInstalldir'=>'Lokales Installationsverzeichnis: %s',
        'lblUpdateContinue'=>'Weiter &raquo;',
        'lblUpdateUnzipFile'=>'Entpacke Datei: %s<br>Nach: %s',
        'lblUpdateUnzipOK'=>'OK: die aktuelle Version wurde erfolgreich entpackt.<br>Wenn Ihnen die Software gef&auml;llt, dann k&ouml;nnen Sie mich unterst&uuml;tzen, indem Sie (unten im Footer) in die Dokumentation wechseln, diese teilen oder ein klein wenig spenden.<br>&Uuml;bersetzer in weitere Sprachen sind ebenfalls willkommen...',
        'lblUpdateUnzipFailed'=>'Fehler: die Zip-Datei konnte nicht ge&ouml;ffnet werden.',
    
    // ------------------------------------------------------------
    // javascript
    // ------------------------------------------------------------
        'js::statsCurrent'=>'aktuell',
        'js::statsAvg'=>'Schnitt',
        'js::statsMax'=>'Maximum',
        'js::statsMin'=>'Minimum',
        'js::srvFilterPlaceholder'=>'Server finden',
    
    // ------------------------------------------------------------
    // admin
    // ------------------------------------------------------------
        'ActionAdd'=>'Hinzuf&uuml;gen',
        'ActionAddServer'=>'Server hinzuf&uuml;gen',
        'ActionAddServergroup'=>'Server-Gruppe hinzuf&uuml;gen',
        'ActionEdit'=>'Bearbeiten',
        'ActionContinue'=>'Weiter',
        'ActionClose'=>'Schliessen',
        'ActionDelete'=>'L&ouml;schen',
        'ActionDeleteHint'=>'Eintrag l&ouml;schen ... wenn du sicher bist.',
        'ActionDownload'=>'Download',
        'ActionDownloadHint'=>'Bibliothek herunterladen und von local verwenden.',
        'ActionOK'=>'OK',
        'ActionOKHint'=>'&Auml;nderungen speichern',
        'ActionLogin'=>'Anmelden',
        'ActionResetToDefaults'=>'Zur&uuml;cksetzen',
        'ActionResetToDefaultsHint'=>'Zur&uuml;cksetzen auf Standardwert',
    
        'AdminMenusettings'=>'Einstellungen',
        'AdminMenuservers'=>'Server',
        'AdminMenulang'=>'Sprachen',
        'AdminMenuvendor'=>'Vendor-Bibliotheken',
        'AdminMenuupdate'=>'Update',
    
        'AdminMenuSettingsCompare'=>'Vergleichen',    
        'AdminHintSettingsCompare'=>'Hier sind alle Default Werte sichtbar und welche von ihnen &uuml;bersteuert werden.<br>',
        'AdminMenuSettings-var'=>'Variablen-Name',
        'AdminMenuSettings-description'=>'Beschreibung',
        'AdminMenuSettings-default'=>'Default',
        'AdminMenuSettings-uservalue'=>'Benutzer-Wert',
        'AdminMenuSettings-value'=>'Wert(e)',
        'AdminHintRaw-internal-config_default'=>'Ansicht der Rohdaten aller Default-Einstellungen.<br>',
        'AdminHintRaw-config_user'=>'Benutzer-Einstellungen als Rohdaten bearbeiten.<br>',
    
        'AdminHintServers'=>'Anlegen von Gruppen.<br>'
            . 'In jeder Gruppe kannst du mehrere Apache Server anlegen, die &uuml;berwacht werden sollen. Alle status Seiten aller Server derselben Gruppe werden simultan eingeholt.<br>'
            . 'So kannst du eine loadbalancte Webseite monitoren.<br>'
            . 'Oder erzeuge eine Gruppe mit Servern, um ein Aug auf den Traffic aller Server (Appserver, Caching-Server) eines Projektes zu haben.<br>',
        'AdminServersLblAddGroup'=>'Neue Gruppe von Servern anlegen.',
        'AdminLblGroup-label'=>'Name der Gruppe',
    
        'AdminServersLblAddServer'=>'Hinzuf&uuml;gen eines Apache Httpd Server in dieser Gruppe.',
    
        'AdminLblServers'=>'Apache-Server konfigurieren',
        'AdminLblServers-ConfirmDelete'=>'Bist du sicherm, dass du den Server-Eintrag l&ouml;schen m&ouml;chtest\n%s?',
        'AdminLblServers-label'=>'Servername',
        'AdminLblServers-label-Hint'=>'Servername des Apache webservers.',
        'AdminLblServers-status-url'=>'Url der Serverstatus Seite',
        'AdminLblServers-status-url-Hint'=>'Url der Serverstatus Seite. Wird sie leer gelassen, wird der Default verwendet: <em>http://[servername]/server-status/</em>. Passe es an, wenn https statt http oder ein anderer Port verwendet wird, z.B. <em>http[s]://[servername]:[port]/server-status/</em>',
        'AdminLblServers-userpwd'=>'Benutzername und Password',
        'AdminLblServers-userpwd-Hint'=>'optional; Nur dann ausf&uuml;llen, wenn die Serverstatus-Seite passwort-.gesch&uuml;tzt ist.<br>Syntax: <em>[username]:[password]</em>',
    
        'AdminMessageServer-add-defaults-ok' => 'OK: Default-Gruppe und -Server werden erstellt.',
        'AdminMessageServer-add-defaults-error' => 'FEHLER: Default-Gruppe und -Server konnten nicht erstellt werden.',
        'AdminMessageServer-addgroup-error' => 'FEHLER: Gruppe %s kann nicht hinzugef&uuml;gt werden.',
        'AdminMessageServer-addgroup-ok' => 'OK: Gruppe %s wurde hinzugef&uuml;gt.',
        'AdminMessageServer-deletegroup-error' => 'FEHLER: Gruppe %wurde nicht gel&ouml;scht.',
        'AdminMessageServer-deletegroup-ok' => 'OK: Gruppe %s wurde gel&ouml;scht.',
        'AdminMessageServer-updategroup-error' => 'FEHLER: Gruppe %s kann nicht aktualisiert werden.',
        'AdminMessageServer-updategroup-ok' => 'OK: Gruppe %s wurde hinzugef&uuml;gt.',
        'AdminMessageServer-addserver-error' => 'FEHLER: Server %s kann nicht hinzugef&uuml;gt werden.',
        'AdminMessageServer-addserver-ok' => 'OK: Server %s wurde hinzugef&uuml;gt.',
        'AdminMessageServer-deleteserver-error' => 'FEHLER: Server %s kann nicht gel&ouml;scht werden.',
        'AdminMessageServer-deleteserver-ok' => 'OK: Server %s wurde gel&ouml;scht.',
        'AdminMessageServer-updateserver-error' => 'FEHLER: Server %s konnte nicht aktualisiert werden.',
        'AdminMessageServer-updateserver-ok' => 'OK: Server %s wurde aktualisiert.',
    
        'AdminMessageSettings-update-error-no-json' => 'SKIP: die gesendeten Daten sind kein valides JSON. Die alten Einstellungen bleiben bestehen. Du kannst zur&uuml;ckwechseln, um es erneut zu versuchen.',
        'AdminMessageSettings-update-error' => 'FEHLER: die Benutzereinstellungen wurden nicht gespeichert :-/',
        'AdminMessageSettings-update-ok' => 'OK: Benutzereinstellungen wurden gespeichert.',
        'AdminMessageSettings-wrong-key' => 'WARNUNG: der Wert [%s] ist ung&uuml;tig. Diese Einstellöung ist sinnlos: ',
        
        'AdminHintVendor'=>'Verwendete Bibliotheken und von wo sie geladen werden. Ob sie von einem CDN oder lokal geladen werden, hat funktional keinen Einfluss. Liegen sie lokal, erh&ouml;ht des die Ladegeschwindigkeit und man kann die Webapplikation ohne externe Internet-Anbindung betreiben.',
        'AdminVendorLib'=>'Bibliothek',
        'AdminVendorVersion'=>'Version',
        'AdminVendorLocal'=>'Lokal',
        'AdminVendorRemote'=>'Remote',
        'AdminVendorLibLocalinstallations'=>'<strong>%s</strong> verwendete Bibliotheken gesamt - davon liegen <strong>%s</strong> lokal. Lade alle herunter, um die beste Performance zu haben.',
        'AdminVendorLibAllLocal'=>'Alle <strong>%s</strong> verwendeten Bibliotheken sind lokal.',
        'AdminVendorLibDelete'=>'Lokale, nicht mehr benutze Bibliotheken: <strong>%s</strong>',
        'AdminVendorLibUnused'=>'nicht mehr verwendet',
    
        'AdminHintUpdates'=>'Update dieser Web-applikation.<br>',

    // ------------------------------------------------------------
    // cfg values
    // ------------------------------------------------------------
        'cfg-auth'=>'Interne Authentifizierung zum Zugriffs-Schutz auf die Apache Serverstatus Daten. Setze Username und Md5 Hash des Passwortes oder aber <em>auth: false</em>, um interne Authentifizierung zu deaktivieren (nutzen Sie Restriktionen in der Httpd Konfiguration)',
            'AdminLblVar-user'=>'Username',
            'AdminLblVar-password'=>'Passwort',
            'cfg-values-auth'=>'(A) Hash mit 2 Keys<br>"<em>user</em>": [Login Username (string)],<br>"<em>password</em>": [md5 Hash des Passwortes (string)]<br>OR<br>(B) <em>false</em> um das login zu deaktivieren (verwende dann Restrictionen in der Httpd-config)',
        'cfg-autoreload'=>'Zeit bis Neuladen der Seite. Das Array enthält die Werte in Sekuinden, die als Dropdown angezeigt werden.',
            'AdminLblVar-autoreload'=>'Zeit in sec',
            'cfg-values-autoreload'=>'(Array) mit Integer Werten in Sekunden',
        'cfg-checkupdate'=>'Intervall zur Pr&uuml;fung eines Updates. Der Wert ist in [s].',
            'cfg-values-checkupdate'=>'(integer); Der Wert 0 schaltet die Pr&uuml;fung ab. Default ist 1 Tag.',
        'cfg-datatableOptions'=>'Javascript Objekt für die Datatable Komponente. Dieses nicht &uuml;berschreiben.',
            'cfg-values-datatableOptions'=>'(object); s. Dokumentation unter datatables.net',
        'cfg-defaultTemplate'=>'Dieses nicht &uuml;berschreiben.',
            'cfg-values-defaultTemplate'=>'(string)',
        'cfg-defaultView'=>'Default Ansicht ist die Server-info Seite.',
            'cfg-values-defaultView'=>'(string)',
        'cfg-debug'=>'Debug-Informationen einschalten. Default ist false (AUS).',
            'cfg-values-debug'=>'(bool); Setze es auf true f&uuml;r in einer Etwicklungsumgebung oder zum Tuning.',
        'cfg-tmpdir'=>'Temp Verzeichnis. Dieses Verzeichis braucht Schreibrechte. Default ist tmp (= [Appdir]/tmp]).',
            'cfg-values-tmpdir'=>'(string) Default ist tmp (= [Appdir]/tmp]).',
        'cfg-execTimeRequest'=>'Array zur Definition der Zeitlimits in [ms] f&uuml;r for langlaufende Requests: ein Wert für Warnungen, einer für kritische.',
            'cfg-values-execTimeRequest'=>'(integer)',
        'cfg-hideCols'=>'Array der auszublendenden Spalten. Man kann etwas Platz sparen, indem nicht gewünschte Spalten ausblendet. Warnung: blenden sie keine wichtigen Spalten aus!',
            'cfg-values-hideCols'=>'(array) Namen der Tabellenspalten.',
        'cfg-hideRows'=>'Array der auszublendenden Zeilen pro Ansicht.<br>Der Name der ersten Ebene ist der Name des Views.<br>Es ist eines aus requests_running|requests_mostrequested|requests_hostlist|requests_methods|requests_clients oder * f&uuml;r alle Ansichtne<br><br>Unterhalb des Keys ist eine Liste von Arrays mit 4 Werten.<ul><li>Keyword: add|remove</li><li>Name der Spalte (wie in den Tabellenspalten genannt)</li><li>Operator: einer aus lt|le|eq|ne|ge|gt|regex</li><li>Wert: Vergleichswert oder Regex</li></ul>',
            'cfg-values-hideRows'=>'(array)',
        'cfg-icons'=>'Dieses nicht &uuml;berschreiben.',
            'cfg-values-icons'=>'(array)',
        'cfg-lang'=>'Aktive Standard-Sprache.',
            'cfg-values-lang'=>'(string); Name einer Sprach-Datei im Verzeichnis ./lang/',
        'cfg-selectLang'=>'Array der im Dropdown w&auml;hlbaren Sprachen',
            'cfg-values-selectLang'=>'(Array) mit Stings der sichtbaren Sprachen im Dropdown der Sprachauswahl',
        'cfg-selectSkin'=>'Array der im Dropdown w&auml;hlbaren Skins',
            'cfg-values-selectSkin'=>'(array) mit Strings der Skins im Dropdown der Skinauswahl',
        'cfg-selfurl'=>'Basis-Url der Applikation setzen (nur bei Verwendung eines Alias erforderlich)',
            'cfg-values-selfurl'=>'(string) Name des Alias mit beginnendem aber ohne endenden Slash, z.B. <em>/apachestatus</em>; Default ist false (=automat. Erkennung des relativen Pfades ab Webroot)',
        'cfg-showHint'=>'Hinweisboxen anzeigen (true/ false)',
            'cfg-values-showHint'=>'(boolean); Default ist true',
        'cfg-skin'=>'Aktiviertes Default-Skin.',
            'cfg-values-skin'=>'(string) Werte s. Skinauswahl-Dropdown',
        'cfg-skin-color2'=>'CSS-Klasse der Kacheln und Titelbalken der Graphen.',
            'cfg-values-skin-color2'=>'(string) einer aus bg-aqua|bg-red|bg-green|bg-yellow',
        'cfg-tdbars'=>'Tabellenspalten mit Werten, zu denen eine Balkengrafik angezeigt werden soll. Der Maximumwert bekommt einen vollen Balken; alle anderen Breiten sind relativ.',
            'cfg-values-tdbars'=>'(array) mit Strings der Namen von Tabellenspalten',
        'cfg-tdlink'=>'Spezielle Links für Tabellenspalten.',
            'cfg-values-tdlink'=>'(array) mit Tabellenspalten als Keys; Werte sind Html Code',
        'cfg-views'=>'Liste der sichtbaren Men&uuml;-Punkte',
            'cfg-values-views'=>'(array) mit Strings',
        'cfg-viewsadmin'=>'Liste der sichtbaren Admin-Men&uuml;-Punkte',
            'cfg-values-viewsadmin'=>'(array) mit Strings',
        'cfg-wrongitem'=>'!!falscher Key!!',
        'cfg-'=>'',
    
);
