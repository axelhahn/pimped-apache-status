<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: SERVERINFOS
 */


$aGroupLinks=array();
if(isset($aEnv['links']['servers']) && count($aEnv['links']['servers'])){
    
    foreach($aEnv['links']['servers'] as $sGroup=>$aMembers){
        $sNewUrl=getNewQs(array('group'=>$sGroup));
        $aGroupLinks[]=array(
            'active'=>$sGroup===$aEnv['active']['group'],
            'url'=>$sNewUrl,
            'label'=>$aCfg['icons']['group'].$sGroup
        );
    }
}
// echo '<pre>'.print_r($aEnv, 1).'</pre>';
// echo '<pre>'.print_r($aTC, 1).'</pre>';

$content=
    $oDatarenderer->renderTabs($aGroupLinks).
    // $oDatarenderer->themeTable($aLangTxt["lblTable_status_workers"], $oDatarenderer->renderWorkersTable($aSrvStatus), $aLangTxt["lblTableHint_status_workers"]).
    // $oDatarenderer->renderTable('workers_table');
    $oDatarenderer->renderGroupAndServers($aSrvStatus).
    $oDatarenderer->renderWorkersTable($aSrvStatus).
    $oDatarenderer->renderTable('status');
