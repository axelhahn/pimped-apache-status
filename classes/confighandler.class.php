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
        if($this->_aCfg===false){
            return false;
        }
        ksort($this->_aCfg);
        // JSON_PRETTY_PRINT reqires PHP 5.4
        $sOut = json_encode($this->_aCfg, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false ));
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
    public function itemdelete($sIndex) {
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
     * get a value from array
     * 
     * @param string  $sKey    keystructure - levels are divided by _sDivider
     * @param array   $aArray  optional array; default is false (=$this->_aCfg)
     * @return any
     */
    function getValue($sKey=false, $aArray=false){
        if(!$aArray){
            $aArray=$this->_aCfg;
        }
        if(!$sKey){
            return $aArray;
        }

        $aTmp=preg_split('/\\'.$this->_sDivider.'/', $sKey);
        $sSubkey=array_shift($aTmp);
        if(!isset($aArray[$sSubkey])){
            die("a varname [$sSubkey] does not exist in the config.\n");
        }
        if(count($aTmp)){
            return $this->getValue(implode($this->_sDivider, $aTmp), $aArray[$sSubkey]);
        }
        return $aArray[$sSubkey];
    }
    
    
    /**
     * set a complete config (be careful)
     * 
     * @param array  $aArray  complete config array
     * @return boolean
     */
    public function set_OLD($aArray) {
        if(!is_array($aArray)){
            return false;
        }
        $this->_aCfg=$aArray;
        $this->_save();
        return true;
    }
    /**
     * set a complete config (be careful!) or a subitem of the config
     * The config array will be saved
     * 
     * @param array  $value     value
     * @param string $sVarname  optional: key of the config - can contain _sDivider to divide levels
     * @return boolean
     */
    function set($value, $sVarname=false){
        if(!$sVarname){
            $this->_aCfg=$value;
        } else {
            $aArray=&$this->_aCfg;
            $aTmp=preg_split('/\\'.$this->_sDivider.'/', $sVarname);
            $sLastKey=array_pop($aTmp);
            if(count($aTmp)){
                foreach($aTmp as $sKeyname){
                    if(!isset($aArray[$sKeyname])){
                        $aArray[$sKeyname]=array();
                    }
                    $aArray=&$aArray[$sKeyname];
                }
            }
            if(is_array($aArray[$sLastKey])){
                $aArray[$sLastKey][]=$value;
            } else {
                $aArray[$sLastKey]=$value;
            }
        }
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
            // echo "WARNING: file " . $sCfgFile . " does not exist.";
        } else {
            $this->_aCfg=json_decode(file_get_contents($sCfgFile),1);
            if ($this->_aCfg===false || !is_array($this->_aCfg)){
                die("ERROR: file " . $sCfgFile . " is not a valid JSON file.");
            }
        }
        return true;
    }

}

