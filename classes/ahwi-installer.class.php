<?php

/*
 * AXEL HAHN's PHP WEB INSTALLER
 * www.axel-hahn.de
 * 
 * I N S T A L L E R
 * 
 * STATUS: alpha - do not use yet
 * 
 * @author Axel Hahn
 */

/**
 * class ahwi
 * make download and install ...
 */
class ahwi {

    // ----------------------------------------------------------------------
    // INTERNAL CONFIG
    // ----------------------------------------------------------------------
    var $aCfg = array();
    var $iTimeStart = false;
    var $sAbout = "PHP WEB INSTALLER";
    var $aErrors = array();

    // ----------------------------------------------------------------------
    // METHODS
    // ----------------------------------------------------------------------
    public function __construct($aCfg) {
        if (!function_exists("curl_init")) {
            die("ERROR: curl module is required for this installer. Please install php-curl first.");
        }
        $this->iTimeStart = microtime(true);
        $this->_setConfig($aCfg);
        return true;
    }

    // ----------------------------------------------------------------------
    // private functions
    // ----------------------------------------------------------------------
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
     * set config array and verify required keys
     * @param array $aCfg  new project data; keys are:
     *      product     - required: name of the product to install (string)
     *      source      - required: download url (string)
     *      installdir  - required: local path where to unzip (string)
     *      tmpzip      - optional: name of local download file as filenamewith full path (string)
     * @return array
     */
    private function _setConfig($aCfg = array()) {
        // verify array
        $sErrors = '';
        foreach (array('product', 'source', 'installdir') as $sKey) {
            if (!array_key_exists($sKey, $aCfg)) {
                $sErrors.="ERROR: missing key $sKey ...\n";
            }
        }
        if ($sErrors) {
            echo $sErrors;
            die();
        }
        $this->aCfg = $aCfg;
        return $this->aCfg;
    }
    
    /**
     * get the filename of the download targezt / local zip with full path
     * It returns option "tmpzip" if it exists or a generated filename in 
     * system temp directory
     * @return string
     */
    private function _getZipfilename() {
        // if it was defined in config then return it
        if (isset($this->aCfg['tmpzip'])){
            return $this->aCfg['tmpzip'];
        }
        // ... otherwise generate somethin in system temp
        $sZipfile=(getenv('temp') ? getenv('temp') : '/tmp')
                .'/'
                .str_replace(" ", "_", $this->aCfg['product'])
                .'__'
                .md5($this->aCfg['source'])
                .'.zip'
                ;
        return $sZipfile;
    }
    
    /**
     * helper function after exracting zip file: skip a single subdir
     * (like zips in github)
     * 
     * @param type $sSubdir
     * @param type $aEntries
     */
    protected function _moveIfSingleSubdir($sSubdir, $aEntries) {
        $sTargetPath = $this->aCfg['installdir'];
        $sFirstDir = $sTargetPath . '/' . $sSubdir;

        // rsort($aEntries);
        $aErrors=array();
        echo "INFO: Copying entries from $sFirstDir to $sTargetPath.\n";
        foreach ($aEntries as $sEntry) {
            $sFrom = $sTargetPath . '/' . $sEntry;
            $sTo = str_replace($sTargetPath . '/'.$sSubdir.'/', $sTargetPath . '/', $sFrom);
            echo "... ";
            if (is_dir($sFrom)){
                echo "INFO: directory $sFrom";
                if (is_dir($sTo)){
                    echo " already exists.";
                } else {
                    if (mkdir($sTo, 0750, true)){
                        echo " $sTo was created.";
                    } else {
                        echo " FAILED to create $sTo.";
                        $aErrors[]="failed to create directory $sTo";
                    }
                }
            } else {
                echo (file_exists($sTo) ? 'UPDATE ' : 'CREATE ');
                if (copy($sFrom, $sTo)){
                    echo " $sTo was OK.";
                } else {
                    echo " FAILED to copy to $sTo.";
                    $aErrors[]="failed copy $sFrom to $sTo";
                }
            }
            echo "\n";
        }

        if (count($aErrors)){
            echo "ERRORS occured ... keeping subdir $sSubdir with all latest files.\n";
        } else {
            echo "INFO: Copy was successful. Now cleaning up dir $sSubdir ...\n";
            rsort($aEntries);
            foreach ($aEntries as $sEntry) {
                $sFrom = $sTargetPath . '/' . $sEntry;
                if (is_dir($sFrom)){
                    if (rmdir($sFrom)){
                        echo "... DELETED DIR $sFrom\n";
                    } else {
                        echo "... ERROR: DIR NOT DELETED $sFrom\n";
                        $aErrors[]="failed delete dir $sFrom";
                    }
                } else {
                    if (unlink($sFrom)){
                        echo "... DELETED $sFrom\n";
                    } else {
                        echo "... ERROR: NOT DELETED $sFrom\n";
                        $aErrors[]="failed delete $sFrom";
                    }
                }
            }
        }
        if (count($aErrors)){
            echo "ERRORS occured while deleting ... some entries in subdir $sSubdir still exist.\n";
        } else {
            echo "OK, cleanup was successful\n";
        }
    }
    // ----------------------------------------------------------------------
    // GETTER
    // read the options after initializing 
    // ----------------------------------------------------------------------
    
    /**
     * get name of the local installation directory as full path
     * @return string
     */
    public function getInstalldir(){
        return $this->aCfg['installdir'];
    }
    
    /**
     * get name of product to install
     * @return string
     */
    public function getProduct(){
        return $this->aCfg['product'];
    }
    
    /**
     * get url of new sources
     * @return string
     */
    public function getSource() {
        return $this->aCfg['source'];
    }
    
    /**
     * get local filename for downloaded zip file
     * @return string
     */
    public function getTmpzip() {
        return $this->_getZipfilename();
    }
    
    // ----------------------------------------------------------------------
    // public install functions
    // ----------------------------------------------------------------------
    
    /**
     * 
     * @return boolean
     */
    private function _checkDenyroot(){
        // on windows the function does not exst
        if (function_exists("posix_getpwuid")
            && isset($this->aCfg['source']['denyroot']) 
            && $this->aCfg['source']['denyroot'])
        {
            $processUser = posix_getpwuid(posix_geteuid());
            if ($processUser['name']=="root"){
                $this->aErrors[]="Do not start the installer as user root";
            }
        }
        return true;
    }
    
    /**
    * check if a php module was found
    * @return boolean
    */
    function _checkModules($aRequiredMods=array()){
        if (isset($this->aCfg['checks']['phpextensions']) 
            && is_array($this->aCfg['checks']['phpextensions'])
            && count($this->aCfg['checks']['phpextensions'])){
            
            $aAllMods=get_loaded_extensions(false);
            asort($aAllMods);
            
            foreach($this->aCfg['checks']['phpextensions'] as $sMod){
                // echo $sMod.' - ';
                if(!array_search($sMod, $aAllMods)===false){
                    // echo  '<span class="ok">OK</span> installed';
                } else {
                    // echo '<span class="error">does not exist</span>';
                    $this->aErrors[]="php module $sMod was not found";
                }
            }
        }

        return true;  
    }
    /**
    * check php version 
    * @return boolean
    */
    function _checkPhpversion($aRequiredMods=array()){
        if (isset($this->aCfg['checks']['phpversion']) 
            && $this->aCfg['checks']['phpversion']
            && version_compare(phpversion(), $this->aCfg['checks']['phpversion'],'<')){
            
            $this->aErrors[]="Your PHP version is ".phpversion()."; required: ".$this->aCfg['checks']['phpversion'];
        }
    }
            
    /*
        example for checks:
        "checks": {
            "denyroot": true,
            "phpversion": "5.3",
            "phpextensions": [ "curl" ]
        },
    */
    
    /**
     * check requirements for setup
     * @return bool
     */
    function checkRequirements() {
        $this->aErrors=array();
        if(!$this->aCfg['source']){
            return true;
        }
        if (isset($this->aCfg['checks']['phpversion']) 
            && $this->aCfg['checks']['phpversion']
            && version_compare(phpversion(), $this->aCfg['checks']['phpversion'],'<')){
            $this->aErrors[]="Your PHP version is ".phpversion()."; required: ".$this->aCfg['checks']['phpversion'];
        }
        $this->_checkModules();
        $this->_checkDenyroot();

        if(count($this->aErrors)){
            echo "Check for requirements failed.\n";
            echo implode("\n*", $this->aErrors);
            die();
        }
        return true;
    }

    /**
     * download latest package of the product
     * @param boolean  $bForce  if download file exists downoad again? Default is true
     * @return bool
     */
    function download($bForce=true) {
        $sUrl = $this->aCfg['source'];
        $sZipfile = $this->_getZipfilename();

        if (file_exists($sZipfile)){
            if (!$bForce) {
                echo "WARNING: file $sZipfile was downloaded already. Skipping download.\n";
                return true;
            } else {
                unlink($sZipfile);
            }
        }
        if(!is_writable(dirname($sZipfile))){
            die("FATAL ERROR: download won\'t be started. Direcory is not writable: ".dirname($sZipfile).".\n");
        }

        echo "INFO: fetching url $sUrl ...\n";
        $sData = $this->_httpGet($sUrl);
        echo 'INFO: size is '.strlen($sData) . " byte\n";
        
        if (strlen($sData) < 1000) {
            die("FATAL ERROR: download failed. The download file seems to be too small.\n");
        }
        
        if (!file_put_contents($sZipfile, $sData)){
            die("ERROR: unable to store download file $sZipfile.\n");
        } else {
            echo "INFO: download was saved as: $sZipfile\n";
        }

        return true;
    }

    /**
     * install/ unzip method; extract downloaded zip file into target directory
     * if the installation was successful the zip file will be deleted
     */
    function install() {
        // $sZipfile = $this->aCfg['tmpzip'];
        $sZipfile = $this->_getZipfilename();
        if(!file_exists($sZipfile)){
            die("FATAL ERROR: install won\'t be started. Zip file does not exist: $sZipfile.\n");
        }
        
        $sTargetPath = $this->aCfg['installdir'];
        if (is_dir($sTargetPath)) {
            echo "INFO: target directory already exists. Making an update.\n";
        }
        if(!is_writable($sTargetPath)){
            die("FATAL ERROR: install won\'t be started. Direcory is not writable: $sTargetPath.\n");
        }

        $zip = new ZipArchive;
        echo "INFO: extracting $sZipfile...\n";
        echo "INFO: to $sTargetPath...\n";
        
        $res = $zip->open($sZipfile);
        if ($res === TRUE) {
            $zip->extractTo($sTargetPath);
            $aDirs=array();
            $aEntries=array();
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $sFirstDir=preg_replace('#[/\\\].*#', '', dirname($zip->getNameIndex($i).'x'));
                $aDirs[$sFirstDir]=1;
                $aEntries[]=$zip->getNameIndex($i);
                // echo '... ' . $zip->getNameIndex($i) . " - $sFirstDir\n";
                echo '... ' . $zip->getNameIndex($i) . "\n";
            }
            echo $zip->getStatusString() . "\n";
            echo $zip->numFiles . " entries are in the zip file.\n";
            $zip->close();
            echo "SUCCESS: files were extracted to directory \"$sTargetPath\".\n";

            // print_r(array_keys($aDirs));
            if(count(array_keys($aDirs))===1){
                $this->_moveIfSingleSubdir($sFirstDir, $aEntries);
            }
            
            // if you come here it was successful ... delete the zip file
            // unlink($sZipfile);
            
        } else {
            die("ERROR: unable to open ZIP file $sZipfile\n");
        }
        if (array_key_exists('postmessage', $this->aCfg)) {
            echo $this->aCfg['postmessage'] . "\n";
        }
        return true;
    }


    /**
     * postinstall ... unused so far
     * @return boolean
     */
    function postinstall() {
        return true;
    }


    /**
     * show a user friendly welcome message ... for cli usage
     */
    function welcome() {
        echo "
===== " . $this->sAbout . " [" . $this->aCfg['product'] . "] =====

What happens next:

--- Download of the files from
    " . $this->aCfg['source'] . "
    to" . $this->_getZipfilename() . "

--- " . $this->aCfg['product'] . " will be installed in directory
    " . $this->aCfg['installdir'] . "
    Current directory is 
    " . getcwd() . "

";
        return true;
    }

}
