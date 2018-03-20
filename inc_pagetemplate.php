<?php

$sOutMode=( (array_key_exists('format', $_GET) && $_GET['format']) ? 'data':'default');
    
$oLog->add('load '.$sOutMode.'/out_html.php ...');
// if (!include(__DIR__ . '/templates/' . $aEnv["active"]["skin"] . '/' . $aCfg['defaultTemplate'])) {
if (!include(__DIR__ . '/templates/'.$sOutMode.'/' . $aCfg['defaultTemplate'])) {
    // die('ERROR: Template could not be included: ' . './templates/' . $aCfg['skin'] . '/' . $aCfg['defaultTemplate'] . '.<br>Check the values "skin" and "defaultTemplate" in your configuration.');
    die('ERROR: Template could not be included: ' . './templates/'.$sOutMode.'/' . $aCfg['defaultTemplate'] . '.<br>Check the values "skin" and "defaultTemplate" in your configuration.');
}



$oPage->setAppDir($sSelfURL);

switch ($oPage->getOutputtype()) {
    case 'html':
        // v1.13: version check
        $sUpdateInfos = checkUpdate();
        $oLog->add('update check done');
        $oPage->setContent(str_replace('<span id="checkversion"></span>', $sUpdateInfos, $oPage->getContent()));
        
        $oPage->setReplacement('{{SKIN}}', $aEnv['active']['skin']);
        $oPage->setJsOnReady($sJsOnReady);
        $sHeader = $oPage->getHeader($sHead);
        if (!$aCfg["showHint"]) {
            $sHeader .= '<style>.hintbox{display: none;}</style>';
        }
        $sHeader .= getHtmlHead($aLangTxt);
        $oPage->setHeader($sHeader);

        $oPage->setFooter('
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                  <b>Version</b> ' . $aEnv["project"]["version"] . ' (' . $aEnv["project"]["releasedate"] . ')
                </div>
                <strong>Axel pimped the Apache status 4U</strong>
                <ul>' . $oDatarenderer->renderLI($aEnv["links"]["project"]) . '</ul>
            </footer>
            <script>initPage();</script>
            ');
        break;
    default:
}