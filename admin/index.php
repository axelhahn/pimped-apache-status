<?php
$adminindex=1;

require_once '../inc_config.php';
require_once '../inc_menu.php';

    unset($aEnv["links"]["servers"]);
    unset($aEnv["links"]["reload"]);
    
    
$oLog->add('aEnv["active"] <pre>'.print_r($aEnv["active"], 1),'</pre>');
$oLog->add('aEnv["links"] <pre>'.print_r($aEnv["links"], 1),'</pre>');
    
require_once '../classes/datarenderer.class.php';
$oDatarenderer = new Datarenderer();

$aEnv["active"]["view"]="admin.php";

$sAction=(array_key_exists('action', $_GET))?$_GET['action']:'servers';
$sAction=(array_key_exists('action', $_POST))?$_POST['action']:$sAction;

$sAppAction=(array_key_exists('appaction', $_GET))?$_GET['appaction']:false;
$sAppAction=(array_key_exists('appaction', $_POST))?$_POST['appaction']:$sAppAction;

// TODO:  Icons into config
$aTabs=array(
    'leave'=> array(
        "label" => $aCfg['icons']['leaveadmin'] . $aLangTxt['menuLeaveAdmin'],
        "url" => '../'.getNewQs(),
        "class" => 'adminlink',
        "active" => false
    ),
    'servers'=>array(
        'label' => $aCfg['icons']['adminServers'] . $aLangTxt['AdminMenuServers'],
        'url' =>  getNewQs(array('action'=>'')),
        'active'=>false
    ),
    'settings'=>array(
        'label' => $aCfg['icons']['adminSettings'] . $aLangTxt['AdminMenuSettings'],
        'url' => getNewQs(array('action'=>'settings')),
        'active'=>false
    ),
    'update'=>array(
        'label' => $aCfg['icons']['adminUpdate'] . $aLangTxt['AdminMenuUpdate'],
        'url' =>  getNewQs(array('action'=>'update')),
        'active'=>false
    ),
);
if(array_key_exists($sAction,$aTabs)){
    $aTabs[$sAction]['active']=true;
} else {
    $aTabs['overview']['active']=true;
}

// TODO

$sTabNavi = $oDatarenderer->renderTabs($aTabs);


$content = '<div id="divtiles">'
            . '<h2>'
                . '<i class="fa fa-cog"></i> Admin'
            . '</h2>'
        . '</div><br>'
        // . $oDatarenderer->renderTabs($aTabs)
        ;
$content = '';
$oLog->add('include inc_'.$sAction.'.php');
ob_start();
switch ($sAction){
    case 'servers':
    case 'update':
    case 'settings':
        include 'inc_'.$sAction.'.php';
        break;
    default:
        include 'inc_servers.php';
}
$content .= ob_get_contents();
ob_end_clean();
// echo '<br><br><br><br>';

/*
$content = '<!--<div id="divtiles">'
            . '<h2>'
                . '<i class="fa fa-cog"></i> Admin'
            . '</h2>'
        . '</div>-->'
        .$oMsg->render()
        . '<div id="divmainbody">'
        . $oDatarenderer->renderTabs($aTabs) 
        . '<div id="divmaincontent">'
        // TODO
        // . 
        .'</div>'
        .'</div>'
        ;
 * 
 */
// TODO

// ----------------------------------------------------------------------
// page
// ----------------------------------------------------------------------
$oLog->add('generating output');
require_once "../classes/page.class.php";
$oPage = new Page();
$oPage->setOutputtype('html');

include ('../inc_pagetemplate.php');

$oPage->setAppDir($sSelfURL);

switch ($oPage->getOutputtype()) {
    case 'html':
        $oPage->setContent($oPage->getContent());

        
        // v1.13: version check
        $sUpdateInfos = checkUpdate();
        $oLog->add('update check done');
        $oPage->setContent(str_replace('<span id="checkversion"></span>', $sUpdateInfos, $oPage->getContent()));

        $oPage->setJsOnReady($sJsOnReady);
        if (!$aCfg["showHint"]) {
            $sHeader = $oPage->getHeader($sHead);
            $oPage->setHeader($sHeader . '<style>.hintbox{display: none;}</style>');
        }
        // @since v1.22 map langtxt for javascript
        $sHeader = $oPage->getHeader($sHead);
        $aLangJs=array();
        foreach($aLangTxt as $sKey => $sVal){
            if (strpos($sKey, 'js::')===0){
                $aLangJs[str_replace('js::','',$sKey)]=$sVal;
            }
        }
        $oPage->setHeader($sHeader . '<script>var aLang='.json_encode($aLangJs).';</script>');

        $oPage->setFooter('
            <div id="divfooter">
                Axel pimped the Apache status 4U - v' . $aEnv["project"]["version"] . ' (' . $aEnv["project"]["releasedate"] . ')
                <ul>' . $oDatarenderer->renderLI($aEnv["links"]["project"]) . '</ul>
                    <script>initPage();</script>
            </div>');
        break;
    default:
}
$oLog->add('sending page');
if($aCfg["debug"]){
    $oPage->addContent($oLog->render());
}
echo $oPage->render();
