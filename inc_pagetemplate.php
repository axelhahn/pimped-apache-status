<?php


$oLog->add('load out_html.php in the skin directory');
if (!include(__DIR__ . '/templates/' . $aEnv["active"]["skin"] . '/' . $aCfg['defaultTemplate'])) {
    die('ERROR: Template could not be included: ' . './templates/' . $aCfg['skin'] . '/' . $aCfg['defaultTemplate'] . '.<br>Check the values "skin" and "defaultTemplate" in your configuration.');
}
