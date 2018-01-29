<?php

/*
 * PIMPED APACHE-STATUS
 * FUNCTIONS (included by ./inc_config.php)
 * 
 */

// ----------------------------------------------------------------------
// FUNKTIONEN
// ----------------------------------------------------------------------

/**
 * get new querystring - create the new querystring by existing query string
 * of current request and given new parameters
 * @param array $aQueryParams
 * @return string
 */
function getNewQs($aQueryParams = array()) {
    $s = false;
    $aDelParams = array("doinstall");

    if ($_GET) {
        $aDefaults = $_GET;
        foreach ($aDelParams as $sParam) {
            if (array_key_exists($sParam, $aDefaults)) {
                unset($aDefaults[$sParam]);
            }
        }
        $aQueryParams = array_merge($aDefaults, $aQueryParams);
    }

    foreach ($aQueryParams as $var => $value) {
        if ($value)
            $s .= "&amp;" . $var . "=" . urlencode($value);
    }
    $s = "?" . $s;
    return $s;
}

/**
 * follow a given url by checking http header data and follow locations
 * @param string   $url          url to follow
 * @return string
 */
function httpFollowUrl($sUrl) {
    $sReturn = $sUrl;
    $sData = httpGet($sUrl, 1);
    preg_match('/Location:\ (.*)/', $sData, $aTmp);
    if (count($aTmp)) {
        $sNextUrl = trim($aTmp[1]);
        if ($sNextUrl && $sNextUrl !== $sUrl) {
            $sReturn = httpFollowUrl($sNextUrl);
        }
    }
    return $sReturn;
}

/**
 * make an http get request and return the response body
 * @param string   $url          url to fetch
 * @param boolean  $bHeaderOnly  send header only
 * @return string
 */
function httpGet($url, $bHeaderOnly = false) {
    global $oLog;
    $ch = curl_init($url);
    $oLog->add(__FUNCTION__ . "($url, $bHeaderOnly) - START");
    if ($bHeaderOnly) {
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
    } else {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'pimped apache status');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $res = curl_exec($ch);
    curl_close($ch);
    $oLog->add(__FUNCTION__ . "($url, $bHeaderOnly) - done.");
    return ($res);
}

/**
 * check authentication if a user and password were configured
 * @global array  $aCfg  config from ./config/config_user.php
 * @return boolean
 */
function checkAuth() {
    global $aCfg;
    global $aLangTxt;
    // echo '<pre>'.print_r($aCfg, 1).'</pre>'; die();
    if (
            !array_key_exists('user', $aCfg['auth']) || !array_key_exists('password', $aCfg['auth']) || (
            $aCfg['auth']['user'] == 'admin' && !$aCfg['auth']['password']
            )
    ) {
        return true;
    }

    if (
            array_key_exists('PHP_AUTH_USER', $_SERVER) && array_key_exists('PHP_AUTH_PW', $_SERVER) && $aCfg['auth']['user'] == $_SERVER['PHP_AUTH_USER'] && $aCfg['auth']['password'] == md5($_SERVER['PHP_AUTH_PW'])
    ) {
        return true;
    }

    header('WWW-Authenticate: Basic realm="Pimped Apache Status"');
    header('HTTP/1.0 401 Unauthorized');
    die($aLangTxt["authAccessDenied"]);
}

/**
 * check for an update of the product
 * @param bool  $bForce  force check and ignore ttl
 * @return type
 */
function checkUpdate($bForce = false) {
    global $aLangTxt;
    global $aEnv;
    global $aCfg;
    global $oLog;
    $sUrlCheck = str_replace(" ", "%20", $aEnv['links']['update']['check']['url']);
    $iTtl = (int) $aCfg["checkupdate"];
    $sTarget = "checkupdate_" . md5($sUrlCheck) . ".tmp";

    // if the user does not want an update check then respect it
    if (!$iTtl && !$bForce) {
        $oLog->add(__FUNCTION__ . ": is disabled");

        return '<a href="' . $aEnv['links']['update']['updater']['url'] . '"'
                . ' target="./admin/' . $aEnv['links']['update']['updater']['target'] . '&skin=' . $aEnv['active']['skin'] . '&lang=' . $aEnv['active']['lang'] . '"'
                . ' class="button"'
                . '>' . $aLangTxt['versionManualCheck'] . '</a>';
        // return false;
    }

    $bExec = true;
    if (file_exists($sTarget)) {
        $bExec = false;
        $aStat = stat($sTarget);
        $iAge = time() - $aStat[9];
        if ($iAge > $iTtl) {
            $bExec = true;
        }
        $oLog->add(__FUNCTION__ . " last exec: " . $iAge . " s ago - timer is $iTtl");
    } else {
        $oLog->add(__FUNCTION__ . " last exec: never (touchfile was not found)");
    }
    if ($bForce) {
        $bExec = true;
        $oLog->add(__FUNCTION__ . " last exec: override: force parameter was found");
    }

    if ($bExec) {
        $oLog->add(__FUNCTION__ . "fetching $sUrlCheck ...");
        $sResult = httpGet($sUrlCheck);
        if (!$sResult) {
            $sResult = ' <span class="version-updateerror">' . $aLangTxt['versionError'] . '</span>';
        } else {
            file_put_contents($sTarget, $sResult);
        }
    } else {
        $oLog->add(__FUNCTION__ . " reading cache $sTarget ...");
        $sResult = file_get_contents($sTarget);
    }

    $sVersion = str_replace("UPDATE: v", "", str_replace(" is available", "", $sResult));
    if (strpos($sResult, "UPDATE") === 0) {
        $sUrl = getNewQs()
                . '&lang=' . $aEnv['active']['lang']
                . '&skin=' . $aEnv['active']['skin']
                . '&action=update'
        ;
        $sResult = ' <span class="version-updateavailable" '
                . 'title="' . $sResult . '">'
                . '<a'
                . ' href="' . $sUrl . '"'
                . '>'
                . sprintf($aLangTxt['versionUpdateAvailable'], $sVersion)
                . '</a>'
                . '</span>';
    } else if (strpos($sResult, "OK") === 0) {
        $sResult = ' <span class="version-uptodate" title="' . $sResult . '">'
                . $aLangTxt['versionUptodate']
                . '</span>';
    }

    return '<span id="checkversion">' . $sResult . '</span>';
}

/**
 * return a bool only: does exist a newer version or not?
 * (used in views/update.php)
 * @param string  
 * @return bool
 */
function hasNewVersion($sUpdateOut = '') {
    $sResult = $sUpdateOut ? $sUpdateOut : checkUpdate(true);
    // echo htmlentities($sResult);
    return (strpos($sResult, "UPDATE") > 0 ? true : false);
}

function getHtmlHead($aLangTxt) {
    global $aEnv;
    
    require_once(__DIR__ . '/classes/cdnorlocal.class.php');
    
    $sVendorUrl=(strpos($_SERVER['REQUEST_URI'], '/admin/?') ? '.' : '') . './vendor/';
    $oCdn = new axelhahn\cdnorlocal(array(
        'vendordir'=>__DIR__ . '/vendor', 
        'vendorurl'=>$sVendorUrl, 
        'debug'=>0
    ));
    $aLangJs = array();
    
    foreach ($aLangTxt as $sKey => $sVal) {
        if (strpos($sKey, 'js::') === 0) {
            $aLangJs[str_replace('js::', '', $sKey)] = $sVal;
        }
    }
    $oCdn->setLibs($aEnv['vendor']);
    /*
    echo '<!-- ';
    print_r($oCdn->getLibs());
    // echo $oCdn->getLibVersion('jquery');
    echo $oCdn->getLibRelpath('jquery');
    echo '--> ';
     */
    
    $sHeader = '<script>var aLang=' . json_encode($aLangJs) . ';</script>' . "\n"

            // jQuery
            . '<script src="' . $oCdn->getFullUrl($oCdn->getLibRelpath('jquery')."/jquery.min.js") . '"></script>' . "\n"

            // datatbles
            . '<script src="' . $oCdn->getFullUrl($oCdn->getLibRelpath('datatables')."/js/jquery.dataTables.min.js") . '"></script>' . "\n"
            . '<link rel="stylesheet" href="' . $oCdn->getFullUrl($oCdn->getLibRelpath('datatables')."/css/jquery.dataTables.min.css") . '">' . "\n"

            // Admin LTE
            . '<script src="' . $oCdn->getFullUrl($oCdn->getLibRelpath('admin-lte')."/js/adminlte.min.js") . '" type="text/javascript"></script>' . "\n"
            . '<link rel="stylesheet" href="' . $oCdn->getFullUrl($oCdn->getLibRelpath('admin-lte')."/css/AdminLTE.min.css") . '">' . "\n"
            . '<link rel="stylesheet" href="' . $oCdn->getFullUrl($oCdn->getLibRelpath('admin-lte')."/css/skins/_all-skins.min.css") . '">' . "\n"

            // Bootstrap    
            . '<link href="' . $oCdn->getFullUrl($oCdn->getLibRelpath('twitter-bootstrap').'/css/bootstrap.min.css') . '" rel="stylesheet">'
            . '<link href="' . $oCdn->getFullUrl($oCdn->getLibRelpath('twitter-bootstrap').'/css/bootstrap-theme.min.css') . '" rel="stylesheet">'
            . '<script src="' . $oCdn->getFullUrl($oCdn->getLibRelpath('twitter-bootstrap').'/js/bootstrap.min.js') . '" type="text/javascript"></script>'

            // Font awesome
            . '<link href="' . $oCdn->getFullUrl('font-awesome/4.7.0/css/font-awesome.min.css') . '" rel="stylesheet">'

            // Morris
            . '<script src="' . $oCdn->getFullUrl("raphael/2.2.7/raphael.min.js") . '"></script>' . "\n"
            . '<script src="' . $oCdn->getFullUrl("morris.js/0.5.1/morris.min.js") . '"></script>' . "\n"

            // Knob
            . '<script src="' . $oCdn->getFullUrl("jQuery-Knob/1.2.13/jquery.knob.min.js") . '"></script>' . "\n"

    ;
    return $sHeader;
}
