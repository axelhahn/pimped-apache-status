<?php

/*
 * PIMPED APACHE-STATUS
 * 
 */

global $aSrvStatus;
global $aSrvMeta;

// --- load default and user config
require("inc_config.php");

// --- check update param ... re-locate into admin
if (array_key_exists('action', $_GET) && $_GET['action']){
    header('location: admin/'. str_replace('&amp;','&',getNewQs()). '&action='.$_GET['action']);
}

require("inc_menu.php");


require_once './classes/serverstatus.class.php';
$oServerStatus = new ServerStatus();

// given status url overrides server selection
if (array_key_exists("url", $_GET)) {
    // $parts=  parse_url($_GET["url"]);
    $aTestUrl = $_GET["url"];
}
$i = 0;
if (isset($aTestUrl)) {
    $oServerStatus->addServer('testurl', array('status-url' => $aTestUrl));
} else if ($aServers2Collect) {
    foreach ($aServers2Collect as $sWebserver) {
        $oServerStatus->addServer($sWebserver, $aServergroups[$aEnv["active"]["group"]]['servers'][$sWebserver]);
        $i++;
    }
}

$oLog->add('start $oServerStatus->getStatus() ');

$aStatus = $oServerStatus->getStatus();
if (count($aStatus["errors"])) {
    foreach ($aStatus["errors"] as $sErr) {
        $oMsg->add($sErr, 'error');
    }
}
$oLog->add('done $oServerStatus->getStatus() ');

$aSrvStatus = $aStatus["data"];
$aSrvMeta = $aStatus["meta"];

if (!count($aServers2Collect)) {
    $oMsg->add(sprintf($aLangTxt['error-no-server-in-group'], $aEnv["active"]["group"]), 'error');
    // $oMsg->add('No server was defined for monitoring. Open <a href="./admin/">Settings</a> or check your config in config/config_servers.json.', 'error');
}


// ----------------------------------------------------------------------
// 
// GENERATE OUTPUT
// 
// ----------------------------------------------------------------------
// ----------------------------------------------------------------------
// create content
// ----------------------------------------------------------------------
require_once './classes/datarenderer.class.php';
$oDatarenderer = new Datarenderer();

// ----------------------------------------------------------------------
// page
// ----------------------------------------------------------------------
$oLog->add('generating output');
include("./classes/page.class.php");
$oPage = new Page();
$oPage->setOutputtype('html');

// load out_html.php in the skin directory
$oLog->add(__FILE__ . ' start including view ' . $aEnv["active"]["view"]);
$content='';
if (!@include(__DIR__ . '/views/' . $aEnv["active"]["view"])) {
    $oMsg->add('View could not be included: ' . $aEnv["active"]["view"], 'error');
}
$oLog->add(__FILE__ . ' view done');

include ('./inc_pagetemplate.php');
$oLog->add(__FILE__ . ' inc_pagetemplate.php done');

$oPage->setAppDir($sSelfURL);

switch ($oPage->getOutputtype()) {
    case 'html':
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
