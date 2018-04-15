<?php

if (!isset($adminindex)) {
    die("Abort." . __FILE__);
}

$oCfg=new confighandler("config_user");
// $aUserCfg=$oCfg->get();
$aTC = array();

// ----------------------------------------------------------------------
// actions
// ----------------------------------------------------------------------

if($sAppAction){
    $aResult=array();
    switch ($sAppAction){
        
        case 'updateconfig':
            $aRawCfg=json_decode($_POST['rawdata'], 1);
            if(!is_array($aRawCfg)){
                $oMsg->add($aLangTxt['AdminMessageSettings-update-error-no-json'] . '<button class="btn" onclick="javascript:history.back();">back</button>', 'error');
            } else {
                if (!$oCfg->set($aRawCfg)){
                    $oMsg->add($aLangTxt['AdminMessageSettings-update-error'], 'error');
                } else {
                    $oMsg->add($aLangTxt['AdminMessageSettings-update-ok'], 'success');
                }
            }
            break;
        
        default:
            $oMsg->add("SKIP: action $sAppAction is not implemented (yet).", 'error');
    }
}

// RESCAN CONFIG - repeated in inc_config.php
$aUserCfg=$oCfg->get("config_user");
$aCfg = array_merge($aDefaultCfg, $aUserCfg);


foreach(array_keys($aUserCfg) as $sKey){
    if (!array_key_exists($sKey, $aDefaultCfg)){
        $oMsg->add(sprintf($aLangTxt['AdminMessageSettings-wrong-key'], $sKey) . " <pre>'$sKey': ".  json_encode($aUserCfg[$sKey])."</pre>", 'warning');
    }
}


// ----------------------------------------------------------------------
// 2 tabs for raw data
// ----------------------------------------------------------------------

foreach (array(
    "config_user" => array("edit" => true),
    // "internal-config_default" => array("edit" => false),
) as $sCfgfile => $aSettings) {
    $myvar = $sCfgfile;
    $sData = file_get_contents(dirname(__DIR__) . '../../config/' . $sCfgfile . '.json');
    $sOut=$aSettings['edit']
            ? '<form class="form" method="post" action="'.getNewQs(array()).'">'
                . '<input name="appaction" value="updateconfig" type="hidden">'
                . '<input name="appconfig" value="'.$sCfgfile.'" type="hidden">'
                . '<textarea id="ta" name="rawdata" class="form-control raw" rows="15">' . htmlentities($sData) . '</textarea><br>'
                . '<button class="btn btn-primary" title="'.$aLangTxt['ActionOKHint'].'"'
                    . '>'.$aCfg['icons']['actionOK'].$aLangTxt['ActionOK'].'</button>'
            . '</form>'
            : '<pre>' . htmlentities($sData) . '</pre>'
            ;
    
    $aTC[] = array(
        'tab' => $aCfg['icons']['tab_'.$myvar] . $myvar,
        'content' => '<h4>' . $aCfg['icons']['tab_'.$myvar] . $sCfgfile . '</h4>
            <div class="subh3">'
            . '<div class="hintbox">'
                . $aLangTxt['AdminHintRaw-' . $sCfgfile]
            . '</div>'
            . $sOut
          . '</div>
        '
    );
    
}

// ----------------------------------------------------------------------
// tab settings that shows overrides 
// ----------------------------------------------------------------------
$aCfgUser = $oCfg->get("config_user");


// $myvar = 'overrides';
$sTable = '<table class="table datatable"><thead>'
        . '<tr>'
        . '<th>' . $aLangTxt['AdminMenuSettings-var'] . '</th>'
        . '<th>' . $aLangTxt['AdminMenuSettings-description'] . '</th>'
        . '<th>' . $aLangTxt['AdminMenuSettings-uservalue'] . '</th>'
        . '<th>' . $aLangTxt['AdminMenuSettings-default'] . '</th>'
        . '</tr>'
        . '</thead>'
        . '<tbody>';
foreach ($aDefaultCfg as $sKey => $val) {
    $value = '';
    $sClass = "default";
    $bHasUserCfg=array_key_exists($sKey, $aCfgUser);
    // genenerate new config
    $aNewCfg=$aCfgUser;
    
    if ($bHasUserCfg) {
        $sClass = "user";
        $value = $aCfgUser[$sKey];
        unset($aNewCfg[$sKey]);
        $sFormButton='<button class="btn btn-default" title="'.$aLangTxt['ActionResetToDefaultsHint'].' '.$sKey.'"'
            . '>'.$aCfg['icons']['actionReset'].$aLangTxt['ActionResetToDefaults'].'</button>';
    } else {
        $aNewCfg[$sKey]=$val;
        $sFormButton='<button class="btn btn-default" title="'.$aLangTxt['ActionAdd'].' '.$sKey.'"'
            . '>'.$aCfg['icons']['actionAdd'].$aLangTxt['ActionAdd'].'</button>';
    }
    if (!isset($aLangTxt['cfg-' . $sKey])) {
        $sClass = "error";
    }
    
    $sNewCfg=json_encode($aNewCfg);
    
    $sTable.='<tr class="' . $sClass . '">' . "\n"
            . '<td>' . $sKey . '</td>' . "\n"
            . '<td>' . (isset($aLangTxt['cfg-' . $sKey]) ? $aLangTxt['cfg-' . $sKey] : $aLangTxt['cfg-wrongitem'] ) . '</td>' . "\n"
            // . '<td><pre>' . htmlentities(print_r($val, 1)) . '</pre></td>' . "\n"
            . '<td>' . ($bHasUserCfg ? '<pre class="active">' . htmlentities(json_encode($aCfgUser[$sKey], JSON_PRETTY_PRINT)) . '</pre>' : '-' ) . '</td>' . "\n"
            . '<td><pre' . (!$bHasUserCfg ? ' class="default"': '' ) 
                . '>"'.$sKey.'": '.htmlentities(json_encode($val, JSON_PRETTY_PRINT)) 
                .($sNewCfg
                    ?  '<form class="form" method="post" action="'.getNewQs(array()).'">'
                        . '<input name="appaction" value="updateconfig" type="hidden">'
                        . '<input name="appconfig" value="'.$sCfgfile.'" type="hidden">'
                        . '<textarea id="ta" name="rawdata" class="form-control raw" rows="15" style="display: none;">' . htmlentities($sNewCfg) . '</textarea><br>'
                        . $sFormButton
                    . '</form>'
                    : ''
                 )
                . '</pre>'
            . '</td>' . "\n"
            . '</tr>' . "\n"
    ;
}
$sTable.='<tbody></table>';


$aTC[] = array(
        'tab' => $aCfg['icons']['tab_Compare'] . $aLangTxt['AdminMenuSettingsCompare'],
        'content' => '<h4>' . $aCfg['icons']['tab_Compare'] . $aLangTxt["AdminMenuSettingsCompare"] . '</h4>
            <div class="subh3">'
            . '<div class="hintbox">'
                . $aLangTxt['AdminHintSettingsCompare']
            . '</div>'
            . $sTable
        . '</div>'
    );

// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------

echo $oDatarenderer->renderTabbedContent($aTC);

