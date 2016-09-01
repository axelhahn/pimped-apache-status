<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: Performance analysis
 */

$oDatarenderer=new Datarenderer();

$content=$oDatarenderer->renderTable('requests_running').
    $oDatarenderer->renderTable('requests_mostrequested').
    $oDatarenderer->renderTable('requests_hostlist').
    $oDatarenderer->renderTable('requests_methods').
    $oDatarenderer->renderTable('requests_clients').
    $oDatarenderer->renderTable('requests_longest');
