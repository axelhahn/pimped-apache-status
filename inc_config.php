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
$sJsOnReady = '';
$aServers2Collect = array();
$sGetStarted = '<br>see documentation <a href="http://www.axel-hahn.de/docs/apachestatus/get_started.htm">get started<a>.';

$sSelfURL=str_replace('\\','/',str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', __DIR__));

$oCfg=new confighandler("internal-env");
$aEnv=$oCfg->get();

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

$aDefaultCfg=$oCfg->get("internal-config_default");
if (!is_array($aDefaultCfg) || !count($aDefaultCfg)) {
    die("ERROR: Config was not loaded. Reinstall with a fresh download.");
}
// repeated in admin/inc_settings.php
$aUserCfg=$oCfg->get("config_user");
$aCfg = array_merge($aDefaultCfg, $aUserCfg);

$aServergroups=$oCfg->get("config_servers");


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
if (!$aEnv["active"]["lang"]){
    $aEnv["active"]["lang"] = 'en';
}
require_once(__DIR__ . "/lang/" . $aEnv["active"]["lang"] . ".php");

$sData = file_get_contents(__DIR__ . "/lang/" . $aEnv["active"]["lang"] . ".js");
if (!$sData) {
    $oMsg->add("language file was not found: lang/" . $aEnv["active"]["lang"] . ".js.", 'error');
    $sData = '{}';
}

// @since v1.16
checkAuth();

$aCfg['datatableOptions'] = str_replace("__LANG__", $sData, $aCfg['datatableOptions']);

// --- view
$aEnv["active"]["view"] = array_key_exists("view", $_GET) ? $_GET["view"] : $aCfg['defaultView'];
$aEnv["active"]["view"] = $aEnv["active"]["view"] ? $aEnv["active"]["view"] : $aCfg['views'][0];

// --- skins
$aEnv["active"]["skin"] = array_key_exists("skin", $_GET) ? $_GET["skin"] : $aCfg['skin'];

// --- autoreload
$aEnv["active"]["reload"] = array_key_exists("reload", $_GET) ? $_GET["reload"] : false;

// -- servergroup
$aEnv["active"]["group"] = array_key_exists("group", $_GET) ? $_GET["group"] : false;
if (!$aEnv["active"]["group"]) {

    foreach ($aServergroups as $sGroup => $aData) {
        $aEnv["active"]["group"] = $sGroup;
        break;
    }
}
$aEnv["active"]["servers"] = array_key_exists("servers", $_GET) ? $_GET["servers"] : false;

if ($aServergroups && !array_key_exists($aEnv["active"]["group"], $aServergroups)) {
    $oMsg->add(sprintf($aLangTxt['error-wrong-group'], $aEnv["active"]["group"]), 'error');
}
if (!$aEnv["active"]["group"]) {
    $oMsg->add(sprintf($aLangTxt['error-no-group'], $aEnv["active"]["group"]), 'error');
} else {

    foreach ($aServergroups[$aEnv["active"]["group"]]["servers"] as $sHost => $aData2) {
        $aServers2Collect[] = $sHost;
    }

    $aServers2Collect = array_key_exists("servers", $_GET) ? explode(",", $_GET["servers"]) : $aServers2Collect;

    // check: all servers are in my group?
    if ($aServers2Collect) {
        foreach ($aServers2Collect as $sHost) {
            if (!array_key_exists($sHost, $aServergroups[$aEnv["active"]["group"]]['servers'])) {
                $oMsg->add(sprintf($aLangTxt['error-server-not-in-group'], $sHost, $aEnv["active"]["group"]), 'error');
            }
        }
    }
}