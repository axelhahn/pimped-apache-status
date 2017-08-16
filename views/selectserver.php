<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: SELECT SERVER
 */

$content='';
foreach ($aEnv["links"]["servers"] as $sKey=>$aLinks){
    $content.='<strong>'.$oDatarenderer->renderA($aEnv["links"]["servers"][$sKey]).'</strong><br>';
    foreach ($aLinks["subitems"] as $aLink){
            $content.=$oDatarenderer->renderA($aLink) . ' | ';
    }
    $content.='<br>';
}
// $content.='<hr>'.$oDatarenderer->renderLI($aEnv["links"]["servers"]);
// $content.="<pre>".print_r($aEnv["links"]["servers"], true)."</pre>";

// ----------------------------------------------------------------------
// Output
// ----------------------------------------------------------------------


echo $content;
