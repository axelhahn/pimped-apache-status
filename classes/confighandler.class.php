<?php

namespace axelhahn;

/**
 * confighandler  class
 * handle config data and store as json file
 * 
 * @package confghandler
 * @version 0.00
 * @author Axel Hahn (https://www.axel-hahn.de/)
 * @license GNU GPL v 3.0
 * @link https://github.com/axelhahn/TODO
 * 
 */
class confighandler {
    
    /**
     * all items in the loaded config
     * @var array
     */
    protected $_aCfg=array();
    
    /**
     * current config id
     * @var string
     */
    protected $_sCfgId=false;
    
    /**
     * flag: autosave is enabled true/ false
     * @var boolean
     */
    protected $_bAutosave=true;
    
    /**
     * divider for array subkeys in string parameters
     * @var type 
     */
    protected $_sDivider='.';
    
    // ----------------------------------------------------------------------
    // CONSTRUCTOR
    // ----------------------------------------------------------------------
    
    /**
     * init method
     * @param string  $sId  config id (without ".json" extension
     */
    public function __construct($sId=false) {
        $this->configSet($sId);
    }
    
    // ----------------------------------------------------------------------
    // private functions
    // ----------------------------------------------------------------------
    
    /**
     * get config directory as full path .. it is in [class dir]/../config/
     * @return string
     */
    private function _getCfgDir() {
        $sDir=__DIR__.'/../config/';
        if(!file_exists($sDir)){
            die("ERROR: directory does not exist: $sDir");
        }
        return $sDir;
    }
    /**
     * get config file with full path .. it is in [class dir]/../config/
     * @param type $sId  
     * @return string
     */
    private function _getCfgFile($sId) {
        return $this->_getCfgDir().$sId.'.json';
    }
    
    /**
     * save all elements of the current config
     * @return boolean
     */
    public function _save() {
        if($this->_aCfg===false){
            return false;
        }
        ksort($this->_aCfg);
        // JSON_PRETTY_PRINT reqires PHP 5.4
        $sOut = json_encode($this->_aCfg, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false ));
        return file_put_contents($this->_getCfgFile($this->_sCfgId), $sOut);
    }
    
    // ----------------------------------------------------------------------
    // public functions :: handle config sets
    // ----------------------------------------------------------------------
    /**
     * get current config id
     * @return string
     */
    public function configGetId() {
        return $this->_sCfgId;
    }
    
    /**
     * get all available config files as flat array
     * @return array
     */
    public function configGetList(){
        $aReturn=array();
        $sDir=$this->_getCfgDir();
        foreach (glob($sDir."*.json") as $sFile){
            $aReturn[]=str_replace(".json", "", basename($sFile));
        }
        return $aReturn;
    }

    /**
     * set a config id and load its items
     * @param string  $sId  id of config (without extension)
     */
    public function configSet($sId=false) {
        if(!$sId){
            return false;
        }
        $sCfgFile=$this->_getCfgFile($sId);
        $this->_aCfg=array();
        $this->_sCfgId=$sId;
        if(!file_exists($sCfgFile)){
            // echo "WARNING: file " . $sCfgFile . " does not exist.";
        } else {
            $this->_aCfg=json_decode(file_get_contents($sCfgFile),1);
            if ($this->_aCfg===false || !is_array($this->_aCfg)){
                die("ERROR: file " . $sCfgFile . " is not a valid JSON file.");
            }
        }
        return true;
    }

    // ----------------------------------------------------------------------
    // public functions :: handle config items
    // ----------------------------------------------------------------------
    
    /**
     * turn on / off autosave of config items
     * @param bool  $bool   true = auto save ON
     * @return current value
     */
    public function autosave($bool){
        $this->_bAutosave=(bool)$bool;
        return $this->_bAutosave;
    }
    
    /**
     * delete a subitem of the config
     * The config array will be saved
     * 
     * @param string  $sKey    keystructure - levels are divided by _sDivider
     * @param array   $aArray  optional array; default is false (=$this->_aCfg)
     * @return boolean
     */
    function delete($sKey, $aArray=false){
        if(!$this->keyExists($sKey, $aArray)){
            die("ERROR: a varname [$sKey] does not exist in the config [".$this->configGetId()."].\n");
        }
        if(!$aArray){
            $aArray=&$this->_aCfg;
        }
        $aTmp=preg_split('/\\'.$this->_sDivider.'/', $sKey);
        $sLastKey=array_pop($aTmp);
        if(count($aTmp)){
            foreach($aTmp as $sKeyname){
                if(!isset($aArray[$sKeyname])){
                    $aArray[$sKeyname]=array();
                }
                $aArray=&$aArray[$sKeyname];
            }
        }
        unset($aArray[$sLastKey]);
        return $this->_save();
    }
    
    /**
     * dump config array
     */
    public function dump() {
        echo __METHOD__ . '<br><pre>' . print_r($this->_aCfg, 1) . '</pre>';
    }
    
    
    /**
     * get the full config array; 
     * you can set a config id to switch the config id (it executes setCfgId() 
     * internally)
     * @see setCfgId()
     * 
     * @param string  $sId  optional: id of config (without extension)
     * @return type
     */
    public function getFullConfig($sId=false) {
        if ($sId){
            $this->configSet($sId);
        }
        return $this->_aCfg;
    }
    
    /**
     * get a value from array
     * 
     * @param string  $sKey    keystructure - levels are divided by _sDivider
     * @param array   $aArray  optional array; default is false (=$this->_aCfg)
     * @return any
     */
    function get($sKey=false, $aArray=false){
        if(!$this->keyExists($sKey, $aArray)){
            die("ERROR: a varname [$sKey] does not exist in the config [".$this->configGetId()."].\n");
        }
        return $this->getPointer($sKey, $aArray);
    }
    
    /**
     * get information if a config key exists; it returns a pointer to the
     * loaded config - or false if the key does not exist
     * 
     * @param string  $sKey    keystructure - levels are divided by _sDivider
     * @param array   $aArray  optional array; default is false (=$this->_aCfg)
     * @return boolean
     */
    public function getPointer($sKey, $aArray=false){
        return $this->keyExists($sKey, $aArray, true);
    }
    /**
     * get information if a config key exists; it returns a pointer to the
     * loaded config - or false if the key does not exist
     * 
     * @param string  $sKey    keystructure - levels are divided by _sDivider
     * @param array   $aArray  optional array; default is false (=$this->_aCfg)
     * @return boolean
     */
    public function keyExists($sKey, $aArray=false, $bReturnPointer=false){
        if(!$aArray){
            $aArray=&$this->_aCfg;
        }
        // return complete config if the search key is empty
        if(!$sKey){
            return $aArray;
        }
        $aTmp=preg_split('/\\'.$this->_sDivider.'/', $sKey);
        $sSubkey=array_shift($aTmp);
        if(!isset($aArray[$sSubkey])){
            return false;
        }
        if(count($aTmp)){
            return $this->keyExists(implode($this->_sDivider, $aTmp), $aArray[$sSubkey], $bReturnPointer);
        }
        return $bReturnPointer ? $aArray[$sSubkey] : true;
    }
    /**
     * set a complete config (be careful!) or just a subitem of the config
     * The config array will be saved
     * 
     * @param array  $value     value
     * @param string $sKey      optional: key of the config - can contain _sDivider to divide levels
     * @return boolean
     */
    function set($value, $sKey=false){
        if(!$sKey){
            $this->_aCfg=$value;
        } else {
            $aArray=&$this->_aCfg;
            $aTmp=preg_split('/\\'.$this->_sDivider.'/', $sKey);
            $sLastKey=array_pop($aTmp);
            if(count($aTmp)){
                foreach($aTmp as $sKeyname){
                    if(!isset($aArray[$sKeyname])){
                        $aArray[$sKeyname]=array();
                    }
                    $aArray=&$aArray[$sKeyname];
                }
            }
            if(isset($aArray[$sLastKey]) && is_array($aArray[$sLastKey])){
                $aArray[$sLastKey][]=$value;
            } else {
                $aArray[$sLastKey]=$value;
            }
        }
        return $this->_save();
    }

}