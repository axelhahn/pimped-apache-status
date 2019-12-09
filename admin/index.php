<?php

$adminindex = 1;

if (!array_key_exists('action', $_GET)) {
    header('location: ..');
}
require_once '../inc_config.php';
require_once '../inc_menu.php';

$oLog->add('aEnv["active"] <pre>' . print_r($aEnv["active"], 1), '</pre>');
$oLog->add('aEnv["links"] <pre>' . print_r($aEnv["links"], 1), '</pre>');



$sAction = (array_key_exists('action', $_GET)) ? $_GET['action'] : 'servers';
$sAction = (array_key_exists('action', $_POST)) ? $_POST['action'] : $sAction;

$sAppAction = (array_key_exists('appaction', $_GET)) ? $_GET['appaction'] : false;
$sAppAction = (array_key_exists('appaction', $_POST)) ? $_POST['appaction'] : $sAppAction;

$aEnv["active"]["view"] = ( $sAction && array_search($sAction, $aCfg['viewsadmin']) !== false ? $sAction : 'servers'
        );

// remove menu items
unset($aEnv["links"]["servers"]);
unset($aEnv["links"]["reload"]);

require_once '../classes/datarenderer.class.php';
$oDatarenderer = new Datarenderer();

$content = '';
$sIncView = $bIsAuthenticated ? ('admin/' . ($sAction ? $sAction : 'servers') . '.php') : 'login.php'
;

$oLog->add('include ' . $sIncView);
ob_start();
if (!@include(__DIR__ . '/../views/' . $sIncView)) {
    $oMsg->add('View could not be included: ' . $sIncView, 'error');
}
if ($bIsAuthenticated) {
    $content .= $oDatarenderer->themeBox(
            $aCfg['icons']['admin' . $sAction] . ' ' . $aLangTxt['AdminMenu' . $sAction]
            , ob_get_contents()
    );
}
ob_end_clean();

// ----------------------------------------------------------------------
// page
// ----------------------------------------------------------------------
$oLog->add('generating output');
require_once "../classes/page.class.php";
$oPage = new Page();
$oPage->setOutputtype('html');

$oLog->add(__FILE__ . ' inc_pagetemplate.php start');
include ('../inc_pagetemplate.php');
$oLog->add(__FILE__ . ' inc_pagetemplate.php done');

$oLog->add('sending page');
if ($aCfg["debug"]) {
    $oPage->addContent($oLog->render());
}
echo $oPage->render();
