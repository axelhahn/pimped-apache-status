<?php
global $aLangTxt;
$aLangTxt = array(

    'id'=>'deutsch',
    
    'menuAdmin'=>'Admin',
    'menuGroup'=>'Gruppe: ',
    'menuLang'=>'Sprache: ',
    'menuSkin'=>'Skin: ',
    'menuReload'=>'Aktualisierungs-Intervall: ',
    
    // ------------------------------------------------------------
    // version check
    // ------------------------------------------------------------
    'versionUptodate'=>'OK (aktuell)',
    'versionError'=>'??',
    'versionUpdateAvailable'=>'Version %s verf&uuml;gbar',
    'versionManualCheck'=>'auf neue Version testen',

    'authAccessDenied'=>'<h1>Zugriff verweigert</h1>Benutzername und Passwort sind erforderlich.',
    
    // ------------------------------------------------------------
    // for menu of views:
    // label for menu and h2
    // ------------------------------------------------------------
 
        'view_allrequests.php_label'=>'Alle Requests',
        'view_original.php_label'=>'Original Server-status',
        'view_performance-check.php_label'=>'Performance-Daten',
        'view_serverinfos.php_label'=>'Server-Infos',
        'view_help.php_label'=>'Hilfe',
        'view_dump.php_label'=>'Dumps',
        'view_setup.php_label'=>'Setup',
        'view_update.php_label'=>'Update',
        'view_selectserver.php_label'=>'Liste der Gruppen und Server',

    // ------------------------------------------------------------
    // for all tables in the views
    // ------------------------------------------------------------

        // ............................................................
        'lblTable_status_workers'=>'Worker Status',
        'lblTableHint_status_workers'=>'Die Tabelle zeigt den Status der Apache Worker Prozesse vom markierten Server bzw. von allen 
            Servern der Gruppe.<br>
            <ul>
                <li>"total" ist die Gesamtzahl der Worker-Prozesse</li>
                <li>"aktiv" Anzahl aller aktiven Prozesse (Der Status M ist ungleich "_" und ungleich ".").</li>
                <li>"idle" ist die Anzahl der Prozesse im Status "_".</li>
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
        'thWorkerTotal' => 'total',
        'thWorkerActive' => 'aktiv',
        'thWorkerWait' => 'idle',
        'thWorkerBar' => 'Grafik',
        'thWorkerActions' => 'Aktionen',
        'thCount'=>'Anzahl',
    
        'bartitleFreeWorkers' => 'freie Worker-Prozesse',
        'bartitleBusyWorkers' => 'aktive Worker-Prozesse',
        'bartitleIdleWorkers' => 'idle Worker-Prozesse',
    
        'lblLink2Top' => 'Seitenanfang',
        'lblHintFilter' => 'Filtere nach',
        'lblReload' => 'Jetzt Aktualisieren',
        'lblExportLinks' => 'Tabelle (ungefiltert) exportieren',

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
            Ziehe den nachfolgenden Link in deine Bookmarks: ',
    
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
                <li>jQuery: <a href="http://jquery.com/">http://jquery.com/</a></li>
                <li>Datatables - sortierbare Tabellen: <a href="http://datatables.net/">http://datatables.net/</a></li>
                <li>array2xml.class - XML Export: <a href="http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes">http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes</a></li>
                <li>Bootstrap - Html-Framework: <a href="http://getbootstrap.com/">http://getbootstrap.com/</a></li>
                <li>Font-awesome - Icons: <a href="http://fontawesome.io/">http://fontawesome.io/</a></li>
                <li>jQuery Knob - Skalen: <a href="https://github.com/aterrien/jQuery-Knob">https://github.com/aterrien/jQuery-Knob</a></li>
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
        'lblUpdateNoNewerVerionAvailable'=>'Hinweis: Es gibt keine neuere Version. Das Ausf&uuml;hren des Updates ist nicht notwendig.',
        'lblUpdateHints'=>'
            Die Aktualisierung erfolgt in 2 Schritten:
            <ol>
                <li>Download der aktuellsten ZIP-Datei<br>%s</li>
                <li>Entpacken der ZIP-Datei</li>
            </ol>
            ',
        'lblUpdateDonwloadDone'=>'OK, die Datei wurde erfolgreich heruntergeladen.<br>Als n&auml;chstes wird sie entpackt.',
        'lblUpdateDonwloadFailed'=>'Fehler: die Datei konnte nicht heruntergeladen werden.',
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
    // admin TODO
    // ------------------------------------------------------------
        'AdminMenuServers'=>'Server',
        'AdminLblServers'=>'Verwaltung der Server',
        'AdminLblServergroup'=>'server group',
);
