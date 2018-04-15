<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: SERVERINFOS
 */


$oDatarenderer=new Datarenderer();
$content=
    // $oDatarenderer->themeTable($aLangTxt["lblTable_status_workers"], $oDatarenderer->renderWorkersTable($aSrvStatus), $aLangTxt["lblTableHint_status_workers"]).
    // $oDatarenderer->renderTable('workers_table');
    $oDatarenderer->renderWorkersTable($aSrvStatus).
    $oDatarenderer->renderTable('status');
