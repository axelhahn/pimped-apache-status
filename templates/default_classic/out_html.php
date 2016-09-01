<?php

/*
 * PIMPED APACHE-STATUS
 * DEFAULT template
 * 
 */


// ======================================================================
// html header
// ======================================================================
// default CSS and JS
$sHead = '<link rel="stylesheet" type="text/css" href="./templates/' . basename(dirname(__FILE__)) . '/style.css" media="screen">';

// add a meta refresh tag if needed
if ($aEnv["active"]["reload"]) {
    $sHead.='<meta http-equiv="refresh" content="' . $aEnv["active"]["reload"] . '">';
}

// ======================================================================
// 
// generate content
// 
// ======================================================================
// ----------------------------------------------------------------------
// first I draw menu bar on top. It contains
// - Project and version in a H1 tag
// - top right:
//     - dropdown menus to select reload timer, skin and language
// - a dropdown to select a server or servergroup
// - a reload button
// ----------------------------------------------------------------------


ob_start();
if (!include('./views/' . $aEnv["active"]["view"])) {
    $oMsg->add('View could not be included: ' . $aEnv["active"]["view"], 'error');
}
$content = ob_get_contents();
ob_end_clean();

$sBody = '
        <div id="divmenu">

            <span style="float: right;">' .
            $aCfg['icons']['refresh'].
            $aLangTxt['menuReload'] . ' ' . $oDatarenderer->renderDropdown($aEnv["links"]["reload"]) .
            ' ' .
            $aCfg['icons']['skin'].
            $aLangTxt['menuSkin'] . $oDatarenderer->renderDropdown($aEnv["links"]["skins"]) .
            ' ' .
            $aCfg['icons']['lang'].
            $aLangTxt['menuLang'] . $oDatarenderer->renderDropdown($aEnv["links"]["lang"]) .
            '<span id="h3menu"></span>
            </span>
            <h1 class="title" id="top">'
                . $aCfg['icons']['title']
                . $aEnv["project"]["title"]
                . ' <span>v' . $aEnv["project"]["version"] . '</span>
                <span id="checkversion"></span>
            </h1>' .
        $aCfg['icons']['group'].
        $aLangTxt['menuGroup'] . ' ' .
        $oDatarenderer->renderDropdown($aEnv["links"]["servers"]) .
        ' '.
        $aCfg['icons']['time'].
        ' ' . date("Y-m-d H:i:s") . ' ' .
        ' <input class="button" type="button" 
                onclick="location.reload();" 
                value="' . $aLangTxt['lblReload'] . '"
              >
        </div>';

// ----------------------------------------------------------------------
// add a DIV with the tiles
// ----------------------------------------------------------------------

$sBody.='
        <div id="divtiles">
        ';
foreach ($oDatarenderer->getValidTiles() as $sTilename) {
    $sBody.=$oDatarenderer->renderTile($sTilename);
}
$sBody.='</div>';

// ----------------------------------------------------------------------
// add Startup-Logs if any exists
// ----------------------------------------------------------------------
$sBody.=$oMsg->render();


// ----------------------------------------------------------------------
// add a DIV with the content
// 
// - a menu for the views with tabs 
// - a DIV wih main content
// - a DIV with a link to jump to top of page
// ----------------------------------------------------------------------

$sBody.='
        <div id="divmainbody">
            ' . $oDatarenderer->renderTabs($aEnv["links"]["views"]) .
        '<div id="divmaincontent">
                <!--
                    <h2>' . $aLangTxt["view_" . $aEnv["active"]["view"] . "_label"] . '</h2>
                -->

                <div class="h2">' .
        $content .
        '</div>
            </div>
        </div>
        <div id="divgotop">
            <a href="#"> ^ <span>' . $aLangTxt['lblLink2Top'] . '</span></a>
        </div>
        ';

// ----------------------------------------------------------------------
// add rendering logs
// ----------------------------------------------------------------------
$sBody.=$oMsg->render();


// ======================================================================
// put header and body to the page object
// ======================================================================

$oPage->setHeader($sHead);
$oPage->setContent($sBody);
?>