<?php

/*
 * PIMPED APACHE-STATUS
 * 
 */

require_once __DIR__ . '/classes/confighandler.class.php';
require_once __DIR__ . '/classes/primitivelogger.class.php';
require_once __DIR__ . '/classes/logger.class.php';

global $aEnv;
global $oMsg;
global $oLog;
global $aServergroups, $aDefaultCfg, $aCfg;
global $sJsOnReady;
global $aLangTxt;

$sJsOnReady = '';
$aServers2Collect = array();
$sGetStarted = '<br>see documentation <a href="https://www.axel-hahn.de/docs/apachestatus/get_started.htm">get started<a>.';


$oCfg=new axelhahn\confighandler("internal-env");
$aEnv=$oCfg->getFullConfig();

// I wanna see all warnings 
if (stripos($aEnv["project"]["version"], "beta")) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
}
$aEnv["links"]["update"]["check"]["url"]=str_replace("[VERSION]", $aEnv["project"]["version"], $aEnv["links"]["update"]["check"]["url"]);

$oMsg = new PrimitiveLogger();
$oLog = new logger();

$oLog->add('$_GET: <pre>' . print_r($_GET, 1).'</pre>');
$oLog->add('$_POST: <pre>' . print_r($_POST, 1).'</pre>');

// --- load default and user config
require_once("inc_functions.php");
$oLog->add('inc_function was loaded');

$aDefaultCfg=$oCfg->getFullConfig("internal-config_default");
if (!is_array($aDefaultCfg) || !count($aDefaultCfg)) {
    die("ERROR: Config was not loaded. Reinstall with a fresh download.");
}

$aUserCfg=$oCfg->getFullConfig("config_user");
if (!is_array($aUserCfg)|| !count($aUserCfg)) {
    $_GET["view"]='install.php';
}
$aCfg = array_merge($aDefaultCfg, $aUserCfg);
$oLog->add('<pre>'.print_r($aCfg, 1).'</pre>');
$sSelfURL=$aCfg['selfurl'] ? $aCfg['selfurl'] : str_replace('\\','/',str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', __DIR__));


// ------------------------------------------------------------
// check required features
// ------------------------------------------------------------

if (!function_exists("curl_multi_init")) {
    die("ERROR: PHP-CURL is not installed. It is required to run." . $sGetStarted);
}

if (!class_exists("DomDocument")) {
    $oMsg->add("PHP-XML is not installed. XML Export is not available.", 'warning');
}

// ------------------------------------------------------------
// check GET
// ------------------------------------------------------------
// --- languages
$aEnv["active"]["lang"] = array_key_exists("lang", $_GET) ? $_GET["lang"] : $aCfg['lang'];
if (!$aEnv["active"]["lang"] || !file_exists(__DIR__ . "/lang/" . $aEnv["active"]["lang"] . ".php")){
    $aEnv["active"]["lang"] = 'en';
}
require_once(__DIR__ . "/lang/" . $aEnv["active"]["lang"] . ".php");

$sData = file_get_contents(__DIR__ . "/lang/" . $aEnv["active"]["lang"] . ".js");
if (!$sData) {
    $oMsg->add("language file was not found: lang/" . $aEnv["active"]["lang"] . ".js.", 'error');
    $sData = '{}';
}

// @since v1.16
$bIsAuthenticated=checkAuth();

$aCfg['datatableOptions']["oLanguage"]= json_decode($sData, 1);

$aCfg['datatableOptions']= json_encode($aCfg['datatableOptions']);
// print_r($aCfg['datatableOptions']);

// --- view
$aEnv["active"]["view"] = array_key_exists("view", $_GET) ? $_GET["view"] : $aCfg['defaultView'];
$aEnv["active"]["view"] = $aEnv["active"]["view"] ? $aEnv["active"]["view"] : $aCfg['views'][0];
$aEnv["active"]["view"] = array_key_exists("action", $_GET) ? $_GET["action"] : $aEnv["active"]["view"];

// --- skins
$aEnv["active"]["skin"] = array_key_exists("skin", $_GET) ? $_GET["skin"] : $aCfg['skin'];

// --- autoreload
$aEnv["active"]["reload"] = array_key_exists("reload", $_GET) ? $_GET["reload"] : false;

$bIsExternalUrl=isset($_GET["url"]);

// -- servergroup
// --- load server groups and servers
$aServergroups=$oCfg->getFullConfig("config_servers");

// if no server was configured then setup defaults
if (!count($aServergroups) && is_array($aUserCfg) ){
    require_once __DIR__ . '/classes/configserver.class.php';
    $oServers=new configServer();
    
    $aServergroups=$oCfg->getFullConfig("config_servers");
    if ($aServergroups && count($aServergroups)){
        $oMsg->add($aLangTxt['AdminMessageServer-add-defaults-ok'], 'success');
    } else {
        $oMsg->add($aLangTxt['AdminMessageServer-add-defaults-error'], 'error');
    }
}

$aEnv["active"]["group"] = $bIsExternalUrl 
    ? $aLangTxt['menuGroupNone']
    : (array_key_exists("group", $_GET) ? $_GET["group"] : false)
    ;
if (!$aEnv["active"]["group"]) {

    foreach ($aServergroups as $sGroup => $aData) {
        $aEnv["active"]["group"] = $sGroup;
        break;
    }
}


$aEnv["active"]["servers"] = array_key_exists("servers", $_GET) ? $_GET["servers"] : false;

if (!$bIsExternalUrl && $aServergroups && !array_key_exists($aEnv["active"]["group"], $aServergroups)) {
    $oMsg->add(sprintf($aLangTxt['error-wrong-group'], $aEnv["active"]["group"]), 'error');
}

// show menu items if a user config exists 
if(is_array($aUserCfg)){
    if (!$aEnv["active"]["group"]) {
        $oMsg->add(sprintf($aLangTxt['error-no-group'], $aEnv["active"]["group"]), 'error');
    } else {

        if(!$bIsExternalUrl){
            foreach ($aServergroups[$aEnv["active"]["group"]]["servers"] as $sHost => $aData2) {
                $aServers2Collect[] = $sHost;
            }

            $aServers2Collect = array_key_exists("servers", $_GET) ? explode(",", $_GET["servers"]) : $aServers2Collect;

            // check to show config warnings: all servers are in my group?
            if ($aServers2Collect) {
                foreach ($aServers2Collect as $sHost) {
                    if (!array_key_exists($sHost, $aServergroups[$aEnv["active"]["group"]]['servers'])) {
                        $oMsg->add(sprintf($aLangTxt['error-server-not-in-group'], $sHost, $aEnv["active"]["group"]), 'error');
                    }
                }
            } else {
                if (!isset($adminindex) || !$adminindex){
                    $oMsg->add(sprintf($aLangTxt['error-no-server']), 'error');
                }
            }
        }
    }
}