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
    protected $_sIdUser = "config_user";
    /**
     * id of the default config file
     * @var string 
     */
    protected $_sIdForm = "internal-config_form";
    
    /**
     * id of the user config file
     * @var string 
     */
    protected $_sIdDefaults = "internal-config_default";


    protected $_aDefaults = array();
    protected $_aUser = array();
    protected $_aForm = array();


    /**
     * internally used confighandler object
     * @var object 
     */
    protected $_oCfg = false;

    // ----------------------------------------------------------------------
    // CONSTRUCTOR
    // ----------------------------------------------------------------------

    /**
     * init method; it loads the server config
     */
    public function __construct() {
        $this->_oCfg = new confighandler();
        $this->_load();
    }

    // ----------------------------------------------------------------------
    // private functions
    // ----------------------------------------------------------------------
    
    /**
     * load serverver config
     * @return type
     */
    private function _load() {
        $this->_aUser     = $this->_oCfg->getFullConfig($this->_sIdUser);
        $this->_aDefaults = $this->_oCfg->getFullConfig($this->_sIdDefaults);
        $this->_aForm     = $this->_oCfg->getFullConfig($this->_sIdForm);
        return true;
    }
    
    /**
     * save user data
     * @return type
     */
    private function _save() {
        $this->_sort();
        
        $this->_oCfg->configSet($this->_sIdUser);
        return $this->_oCfg->set($this->_aUser);
    }
    
    
    /**
     * sort user config
     * @return boolean
     */
    private function _sort() {
        if(!count($this->_aUser)){
            return true;
        }
        ksort($this->_aUser);
        return true;
    }

    private function _getFormKeys() {
        $aReturn=array();
        foreach($this->getDefaultkeys() as $sKey){
            if(array_key_exists($sKey, $this->_aForm)){
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
            if (array_key_exists($sKey, $this->_aDefaults)){
                return $this->_aDefaults[$sKey];
            } else {
                return false;
            }
        }
        return $this->_aDefaults;
    }
    
    /**
     * return all valid array keys for config values
     * @return type
     */
    public function getDefaultkeys() {
        return array_keys($this->_aDefaults);
    }

    /**
     * get an item or full array of config (merged from defaults and user config)
     * 
     * @see  $this->getDefaultkeys()
     * 
     * @param  string  $sKey  optional: key of a single item
     * @return mixed
    public function getConfig_OLD($sKey=false) {
        $aCfg=array_merge($this->_aDefaults, $this->_aUser);
        if($sKey){
            if (array_key_exists($sKey, $aCfg)){
                return $aCfg[$sKey];
            } else {
                return false;
            }
        }
        return $aCfg;
    }
     */
    
    
    /**
     * get an item or full array of config (merged from defaults and user config)
     * 
     * @see  $this->getDefaultkeys()
     * 
     * @param  string  $sKey    optional: key of a single item
     * @param  string  $aArray  optional: array to search in; default is false (=merge of aDefaults and user settings)
     * @return mixed
     */
    function getConfig($sKey=false, $aArray=false){
        $sDivider='.';
        if(!$aArray){
            $aArray=array_merge($this->_aDefaults, $this->_aUser);
        }
        if(!$sKey){
            return $aArray;
        }
        return $this->_oCfg->get($sKey, $aArray);
    }

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
    public function addGroup($aItem){
        if(array_key_exists($aItem['label'], $this->_aServer)){
            return array('result'=>false,'error'=>'group already exists.');
        }
        
        // --- create new group item
        $this->_aServer[$aItem['label']]=array('servers'=>array());
        
        // --- save
        $this->_save();
        return array('result'=>true);
    }
    
    /**
     * delete a group. There is no check if it has server nodes added.
     * be careful.
     * @param array $aItem  item with key "oldlabel" will be deleted
     * @param bool  $bSave  flag save or not; is set to false in set() method
     * @return array
     */
    public function deleteGroup($aItem, $bSave=true){
        if(!array_key_exists('oldlabel', $aItem)){
            return array('result'=>false, 'error'=>'old label is required');
        }
        if(!array_key_exists($aItem['oldlabel'], $this->_aServer)){
            return array('result'=>false, 'error'=>'old label does not exist');
        }
        
        // remove key
        unset($this->_aServer[$aItem['oldlabel']]);
        
        // --- save
        if ($bSave){
            $this->_save();
        }
        return array('result'=>true);
        
    }
    /**
     * update server; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem
     * @return array
     */
    public function setGroup($aItem){
        if(!array_key_exists('oldlabel', $aItem)){
            return array('result'=>false, 'error'=>'old label is required');
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
        return array('result'=>true);
    }
    
    // ----- SERVER
    
    /**
     * helper function to check item array ; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param type $aItem
     * @return type
     */
    private function _checkServerItem($aItem){
        // --- checks
        if(!array_key_exists('group', $aItem)
          || !array_key_exists('label', $aItem)
          || !$aItem['group']
          || !$aItem['label']
        ){
            return array('result'=>false,'error'=>'group and label are required');
        }
        if(!array_key_exists($aItem['group'], $this->_aServer)){
            return array('result'=>false,'error'=>'given group is invalid.');
        }
        if(!array_key_exists('servers', $this->_aServer[$aItem['group']])){
            $this->_aServer[$aItem['group']]['servers']=array();
        }
        return array('result'=>true);
    }
    
    /**
     * add a new server; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem
     * @return array
     */
    public function addServer($aItem){
        $aResult=$this->_checkServerItem($aItem, 1);
        if (!$aResult['result']){
            return $aResult;
        }
        if(array_key_exists($aItem['label'], $this->_aServer[$aItem['group']]['servers'])){
            return array('result'=>false,'error'=>'given server label already exists.');
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
        return array('result'=>true);
    }
    
    /**
     * delete a server entry of a group; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem
     * @return array
     */
    public function deleteServer($aItem){
        if(!array_key_exists('oldlabel', $aItem)){
            return array('result'=>false, 'error'=>'old label is required');
        }
        if(!array_key_exists($aItem['oldlabel'], $this->_aServer[$aItem['group']]['servers'])){
            return array('result'=>false, 'error'=>'old label does not exist');
        }
        
        // remove key
        unset($this->_aServer[$aItem['group']]['servers'][$aItem['oldlabel']]);
        
        // --- save
        $this->_save();
        return array('result'=>true);
        
    }

    /**
     * update server; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem
     * @return array
     */
    public function setServer($aItem){
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
        foreach (array("label", "status-url", "userpwd") as $sKey){
            if(array_key_exists($sKey, $aItem) && $aItem[$sKey]){
                $aServer[$sKey]=$aItem[$sKey];
            }
        }
        $this->_aServer[$aItem['group']]['servers'][$aItem['label']]=$aServer;
        
        // --- save
        $this->_save();
        return array('result'=>true);
    }
    
    

    // ----------------------------------------------------------------------
    // public functions render output
    // ----------------------------------------------------------------------
    
    private function _renderItem($aForm, $sKey){
        global $aLangTxt;
        $sHtml='';
        
        if(!count($aForm[$sKey])){
            return false;
        }
        if (!array_key_exists('_type', $aForm[$sKey])){
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
    
    public function renderForm4UserConfig(){
        global $aLangTxt;
        $sHtml='';
        foreach ($this->_getFormKeys() as $sKey){
            if (array_key_exists($sKey, $this->_aForm)){
                $sHtml.='<form action="'.getNewQs(array()).'" class="form-horizontal" method="POST" >'
                . '<input type="hidden" name="appaction" value="setUservalue"/>'
                .'<div class="form-group">'
                .'<h4>'.$sKey.'</h4><div class="hintbox">'.$aLangTxt['cfg-'.$sKey].'</div>';
                $sHtml.=$this->_renderItem($this->_aForm, $sKey);
                $sHtml.='</div>';
                $sHtml.='</form>';
            }
        }
        return $sHtml;
    }
    
    /**
     * get html code for a form for new/ update a group
     * 
     * @param string $sGroup  name of the group
     * @param string $sId     id of the server; leave empty for NEW
     * @return string
     */
    public function renderFormGroup($sGroup=false){
        global $aLangTxt;
        $bNew=!($sGroup>'');
        
        $sHtml='';
        $sFormId='divfrm-group-'.md5($sGroup );
        

        $sSubmitClass= $bNew ? 'btn-success' : 'btn-default';
        $sAppAction=   $bNew ? 'addgroup' : 'updategroup';
        
        if($sGroup){
            $sHtml.='<div class="divGroup" id="'.$this->getDivId($sGroup).'">'
                    . '<h3>'
                    . '<button class="btn btn-default" onclick="$(\'#'.$sFormId.'\').slideToggle();">'
                    . '<i class="fas fa-pencil-alt"></i> ' . $aLangTxt['ActionEdit']
                    . '</button> '
                    . '<i class="fas fa-cubes"></i> '
                    . $sGroup .' <span class="badge">'.count($this->getServers($sGroup)).'</span>'
                    . '</h3>'
                    ;
        }
        $sHtml.='<div id="'.$sFormId.'" class="divFrm"'
            . ($sGroup ? ' style="display: none;"' : '')
            . '>'
            // . '<br>'
            . '<form action="'.getNewQs(array()).'" class="form-inline" method="POST" style="float: left;">'
            . '<input type="hidden" name="appaction" value="'.$sAppAction.'"/>'
            . ($sGroup ? '<input type="hidden" name="oldlabel" value="'.$sGroup.'"/>' : '')
            ;

        $sKey='label';
        $sFieldId='group-'.md5($sGroup );
        $sHtml.='<div class="form-group">'
                . '<label for="'.$sFieldId.'" >'.$aLangTxt['AdminLblGroup-'.$sKey].'</label> '
                . '<input type="text" class="form-control" id="'.$sFieldId.'" name="label" size="40" value="'.$sGroup.'" placeholder="" />'
                . '</div>';
        
        $sHtml.='<button type="submit" class="btn '.$sSubmitClass.'" title="'.$aLangTxt['ActionOKHint'].'"'
                . '><i class="fas fa-check"></i> '.$aLangTxt['ActionOK'].'</button>'
                . '</form>'
                ;
        
        // delete button for a group: only if the group has no servers below
        if($sGroup && !count($this->getServers($sGroup))){
            $sHtml.='<form action="'.getNewQs(array()).'" class="form-inline" method="POST">'
            . '<input type="hidden" name="appaction" value="deletegroup"/>'
            . '<input type="hidden" name="oldlabel" value="'.$sGroup.'"/>'
            . '<button type="submit" class="btn btn-danger" title="'.$aLangTxt['ActionDeleteHint'].'"'
            . '><i class="fas fa-trash"></i> '.$aLangTxt['ActionDelete'].'</button>'
            . '</form>'
            ;            
        } 
        if($sGroup){
            $sHtml.='<br><br></div>';
        }
        else {
            $sHtml.='<div style="clear: both;"></div>';
        }
        $sHtml.='</div>';
        /*
        echo htmlentities($sHtml); 
        echo $sHtml;
        die();
         * 
         */
        return $sHtml;
    }
    
    /**
     * get html code for a form for new/ update a single server
     * @see getGroups() 
     * @see getServers($sGroup)
     * 
     * @param string $sGroup  name of the group
     * @param string $sId     id of the server; leave empty for NEW
     * @return string
     */
    public function renderFormServer($sGroup, $sId=false){
        global $aLangTxt;
        $bNew=!($sId>'');
        
        $sHtml='';
        $sFormId='divfrm-'.md5($sGroup ).'-'.md5($sId);
        
        $aSrv=         $bNew ? array(): $this->getServerDetails($sGroup, $sId);
        $sSubmitClass= $bNew ? 'btn-success' : 'btn-default';
        // $sSubmitClass= 'btn-success';
        $sAppAction=   $bNew ? 'addserver' : 'updateserver';
        
        if($sId){
            $sHtml.='<div class="divServer" id="'.$this->getDivId($sGroup, $sId).'">'
                    
                    .'<form action="'.getNewQs(array()).'" class="form-inline" method="POST" style="float: right;">'
                    . '<input type="hidden" name="appaction" value="deleteserver"/>'
                    . '<input type="hidden" name="group" value="'.$sGroup.'"/>'
                    . '<input type="hidden" name="oldlabel" value="'.$sId.'"/>'
                    . '<button type="submit" class="btn btn-danger" title="'.$aLangTxt['ActionDeleteHint'].'" '
                        . 'onclick="return confirm(\''.sprintf($aLangTxt['AdminLblServers-ConfirmDelete'], $aSrv['label'].'; '. $aSrv['status-url']) .'\');"'
                        . '><i class="fas fa-trash"></i> '.$aLangTxt['ActionDelete']
                    . '</button>'
                    . '</form>'
                    
                    . '<button class="btn btn-default" onclick="$(\'#'.$sFormId.'\').slideToggle();">'
                    . '<i class="fas fa-pencil-alt"></i> ' . $aLangTxt['ActionEdit']
                    . '</button>'
                    . ' <strong><i class="far fa-hdd"></i> '.$aSrv['label'].'</strong>'
                    . ' ('.$aSrv['status-url'].')'
                    . ''
                    ;
        }
        $sHtml.='<div id="'.$sFormId.'" class="divFrm"'
            . ($sId ? ' style="display: none;"' : '')
            . '>'
            // . '<br>'
            . '<form action="'.getNewQs(array()).'" class="form-horizontal" method="POST" >'
            . '<input type="hidden" name="appaction" value="'.$sAppAction.'"/>'
            . '<input type="hidden" name="group" value="'.$sGroup.'"/>'
            . ($sId ? '<input type="hidden" name="oldlabel" value="'.$sId.'"/>' : '')
            ;

        foreach (array("label", "status-url", "userpwd") as $sKey){
            $sFieldId='srv-'.md5($sGroup ).'-'.md5($sId).'-'.md5($sKey);
            $sValue=(array_key_exists($sKey, $aSrv) ? $aSrv[$sKey] : '') ;
            $iSize=$sKey=="status-url" ? 40 : 20;
            $sHtml.='<div class="form-group">'
                        . '<label for="'.$sFieldId.'" class="col-sm-2 ">'.$aLangTxt['AdminLblServers-'.$sKey].'</label>'
                        . '<div class="col-sm-3">'
                            . '<input type="text" class="form-control" id="'.$sFieldId.'" name="'.$sKey.'" size="'.$iSize.'" value="'.$sValue.'" placeholder="" />'
                        . '</div>'
                        . '<div class="col-sm-7">'
                            . $aLangTxt['AdminLblServers-'.$sKey.'-Hint']
                        . '</div>'
                    . '</div>';
        }
        
        $sHtml.='<button type="submit" class="btn '.$sSubmitClass.'" title="'.$aLangTxt['ActionOKHint'].'"'
                . '><i class="fas fa-check"></i> '.$aLangTxt['ActionOK'].'</button>'
                . '</form>'
                ;
        
        if($sId){
            /*
            $sHtml.='<form action="'.getNewQs(array()).'" class="form-inline" method="POST" style="float: left;">'
            . '<input type="hidden" name="appaction" value="deleteserver"/>'
            . '<input type="hidden" name="group" value="'.$sGroup.'"/>'
            . '<input type="hidden" name="oldlabel" value="'.$sId.'"/>'
            . '<button type="submit" class="btn btn-danger" title="'.$aLangTxt['ActionDeleteHint'].'"'
            . '><i class="fas fa-trash"></i> '.$aLangTxt['ActionDelete'].'</button>'
            . '</form></div>'
            ; 
             * 
             */           
            $sHtml.='</div>';
        } else {
            $sHtml.='<div style="clear: both;"></div>';
        }
        $sHtml.='</div>';
        return $sHtml;
    }


}