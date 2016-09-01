<?php

/**
 * Debug logging during a client request.
 *
 * @author hahn
 */
class logger {

    protected $aMessages = array();

    /**
     * constuctor
     * @param  string $sInitMessage  init message
     * @return boolean
     */
    public function __construct($sInitMessage = "Logger was initialized.") {
        $this->add($sInitMessage);
        return true;
    }

    /**
     * add a logging message
     * @param type $sMessage
     * @param type $sLevel
     * @return boolean
     */
    public function add($sMessage, $sLevel = "info") {
        global $aCfg;
        $this->aMessages[] = array(
            'time' => microtime(true),
            'message' => $sMessage,
            'level' => $sLevel
        );
        if ($sLevel == "MAIL") {
            mail($aCfg["emailDeveloper"], "Logmessage", $sMessage);
        }

        return true;
    }

    /**
     * render output of all logging messages
     */
    public function render() {
        $sOut = '';
        $sStarttime = $this->aMessages[0]["time"];

        $iCounter = 0;
        $sMaxRowId = false;
        $iMaxtime = -1;
        $sWarnings = false;

        $iLasttime = $sStarttime;

        foreach ($this->aMessages as $aLogentry) {
            $iCounter++;
            $sTrId = 'debugTableRow' . $iCounter;
            $iDelta = $aLogentry["time"] - $iLasttime;
            if ($iDelta > $iMaxtime) {
                $iMaxtime = $iDelta;
                $sMaxRowId = $sTrId;
            }


            $sStyle = ($iDelta > 1) ? 'color: #f82;' : '';
            if (($iDelta > 1) || $aLogentry["level"] == "warning"
            ) {
                $sWarnings.='<a href="#' . $sTrId . '" title="' . sprintf("%01.4f", $iDelta) . ' s">' . $iCounter . '</a>&nbsp;';
            }
            $sOut.='<tr class="' . $aLogentry["level"] . '" id="' . $sTrId . '" style="' . $sStyle . '">' .
                    '<td>' . $iCounter . '</td>' .
                    '<td>' . sprintf("%01.3f", $aLogentry["time"] - $sStarttime) . '</td>' .
                    '<td>' . sprintf("%01.3f", $iDelta) . '</td>' .
                    '<td>' . $aLogentry["level"] . '</td>' .
                    '<td>' . $aLogentry["message"] . '</td>' .
                    '</tr>';
            $iLasttime = $aLogentry["time"];
        }
        $iTotal = $iLasttime - $sStarttime;
        if ($sWarnings) {
            $sWarnings = '<br>warnings:&nbsp;' . $sWarnings;
        }

        if ($sOut)
            $sOut = '
            <div style="position: fixed; right: 25em; top: 6em; background: rgba(255,80,80, 0.1); padding: 0.5em; z-index: 99999;">
                <span style="font-size: 130%;">total:&nbsp;' . sprintf("%01.3f", $iTotal) . '&nbsp;s</span><br>
                <span>longest&nbsp;action:&nbsp;<a href="#' . $sMaxRowId . '">' . sprintf("%01.3f", $iMaxtime) . '&nbsp;s</a></span>
                <span>' . $sWarnings . '</span>
            </div>
            <br>
            <br>
            <br>
            <br>
            <br>
            <h3>DEBUG</h3><br>
            <table class="datatable table table-striped debugtable">
            <thead>
            <tr>
                <th>#</th>
                <th>delta to start time</th>
                <th>delta to previuos</th>
                <th>level</th>
                <th>message</th>
            </tr></thead><tbody>
            ' . $sOut . '</tbody></table>'
                    . '<script>$(\'#' . $sMaxRowId . '\').css(\'color\', \'#f00\');</script>';
        return $sOut;
    }

}
