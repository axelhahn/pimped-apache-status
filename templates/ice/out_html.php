<?php
/*
 * PIMPED APACHE-STATUS
 * TEMPLATE "ICE".
 * 
 * It includes the default template and uses its own CSS file.
 */

include (dirname(__DIR__).'/default/out_html.php');
$sHeader=str_replace('default/style.css', basename(__DIR__).'/style.css', $oPage->getHeader());
$oPage->setHeader($sHeader);
