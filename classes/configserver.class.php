<?php

require_once 'confighandler.class.php';

/**
 * and/ delete server; handle grouping
 * this class is used in admin only
 *
 * @author Axel
 */
class configServer {

    /**
     * all Servers
     * @var array
     */
    protected $_aServer = [];
    
    /**
     * id of the config file
     * @var string 
     */
    protected $_sIdCfg = "config_servers";
    
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
        $this->_oCfg = new axelhahn\confighandler($this->_sIdCfg);
        $this->_load();
    }

    // ----------------------------------------------------------------------
    // private functions
    // ----------------------------------------------------------------------

    private function _initMinimalConfig(): void {
        $this->_aServer=[];
        $sDefaultGroup='default';
        $sDefaultServer='localhost';
        
        $aResult=$this->addGroup(['label'=>$sDefaultGroup]);
        if (isset($aResult['error'])){
            die($aResult['error']);
        }

        $aResult=$this->addServer([
            'label'=>$sDefaultServer,
            'group'=>$sDefaultGroup
            ]);
        if (isset($aResult['error'])){
            die($aResult['error']);
        }
        $this->_save();
    }
    
    /**
     * load serverver config
     * @return boolean
     */
    private function _load(): bool {
        $this->_aServer = $this->_oCfg->getFullConfig();
        if (!$this->_aServer || !count($this->_aServer)){
            $this->_initMinimalConfig();
            $this->_save();
        }
        return true;
    }
    
    /**
     * save 
     * @return boolean
     */
    private function _save(): mixed {
        $this->_sort();
        
        return $this->_oCfg->set($this->_aServer);
    }
    
    
    /**
     * sort 
     * @return boolean
     */
    private function _sort(): bool {
        if(!count($this->_aServer)){
            $this->_initMinimalConfig();
            return true;
        }
        ksort($this->_aServer);
        foreach(array_keys($this->_aServer) as $sGroup){
            if($this->_aServer[$sGroup]['servers']??false){
                ksort($this->_aServer[$sGroup]['servers']);
            }
        }
        return true;
    }

    // ----------------------------------------------------------------------
    // public functions getter
    // ----------------------------------------------------------------------

    /**
     * get full array of server config with groups and servers
     * @return array
     */
    public function get(): array {
        return $this->_aServer;
    }

    public function getDivId($sGroup, $sServername=false): string{
        return $sServername ? 'div-server-'.md5($sGroup . $sServername)
                :'div-group-'.md5($sGroup)
                ;
    }
            
    /**
     * get the existing group ids as a flat array
     * @return array
     */
    public function getGroups(): array {
        return array_keys($this->_aServer);
    }

    /**
     * get the existing server ids of a given group as a flat array;
     * to get the existing groups use getGroups before
     * @see getGroups()
     * @param string $sGroup  name of group
     * @return array
     */
    public function getServers(string $sGroup): array {
        if (!($this->_aServer[$sGroup]??false) ) {
            return ['result'=>false,'error'=>'group '.$sGroup.' does not exist.'];
        }
        if (!($this->_aServer[$sGroup]['servers']??false)) {
            return [];
        }
        return array_keys($this->_aServer[$sGroup]['servers']);
    }
    
    /**
     * get connection details for a server in a group.
     * @see getGroups()
     * @see getServers($sGroup)
     * 
     * @param string $sGroup  name of group
     * @param string $sId     name of server
     * @return array
     */
    public function getServerDetails($sGroup,$sId): array {
        if (!($this->_aServer[$sGroup]??false) ) {
            return ['result'=>false,'error'=>'group '.$sGroup.' does not exist.'];
        }
        if (!($this->_aServer[$sGroup]['servers']??false)
                || !($this->_aServer[$sGroup]['servers'][$sId]??false)
        ) {
            return ['result'=>false,'error'=>'server '.$sId.' does not exist.'];
        }

        $aReturn=$this->_aServer[$sGroup]['servers'][$sId];
        
        $aReturn['label']??=$sId;
        $aReturn['status-url']??='http://'.$sId.'/server-status';
        $aReturn['userpwd']??='';

        return $aReturn;
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
    public function addGroup($aItem): array{
        if($this->_aServer[$aItem['label']]??false){
            return ['result'=>false,'error'=>'group already exists.'];
        }
        
        // --- create new group item
        $this->_aServer[$aItem['label']]=array('servers'=>array());
        
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
    public function deleteGroup(array $aItem, $bSave=true): array{
        if(!$aItem['oldlabel']??false){
            return ['result'=>false, 'error'=>'old label is required'];
        }
        if(!($this->_aServer[$aItem['oldlabel']]??false)){
            return ['result'=>false, 'error'=>'group ['.$aItem['oldlabel'].'] does not exist'];
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
     * return (true/ false) and error (error message)
     * @param array $aItem
     * @return array
     */
    public function setGroup($aItem): array{
        if(!isset($aItem['oldlabel'])){
            return ['result'=>false, 'error'=>'old label is required'];
        }
        if(!isset($this->_aServer[$aItem['oldlabel']])){
            return ['result'=>false, 'error'=>'server '.$aItem['oldlabel'].' does not exist'];
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
        if(!($aItem['group']??false) || !($aItem['label']??false) ){
            return ['result'=>false,'error'=>'group and label are required'];
        }
        if(!($this->_aServer[$aItem['group']??false])){
            return ['result'=>false,'error'=>'given group is invalid.'];
        }
        $this->_aServer[$aItem['group']]['servers']??=[];
        return ['result'=>true];
    }
    
    /**
     * add a new server; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem  array with server data; keys are
     *                      - group         server group
     *                      - label         hostname
     *                      - status-url    server status url
     *                      - userpwd       optional basic authentication in syntax "user:password"
     * @return array
     */
    public function addServer(array $aItem): array{
        $aResult=$this->_checkServerItem($aItem);
        if (!$aResult['result']){
            return $aResult;
        }
        if(( $this->_aServer[$aItem['group']]['servers'][$aItem['label']]??false )){
            return ['result'=>false,'error'=>'given server label already exists.'];
        }
        
        // --- create new server item
        foreach (["label", "status-url", "userpwd"] as $sKey){
            $aServer[$sKey]=$aItem[$sKey]??null;
        }
        $this->_aServer[$aItem['group']]['servers'][$aItem['label']]=$aServer;
        
        // --- save
        $this->_save();
        return ['result'=>true];
    }
    
    /**
     * delete a server entry of a group; it returns an array with the keys
     * return (true/ false) and error (error message)
     * @param array $aItem  server item to delete; the key to be deleted must be
     *                      "oldlabel"
     * @return array
     */
    public function deleteServer(array $aItem): array{
        if(!($aItem['oldlabel']??false)){
            return ['result'=>false, 'error'=>'old label is required'];
        }
        if(!( $this->_aServer[$aItem['group']]['servers'][$aItem['oldlabel']]??false )){
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
        foreach (array("label", "status-url", "userpwd") as $sKey){
            $aServer[$sKey]=$aItem[$sKey]??null;
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
     * get html code for a form for new/ update a group
     * 
     * @param string $sGroup  name of the group
     * @return string html code
     */
    public function renderFormGroup(string $sGroup=''): string{
        global $aLangTxt, $aCfg;
        $bNew=!($sGroup>'');
        
        $sHtml='';
        $sFormId='divfrm-group-'.md5($sGroup );
        

        $sSubmitClass= $bNew ? 'btn-success' : 'btn-default';
        $sAppAction=   $bNew ? 'addgroup' : 'updategroup';
        
        if($sGroup){
            $sHtml.='<div class="divGroup" id="'.$this->getDivId($sGroup).'">'
                    . '<h4 style="float: left;">'
                    . '&nbsp; '
                    . '<i class="fa-solid fa-cubes"></i> '
                    . $sGroup .' <span class="badge">'.count($this->getServers($sGroup)).'</span>'
                    . '&nbsp;<button class="btn btn-default" onclick="$(\'.divFrm\').hide();$(\'#'.$sFormId.'\').slideToggle();">'
                    . '<i class="fa-solid fa-pencil-alt"></i> ' . $aLangTxt['ActionEdit']
                    . '</button> '
                    . '</h4>'
                    ;
        } else {
            $sHtml.='<button class="btn btn-success" onclick="$(\'.divFrm\').hide();$(\'#' . $sFormId . '\').slideToggle();">'
                . $aCfg['icons']['actionAdd'].  $aLangTxt['ActionAddServergroup']
            . '</button><br>'
           ;
        }

        $sKey='label';
        $sFieldId='group-'.md5($sGroup );
        $sHtml.='<div id="'.$sFormId.'" class="divFrm'.($sGroup ? '' : ' divNew').'"'. '>'
                .($sGroup ? '' : '<p>' . $aLangTxt['AdminServersLblAddGroup'] . '</p>')
            // . '<br>'
            . '<form action="'.getNewQs(array()).'" class="form-inline" method="POST" style="float: left;">'
            . '<input type="hidden" name="appaction" value="'.$sAppAction.'"/>'
            . ($sGroup ? '<input type="hidden" name="oldlabel" value="'.$sGroup.'"/>' : '')
            . '<div class="form-group">'
                . '<label for="'.$sFieldId.'" >'.$aLangTxt['AdminLblGroup-'.$sKey].'</label> '
                . '<input type="text" class="form-control" id="'.$sFieldId.'" name="label" size="40" value="'.$sGroup.'" placeholder="" />'
            . '</div>'
        
            . '<button type="submit" class="btn '.$sSubmitClass.'" title="'.$aLangTxt['ActionOKHint'].'"'
                . '><i class="fa-solid fa-check"></i> '.$aLangTxt['ActionOK'].'</button>'
                . '</form>'
            ;
        
        // delete button for a group: only if the group has no servers below
        if($sGroup && !count($this->getServers($sGroup))){
            $sHtml.='<form action="'.getNewQs(array()).'" class="form-inline" method="POST">'
            . '<input type="hidden" name="appaction" value="deletegroup"/>'
            . '<input type="hidden" name="oldlabel" value="'.$sGroup.'"/>'
            . '<button type="submit" class="btn btn-danger" title="'.$aLangTxt['ActionDeleteHint'].'"'
            . '><i class="fa-solid fa-trash"></i> '.$aLangTxt['ActionDelete'].'</button>'
            . '</form>'
            ;            
        } 
        if($sGroup){
            $sHtml.='</div><div style="clear: both;"></div>';
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
    public function renderFormServer($sGroup, $sId=false): string{
        global $aLangTxt, $aCfg;
        $bNew=!($sId>'');
        
        $sHtml='';
        $sFormId='divfrm-'.md5($sGroup ).'-'.md5($sId);
        
        $aSrv=         $bNew ? []: $this->getServerDetails($sGroup, $sId);
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
                        . '>'. $aCfg['icons']['actionDelete'] . $aLangTxt['ActionDelete']
                    . '</button>'
                    . '</form>'
                    
                    . '<button class="btn btn-default" onclick="$(\'.divFrm\').hide();$(\'#'.$sFormId.'\').slideToggle();">'
                    . $aCfg['icons']['actionEdit'] . $aLangTxt['ActionEdit']
                    . '</button>'
                    . ' <strong><i class="fa-regular fa-hdd"></i> '.$aSrv['label'].'</strong>'
                    . ' ('.$aSrv['status-url'].')'
                    . ''
                    ;
        } else {
            $sHtml.='<button class="btn btn-success" onclick="$(\'.divFrm\').hide();$(\'#' . $sFormId . '\').slideToggle();">'
                    . $aCfg['icons']['actionAdd'] . $aLangTxt['ActionAddServer']
                . '</button>'
                . '<br>'
            ;
        }
        $sHtml.='<div id="'.$sFormId.'" class="divFrm'.($sId ? '' : ' divNew').'">'
                .($sId ? '' : '<p>' . $aLangTxt['AdminServersLblAddServer'] . '</p>')
            // . '<br>'
            . '<form action="'.getNewQs(array()).'" class="form-horizontal" method="POST" >'
            . '<input type="hidden" name="appaction" value="'.$sAppAction.'"/>'
            . '<input type="hidden" name="group" value="'.$sGroup.'"/>'
            . ($sId ? '<input type="hidden" name="oldlabel" value="'.$sId.'"/>' : '')
            ;

        foreach (array("label", "status-url", "userpwd") as $sKey){
            $sFieldId='srv-'.md5($sGroup ).'-'.md5($sId).'-'.md5($sKey);
            $sValue=$aSrv[$sKey]??'';
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
                . '>'.$aCfg['icons']['actionOK']. $aLangTxt['ActionOK'].'</button>'
                . '</form>'
                ;
        
        if($sId){
            /*
            $sHtml.='<form action="'.getNewQs(array()).'" class="form-inline" method="POST" style="float: left;">'
            . '<input type="hidden" name="appaction" value="deleteserver"/>'
            . '<input type="hidden" name="group" value="'.$sGroup.'"/>'
            . '<input type="hidden" name="oldlabel" value="'.$sId.'"/>'
            . '<button type="submit" class="btn btn-danger" title="'.$aLangTxt['ActionDeleteHint'].'"'
            . '><i class="fa-solid fa-trash"></i> '.$aLangTxt['ActionDelete'].'</button>'
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