<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: ORIGINAL server-status
 */

// tabbed content
$aTC = [];
if (count($aSrvStatus) > 0) {
    foreach ($aSrvStatus as $sHost => $aData) {
        $aTC[] = array(
            'tab' => $oDatarenderer->renderHostTab($aSrvStatus, $sHost),
            'content' => '<h4>' .$aCfg['icons']['server'].' '. $sHost . '</h4><br><div class="console" style="font-family: \'lucida console\'; font-size: 80%;">' . $aData['orig'] . '</div>'
        );
    }
}

$content = $oDatarenderer->themeBox(
    $aCfg['icons']['original.php'] . ' ' . $aLangTxt['lblHelpOriginal']
    , $oDatarenderer->renderTabbedContent($aTC)
    , $aLangTxt['lblHintHelpOriginal']
);
