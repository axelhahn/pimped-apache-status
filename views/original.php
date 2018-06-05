<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: ORIGINAL server-status
 */

// tabbed content
$aTC = array();
if (count($aSrvStatus) > 0) {
    foreach ($aSrvStatus as $sHost => $aData) {
        $aTC[] = array(
            'tab' => $aCfg['icons']['server'].' '.$sHost,
            'content' => '<h4>' . $sHost . '</h4><div class="console" style="font-family: \'lucida console\'; font-size: 80%;">' . utf8_encode($aData['orig']) . '</div>'
        );
    }
}

$content = $oDatarenderer->themeBox(
    $aCfg['icons']['original.php'] . ' ' . $aLangTxt['lblHelpOriginal']
    , $oDatarenderer->renderTabbedContent($aTC)
    , $aLangTxt['lblHintHelpOriginal']
);
