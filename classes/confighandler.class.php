<?php
/**
 * handle config data and store as json file
 * 
 *
 * @author Axel
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
    
    // ----------------------------------------------------------------------
    // CONSTRUCTOR
    // ----------------------------------------------------------------------
    
    /**
     * init method
     * @param string  $sId  config id (without ".json" extension
     */
    public function __construct($sId=false) {
        $this->setCfgId($sId);
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
        if(!$this->_aCfg){
            return false;
        }
        ksort($this->_aCfg);
        // JSON_PRETTY_PRINT reqires PHP 5.4
        if (defined('JSON_PRETTY_PRINT')) {
            $sOut = json_encode($this->_aCfg, JSON_PRETTY_PRINT);
        } else {
            $sOut = json_encode($this->_aCfg);
        }
        file_put_contents($this->_getCfgFile($this->_sCfgId), $sOut);
        return true;
    }
    
    // ----------------------------------------------------------------------
    // public functions
    // ----------------------------------------------------------------------
    
    /**
     * delete a config item
     * @param string  $sIndex  key
     * @return boolean
     */
    public function itemdelete($sIndex, $sValue) {
        if(!array_key_exists($sIndex, $this->_aCfg)){
            return false;
        }
        unset ($this->_aCfg[$sIndex]);
        $this->_save();
        return true;
    }
    /**
     * add or set a config item
     * @param string  $sIndex  key
     * @param any     $sValue  value in any type
     * @return boolean
     */
    public function itemset($sIndex, $sValue) {
        $this->_aCfg[$sIndex]=$sValue;
        $this->_save();
        return true;
    }
    
    /**
     * turn on / off autosave of config items
     * @param bool  $bool   true = auto save ON
     * @return current value
     */
    public function autosave($bool){
        $this->_bAutosave=$bool;
        return $this->_bAutosave;
    }
    
    
    /**
     * dump config array
     */
    public function dump() {
        echo 'dump ' . __CLASS__ . '<br><pre>' . print_r($this->_aCfg, 1) . '</pre>';
    }
    
    /**
     * get all available config files as flat array
     * @return array
     */
    public function getAllConfigs(){
        $aReturn=array();
        $sDir=$this->_getCfgDir();
        foreach (glob($sDir."*.json") as $sFile){
            $aReturn[]=str_replace(".json", "", basename($sFile));
        }
        return $aReturn;
    }

    
    /**
     * get current config id
     * @return string
     */
    public function getCfgId() {
        return $this->_sCfgId;
    }
    
    /**
     * get the full config array; 
     * you can set a config id to switch the config id (it executes setCfgId() 
     * internally)
     * @see setCfgId()
     * @param string  $sId  optional: id of config (without extension)
     * @return type
     */
    public function get($sId=false) {
        if ($sId){
            $this->setCfgId($sId);
        }
        return $this->_aCfg;
    }
    
    /**
     * set a complete config (be careful)
     * @return array
     */
    public function set($aArray) {
        if(!is_array($aArray)){
            return false;
        }
        $this->_aCfg=$aArray;
        $this->_save();
        return true;
    }
    
    /**
     * set a config id and load its items
     * @param string  $sId  id of config (without extension)
     */
    public function setCfgId($sId=false) {
        if(!$sId){
            return false;
        }
        $sCfgFile=$this->_getCfgFile($sId);
        $this->_aCfg=array();
        $this->_sCfgId=$sId;
        if(!file_exists($sCfgFile)){
            echo "WARNING: file " . $sCfgFile . " does not exist.";
        } else {
            $this->_aCfg=json_decode(file_get_contents($sCfgFile),1);
            if (!$this->_aCfg || !is_array($this->_aCfg)){
                die("ERROR: file " . $sCfgFile . " is not a valid JSON file.");
            }
        }
        return true;
    }

}

