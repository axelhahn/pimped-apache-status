<?php
/**
 * PIMPED APACHE-STATUS
 * datarenderer helps to render tiles and output sections with title,
 * hint, sortable tables
 *
 * @package pimped_apache_status
 * @author Axel Hahn
 */
class Datarenderer {

    /**
     * allowed tilenames - these keys have predefined filter rules
     * @var array
     */
    private $aValidTiles = array(
        // removed in 2.00.21 beta
        // 'server_count',
        'server_responsetime',
        'requests_all',
        'requests_running',
        'requests_clients',
        'requests_mostrequested',
        'requests_longest',
    );

    /**
     * allowed tablenames - these keys have predefined filter rules
     * @var array
     */
    private $aValidTables = array(
        'status',
        'requests_all',
        'requests_running',
        'requests_mostrequested',
        'requests_hostlist',
        'requests_methods',
        'requests_clients',
        'requests_longest',
        'worker_status',
    ); // allowed tables

    /**
     * list of filterarrays; these are filter rules for tiles and tables
     * and will be used for for $oServerStatus->dataFilter to generate
     * a view to the server status data
     * @var array
     */
    private $aFilterPresets = array(
        'status' => array('sType' => 'status'),
        'requests_all' => array('sType' => 'requests'),
        'requests_running' => array(
            'sType' => 'requests',
            'aRules' => array(
                array("add", "M", "gt", " "),
                array("remove", "M", "eq", "_"),
                array("remove", "M", "eq", "."),
            ),
        ),
        'requests_mostrequested' => array(
            'sType' => 'requests',
            // 'sSortkey' => array('Request', 'VHost'),
            'sSortkey' => 'Request',
            'bGroup' => true,
        ),
        'requests_hostlist' => array(
            'sType' => 'requests',
            'sSortkey' => 'VHost',
            'bGroup' => true,
        ),
        'requests_methods' => array(
            'sType' => 'requests',
            'sSortkey' => 'Method',
            'bGroup' => true,
        ),
        'requests_clients' => array(
            'sType' => 'requests',
            'sSortkey' => 'Client',
            'bGroup' => true,
        ),
        'requests_longest' => array(
            'sType' => 'requests',
            'aRows' => array("Req", "M", "VHost", "Request", "Webserver", "Comment"),
            'sSortkey' => 'Req',
            'sortorder' => SORT_DESC,
            'iLimit' => 25,
        ),
        'server_count' => array(
            'sType' => 'meta',
        ),
        'server_responsetime' => array(
            'sType' => 'meta',
        ),
        'workers_table' => array(
            'callfunction' => "_getWorkersData",
        ),
    );

    /**
     * list of css classnames for request methods, http methods and exectime
     * that will be used for all tables with request data
     * @var type 
     */
    private $aCssRows = array(
        'M' => array(
            '_' => array('class' => 'actunderscore'),
            'S' => array('class' => 'actS'),
            'R' => array('class' => 'actR'),
            'W' => array('class' => 'actW'),
            'K' => array('class' => 'actK'),
            'D' => array('class' => 'actD'),
            'C' => array('class' => 'actC'),
            'L' => array('class' => 'actL'),
            'G' => array('class' => 'actG'),
            'I' => array('class' => 'actI'),
            '.' => array('class' => 'actdot'),
        ),
        'Request' => array(
            // http methods:
            // http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
            'GET' => array('class' => 'methodget'),
            'HEAD' => array('class' => 'methodhead'),
            'POST' => array('class' => 'methodpost'),
            'PUT' => array('class' => 'methodput'),
            'DELETE' => array('class' => 'methoddelete'),
            'TRACE' => array('class' => 'methodtrace'),
            'CONNECT' => array('class' => 'methodconnect'),
            // other things in apache server-status:
            'NULL' => array('class' => 'methodnull'),
            'OPTIONS' => array('class' => 'methodoptions'),
            'PROPFIND' => array('class' => 'methodpropfind'),
        ),
        'exectime' => array(
            'warning' => array('class' => 'exectimewarn'),
            'critical' => array('class' => 'exectimecritical'),
        ),
    );

    /**
     * export types for tables
     * @var array
     */
    private $aExports = array(
        'csv' => array('mime' => 'text/csv', 'ext' => 'csv'),
        'json' => array('mime' => 'application/json', 'ext' => 'json'),
        'serialize' => array('mime' => 'text/plain', 'ext' => 'data'),
        'xml' => array('mime' => 'text/xml', 'ext' => 'xml'),
    );

    // ----------------------------------------------------------------------
    // 
    // ----------------------------------------------------------------------

    /**
     * constructor (it does nothing)
     * @return boolean (true)
     */
    public function __construct() {

        // this test disables xml export if php-xml is not available
        if (!class_exists("DomDocument")) {
            unset($this->aExports["xml"]);
        }
        // $this->_oA=new renderadminlte();
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
        if (!$oLog || !is_object($oLog) || !method_exists($oLog, "add")) {
            return false;
        }
        return $oLog->add("class " . __CLASS__ . " - " . $sMessage, $sLevel);
    }

    // ----------------------------------------------------------------------
    // GETTER
    // ----------------------------------------------------------------------

    /**
     * helper function: get the position of a key in an 
     * associated array
     * @global array $aCfg  user configuration
     * @param string $keyname
     * @param array $array
     * @return int 
     */
    private function _getNumberOfArraykey($keyname, $array) {
        global $aCfg;
        if (!count($array)) {
            return false;
        }
        $i = 0;
        foreach (array_keys($array) as $key) {
            if (in_array($key, $aCfg["hideRows"])) {
                continue;
            }
            if ($key == $keyname) {
                return $i;
            }
            $i++;
        }
        return false;
    }

    /**
     * get all maximum values of each column of a table; result is an array 
     * with column names as key and the maximum of this column
     * @param array $aTable
     * @return array
     */
    private function _getMaxima($aTable) {
        $aReturn = array();
        foreach ($aTable as $row) {
            foreach (array_keys($row) as $key) {
                if (!array_key_exists($key, $aReturn)) {
                    $aReturn[$key] = $row[$key];
                }
                if ($row[$key] > $aReturn[$key]) {
                    $aReturn[$key] = $row[$key];
                }
            }
        }
        return $aReturn;
    }

    /**
     * built an html tablerow for a single request
     * 
     * @global array   $aLangTxt  language texts
     * @global array   $aCfg      current configuration
     * 
     * @param  array   $aRows     data for the current row
     * @param  array   $aHeader   columns in apache status header
     * @param  string  $sTableID  table id; it is set of we have a datatable with a table field
     * @return string
     */
    private function _getReqLine($aRows, $aHeader, $sTableID = false, $aMax = array()) {

        global $aLangTxt; // texts
        global $aCfg; // configuration

        $bSpeedUp = false;

        $bHasTdBars = array_key_exists("tdbars", $aCfg) && is_array($aCfg['tdbars']) && count($aCfg['tdbars']);
        $aClick2Filter = array("Client", "VHost", "Request", "Method", "Comment");
        $sHtml = '';
        $sClass = '';
        $sComment = '';

        if (!$bSpeedUp) {
            $iExecWarn = $aCfg['execTimeRequest']['warning'];
            $iExecCritical = $aCfg['execTimeRequest']['critical'];

            // set colors with css classes for tr tag
            $aClasses = $this->getCssClasses();

            if (array_key_exists('M', $aRows)) {
                $sM = $aRows['M']; // status of http
                if ($aClasses['M'][$sM]) {
                    $sComment.=$aLangTxt['lblStatus'] . ': ' . $aLangTxt['cmtStatus' . $sM] . ' ';
                    $sClass.=$aClasses['M'][$sM]['class'] . ' ';
                }
            }

            if (array_key_exists('Req', $aRows)) {
                $iExecTime = $aRows['Req']; // exec time of the request
                if ($iExecTime >= $iExecWarn && $iExecTime < $iExecCritical) {
                    $sClass.="exectimewarn ";
                    $sComment.='Warnung: Exec= ' . $iExecTime / 1000 . ' s (>' . $iExecWarn . ' ms).';
                }
                if ($iExecTime >= $iExecCritical) {
                    $sClass.="exectimecritical ";
                    $sComment.='CRITICAL: Exec= ' . $iExecTime / 1000 . ' s. (>' . $iExecCritical . ' ms).';
                }
            }

            if (array_key_exists('Request', $aRows)) {
                $sR = preg_replace('/([A-Z]*)\ .*/', "$1", $aRows['Request']);
                if (array_key_exists($sR, $aClasses['Request'])) {
                    if (array_key_exists('class', $aClasses['Request'][$sR]))
                        $sClass.=$aClasses['Request'][$sR]['class'] . ' ';
                }
                // if ($aCfgTabledata['status'][$sM]) {
                if (strpos($sR, "OPTIONS ") === 0) {
                    $sClass.="typeoptions ";
                }
                if (strpos($sR, "HEAD ") === 0) {
                    $sClass.="typehead ";
                }
                if (strpos($sR, "GET ") === 0) {
                    $sClass.="typeget ";
                }
                if (strpos($sR, "POST ") === 0) {
                    $sClass.="typepost ";
                }
                if (strpos($sR, "PROPFIND ") === 0) {
                    $sClass.="typepropfind ";
                }
            }
        }

        $aRows['Comment'] = $sComment;


        $sHtml.= "<tr class=\"$sClass\">";


        // loop over columns and set table cell values for the single row
        foreach (array_keys($aHeader) as $key) {
            if (in_array($key, $aCfg["hideRows"])) {
                continue;
            }
            // $s is value of the table cell
            $s = $aRows[$key];

            if (!$bSpeedUp) {
                // make a value clickable to filter the table by it
                if ($sTableID) {
                    foreach ($aClick2Filter as $sFilterkey) {
                        if ($key == $sFilterkey) {
                            $s = '<a 
                                href="#" 
                                title="' . $aLangTxt['lblHintFilter'] . ' ' . $key . ' &laquo;' . $s . '&raquo;"
                                onclick="$(\'#' . $sTableID . '_filter>INPUT\').val(\'' . $s . '\'); $(\'#' . $sTableID . '\').dataTable().fnFilter(\'' . $s . '\'); return false;"
                                >' . $s . '</a>';
                        }
                    }
                }

                // 
                if (
                        array_key_exists("tdlink", $aCfg) && array_key_exists($key, $aCfg["tdlink"])
                ) {
                    $sLink = '';
                    foreach ($aCfg["tdlink"][$key] as $sAttr => $sVal) {
                        $sLink.=$sAttr . '="' . sprintf($sVal, $aRows[$key]) . '" ';
                    }
                    if ($sLink) {
                        $s = '<a ' . $sLink . '>' . $aRows[$key] . '</a>';
                    }
                }
            }

            $sHtml.='<td class="col-' . $key . '">' . $s . '</td>';

            // v1.07: bar
            // TODO: this feature costs 1/4 of request time
            if ($bHasTdBars) {
                foreach ($aCfg["tdbars"] as $sCol) {
                    if ($key === $sCol ||
                            (array_key_exists($sCol, $aLangTxt) && $key == $aLangTxt[$sCol])
                    ) {
                        if (!$bSpeedUp) {
                            $iMax = false;
                            if (array_key_exists($key, $aMax)) {
                                $iMax = $aMax[$key];
                            }
                            if ($iMax <> 0) {
                                $iValue = $aRows[$key];
                                $iWidth = round($iValue / $iMax * 100);
                                $sHtml.='<td><span style="display: none;">' . str_pad($iWidth, 3, ' ', STR_PAD_LEFT) . '</span>
                                            <div class="barTotal" style="width: 50px; float: right;"
                                                title="' . $key . '=' . $iValue . ' (' . $iWidth . '%)" 
                                                ><div class="barValue"     
                                                    style="width:' . $iWidth . '%;"
                                                    >&nbsp;</div>
                                            </div>
                                        </td>';
                            }
                        } else {
                            $sHtml.='<td></td>';
                        }// speedup
                    }
                }
            }
        }

        $sHtml.= "</tr>";

        return $sHtml;
    }

    /**
     * get array keys for server-status tables for infos and for requests
     * @param array  $aSrvData  server status data
     * @param string $sType     type; one of status|requests
     * @return array
     */
    private function _getTableheads($aSrvData, $sType) {
        $aTmp = array();
        if (!count($aSrvData)) {
            return array();
        }
        foreach ($aSrvData as $sHost => $aData) {
            if (array_key_exists("status", $aData) && count($aData['status'])) {
                foreach ($aData['status'] as $sKey => $aData2) {
                    $aTmp['status'][$sKey] = true;
                }
            }
            if (array_key_exists("requests", $aData) && count($aData['requests'])) {
                foreach ($aData['requests'] as $sKey => $aData2) {
                    foreach (array_keys($aData2) as $key)
                        $aTmp['requests'][$key] = true;
                }
            }
        }
        $aTmp['requests']['Method'] = true;
        $aTmp['requests']['Comment'] = true;

        return array_key_exists($sType, $aTmp) ? array_keys($aTmp[$sType]) : array();
    }

    /**
     * get list of valid filters
     * @return array
     */
    public function getValidFilters($aSrvData) {
        $aTmp = $this->aFilterPresets;

        foreach ($aTmp as $f => $data) {
            if (!array_key_exists("callfunction", $data) && !array_key_exists("aRows", $data)) {
                $aTmp[$f]["aRows"] = $this->_getTableheads($aSrvData, $data["sType"]);
            }
        }
        return $aTmp;
    }

    /**
     * get list of valid keys of tiles
     * @return array
     */
    public function getValidTiles() {
        return $this->aValidTiles;
    }

    /**
     * get css classes
     * you get classnames for request methods, http methods and exectime
     * that will be used for all tables with request data
     * @return array
     */
    public function getCssClasses() {
        return $this->aCssRows;
    }

    /**
     * get available export formats 
     * @return array
     */
    public function getExportFormats() {
        return $this->aExports;
    }

    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------
    // ----------------------------------------------------------------------
    // OUTPUT
    // ----------------------------------------------------------------------

    /**
     * create bookmarklet with the current installation of pimped apachestatus
     * @return string
     */
    public function genBookmarklet($sLabel = 'Pimped Apache Status') {
        $sMyUrl = $_SERVER["REQUEST_SCHEME"]
                . "://"
                . $_SERVER["HTTP_HOST"]
                . ':' . $_SERVER["SERVER_PORT"]
                . $_SERVER["SCRIPT_NAME"];
        $sMyUrl.="?url=";

        $sHref = "javascript:document.location.href='$sMyUrl'+encodeURI(document.location.href);";

        return '<a href="' . $sHref . '">' . $sLabel . '</a>';
    }

    /**
     * get Export links of a table with a given filter
     * @param type $sFiltername
     * @return boolean|string
     */
    public function genExportLinks($sFiltername) {
        if (!$sFiltername)
            return false;

        $sBaseUrl = '?skin=data&amp;filter=' . $sFiltername;
        if (array_key_exists("group", $_GET)) {
            $sBaseUrl.='&amp;group=' . urlencode($_GET["group"]);
        }
        if (array_key_exists("servers", $_GET)) {
            $sBaseUrl.='&amp;servers=' . urlencode($_GET["servers"]);
        }
        if (array_key_exists("url", $_GET)) {
            $sBaseUrl.='&amp;url=' . urlencode($_GET["url"]);
        }
        if (array_key_exists("lang", $_GET)) {
            $sBaseUrl.='&amp;lang=' . urlencode($_GET["lang"]);
        }
        foreach ($this->getExportFormats() as $sFormat => $data) {
            $aExportLinks[$sFormat] = array(
                'url' => $sBaseUrl . '&amp;format=' . $sFormat,
                'label' => $sFormat,
            );
        }
        return $aExportLinks;
    }

    /**
     * create a local Link
     * @param string $sTarget
     * @param string $sLabel (optional)
     * @return string
     */
    public function genLink($sTarget, $sLabel = '') {
        if (!$sLabel) {
            $sLabel = $sTarget;
        }
        return '<a href="#' . md5($sTarget) . '">' . $sLabel . '</a>';
    }
    
    /**
     * generate javascript code to put performance data into localstorage
     * @param array $aSrvStatus
     * @return boolean|string
     */
    public function storeLocally($aSrvStatus){
        $sReturn='';
        $aKeys2Store=array(
            'dummyJust2WriteSomething',
            'requests/sec',
            'size/sec',
            'requests_active',
            'slots_busy',
        );
        
        // in admin area it has no data
        if(!$aSrvStatus || !is_array($aSrvStatus)){
            return false;
        }
        foreach(array_keys($aSrvStatus) as $sMyWebserver){
            if (isset($aSrvStatus[$sMyWebserver]['counter'])){
                foreach($aSrvStatus[$sMyWebserver]['counter'] as $sKey=>$value){
                    if (!array_search($sKey, $aKeys2Store)===false && !is_array($value)){
                        $sReturn.='oCounter=new counterhistory("' . $sMyWebserver . '", "' . $sKey . '"); oCounter.add(' . $value . ');' . "\n";
                    }
                }
            }
        }
        if($sReturn){
            $sReturn='<script>var '.$sReturn.'</script>';
        }
        // die('<pre>'.htmlentities($sReturn));
        return $sReturn;
    }

    /**
     * generate a datalist based input field
     * @param array $aList flat list
     * @return string
     */
    public function renderDatalist($aList) {
        $sReturn = '';
        $sId = "list" . md5(date("U"));
        foreach ($aList as $sValue) {
            $sReturn.='<option value="' . $sValue . '">';
        }
        $sReturn = '<input name="servers" list="' . $sId . '">'
                . '<datalist id="' . $sId . '">' . $sReturn . '</datalist>';
        return $sReturn;
    }

    /**
     * generate a link; use $aEnv["links"]["name"][KEY] as first parameter
     * @param array $aLink
     * @return string
     */
    public function renderA($aLink) {
        $sReturn = '<a ';

        foreach (array("class", "id", "onclick", "target") as $sAttribute) {
            if (array_key_exists($sAttribute, $aLink)) {
                $sReturn.=$sAttribute . '="' . $aLink[$sAttribute] . '" ';
            }
        }
        if (array_key_exists("url", $aLink)) {
            $sReturn.='href="' . $aLink["url"] . '" ';
        }
        $sReturn.='>';

        if (array_key_exists("label", $aLink)) {
            $sReturn.=$aLink["label"];
        }

        $sReturn.='</a>';
        return $sReturn;
    }

    /**
     * generate a dropdown menu; use $aEnv["links"]["name"] as
     * first parameter
     * @param array $aLinks
     * @param boolean $bDrawSelect
     * @return string
     */
    public function renderDropdown($aLinks, $bDrawSelect = true) {
        $sReturn = '';
        foreach ($aLinks as $aLink) {
            $sReturn.='<option ';
            $sCssClass = '';
            foreach (array(/* "class", */ "id") as $sAttribute) {
                if (array_key_exists($sAttribute, $aLink)) {
                    $sReturn.=$sAttribute . '="' . $aLink[$sAttribute] . '" ';
                }
            }
            if (array_key_exists("url", $aLink)) {
                $sReturn.='value="' . $aLink["url"] . '" ';
            }
            if (array_key_exists("active", $aLink) && $aLink["active"]) {
                $sReturn.='selected="selected" ';
                $sCssClass = 'active ';
            }
            if (array_key_exists("class", $aLink)) {
                $sCssClass.=$aLink["class"];
            }
            if ($sCssClass) {
                $sReturn.='class="' . $sCssClass . '" ';
            }

            $sReturn.='>';

            if (array_key_exists("label", $aLink)) {
                $sReturn.=$aLink["label"];
            }

            $sReturn.='</option>';

            if (array_key_exists("subitems", $aLink)) {
                $sReturn.=$this->renderDropdown($aLink["subitems"], false);
            }
        }
        if ($bDrawSelect) {
            $sReturn = '<select class="select" onchange="location.href=this.value;">' . $sReturn . '</select>';
        }
        return $sReturn;
    }

    /**
     * generate a nested menu with li elements; use $aEnv["links"]["name"] as
     * first parameter
     * @param array $aLinks
     * @param boolean $bDrawSelect
     * @return string
     */
    public function renderLI($aLinks) {
        $sReturn = '';
        if (!is_array($aLinks) || !count($aLinks)) {
            return false;
        }

        foreach ($aLinks as $aLink) {
            $sClass="treeview";
            $bActive=array_key_exists("active", $aLink) && $aLink["active"];
            $sClass.= $bActive ? ' active menu-open' : '';
            
            $sReturn.='<li class="'.$sClass.'"';
            $sReturn.='>' . $this->renderA($aLink);

            if (array_key_exists("subitems", $aLink)) {
                $sReturn.='<ul>' . $this->renderLI($aLink["subitems"]) . '</ul>';
            }
            $sReturn.=$bActive ? '<span class="submenu"></span>' : '';
            $sReturn.='</li>';
        }
        return $sReturn;
    }

    private function _genChart($aTable, $sTableId){
        $sReturn='';
        $sJs='';
        $iMaxJsValues=10;
        /*
            Morris.Donut({
              element: 'donut-example',
              data: [
                {label: "Download Sales", value: 12},
                {label: "In-Store Sales", value: 30},
                {label: "Mail-Order Sales", value: 20}
              ]
            });
         
         */
        $iCount=0;
        $iRest=0;
        foreach ($aTable as $row) {
            if(count($row) != 2){
                return false;
            }
            // $sReturn.='- '.print_r($row, 1).'<br>';
            $aVal=array_values($row);
            if (is_int($aVal[0])){
                if($iMaxJsValues>$iCount){
                    $sJs.=$sJs ? ',': '';
                    $sJs.='{label: "'.$aVal[1].'", value: '.$aVal[0].'}';
                } else {
                    $iRest+=$aVal[0];
                }
                $iCount++;
            }
        }
        if($iRest){
            $sJs.=',{label: "(...)", value: '.$iRest.'}';
        }

        $sDivIdBars='divChartBars'.$sTableId;
        $sIdBars='Bars'.$sTableId;
        $sDivIdDonut='divChartDonut'.$sTableId;
        $sIdDonut='Donut'.$sTableId;
        
        $sBarsCallback='function(index, options, content) {'.$sIdDonut.'.select(index); return \'<div>\'+content+\'</div>\'; }';
        $sJsOut='var '.$sIdDonut.' = Morris.Donut({element: \''.$sDivIdDonut.'\', data: ['.$sJs.'], resize: true }); '
            . 'var '.$sIdBars.' = Morris.Bar({element: \''.$sDivIdBars.'\', data: ['.$sJs.'], xkey: \'label\', ykeys: [\'value\'], labels: [\'n\'], hoverCallback: '.$sBarsCallback.' });'
                . '$(\'.morris-hover\').css("display", "none");'
            ;
        $sReturn.=''
                . '<div class="chartsfortable" style="">'
                    . '<div id="'.$sDivIdBars.'" class="divbars"></div>'
                    . '<div id="'.$sDivIdDonut.'" class="divdonut"></div>'
                    . '<script>' . $sJsOut . '</script>'
                . '</div>'
                ;
        
        return $sReturn;
    }


    /**
     * renderTable shows the array from function dataFilter
     * It additionally fills the global variable $sJsOnReady
     * 
     * @global array $aCfg  user configuration
     * @global string $sJsOnReady
     * @staticvar int $iTableCounter to generate uniq id for the table
     * @param array $aTable  data from function dataFilter 
     * @param type $sDatatableOptions additional options for jquery plugin datatable 
     * @return string html code
     */
    function renderRequestTable($aTable, $sDatatableOptions = '') {
        $this->log(__FUNCTION__ . "(aTable, sDatatableOptions) start");
        global $aCfg;
        global $aEnv;
        global $sJsOnReady;
        global $aLangTxt;
        $sHtml = '';
        $sJs = '';
        static $iTableCounter = 0;
        $iTableCounter++;
        $aHeader = array();
        $sTableId = 'tableRendered' . preg_replace('#[^a-z0-9]#', "", $aEnv["active"]["view"]) . $iTableCounter;

        $aHasBar = array();
        if (array_key_exists("tdbars", $aCfg)) {
            foreach ($aCfg["tdbars"] as $sKey) {
                $aHasBar[$sKey] = true;
                if (array_key_exists($sKey, $aLangTxt)) {
                    $aHasBar[$aLangTxt[$sKey]] = true;
                }
            }
        }
        $this->log(__FUNCTION__ . " getMaxima");
        $aMax = $this->_getMaxima($aTable);

        $this->log(__FUNCTION__ . " loop over " . count($aTable) . " table rows");
        foreach ($aTable as $row) {

            $sRow = '';
            $sHead = '';
            foreach ($row as $key => $value) {
                if (in_array($key, $aCfg["hideRows"])) {
                    continue;
                }
                if (!$sHtml) {
                    $sHead.='<th>' . $key . '</th>';
                    if (array_key_exists($key, $aHasBar) && $aMax[$key]) {
                        $sHead.='<th> </th>';
                    }
                    $aHeader[$key] = true;
                }
                $sRow.='<td>' . $value . '</td>';
            }
            if (!$sHtml) {
                $sHtml = '<thead><tr>' . $sHead . '</tr></thead><tbody>';
            }
            $sHtml.=$this->_getReqLine($row, $aHeader, $sTableId, $aMax);
        }


        // take default options for datatable (in user config) and insert 
        // parameter values of this function
        $sOptions = $aCfg['datatableOptions'] ? $aCfg['datatableOptions'] : '{}';

        if ($sDatatableOptions) {
            $this->log(__FUNCTION__ . " add custom DatatableOptions " .print_r(json_decode('{'.$sDatatableOptions.'}', 1),1));
            $sOptions = json_encode(array_merge(
                    json_decode($sOptions, 1),
                    json_decode('{'.$sDatatableOptions.'}', 1)
            ));
        }
        if ($sHtml) {
            $sHtml = ''
                    .'<table id="' . $sTableId . '" class="table table-hover datatable" style="float: left;">'
                    // . '<tbody>' 
                    . utf8_encode($sHtml) 
                    . '</tbody></table>'
                    . $this->_genChart($aTable, $sTableId)
                    . '<div style="clear: both;"><br></div>';
        }
        $sJsResetOnclick = '$(\\\'#' . $sTableId . '_filter>INPUT\\\').val(\\\'\\\'); $(\\\'#' . $sTableId . '\\\').dataTable().fnFilter(\\\'\\\');';
        $sHtmlReset = '<a class="btnclose" href="#" onclick="' . $sJsResetOnclick . '; return false; ">X</a>';
        $sJs = '$("#' . $sTableId . '").dataTable(' . $sOptions . ');';
        $sJs.= '$("#' . $sTableId . '_filter").append(\'' . $sHtmlReset . '\');';

        $sJsOnReady.=$sJs;
        return $sHtml;
    }

    /**
     * create a section with header, hint and tabledata
     * @global array $aSrvStatus    array with server status data
     * @global array $aLangTxt      array with translated texts
     * @global array $aCfg      user configuration
     * @param type $sKey            key for preset 
     * @return string               html code
     */
    public function renderTable($sKey = false) {
        $this->log(__FUNCTION__ . "($sKey) start");
        global $aSrvStatus, $aLangTxt, $aCfg;

        if (!$sKey) {
            return false;
        }
        if (!in_array($sKey, $this->aValidTables)) {
            die("ERROR in  " . __CLASS__ . "->" . __FUNCTION__ . "(): unknown tile " . $sKey . "<br>Valid tables are: " . implode("|", $this->getValidTiles()));
        }
        if (!key_exists($sKey, $this->aFilterPresets)) {
            die("ERROR in  " . __CLASS__ . "->" . __FUNCTION__ . "(): aFilterPresets has no definition for " . $sKey . ".");
        }

        // get title and hint text from language file
        $sTitle = $aLangTxt['lblTable_' . $sKey] ? $aLangTxt['lblTable_' . $sKey] : $sKey;
        $sHint = $aLangTxt['lblTableHint_' . $sKey] ? $aLangTxt['lblTableHint_' . $sKey] : "";


        $sType = $this->aFilterPresets[$sKey]['sType'];
        if ($sType == 'requests' || $sType == 'status') {
            $this->aFilterPresets[$sKey]['aRows'] = $this->_getTableheads($aSrvStatus, $sType);
        }

        require_once 'serverstatus.class.php';
        $oServerStatus = new ServerStatus();
        $this->log(__FUNCTION__ . " start oServerStatus->dataFilter");
        $aTData = $oServerStatus->dataFilter($aSrvStatus, $this->aFilterPresets[$sKey]);
        $this->log(__FUNCTION__ . " done oServerStatus->dataFilter");
        if (!count($aTData)) {
            // $oLog->add('Table <em>'.$sKey.'</em> was not rendered. No data.', 'error');
            return false;
        }

        $sTableOptions = '';
        $sOptPaging = '"bPaginate": true, "bLengthChange": true, "aLengthMenu": [[10, 25, 50, 100, -1],[10, 25, 50, 100, "---"]]';
        // $sContent=count($aTData);        
        switch ($sKey) {
            case 'requests_all': break;
            case 'requests_running': $sTableOptions = $sOptPaging;
                break;
            case 'requests_mostrequested':
                $sTableOptions = '"aaSorting": [[0,"desc"]], ' . $sOptPaging;
                break;
            case 'requests_hostlist':
                $sTableOptions = '"aaSorting": [[0,"desc"]], ' . $sOptPaging;
                break;
            case 'requests_methods':
                $sTableOptions = '"aaSorting": [[0,"desc"]], ' . $sOptPaging;
                break;
            case 'requests_clients':
                $sTableOptions = '"aaSorting": [[0,"desc"]], ' . $sOptPaging;
                break;
            case 'requests_longest':
                $iReq = $this->_getNumberOfArraykey("Req", $aTData[0]);
                if (!$iReq)
                    $iReq = 0;
                $sTableOptions = '"aaSorting": [[' . $iReq . ',"desc"]],"bStateSave": false';
                break;
            default:
                break;
        }

        /*
          $sExport = '<ul><li>'
          . $aUserCfg['icons']['export']
          . $aLangTxt["lblExportLinks"] . '</li>' . $this->renderLI($this->genExportLinks($sKey)) . '</ul>';
         */
        // bootstrap
        $sExport = $this->renderLI($this->genExportLinks($sKey));
        $this->log(__FUNCTION__ . " start theming table");

        return $this->themeTable($sTitle, $this->renderRequestTable($aTData, $sTableOptions), $sHint, $sExport);
    }

    /**
     * generate a menu with li elements - surrounded with ul class"tabs"
     * to get a tabbed list; use $aEnv["links"]["name"] as first parameter
     * @param array $aTabbedData  array with items with keys tab and content
     * @return string
     */
    public function renderTabbedContent($aTabbedData) {
        static $iTabid;
        if($iTabid){
            $iTabid=0;
        }
        $sTabs='';
        $sContent='';
        foreach($aTabbedData as $aTabdata){
            $iTabid++;
            $sTabid='tab_'.$iTabid;
            
            $sTabs.='<li class="'.($sTabs ? '' : 'active').'"><a href="#'.$sTabid.'" data-toggle="tab" aria-expanded="false">'.$aTabdata['tab'].'</a></li>';
            $sContent.='<div class="tab-pane'.($sContent ? '' : ' active').'" id="'.$sTabid.'">'.$aTabdata['content'].'</div>';
        }
        return '<div class="nav-tabs-custom">
            <ul class="nav nav-tabs">'.$sTabs.'</ul>
            <div class="tab-content">'.$sContent.'</div>
         </div>';
    }
    /**
     * generate a menu with li elements - surrounded with ul class"tabs"
     * to get a tabbed list; use $aEnv["links"]["name"] as first parameter
     * @param array $aLinks
     * @return string
     */
    public function renderTabs($aLinks) {
        return '<div role="tabpanel"><ul class="nav nav-tabs" role="tablist">' . $this->renderLI($aLinks) . '</ul></div>';
    }

    /**
     * render a tile
     * @global array $aSrvStatus             array with server status data
     * @global array $aLangTxt       array with translated texts
     * @param string $sTilename       key for preset
     * @return string               html code
     */
    public function renderTile($sTilename = false) {
        $this->log(__FUNCTION__ . "($sTilename) - start");
        global $aSrvStatus, $aSrvMeta, $aLangTxt, $aCfg;
        
        if (!$sTilename) {
            return false;
        }
        if (!in_array($sTilename, $this->aValidTiles)) {
            die("ERROR in  " . __CLASS__ . "->" . __FUNCTION__ . "(): unknown tile " . $sTilename . "<br>Valid tiles are: " . implode("|", $this->getValidTiles()));
        }
        if (!key_exists($sTilename, $this->aFilterPresets)) {
            die("ERROR in  " . __CLASS__ . "->" . __FUNCTION__ . "(): aFilterPresets has no definition for " . $sTilename . ".");
        }

        $sUnit = '';
        $sReq = false;

        $sTitle = $aLangTxt['lblTile_' . $sTilename] ? $aLangTxt['lblTile_' . $sTilename] : $sTilename;
        $sHint = $aLangTxt['lblTileHint_' . $sTilename] ? $aLangTxt['lblTileHint_' . $sTilename] : $sTilename;
        $sIcon = isset($aCfg['icons'][$sTilename]) ? $aCfg['icons'][$sTilename] : '';
        $sContent = '';

        $sType = $this->aFilterPresets[$sTilename]['sType'];
        $this->aFilterPresets[$sTilename]['aRows'] = $this->_getTableheads($aSrvStatus, $sType);

        if ($this->aFilterPresets[$sTilename]["sType"] == "meta") {
            $aTData = $aSrvMeta;
        } else {
            require_once 'serverstatus.class.php';
            $oServerStatus = new ServerStatus();
            $this->log(__FUNCTION__ . "($sTilename) - start datafilter");
            $aTData = $oServerStatus->dataFilter($aSrvStatus, $this->aFilterPresets[$sTilename]);
            $this->log(__FUNCTION__ . "($sTilename) - end datafilter");
        }
        $iCount = count($aTData);
        if ($iCount) {
            switch ($sTilename) {
                // case 'requests_all': break;
                // case 'requests_running': break;
                case 'requests_mostrequested':
                    if ($aTData[0] && is_array($aTData[0])) {
                        $iCount = $aTData[0][$aLangTxt['thCount']];
                        if($iCount>1){
                            if (array_key_exists($aLangTxt['thCount'], $aTData[0])) {
                                $sReq = $aTData[0]['Request'];
                            }
                        } else {
                            $iCount='';
                            $sReq = '?';
                        }
                    }
                    break;
                case 'requests_clients':
                    if ($aTData[0] && is_array($aTData[0])) {
                        if (array_key_exists($aLangTxt['thCount'], $aTData[0])) {
                            $sReq = $aTData[0]['Client'];
                        }
                        if (!$sReq) {
                            $sReq = '?';
                        }
                        $iCount = $aTData[0][$aLangTxt['thCount']];
                    }
                    break;
                case 'requests_longest':
                    if ($aTData[0] && is_array($aTData[0])) {
                        if (round($aTData[0]['Req'] / 1000) > 0) {
                            $iCount = $aTData[0]['Req'] / 1000;
                            $sUnit = 's';
                            $sReq = $aTData[0]['Request'];
                            if (!$sReq) {
                                $sReq = '?';
                            }
                        } else {
                            $iCount = '-';
                        }
                    }
                    break;
                case 'server_count':
                    $iCount = $aTData["servers"];
                    break;
                case 'server_responsetime':
                    $iCount = sprintf('%01.3f', $aTData["responsetime"]);
                    $sUnit = 's';
                    break;

                default:
                    break;
            }
        }
        if ($iCount) {
            // since v1.22: store counter values in localstorage
            $sContent = '';
            $sSrvIndex = '';
            $sJsCounter = '';
            $sKnob ='';
            if ($sTilename != 'server_count') {
                $sSrvIndex = md5(implode(",", array_keys($aSrvStatus)));
                $sJsCounter = ''
                        . '<script>'
                        . 'var oCounter=new counterhistory("' . $sSrvIndex . '", "' . $sTilename . '"); '
                        . 'oCounter.add("' . $iCount . '");'
                        . '</script>'
                        . '';
                $sKnob = '<input type="hidden" class="dial" id="dial-'.$sSrvIndex.'-'.$sTilename.'" value="'.$iCount.'">';
            }
            $sContent .= '<span class="counter">'
                        . $iCount . $sUnit 
                    . '</span><br>'
                    . $sReq . '';
            return 
                    ''
                    /*
                    .'<div '
                    . 'title="' . $sHint . '" '
                    . 'class="tile ' . $sTilename . '" '
                    . 'onmouseover="showGraph(\'' . $sSrvIndex . '\', \'' . $sTilename . '\', \'' . $sTitle . '\');" '
                    . 'onmouseout="hideGraph();" '
                    . 'onclick="stickyGraph(\'' . $sSrvIndex . '\', \'' . $sTilename . '\', \'' . $sTitle . '\');" '
                    . '>'
                    . $sKnob
                    . '<span class="title">' . $sTitle . '</span>:<br><span class="content">' . $sContent . '</span>'
                    . '</div>'
                     */
                    .'<div class="col-md-2">
                        <!-- Apply any bg-* class to to the info-box to color it -->
                    <div class="info-box '.$aCfg['skin-color2'].' tile ' . $sTilename . '"'
                        . 'title="' . $sHint . '" '
                        . 'onmouseover="showGraph(\'' . $sSrvIndex . '\', \'' . $sTilename . '\', \'' . $sTitle . '\');" '
                        . 'onmouseout="hideGraph();" '
                        . 'onclick="stickyGraph(\'' . $sSrvIndex . '\', \'' . $sTilename . '\', \'' . $sTitle . '\');" '
                    .'>
                      <span class="info-box-icon">'
                        .$sIcon
                        . '<span style="float: right; position: relative; top: -85px; right: -17px;">'.$sKnob.'</span>'
                      .'</span>
                      <div class="info-box-content">
                        <span class="info-box-text">'
                        . $sTitle
                      .'</span>
                        <span class="info-box-number">'.$iCount.$sUnit.'</span>
                        <!-- The progress section is optional -->
                        <div class="progress"><div class="progress-bar" id="progress-'.$sSrvIndex.'-'.$sTilename.'" style="width: 0;"></div></div>
                        <!-- -->
                        <span class="progress-description">
                          '.$sReq.'
                        </span>
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                    </div>'                    
                    . $sJsCounter
                    ;
        }
        return false;
    }

    private function _getTypes($aSrvStatus) {
        global $aLangTxt;
        $aReturn = array();
    }

    /**
     * get Metadata for workers (total, active, wait) as an array
     * @global array $aLangTxt     language dependent texts
     * @param array  $aSrvStatus   server status array
     * @param bool   $bGenerateBar flag: get html code for a bar for active workers
     * @return string
     */
    public function _getWorkersData($aSrvStatus, $bGenerateBar = false) {
        global $aLangTxt, $aEnv;
        $aReturn = array();
        $iMaxWith = 600;

        $aLinkPCheck = (array_key_exists('performance-check.php', $aEnv['links']['views']) && count($aSrvStatus) > 1
                ) ? $aEnv['links']['views']['performance-check.php'] : false
        ;

        /*
        Array
        (
            [Parent Server Config. Generation] => 1
            [Parent Server MPM Generation] => 0
            [Total accesses] => 3884
            [Total Traffic] => 8283750.4
            [requests/sec] => 0.274
            [size/sec] => 583
            [slots_total] => 150
            [slots_busy] => 7
            [slots_free] => 143
            [requests_active] => 2
            [requests_waiting] => 5
        )
         */
        foreach ($aSrvStatus as $sHost => $aData) {
            
            $iProcesses = $aData['counter']['slots_total'];
            $iSlotsUnused = $aData['counter']['slots_unused'];
            $iActive = $aData['counter']['requests_active'];
            $iWait = $aData['counter']['requests_waiting'];

            $aTmp = array(
                $aLangTxt['thWorkerServer'] => $sHost,
                $aLangTxt['thWorkerTotal'] => $iProcesses,
                $aLangTxt['thWorkerActive'] => $iActive,
                $aLangTxt['thWorkerWait'] => $iWait,
                $aLangTxt['thWorkerUnused'] => $iSlotsUnused,
            );

            if ($bGenerateBar) {
                $iBarFactor = 1;

                if ($iProcesses > $iMaxWith) {
                    $iBarFactor = $iMaxWith / $iProcesses;
                }
                $sHeight='height: 1.6em;';
                $sBar = "<div class=\"barTotal\" style=\"width: " . ($iProcesses * $iBarFactor) . "px; $sHeight\" title=\"" . sprintf($aLangTxt['bartitleUnusedWorkers'], ($iProcesses - $iActive - $iWait)) . "\">
                            <div class=\"barBusyWorker\" style=\"width: " . ($iActive * $iBarFactor) . "px; $sHeight\" title=\"" . sprintf($aLangTxt['bartitleBusyWorkers'], $iActive) . "\"> </div>
                            <div class=\"barIdleWorker\" style=\"width: " . ($iWait * $iBarFactor) . "px; $sHeight\" title=\"" . sprintf($aLangTxt['bartitleIdleWorkers'], $iWait) . "\"> </div>
                        </div>";
                $aTmp[$aLangTxt['thWorkerBar']] = $sBar;
            }

            // add link for performance check of a single server
            if ($aLinkPCheck) {
                $aTmp[$aLangTxt['thWorkerActions']] = '<a href="' . $aLinkPCheck['url'] . '&servers=' . $sHost . '"'
                        . 'title="' . strip_tags($aLinkPCheck['label']) . ' - ' . $sHost . '"'
                        . '>'
                        . str_replace(strip_tags($aLinkPCheck['label']), '', $aLinkPCheck['label']) . ' ' . $sHost
                        . '</a>';
            }
            $aReturn[] = $aTmp;
        }
        return $aReturn;
    }

    /**
     * try to flip table: wide tables with a single line will be flipped
     * @param array   $aTData
     * @return array
     */
    private function _tryFlip($aTData){
        if(count($aTData)==1){
            $aTmp=array();
            $iCount=0;
            foreach($aTData[0] as $sKey=>$value){
                $iCount++;
                $aTmp[]=array('key'=>'<span style="display: none;">'.$iCount.'</span>'.$sKey, 'value'=>$value);
            }
            // echo '<pre>'; print_r($aTData);print_r($aTmp);die();
            $aTData=$aTmp;
        }
        return $aTData;
    }
    
    /**
     * render table with worker status: total/ active/ waiting workers
     * @global type $aLangTxt   language dependend texts
     * @global array $aCfg  user configuration
     * @param type $aSrvStatus data array of apache status
     * @return string html code
     */
    public function renderWorkersTable($aSrvStatus) {
        global $aLangTxt, $aCfg;

        if (!count($aSrvStatus)) {
            // $oLog->add('Workers table was not rendered. No data.', 'error');
            return false;
        }

        $aTData = $this->_getWorkersData($aSrvStatus, true);
        // $aTData = $this->_tryFlip($this->_getWorkersData($aSrvStatus, true));
        /*
          $sExport = '<ul><li>'
          . $aUserCfg['icons']['export']
          . $aLangTxt["lblExportLinks"] . '</li>' . $this->renderLI($this->genExportLinks("workers_table")) . '</ul>';
         * 
         */
        // bootstrap

        $sExport = $this->renderLI($this->genExportLinks("workers_table"));


        return $this->themeTable($aLangTxt["lblTable_status_workers"], $this->renderRequestTable($aTData, ""), $aLangTxt["lblTableHint_status_workers"], $sExport);
    }
    /**
     * render output box with title, hint and data table
     * @param string $sTitle    title for h3
     * @param string $sContent  html content
     * @param string $sHint     hint text (optional)
     * @return string 
     */
    public function themeBox($sTitle, $sContent, $sHint = false) {

        $sReturn = '<!-- Default box -->
      <div class="box ">
        <div class="box-header with-border">
          <h3 class="box-title">' . $sTitle . '</h3>
          <!--
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
          -->
        </div>
        <div class="box-body">';
        if ($sHint) {
            $sReturn.='<div class="hintbox">' . $sHint . '</div>';
        }
        $sReturn.= $sContent . '
        </div>
        <!-- /.box-body
        <div class="box-footer">
          Footer
        </div>
         -->
        <!-- /.box-footer-->
      </div>
      <!-- /.box -->';
        return $sReturn;
    }

    /**
     * render output section with title, hint and data table
     * @param string $sTitle    title for h3
     * @param string $sTable    html content (the table)
     * @param string $sHint     hint text (optional)
     * @param string $sExport   html content (export Links)
     * @return string 
     */
    public function themeTable($sTitle, $sTable, $sHint = false, $sExport = false) {
        global $aLangTxt;
        global $aCfg;

        $sContent = '';
        if ($sHint) {
            $sContent.='<div class="hintbox">' . $sHint . '</div>';
        }
        if ($sExport) {
            $sDropdownId = 'dropdownMenu' . md5($sExport);
            $sExport = '<div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" id="' . $sDropdownId . '" data-toggle="dropdown" aria-expanded="true">
              ' . $aCfg['icons']['export'] . $aLangTxt["lblExportLinks"] . '
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu" aria-labelledby="' . $sDropdownId . '">'
                    . $sExport . '
            </ul>
        </div>';
        }
        return $this->themeBox($sTitle, $sTable.$sExport,$sHint);
    }

}
