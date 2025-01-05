<?php

/**
 * ----------------------------------------------------------------------
 * 
 * Debug logging during a client request.
 * So you can measure any action find bottlenecks in your code.
 * 
 * Licence: GNU GPL 3.0
 * Source:  https://github.com/axelhahn/ahlogger
 * Docs:    https://www.axel-hahn.de/docs/ahlogger/
 * 
 * USAGE:<br>
 * (1) Trigger a message with add() to add a marker<br>
 * (2) The render() method lists all items in a table with time since start
 *     and the delta to the last message. <br>
 * 
 * @author www.axel-hahn.de
 * 
 * ----------------------------------------------------------------------
 * 2016-02-26  init
 * 2016-11-19  add memory usage
 * (...)
 * 2022-09-25  add memory tracking, add cli renderer 
 * 2022-09-27  css updates
 * 2022-10-02  add emoji chars 
 * 2022-10-16  mark longest action with an icon 
 * 2022-12-15  make it compatible to PHP 8.2; add doc + comments
 * 2023-05-15  fix _getBar() - division by zero
 * 2024-07-12  php8 only: use variable types; update phpdocs
 * 2024-09-04  fix short array syntax
 * ----------------------------------------------------------------------
 */
class logger
{

    /**
     * @var {array} array of added messages
     */
    protected $aMessages = [];

    /**
     * @var {bool} flag: show debug infos? default: false
     */
    protected $bShowDebug = false;

    /**
     * @var {int} memory usage on start
     */
    protected $_iMemStart = false;

    /**
     * @var {string} dynamic prefix for used css - it is set in the cronstructor
     */
    protected $sCssPrefix = '';

    protected $sSourceUrl = 'https://github.com/axelhahn/ahlogger';

    // ----------------------------------------------------------------------
    // CONSTRUCTOR
    // ----------------------------------------------------------------------

    /**
     * Constuctor
     * @param  string $sInitMessage  init message
     */
    public function __construct(string $sInitMessage = "Logger was initialized.")
    {
        $this->_iMemStart = memory_get_usage();
        $this->enableDebug(true);
        $this->add($sInitMessage);
        $this->sCssPrefix = 'debug-' . md5(microtime(true));
    }

    // ----------------------------------------------------------------------
    // PUBLIC METHODS
    // ----------------------------------------------------------------------

    /**
     * Add a logging message
     * @param string $sMessage
     * @param string $sLevel
     * @return boolean
     */
    public function add(string $sMessage, string $sLevel = "info"): bool
    {
        if (!$this->bShowDebug) {
            return false;
        }
        $this->aMessages[] = [
            'time' => microtime(true),
            'message' => $sMessage,
            'level' => preg_replace('/[^a-z0-9\-\_]/', '', $sLevel),
            'memory' => memory_get_usage()
        ];

        return true;
    }

    /**
     * Enable / disable debugging
     * @param bool $bEnable
     * @return bool
     */
    public function enableDebug(bool $bEnable = true): bool
    {
        return $this->bShowDebug = !!$bEnable;
    }

    /**
     * Enable client debugging by a given array of allowed ip addresses
     * @param array $aIpArray list of ip addresses in a flat array
     * @return boolean
     */
    public function enableDebugByIp(array $aIpArray): bool
    {
        $this->enableDebug(false);
        if (!$_SERVER || !is_array($_SERVER) || !array_key_exists("REMOTE_ADDR", $_SERVER)) {
            return false;
        }
        if (array_search($_SERVER['REMOTE_ADDR'], $aIpArray) !== false) {
            $this->enableDebug(true);
        }
        return true;
    }

    /**
     * Helper function: prepare array of added massages before output
     * - detect warnings and errors
     * - detect needed time for each action
     * - detect longest action
     * - detect maximum of memory usage
     * - calculate total time
     * 
     * @return array
     */
    protected function _prepareRendering(): array
    {
        $iMem = memory_get_usage();
        $this->add('<hr>');
        $this->add('Memory on start: ' . number_format($this->_iMemStart, 0, '.', ',') . " bytes");
        $this->add('Memory on end: ' . number_format($iMem, 0, '.', ',') . " bytes");
        $this->add('Memory peak: ' . number_format(memory_get_peak_usage(), 0, '.', ',') . " bytes");

        $aReturn = [
            'totaltime' => false,
            'level' => false,
            'warnings' => '',
            'errors' => '',
            'maxrowid' => false,
            'maxtime' => false,
            'result' => []
        ];
        $sStarttime = $this->aMessages[0]["time"];
        $iLasttime = $sStarttime;
        $iCounter = 0;
        $sMaxRowId = false;
        $iMaxtime = -1;
        $iMaxmem = -1;
        $bHasWarning = false;
        $bHasError = false;

        foreach ($this->aMessages as $aLogentry) {
            $iCounter++;

            if ($aLogentry["level"] == "warning") {
                $bHasWarning = true;
            }
            if ($aLogentry["level"] == "error") {
                $bHasError = true;
            }

            $sTrId = $this->sCssPrefix . 'debugTableRow' . $iCounter;
            $iDelta = $aLogentry["time"] - $iLasttime;
            if ($iDelta > $iMaxtime) {
                $iMaxtime = $iDelta;
                $sMaxRowId = $sTrId;
            }
            $iMaxmem = max($aLogentry["memory"], $iMaxmem);


            if (($iDelta > 1) || $aLogentry["level"] == "warning") {
                $aReturn['warnings'] .= '<a href="#' . $sTrId . '" title="' . sprintf("%01.4f", $iDelta) . ' s">' . $iCounter . '</a>&nbsp;';
            }
            if ($aLogentry["level"] == "error") {
                $aReturn['errors'] .= '<a href="#' . $sTrId . '" title="' . sprintf("%01.4f", $iDelta) . ' s">' . $iCounter . '</a>&nbsp;';
            }
            $aReturn['entries'][] = [
                'time' => $aLogentry["time"],
                'level' => $aLogentry["level"],
                'message' => $aLogentry["message"],
                'memory' => sprintf("%01.2f", $aLogentry["memory"] / 1024 / 1024), // MB

                'trid' => $sTrId,
                'trclass' => $aLogentry["level"],
                'counter' => $iCounter,
                'timer' => sprintf("%01.3f", $aLogentry["time"] - $sStarttime),
                'delta' => sprintf("%01.0f", $iDelta * 1000),
            ];
            $iLasttime = $aLogentry["time"];
        }
        $aReturn['level'] = ($bHasWarning
            ? ($bHasError ? 'error' : 'warning')
            : ''
        );
        $aReturn['maxrowid'] = $sMaxRowId;
        $aReturn['maxtime'] = sprintf("%01.3f", $iMaxtime);
        $aReturn['maxmem'] = sprintf("%01.2f", $iMaxmem / 1024 / 1024);
        $aReturn['totaltime'] = sprintf("%01.3f", $aLogentry['time'] - $aReturn['entries'][0]['time']);
        return $aReturn;
    }

    /**
     * Get html code for a progressbar with divs
     * @param  int|float  $iVal  value between 0..max value
     * @param  int|float  $iMax  max value
     * @return string
     */
    protected function _getBar(int|float $iVal, int|float $iMax): string
    {
        return $iMax > 0
            ? '<div class="bar"><div class="progress" style="width: ' . ($iVal / $iMax * 100) . '%;">&nbsp;</div></div>'
            : ''
        ;
    }

    /**
     * Render output of all logging messages
     * @return string
     */
    public function render(): string
    {
        if (!$this->bShowDebug) {
            return false;
        }
        $aData = $this->_prepareRendering();

        /*
        Array
        (
            [totaltime] => 0.006
            [errors] =>  
            [warnings] => 3 
            [maxrowid] => debugTableRow3
            [maxtime] => 0.005
            [result] => Array
                (
                )

            [entries] => Array
                mit Elementen
                Array
                    (
                        [time] => 1663959608.2566
                        [level] => info
                        [message] => Logger was initialized.
                        [memory] => 538056
                        [trid] => debugTableRow1
                        [trclass] => info
                        [trstyle] => 
                        [counter] => 1
                        [timer] => 0.000
                        [delta] => 0.000
                    )
        */

        $sOut = '';
        // echo '<pre>'; print_r($aData); die();
        foreach ($aData['entries'] as $aLogentry) {
            $sOut .= '<tr class="' . $this->sCssPrefix . '-level-' . $aLogentry["level"] . '' . ($aLogentry["trid"] == $aData["maxrowid"] ? ' ' . $this->sCssPrefix . '-maxrow' : '') . '" '
                . 'id="' . $aLogentry["trid"] . '">' .
                '<td align="right">' . $aLogentry["counter"] . '</td>' .
                '<td>' . $aLogentry["level"] . '</td>' .
                '<td align="right">' . $aLogentry["timer"] . '</td>' .
                '<td align="right">' . $this->_getBar($aLogentry["delta"], $aData["maxtime"] * 1000) . ($aLogentry["delta"] == $aData['maxtime'] * 1000 ? '⏱️    ' : '') . $aLogentry["delta"] . ' ms</td>' .
                '<td align="right">' . $this->_getBar($aLogentry["memory"], $aData["maxmem"]) . $aLogentry["memory"] . ' MB' . '</td>' .
                '<td>' . $aLogentry["message"] . '</td>' .
                '</tr>';
        }
        if ($sOut) {
            $sOut = '
            <style>
                .' . $this->sCssPrefix . '-info {position: fixed; top: 6em; right: 1em; background: rgba(230,240,255, 0.8); border: 2px solid rgba(0,0,0,0.2); border-radius: 0.3em; z-index: 99999;}
                .' . $this->sCssPrefix . '-info .loggerhead    {background: rgba(0,0,0,0.4); color: #fff;padding: 0em 0.5em 0.2em; border-radius: 0.3em 0.3em 0 0; }
                .' . $this->sCssPrefix . '-info .loggercontent {padding: 0.5em; }
                .' . $this->sCssPrefix . '-info .loggercontent .total {font-size: 160%; color: rgba(0,0,0,0.5); margin: 0.3em 0; display: inline-block;}

                .' . $this->sCssPrefix . '-messages {margin: 5em 2em 2em;}
                .' . $this->sCssPrefix . '-messages>h3 {font-size: 150%; margin: 0 0 0.5em 0;}
                .' . $this->sCssPrefix . '-messages .bar      {background: rgba(0,0,0,0.03); height: 1.4em; position: absolute; width: 6em; border-right: 1px solid rgba(0,0,0,0.2);}
                .' . $this->sCssPrefix . '-messages .progress {background: rgba(100,140,180,0.2); height: 1.4em; padding: 0; float: left;}
                .' . $this->sCssPrefix . '-messages table{background: #fff; color: #222;table-layout:fixed; border: 2px solid rgba(0,0,0,0.2); border-radius: 0.5em;}
                .' . $this->sCssPrefix . '-messages table th{background: none; color: #222; border-bottom: 2px solid rgba(0,0,0,0.4);}
                .' . $this->sCssPrefix . '-messages table th.barcol{min-width: 7em; position: relative;}
                .' . $this->sCssPrefix . '-messages table td{padding: 3px; vertical-align: top;}
                .' . $this->sCssPrefix . '-messages table th:hover{background:#aaa !important;}

                .' . $this->sCssPrefix . '-level-info{background: #f0f4f4; color:#124}
                .' . $this->sCssPrefix . '-level-warning{background: #fcf8e3; color: #980;}
                .' . $this->sCssPrefix . '-level-error{background: #fce0e0; color: #944;}
                .' . $this->sCssPrefix . '-maxrow{color:#f33; font-weight: bold;}
            </style>
            <div class="' . $this->sCssPrefix . ' ' . $this->sCssPrefix . '-info ' . $this->sCssPrefix . '-level-' . $aData['level'] . '" onclick="location.href=\'#' . $this->sCssPrefix . '-messages\';">
                <div class="loggerhead">ahLogger</div>
                <div class="loggercontent">
                    <span class="total">⏱️ ' . $aData['totaltime'] . '&nbsp;s</span><br>
                    🪲 <a href="#' . $this->sCssPrefix . '-messages">Debug infos</a> | 🔺 <a href="#">top</a><br>
                    <span>longest&nbsp;action: ⏱️&nbsp;<a href="#' . $aData['maxrowid'] . '">' . ($aData['maxtime'] * 1000) . '&nbsp;ms</a></span>
                    ' . ($aData['errors'] ? '<br><span>‼️ Errors: ' . $aData['errors'] . '</span>' : '') . '
                    ' . ($aData['warnings'] ? '<br><span>⚠️ Warnings: ' . $aData['warnings'] . '</span>' : '') . '
                </div>
            </div>

            <div id="' . $this->sCssPrefix . '-messages" class="' . $this->sCssPrefix . ' ' . $this->sCssPrefix . '-messages">
            <h3>ahLogger 🪳 Debug messages</h3>'
                . ($aData['errors'] ? '<span>Errors: ' . $aData['errors'] . '</span><br>' : '')
                . ($aData['warnings'] ? '<span>Warnings: ' . $aData['warnings'] . '</span><br>' : '')
                . '<br>
            <table >
            <thead>
            <tr>
                <th>#</th>
                <th>level</th>
                <th>time [s]</th>
                <th class="barcol">delta</th>
                <th class="barcol">memory</th>
                <th>message</th>
            </tr></thead><tbody>
            ' . $sOut 
            . '</tbody></table>'
            . '🌐 <a href="'.$this->sSourceUrl.'" target="_blank">'.$this->sSourceUrl.'</a>'
            ;
        }
        return $sOut;
    }

    /**
     * Render output of all logging messages for cli output
     * @return string
     */
    public function renderCli(): string
    {
        if (!$this->bShowDebug) {
            return false;
        }
        $aData = $this->_prepareRendering();

        $sOut = '';
        foreach ($aData['entries'] as $aLogentry) {
            $sOut .= $aLogentry["timer"] . ' | '
                . $aLogentry["delta"] . ' ms | '
                . $aLogentry["level"] . ' | '
                . (sprintf("%01.3f", $aLogentry["memory"] / 1024 / 1024)) . ' MB | '
                . $aLogentry["message"] . ' '
                . "\n"
            ;
        }
        $sOut .= "\nTotal time: " . $aData['totaltime'] . "\n";
        return $sOut;
    }
}
