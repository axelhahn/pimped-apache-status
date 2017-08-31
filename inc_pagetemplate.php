<?php


$oLog->add('load out_html.php in the skin directory');
// if (!include(__DIR__ . '/templates/' . $aEnv["active"]["skin"] . '/' . $aCfg['defaultTemplate'])) {
if (!include(__DIR__ . '/templates/default/' . $aCfg['defaultTemplate'])) {
    // die('ERROR: Template could not be included: ' . './templates/' . $aCfg['skin'] . '/' . $aCfg['defaultTemplate'] . '.<br>Check the values "skin" and "defaultTemplate" in your configuration.');
    die('ERROR: Template could not be included: ' . './templates/default/' . $aCfg['defaultTemplate'] . '.<br>Check the values "skin" and "defaultTemplate" in your configuration.');
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