<?php

require_once 'confighandler.class.php';

/**
 * and/ delete server; handle grouping
 * this class is used in admin only
 *
 * @author Axel
 */
class configData {

    /**
     * id of the default config file
     * @var string 
     */
    protected string $_sIdUser = "config_user";
    /**
     * id of the default config file
     * @var string 
     */
    protected string $_sIdForm = "internal-config_form";
    
    /**
     * id of the user config file
     * @var string 
     */
    protected string $_sIdDefaults = "internal-config_default";


    /**
     * Arrray with config defaults
     * @var array
     */
    protected array $_aDefaults = [];

    /**
     * Array with user config
     * @var array
     */
    protected array $_aUser = [];

    /**
     * Array with form default config
     * @var array
     */
    protected array $_aForm = [];

    /**
     * Array with fetched serverstatus data
     * @var array
     */
    protected array $_aServer = [];

    /**
     * internally used confighandler object
     * @var axelhahn\confighandler
     */
    protected axelhahn\confighandler $_oCfg;

    // ----------------------------------------------------------------------
    // CONSTRUCTOR
    // ----------------------------------------------------------------------

    /**
     * init method; it loads the server config
     */
    public function __construct() {
        $this->_oCfg = new axelhahn\confighandler();
        $this->_load();
    }

    // ----------------------------------------------------------------------
    // private functions
    // ----------------------------------------------------------------------
    
    /**
     * load serverver config
     * @return bool
     */
    private function _load(): bool {
        $this->_aUser     = $this->_oCfg->getFullConfig($this->_sIdUser);
        $this->_aDefaults = $this->_oCfg->getFullConfig($this->_sIdDefaults);
        $this->_aForm     = $this->_oCfg->getFullConfig($this->_sIdForm);
        return true;
    }
    
    /**
     * save user data
     * @return int|boolean
     */
    private function _save(): mixed {
        $this->_sort();
        
        $this->_oCfg->configSet($this->_sIdUser);
        return $this->_oCfg->set($this->_aUser);
    }
    
    
    /**
     * sort user config
     * @return boolean
     */
    private function _sort(): bool {
        if(!count($this->_aUser)){
            return true;
        }
        ksort($this->_aUser);
        return true;
    }

    /**
     * get names of known keys of defined forms as a list
     * @return array
     */
    private function _getFormKeys(): array {
        $aReturn=[];
        foreach($this->getDefaultkeys() as $sKey){
            if($this->_aForm[$sKey]??false){
                $aReturn[]=$sKey;
            }
        }
        return $aReturn;
    }
    
    // ----------------------------------------------------------------------
    // public functions getter
    // ----------------------------------------------------------------------

    
    /**
     * get an item or full array of default config
     * 
     * @see  $this->getDefaultkeys()
     * 
     * @param  string  $sKey  optional: key of a single item
     * @return mixed
     */
    public function getDefault($sKey=false) {
        if($sKey){
            return ($this->_aDefaults[$sKey]??false) 
                ? $this->_aDefaults[$sKey] 
                : false
            ;
        }
        return $this->_aDefaults;
    }
    
    /**
     * return all valid array keys for config values
     * @return array
     */
    public function getDefaultkeys(): array {
        return array_keys($this->_aDefaults);
    }
    
    /**
     * get an item or full array of config (merged from defaults and user config)
     * 
     * @see  $this->getDefaultkeys()
     * 
     * @param  string  $sKey    optional: key of a single item
     * @param  array  $aArray  optional: array to search in; default is false (=merge of aDefaults and user settings)
     * @return mixed
    function getConfig(string $sKey='', array $aArray=[]): mixed{
        if(!count($aArray)){
            $aArray=array_merge($this->_aDefaults, $this->_aUser);
        }
        if(!$sKey){
            return $aArray;
        }
        return $this->_oCfg->get($sKey, $aArray);
    }
     */

    // ----------------------------------------------------------------------
    // public functions setter
    // ----------------------------------------------------------------------

    // ----- GROUPS
    
    /**
     * add a new server; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem
     * @return array
     */
    public function addGroup(array $aItem): array{
        if( $this->_aServer[$aItem['label']]??false){
            return ['result'=>false,'error'=>'group already exists.'];
        }
        
        // --- create new group item
        $this->_aServer[$aItem['label']]=['servers'=>[]];
        
        // --- save
        $this->_save();
        return ['result'=>true];
    }
    
    /**
     * delete a group. There is no check if it has server nodes added.
     * be careful.
     * @param array $aItem  item with key "oldlabel" will be deleted
     * @param bool  $bSave  flag save or not; is set to false in set() method
     * @return array
     */
    public function deleteGroup(array $aItem, bool $bSave=true){
        if(!($aItem['oldlabel']??false)){
            return ['result'=>false, 'error'=>'old label is required'];
        }
        if(!( $this->_aServer[$aItem['oldlabel']]??false)){
            return ['result'=>false, 'error'=>'old label does not exist'];
        }
        
        // remove key
        unset($this->_aServer[$aItem['oldlabel']]);
        
        // --- save
        if ($bSave){
            $this->_save();
        }
        return ['result'=>true];
        
    }
    /**
     * update server; it returns an array with the keys
     * 
     * @param array $aItem
     * @return array
     */
    public function setGroup(array $aItem): array{
        if(!($aItem['oldlabel']??false)){
            return ['result'=>false, 'error'=>'old label is required'];
        }
        
        $aTmp=$this->_aServer[$aItem['oldlabel']];
        
        // --- remove old key
        $aResult=$this->deleteGroup($aItem, false);
        if (!$aResult['result']){
            return $aResult;
        }
        
        // --- create new server item
        $this->_aServer[$aItem['label']]=$aTmp;
        
        // --- save
        $this->_save();
        return ['result'=>true];
    }
    
    // ----- SERVER
    
    /**
     * helper function to check item array ; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem
     * @return array
     */
    private function _checkServerItem(array $aItem): array{
        // --- checks
        if(!($aItem['group']??false)
          || !($aItem['label']??false)
        ){
            return ['result'=>false,'error'=>'group and label are required'];
        }
        if(!($this->_aServer[$aItem['group']]??false)){
            return ['result'=>false,'error'=>'given group is invalid.'];
        }
        if(!array_key_exists('servers', $this->_aServer[$aItem['group']])){
            $this->_aServer[$aItem['group']]['servers']=[];
        }
        return ['result'=>true];
    }
    
    /**
     * add a new server; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem
     * @return array
     */
    public function addServer(array $aItem): array{
        $aResult=$this->_checkServerItem($aItem);
        if (!$aResult['result']){
            return $aResult;
        }
        if(array_key_exists($aItem['label'], $this->_aServer[$aItem['group']]['servers'])){
            return ['result'=>false,'error'=>'given server label already exists.'];
        }
        
        // --- create new server item
        foreach (array("label", "status-url", "userpwd") as $sKey){
            if(array_key_exists($sKey, $aItem) && $aItem[$sKey]){
                $aServer[$sKey]=$aItem[$sKey];
            }
        }
        $this->_aServer[$aItem['group']]['servers'][$aItem['label']]=$aServer;
        
        // --- save
        $this->_save();
        return ['result'=>true];
    }
    
    /**
     * delete a server entry of a group; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem
     * @return array
     */
    public function deleteServer(array $aItem): array{
        if(!($aItem['oldlabel']??false)){
            return ['result'=>false, 'error'=>'old label is required'];
        }
        if(!array_key_exists($aItem['oldlabel'], $this->_aServer[$aItem['group']]['servers'])){
            return ['result'=>false, 'error'=>'old label does not exist'];
        }
        
        // remove key
        unset($this->_aServer[$aItem['group']]['servers'][$aItem['oldlabel']]);
        
        // --- save
        $this->_save();
        return ['result'=>true];
        
    }

    /**
     * update server; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem
     * @return array
     */
    public function setServer(array $aItem): array{
        // --- checks
        $aResult=$this->_checkServerItem($aItem);
        if (!$aResult['result']){
            return $aResult;
        }
        
        // --- remove old key
        $aResult=$this->deleteServer($aItem);
        if (!$aResult['result']){
            return $aResult;
        }
        
        // --- create new server item
        foreach (["label", "status-url", "userpwd"] as $sKey){
            if($aItem[$sKey]??false){
                $aServer[$sKey]=$aItem[$sKey];
            }
        }
        $this->_aServer[$aItem['group']]['servers'][$aItem['label']]=$aServer;
        
        // --- save
        $this->_save();
        return ['result'=>true];
    }
    
    

    // ----------------------------------------------------------------------
    // public functions render output
    // ----------------------------------------------------------------------
    
    /**
     * render a form item
     * @param array $aForm
     * @param string $sKey
     * @return bool|string
     */
    private function _renderItem(array $aForm, string $sKey): bool|string{
        global $aLangTxt;
        $sHtml='';
        
        if(!count($aForm[$sKey])){
            return false;
        }
        if (!($aForm[$sKey]['_type']??false)){
            foreach(array_keys($aForm[$sKey]) as $sKey2){
                $sHtml.=$this->_renderItem($aForm[$sKey], $sKey2);
            }
            return $sHtml;
        }
        $sFieldId='e'.$sKey;
        $sType=$aForm[$sKey]['_type'];
        $sValue='TODO';
        switch ($sType) {
            case 'text':
                $sHtml.='<label for="'.$sFieldId.'" class="col-sm-2">'.$aLangTxt['AdminLblVar-'.$sKey].'</label>'
                    . '<div class="col-sm-10">'
                    . '<input type="'.$sType.'" class="form-control" id="'.$sFieldId.'" name="label" size="40" value="'.$sValue.'" placeholder="" />'
                    . '</div>';
                break;
            case 'password':
                $sHtml.='<label for="'.$sFieldId.'" class="col-sm-2">'.$aLangTxt['AdminLblVar-'.$sKey].'</label>'
                    . '<div class="col-sm-10">'
                        . '<input type="'.$sType.'" class="form-control" id="'.$sFieldId.'" name="label" size="40" value="12345678" placeholder="" />'
                    . '</div>'
                    . '<label for="'.$sFieldId.'" class="col-sm-2">'.$aLangTxt['AdminLblVar-'.$sKey].'</label>'
                    . '<div class="col-sm-10">'
                        . '<input type="'.$sType.'" class="form-control" id="'.$sFieldId.'" name="label" size="40" value="87654321" placeholder="" />'
                    . '</div>'
                    ;
                break;

            default:
                $sHtml.='ERROR: '.__FUNCTION__.'("'.$sKey.'"): type ['.$sType.'] is not supported yet.<br>';
                break;
        }
        return $sHtml;
    }

}