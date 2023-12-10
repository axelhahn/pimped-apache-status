<?php
/*
 * AXEL HAHN's PHP WEB INSTALLER
 * www.axel-hahn.de
 * 
 * U P D A T E  C H E C K
 * 
 * STATUS: alpha - do not use yet
 * 
 * @example
 * $oUpdate=new ahwiupdatecheck(array(
 *         'product'=>$this->aAbout['product'],
 *         'version'=>$this->aAbout['version'],
 *         'baseurl'=>'https://www.example.com/versions/',
 *         'tmpdir'=>__DIR__.'/../../tmp/',
 *         'ttl'=>86400,     // 1 day
 * ));
 * print_r($oUpdate->getUpdateInfos());
 * 
 * source: https://github.com/axelhahn/ahwebinstall
 * 
 * @author Axel Hahn
 */
class ahwiupdatecheck {

    
    protected $_sCheckUrl=false;
    protected $_aCfg=array(
        'product'=>false,
        'version'=>false,
        'baseurl'=>false,
        'url'=>false,
        'tmpdir'=>false,
        'ttl'=>0,     // 1 day
    );
    protected $_aInfosDefault=array(
            'flag_update'=>false,
            'message'=>false,
            'error'=>false,
            'clientversion'=>'unknown',
            'latest_version'=>'unknown',
            'release'=>'unknown',
            'download'=>false,
            'md5'=>false,
    );
    protected $_aInfos=array();
    
    // ----------------------------------------------------------------------
    // constructor
    // ----------------------------------------------------------------------

    public function __construct($aCfg) {
        $this->_setCfg($aCfg);
        return true;
    }

    // ----------------------------------------------------------------------
    // protected
    // ----------------------------------------------------------------------
    
    
    /**
     * search temp directory using tmpdir in config. if false then use system
     * temp dir
     * @param bool  $bForce  force check and ignore ttl
     * @return type
     */
    protected function _getTempdir(){
        $sTmpDir=(isset($this->_aCfg['tmpdir']) && $this->_aCfg['tmpdir']) ? $this->_aCfg['tmpdir'] : sys_get_temp_dir();

        if($sTmpDir && !($sTmpDir[0]==='/' || $sTmpDir[1]===':')){
            $sTmpDir=__DIR__ . '/' . $sTmpDir;
        }
        if(!is_writable($sTmpDir)){
            die('ERROR: directory is not writable: ['.$sTmpDir.'] - check write access or set a new value for "tmpdir"<br>');
        }
        return $sTmpDir;
    }
    
    protected function _getCacheFilename(){
        return $this->_getTempdir() . '/checkupdate_' 
                . ($this->_aCfg['product'] ? strtolower($this->_aCfg['product']) : md5($this->_aCfg['url'])) 
                . '.tmp';
    }

    /**
     * make an http(s) get request and return the response body
     * @param string   $url          url to fetch
     * @param boolean  $bHeaderOnly  send header only
     * @return string
     */
    private function _httpGet($url, $bHeaderOnly = false) {
        $ch = curl_init($url);
        if ($bHeaderOnly) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'php-curl :: web installer');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $res = curl_exec($ch);
        curl_close($ch);
        return ($res);
    }
    
    /**
     * override current values of $this->_aCfg
     * @param array $aCfg  new cfg values
     * @return boolean
     */
    protected function _setCfg($aCfg){
        // echo __METHOD__. ' new data: <pre>'.print_r($aCfg, 1).'</pre>';
        if(is_array($aCfg)){
            foreach(array_keys($this->_aCfg) as $sKey){
                $this->_aCfg[$sKey]=isset($aCfg[$sKey]) ? $aCfg[$sKey] : $this->_aCfg[$sKey];
            }
            if($this->_aCfg['product'] && $this->_aCfg['version'] && $this->_aCfg['baseurl']){
                $this->_aCfg['url']=$this->_aCfg['baseurl'].strtolower($this->_aCfg['product']).'_'.$this->_aCfg['version'].'.json';
            }
        }
        $this->getUpdateInfos();
        // echo __METHOD__. '<pre>'.print_r($this->_aCfg, 1).'</pre>';
        return true;
    }

    // ----------------------------------------------------------------------
    // public ... fetch update infos
    // ----------------------------------------------------------------------
    
    

    /**
     * get array with update infos 
     * Array
     * (
     *     [flag_update] => 
     *     [message] => OK: this is the latest version.
     *     [clientversion] => 2.00.03
     *     [release] => stable
     *     [latest_version] => 2.00.03
     *     [download] => https://sourceforge.net/projects/pimpapachestat/files/latest/download
     *     [md5] => https://sourceforge.net/projects/pimpapachestat/files/versionNNN.md5/download
     * )
     * 
     * @global type $aEnv
     * @global array $aCfg
     * @return array
     */
    function getUpdateInfos($bForce = false){
        
        $sUrlCheck=$this->_aCfg['url'];
        if(!$sUrlCheck){
            echo __METHOD__." ABORT<br>";
            return false;
        }
        // global $oLog;
        
        $sCachefile = $this->_getCacheFilename();
        $bExec = true;
        $iTtl = (int) $this->_aCfg['ttl'];
        $iAge = false;

        $aDefault=$this->_aInfosDefault;

        // 
        if ($bForce) {
            $bExec = true;
            // $oLog->add(__FUNCTION__ . " last exec: override: force parameter was found");
        } else if (file_exists($sCachefile)) {
            $bExec = false;
            $iAge = time() - filemtime($sCachefile);
            if ($iAge > $iTtl) {
                $bExec = true;
            }
            // $oLog->add(__FUNCTION__ . " last exec: " . $iAge . " s ago - timer is $iTtl");
        } else {
            // $oLog->add(__FUNCTION__ . " last exec: never (touchfile was not found)");
        }

        if ($bExec) {
            // $oLog->add(__FUNCTION__ . " fetching $sUrlCheck ...");
            $sResponse = $this->_httpGet($sUrlCheck);
            // echo "DEBUG: $sResult<br>";
            if (!$sResponse) {
                $aUpdateInfos['error']= 'request for fetching update infos failed.';
                // $this->_aInfos=array_merge($aDefault, $aUpdateInfos);
                // $oLog->add(__FUNCTION__ . " unable to check version.");
            } else {
                // $oLog->add(__FUNCTION__ . " <pre>$sResult</pre>");
                $aUpdateInfos= json_decode($sResponse, 1);
                if(!is_array($aUpdateInfos)){
                    $aUpdateInfos=array('error'=>$sResponse);
                    // $this->_aInfos=array_merge($aDefault, $aUpdateInfos);
                } else {
                    
                }
            }
            $this->_aInfos=array_merge($aDefault, $aUpdateInfos);
            // echo "DEBUG write cache $sCachefile <pre>".print_r($this->_aInfos, 1)."</pre>";
            if (!file_put_contents($sCachefile, json_encode($this->_aInfos,JSON_PRETTY_PRINT))){
                // $oLog->add(__FUNCTION__ . " unable to write file [$sTarget]", "error");
            }
            $this->_aInfos['_source']='live';
            $this->_aInfos['_age']=$iAge;
        } else {
            // $oLog->add(__FUNCTION__ . " reading cache $sTarget ...");
            $this->_aInfos = json_decode(file_get_contents($sCachefile), 1);
            $this->_aInfos['_source']='cache';
            $this->_aInfos['_age']=$iAge;
        }
        // $oLog->add(__FUNCTION__ . " <pre>".print_r($aUpdateInfos, 1)."</pre>");
        // echo " <pre>".print_r($this->_aInfos, 1)."</pre>";
        return $this->_aInfos;
    }

    // ----------------------------------------------------------------------
    // public ... ask for update infos
    // ----------------------------------------------------------------------
    
    /**
     * get info if an update is available as boolean
     * @return boolean
     */
    public function hasUpdate(){
        return $this->_aInfos['flag_update'];
    }
    /**
     * get version of installed software
     * @return boolean
     */
    public function getClientVersion(){
        return $this->_aInfos['clientversion'];
    }
    /**
     * get download url for latest software version
     * @return boolean
     */
    public function getDownloadUrl(){
        return $this->_aInfos['download'];
    }
    /**
     * get download url for latest software version
     * @return boolean
     */
    public function getChecksumUrl(){
        return $this->_aInfos['md5'];
    }
    /**
     * get version of latest software version
     * @return boolean
     */
    public function getLatestVersion(){
        return $this->_aInfos['latest_version'];
    }
}
