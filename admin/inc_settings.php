<?php

if (!isset($adminindex)) {
    die("Abort." . __FILE__);
}

$sJsOnReady.='
	//On Click Event
	$(".subh2 ul.nav li").click(function() {
		$(this.parentNode).find("li").removeClass("active"); //Remove any "active" class
		$(this).addClass("active"); //Add "active" class to selected tab
		return false;
	});
        
        $(".subh2 div ul.nav li a").filter(":first").trigger("click");
';
$aTab = array();
$sHtml = '';

$oCfg=new confighandler("config_user");
// $aUserCfg=$oCfg->get();

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
                // $aNewCfg=array();
                /*
                foreach(array_keys($aRawCfg) as $sKey){
                    if (!array_key_exists($sKey, $aDefaultCfg)){
                        $oMsg->add("WARNING: key [$sKey] is not a valid configuration. This information is useless: <pre>'<strong>$sKey</strong>':".  json_encode($aRawCfg[$sKey])."</pre>", 'warning');
                    }
                }
                 * 
                 */
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
// tab settings
// ----------------------------------------------------------------------

/*
  $myvar='settings';
  $aTab[$myvar] = array(
  'url' => '#',
  'label' => $aLangTxt['AdminMenuSettings'] ,
  'onclick' => 'return showTab(\'#h3' . md5($myvar) . '\');',
  );

  require_once '../classes/configdata.class.php';
  $oData=new configData();
  $sHtml.='
  <h3 id="h3' . md5($myvar) . '">' . $aLangTxt["AdminMenuSettings"] . '</h3>
  <div class="subh3">'
  . $oData->renderForm4UserConfig()
  . '</div>';

 */

// ----------------------------------------------------------------------
// 2 tabs for raw data
// ----------------------------------------------------------------------

foreach (array(
    "config_user" => array("edit" => true),
    "internal-config_default" => array("edit" => false),
) as $sCfgfile => $aSettings) {
    $myvar = $sCfgfile;
    $aTab[$myvar] = array(
        'url' => '#',
        'label' => $myvar,
        'onclick' => 'return showTab(\'#h3' . md5($myvar) . '\');',
    );
    $sData = file_get_contents(dirname(__DIR__) . '/config/' . $sCfgfile . '.json');
    $sOut=$aSettings['edit']
            ? '<form class="form" method="post" action="'.getNewQs(array()).'">'
                . '<input name="appaction" value="updateconfig" type="hidden">'
                . '<input name="appconfig" value="'.$sCfgfile.'" type="hidden">'
                . '<textarea id="ta" name="rawdata" class="form-control raw" rows="15">' . htmlentities($sData) . '</textarea><br>'
                . '<button class="btn btn-primary" title="'.$aLangTxt['ActionOKHint'].'"'
                    . '><i class="fa fa-check"></i> '.$aLangTxt['ActionOK'].'</button>'
            . '</form>'
            : '<pre>' . htmlentities($sData) . '</pre>'
            ;
    $sHtml.='
                <h3 id="h3' . md5($myvar) . '">' . $sCfgfile . '</h3>
                <div class="subh3">'
            . '<div class="hintbox">'
            . $aLangTxt['AdminHintRaw-' . $sCfgfile]
            . '</div>'
            . $sOut
            . '</div>
        ';
}

// ----------------------------------------------------------------------
// tab settings that shows overrides 
// ----------------------------------------------------------------------
$aCfgUser = $oCfg->get("config_user");


$myvar = 'overrides';
$aTab[$myvar] = array(
    'url' => '#',
    'label' => $aLangTxt['AdminMenuSettingsCompare'],
    'onclick' => 'return showTab(\'#h3' . md5($myvar) . '\');',
);
$sTable = '<table class="table datatable"><thead>'
        . '<tr>'
        . '<th>' . $aLangTxt['AdminMenuSettings-var'] . '</th>'
        . '<th>' . $aLangTxt['AdminMenuSettings-description'] . '</th>'
        . '<th>' . $aLangTxt['AdminMenuSettings-default'] . '</th>'
        . '<th>' . $aLangTxt['AdminMenuSettings-uservalue'] . '</th>'
        . '</tr>'
        . '</thead>'
        . '<tbody>';
foreach ($aDefaultCfg as $sKey => $val) {
    $value = '';
    $sClass = "default";
    if (array_key_exists($sKey, $aCfgUser)) {
        $sClass = "user";
        $value = $aCfgUser[$sKey];
    }
    if (!isset($aLangTxt['cfg-' . $sKey])) {
        $sClass = "error";
    }
    $sTable.='<tr class="' . $sClass . '">' . "\n"
            . '<td>' . $sKey . '</td>' . "\n"
            . '<td>' . (isset($aLangTxt['cfg-' . $sKey]) ? $aLangTxt['cfg-' . $sKey] : $aLangTxt['cfg-wrongitem'] ) . '</td>' . "\n"
            // . '<td><pre>' . htmlentities(print_r($val, 1)) . '</pre></td>' . "\n"
            . '<td><pre' . (!array_key_exists($sKey, $aCfgUser) ? ' class="default"': '' ) 
                . '>'.htmlentities(print_r($val, 1)) 
                . '</pre>' . '</td>' . "\n"
            . '<td>' . (array_key_exists($sKey, $aCfgUser) ? '<pre class="active">' . htmlentities(print_r($value, 1)) . '</pre>' : '-' ) . '</td>' . "\n"
            . '</tr>' . "\n"
    ;
}
$sTable.='<tbody></table>';

$sHtml.='
            <h3 id="h3' . md5($myvar) . '">' . $aLangTxt["AdminMenuSettingsCompare"] . '</h3>
            <div class="subh3">'
        . '<div class="hintbox">'
        . $aLangTxt['AdminHintSettingsCompare']
        . '</div>'
        . $sTable
        . '</div>';

// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------
echo '<div class="subh2"><h3><i class="fa fa-bars"></i> Overview </h3>'
 . $oDatarenderer->renderTabs($aTab)
 . '<div style="clear: both"></div><br>'
 . $sHtml
 . '</div>'
 . '';

?>
<!--
<script src="../vendor/codemirror/lib/codemirror.js"></script>
<link rel="stylesheet" href="../vendor/codemirror/lib/codemirror.css">
<script src="../vendor/codemirror/mode/javascript/javascript.js"></script>
<script>
    editor = CodeMirror.fromTextArea(document.getElementById('ta'), {
        lineNumbers: true,
        lineWrapping: true,
        json: true
    });
</script>
-->