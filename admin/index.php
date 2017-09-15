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


if(array_key_exists($sAction,$aCfg['viewsadmin'])){
    $aCfg['viewsadmin'][$sAction]['active']=true;
} else {
    $aCfg['viewsadmin']['overview']['active']=true;
}

$content = '<div id="divtiles">'
            . '<h2>'
                . '<i class="fa fa-cog"></i> Admin'
            . '</h2>'
        . '</div><br>'
        // . $oDatarenderer->renderTabs($aTabs)
        ;
$content = '';
$sIncView='admin/'.($sAction ? $sAction : 'servers').'.php';

$oLog->add('include '.$sIncView);
ob_start();
if (!@include(__DIR__ . '/../views/' . $sIncView)) {
    $oMsg->add('View could not be included: ' . $sIncView, 'error');
}
/*
switch ($sAction){
    case 'lang':
    case 'servers':
    case 'update':
    case 'settings':
        include 'inc_'.$sAction.'.php';
        break;
    default:
        include 'inc_servers.php';
}
 * 
 */
$content .= $oDatarenderer->themeBox(
        $aCfg['icons']['admin'. $sAction] .' '. $aLangTxt['AdminMenu'.$sAction]
        , ob_get_contents()
);

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

$oLog->add(__FILE__ . ' inc_pagetemplate.php start');
include ('../inc_pagetemplate.php');
$oLog->add(__FILE__ . ' inc_pagetemplate.php done');

$oLog->add('sending page');
if($aCfg["debug"]){
    $oPage->addContent($oLog->render());
}
echo $oPage->render();
