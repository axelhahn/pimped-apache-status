<?php

/*
 * PIMPED APACHE-STATUS
 * Serverstatus class
 *
 * @package pimped_apache_status
 * @author Axel Hahn
 */

class ServerStatus {

    private $a = array();
    private $aServer = array();
    private $_fResponsetime = false;
    private static $curl_opts = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FAILONERROR => 1,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_USERAGENT => 'pimped apache status',
            // CURLMOPT_MAXCONNECTS => 10
    );

    /**
     * constructor (it does nothing)
     * @return boolean (true)
     */
    public function __construct() {
        return true;
    }
    
    /**
     * add a log messsage
     * @global object $oLog
     * @param  string $sMessage  messeage text
     * @param  string $sLevel    warnlevel of the given message
     * @return bool
     */
    private function log($sMessage, $sLevel = "info") {
        global $oLog;
        if (!$oLog ||! is_object($oLog) || !method_exists($oLog, "add")){
            return false;
        }
        return $oLog->add("class " . __CLASS__ . " - " . $sMessage, $sLevel);
    }

    /**
     * parse response of original apachestatus and put it to an array
     * @param string $sStatus    response from serverstatus request (html code)
     * @param string $sHostname  host is used as key for the result array
     * @return array
     */
    private function _getServerData($sStatus, $sHostname = '') {
        $this->log('start '. __FUNCTION__ ."([sStatus], $sHostname)");
        if (!$sStatus)
            return false;
        if (!$sHostname)
            return false;
        if (!strpos($sStatus, "Apache Server Status for"))
            return false;

        $sStatusNobr = str_replace("\n", "", $sStatus);
        $aReturn = array();

        // regex to fetch server infos
        $sRegexStatus = '/\<dt\>(.*)\<\/dt\>/U'; // status data are in dt tags
        $sRegexStatus2 = '/(.*)\:\ (.*)/U'; // status data are in dt tags
        // regex to fetch the current requests
        // isolate the table with requests
        $sRegexGetRequestsTable = '#(<table border="0">.*</table>)#';
        $sRegFields = '/\<th\>(.*)\<\/th\>/Um'; // to fetch column names in th
        $sRegexRequests = '/\<tr\>\<td\>\<b\>(.*\n)*.*\<\/td\>\<\/tr\>/U';
        $sRegexRequests2 = '/<td[\ nowrap]*\>(.*)\<\/td\>/mU';

        // ----- get status infos
        if (preg_match_all($sRegexStatus, $sStatusNobr, $aStatusinfos)) {
            if ($aStatusinfos[1]) {
                foreach ($aStatusinfos[1] as $sStatusInfo) {

                    // devide infos from line [varianble]: [value]
                    $aStatusinfos2 = explode(" - ", $sStatusInfo);
                    foreach ($aStatusinfos2 as $sStatusinfo2) {
                        $aStatusinfos3 = explode(": ", $sStatusinfo2);
                        // found a key value pair
                        if (count($aStatusinfos3) == 2) {
                            $aReturn[$sHostname]['status'][$aStatusinfos3[0]] = $aStatusinfos3[1];
                        } else {
                            $sLine = strip_tags($aStatusinfos3[0]);
                            if (strpos($sLine, " CPU load") > 0) {
                                $aReturn[$sHostname]['status']['CPU load'] = str_replace(" CPU load", "", $sLine);
                            }
                            if (strpos($sLine, " requests/sec") > 0) {
                                $aReturn[$sHostname]['status']['requests/sec'] = str_replace(" requests/sec", "", $sLine);
                            }
                            if (strpos($sLine, "/second") > 0) {
                                $aReturn[$sHostname]['status']['size/sec'] = str_replace("/second", "", $sLine);
                            }
                            // TODO: this line is left - what are u, s, cu, cs?
                            // CPU Usage: u1548.32 s194.7 cu.15 cs0 - 3.9% CPU load
                            // CPU Usage: u380.625 s1014.43 cu0 cs0 - 2.67% CPU load
                        }
                        // print_r($aStatusinfos3);
                    }
                }
            }
        }

        // print_r($aReturn); die();
        // echo "<hr>"; print_r($aStatusinfos); die();
        // ----- get list of requests:
        $sRequestTable = false;
        $dummy = preg_match_all($sRegexGetRequestsTable, $sStatusNobr, $aTmpTable);
        if ($dummy) {
            $sRequestTable = $aTmpTable[0][0];
        }

        if ($sRequestTable) {
            $formatOk = preg_match_all($sRegFields, $sRequestTable, $aFieldnames);
            $aStatusfields = $aFieldnames[1];

            $formatOk = preg_match_all($sRegexRequests, $sRequestTable, $matches);
            if ($formatOk) {
                foreach ($matches[0] as $sRequestData) {
                    $formatOk = preg_match_all($sRegexRequests2, str_replace("\n", "", $sRequestData), $matches2);

                    $aTmp = array();
                    $aTmp['Webserver'] = $sHostname;
                    foreach ($matches2[0] as $iKey => $aEntry) {
                        $aTmp[strip_tags($aStatusfields[$iKey])] = strip_tags($aEntry);
                    }

                    // START extract Method from column "Request"
                    if (array_key_exists("Request", $aTmp)) {
                        $sR = preg_replace('/([A-Z]*)\ .*/', "$1", $aTmp['Request']);
                    }
                    $aTmp['Method'] = $sR;
                    // END extract Method from column "Request"

                    $aReturn[$sHostname]['requests'][] = $aTmp;
                }
            }
        }
        $aReturn[$sHostname]['orig'] = $sStatus;

        return $aReturn;
    }

    // --- helpers for dataFilterCheckRow(): compare functions

    /**
     * filter lt - returns bool $a lower than $b
     * @param type $a
     * @param type $b
     * @return boolean
     */
    private function _filter_lt($a, $b) {
        return $a < $b;
    }

    /**
     * filter le - returns bool $a lower equal $b
     * @param type $a
     * @param type $b
     * @return boolean
     */
    private function _filter_le($a, $b) {
        return $a <= $b;
    }

    /**
     * filter eq - returns bool $a equal $b
     * @param type $a
     * @param type $b
     * @return boolean
     */
    private function _filter_eq($a, $b) {
        return $a == $b;
    }

    /**
     * filter ne - returns bool $a not equal $b
     * @param type $a
     * @param type $b
     * @return boolean
     */
    private function _filter_ne($a, $b) {
        return $a != $b;
    }

    /**
     * filter ge - returns bool $a greater or equal $b
     * @param type $a
     * @param type $b
     * @return boolean
     */
    private function _filter_ge($a, $b) {
        return $a >= $b;
    }

    /**
     * filter gt - returns bool $a greater than $b
     * @param type $a
     * @param type $b
     * @return boolean
     */
    private function _filter_gt($a, $b) {
        return $a > $b;
    }

    /**
     * filter regex - returns bool regex $b matches $a
     * @param type $a
     * @param type $b
     * @return boolean
     */
    private function _filter_regex($a, $b) {
        return preg_match($b, $a);
    }

    /**
     * check a line in tabledata and apply the filter rules to a table
     * this function is a helper function for dataFilter()
     * @param type $aSingleRow
     * @param type $aFilter array    rules to reduce visible data
     *     array( add/ remove , columnname , operator , value to compare )
     */
    private function _dataFilterCheckRow($aSingleRow, $aFilter = false) {
        if (!$aFilter) {
            return $aSingleRow;
        }
        $bAdd = false;
        $bRemove = false;
        foreach ($aSingleRow as $sDataKey => $value) {

            foreach ($aFilter as $aFilterRule) {
                list($cmd, $col, $op, $val) = $aFilterRule;
                if ($cmd == "add") {
                    if ($sDataKey == $col) {
                        // echo "rule ADD filter_$op, $value, $val<br>";
                        $bAdd = $bAdd || $this->{'_filter_' . $op}($value, $val);
                    }
                }
                if ($cmd == "add_and") {
                    if ($sDataKey == $col) {
                        // echo "rule ADD filter_$op, $value, $val<br>";
                        $bAdd = $bAdd && $this->{'_filter_' . $op}($value, $val);
                    }
                }
                if ($cmd == "remove") {
                    if ($sDataKey == $col) {
                        // echo "rule REMOVE filter_$op, $value, $val<br>";
                        $bRemove = $bRemove || $this->{'_filter_' . $op}($value, $val);
                    }
                }
            }
        }

        // debugging stuff
        /*
          echo "DATA: " . print_r($aSingleRow, true);
          echo "FILTER: " . print_r($aFilter, true);
          echo "add: $bAdd - remove: $bRemove - "; if ($bRemove || !$bAdd) echo " SKIP"; else echo " OK "; echo "<hr>";
         */
        if ($bRemove || !$bAdd) {
            return false;
        }
        return $aSingleRow;
    }

    /**
     * filterfunction to extract, filter and sort status data
     * @param array  $a
     * @param filter $aFilter 
     *      $aFilter ... Array
     *         'sServer'      string   name of server to fetch the data from; default: all servers
     *         'sType'        string   one of "status" | "requests"
     *         'aRows'        array    keys to display as rows 
     *         'sSortkey'     string   sort a specific row 
     *         'sortorder'    const    SORT_ASC, SORT_DESC, SORT_REGULAR, SORT_NUMERIC, SORT_STRING
     *         'aRules'       array    rules to reduce visible data
     *              array( action , columnname , operator , value )
     *                  action:   add/ add_or/ remove
     *                  column:   key of array
     *                  operator: lt le eq ne ge gt regex
     *                  value:    value to compare
     *         'bGroup'       boolean  sort a specific row 
     *         'iLimit'       integer  max count of returned rows
     * @return array
     */
    function dataFilter($a = false, $aFilter) {
        // $this->log(__FUNCTION__ . "([data], <pre>".print_r($aFilter,1).")</pre> - start");

        global $aLangTxt;
        $aReturn = array();
        if (!$a) {
            $a = $this->a;
        }

        // ------------------------------------------------------------
        // check parameters
        // ------------------------------------------------------------


        if (!array_key_exists('sType', $aFilter)) {
            die("ERROR: " . __FUNCTION__ . " requires sType.");
        }
        if (!array_key_exists('aRows', $aFilter)) {
            die("ERROR: " . __FUNCTION__ . " requires aRows.");
        }

        if ($aFilter['sType'] != "status" && $aFilter['sType'] != "requests") {
            die("ERROR: function " . __FUNCTION__ . " does not support value in sType=>" . $aFilter['sType'] . ".");
        }
        if (array_key_exists('sortorder', $aFilter)) {
            if (
                    $aFilter['sortorder'] != SORT_ASC && $aFilter['sortorder'] != SORT_DESC && $aFilter['sortorder'] != SORT_DESC && $aFilter['sortorder'] != SORT_REGULAR && $aFilter['sortorder'] != SORT_NUMERIC && $aFilter['sortorder'] != SORT_STRING
            )
                die("ERROR: function " . __FUNCTION__ . " does not support value in sortorder=>" . $aFilter['sortorder'] . ".");
        }

        // ------------------------------------------------------------
        // flatten data
        // ------------------------------------------------------------
        foreach ($a as $sHost => $aData) {
            if (
                    (array_key_exists('sServer', $aFilter) && $aFilter['sServer'] == $sHost) ||
                    (!array_key_exists('sServer', $aFilter))
            ) {
                $aSingleRow = array();
                if ($aFilter['sType'] == "status") {
                    $aSingleRow['Webserver'] = $sHost;
                    foreach ($aFilter['aRows'] as $key) {
                        $aSingleRow[$key] = array_key_exists($key, $aData[$aFilter['sType']]) ? $aData[$aFilter['sType']][$key] : false;
                    }
                    $aSingleRow = $this->_dataFilterCheckRow($aSingleRow, array_key_exists('aRules', $aFilter) ? $aFilter['aRules'] : false);
                    if ($aSingleRow) {
                        $aReturn[] = $aSingleRow;
                    }
                }
                if ($aFilter['sType'] == "requests" && array_key_exists("requests", $aData)
                ) {
                    foreach ($aData[$aFilter['sType']] as $aRequest) {
                        $aSingleRow['Webserver'] = $sHost;
                        foreach ($aFilter['aRows'] as $key) {
                            $aSingleRow[$key] = array_key_exists($key, $aRequest) ? $aRequest[$key] : '';
                        }
                        $aSingleRow = $this->_dataFilterCheckRow($aSingleRow, array_key_exists('aRules', $aFilter) ? $aFilter['aRules'] : false);
                        if ($aSingleRow) {
                            $aReturn[] = $aSingleRow;
                        }
                    }
                }
            }
        }


        // ------------------------------------------------------------
        // sort array by a given key
        // http://php.net/manual/en/function.array-multisort.php
        // ------------------------------------------------------------
        $aSortCol = array();
        if (array_key_exists('sSortkey', $aFilter)) {
            foreach ($aReturn as $key => $row) {
                $aSortCol[$key] = $row[$aFilter['sSortkey']];
            }
            $sortorder = SORT_ASC;
            if (array_key_exists('sortorder', $aFilter)) {
                $sortorder = $aFilter['sortorder'];
            }
            if ($aSortCol && count($aSortCol)) {
                array_multisort($aSortCol, $sortorder, $aReturn);
            }
        }

        // ------------------------------------------------------------
        // group a column
        // ------------------------------------------------------------
        if (array_key_exists('bGroup', $aFilter)) {
            $aGroup = array();
            foreach ($aReturn as $key => $row) {
                // $aGroup[$row[$aFilter['sSortkey']]]++;
                if (array_key_exists($row[$aFilter['sSortkey']], $aGroup)) {
                    $aGroup[$row[$aFilter['sSortkey']]] ++;
                } else {
                    $aGroup[$row[$aFilter['sSortkey']]] = 1;
                }
            }
            arsort($aGroup);


            // create a new return array: count and grouped column
            // $aReturn=array("count"=>true, $aFilter['sSortkey']=>true);
            $aReturn = array();
            foreach ($aGroup as $key => $value) {
                $aReturn[] = array(
                    $aLangTxt['thCount'] => $value,
                    $aFilter['sSortkey'] => $key,
                );
            }
        }

        // ------------------------------------------------------------
        // limit
        // ------------------------------------------------------------
        if (array_key_exists('iLimit', $aFilter)) {
            while (count($aReturn) > $aFilter['iLimit']) {
                array_pop($aReturn);
            }
        }

        return $aReturn;
    }

    // ----------------------------------------------------------------------
    // GETTER
    // ----------------------------------------------------------------------
    /**
     * get response time to fetch all statuspages of all webservers
     * @see $this->getStatus()
     * @return integer
     */
    public function getResponseTime() {
        return $this->_fResponsetime;
    }

    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------

    /**
     * add a webserver
     * The parameter is the servername and array containing server config
     *     'webserver-01', array('status-url' => 'http://webserver-01:8888/server-status')
     * 
     * @param string $servername
     * @param array $serveropts
     */
    public function addServer($servername, $serveropts = array()) {
        if ($serveropts === null) {
            $serveropts = array();
        }
        if (!array_key_exists('status-url', $serveropts)) {
            $serveropts['status-url'] = "http://$servername/server-status";
        }
        $this->aServer[$servername] = $serveropts;
    }

    /**
     * helper function for multi_curl_exec
     * hint from kempo19b
     * http://php.net/manual/en/function.curl-multi-select.php
     * 
     * @param handle  $mh             multicurl master handle
     * @param boolean $still_running  
     * @return type
     */
    private function full_curl_multi_exec($mh, &$still_running) {
        do {
            $rv = curl_multi_exec($mh, $still_running);
        } while ($rv == CURLM_CALL_MULTI_PERFORM);
        return $rv;
    }

    // ----------------------------------------------------------------------
    // ACTIONS
    // ----------------------------------------------------------------------
    /**
     * make simultaneous requests to all server status pages 
     * parse them and and merge all data of all servers to a single array
     * @return array with keys "data" (results) and "error" (error messages)
     */
    public function getStatus() {

        $this->a = array();
        $aErrors = array();
        $running = false;

        $this->_fResponsetime = false;
        $iStart = microtime(true);

        // prepare curl object
        $master = curl_multi_init();

        // requires php>=5.5:
        if (function_exists('curl_multi_setopt')) {
            // force parallel requests
            curl_multi_setopt($master, CURLMOPT_PIPELINING, 0);
            // curl_multi_setopt($master, CURLMOPT_MAXCONNECTS, 50);
        }

        $curl_arr = array();
        $i = 0;
        foreach ($this->aServer as $sServer => $aData) {
            $sUrl = $aData['status-url'];
            $curl_arr[$i] = curl_init($sUrl);
            curl_setopt_array($curl_arr[$i], self::$curl_opts);
            if (array_key_exists('userpwd', $aData)) {
                curl_setopt($curl_arr[$i], CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($curl_arr[$i], CURLOPT_USERPWD, $aData['userpwd']);
            }
            curl_multi_add_handle($master, $curl_arr[$i]);
            $i++;
        }

        // make all requests
        self::full_curl_multi_exec($master, $running);
        do {
            curl_multi_select($master);
            self::full_curl_multi_exec($master, $running);
            while ($info = curl_multi_info_read($master)) {
                
            }
        } while ($running);

        // get results
        $i = 0;
        foreach ($this->aServer as $sServer => $aData) {
            $sUrl = $aData['status-url'];

            $s = curl_multi_getcontent($curl_arr[$i]);
            if (!$s) {
                if (curl_error($curl_arr[$i])) {
                    $aErrors[] = 'failed to fetch ' . $sUrl . ' - ' . curl_error($curl_arr[$i]) . ' - Maybe you need to check your server config.';
                } else {
                    $aErrors[] = 'failed to fetch ' . $sUrl . ' - Maybe you need to check your server config.';
                }
            } else {
                $aServerdata = $this->_getServerData($s, $sServer);
                if ($aServerdata && is_array($aServerdata)) {
                    // echo "<pre>"; print_r($aServerdata[$sServer]);
                    if (array_key_exists("requests", $aServerdata[$sServer])) {
                        $this->a = array_merge($this->a, $aServerdata);
                    } else {
                        $this->a = array_merge($this->a, $aServerdata);
                        $aErrors[] = 'Url ' . $sUrl . ' was found and is a server-status page - but you need to enable "Extended Status On".';
                    }
                } else {
                    $aErrors[] = 'Url ' . $sUrl . ' was found but this is not a server-status page.';
                }
            }
            curl_multi_remove_handle($master, $curl_arr[$i]);
            $i++;
        }
        curl_multi_close($master);
        $this->_fResponsetime = microtime(true) - $iStart;
        return array(
            'data' => $this->a,
            'errors' => $aErrors,
            'meta' => array(
                'servers' => $i,
                'responsetime' => $this->_fResponsetime,
            )
        );
    }

}
