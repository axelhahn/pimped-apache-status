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
 * make an http get request and return the response body
 * @global array   $oLog         logger class
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
 * @global array $aCfg      configuration settings
 * @global array $aLangTxt  language specific texts
 * @return boolean
 */
function checkAuth() {
    global $aCfg;
    global $aLangTxt;    
    if (
            !$aCfg['auth'] || !array_key_exists('user', $aCfg['auth']) || !array_key_exists('password', $aCfg['auth']) 
            || ($aCfg['auth']['user'] == 'admin' && !$aCfg['auth']['password'] )
    ) {
        return true;
    }
        
    session_start([
        'cookie_lifetime' => 86400,
    ]);
    
    // logout on [anyurl]?logout
    if(isset($_SESSION['lastUser']) && $_SESSION['lastUser']){
        if ($_SERVER['QUERY_STRING']==='logout'){
            session_destroy();
            header('location: ?');
        }
    }
    if(isset($_POST['username'])){
        $_SESSION['lastUser']=isset($_POST['username']) ? $_POST['username'] : '';
        $_SESSION['lastPasswordhash']=isset($_POST['password']) ? md5($_POST['password']) : '';
    } else {
        $_SESSION['lastUser']=isset($_SESSION['lastUser']) ? $_SESSION['lastUser'] : '';
        $_SESSION['lastPasswordhash']=isset($_SESSION['lastPasswordhash']) ? $_SESSION['lastPasswordhash'] : '';
    }
    // react if the user or pw were changed in the config
    if (
            $aCfg['auth']['user'] == $_SESSION['lastUser'] && $aCfg['auth']['password'] == $_SESSION['lastPasswordhash']
    ) {
        return true;
    }
    $_SESSION['lastUser']='';
    $_SESSION['lastPasswordhash']='';
    
    session_destroy();
    return false;
}

/**
 * search temp directory using tmpdir in config. if false then use system
 * temp dir
 * @global array $aCfg      configuration settings
 * @global array $oLog      logger class
 * @param bool  $bForce  force check and ignore ttl
 * @return type
 */
function getTempdir(){
    global $aCfg;
    global $oLog;
    $oLog->add(__FUNCTION__ . '() start');
    $sTmpDir=(isset($aCfg['tmpdir']) && $aCfg['tmpdir']) ? $aCfg['tmpdir'] : sys_get_temp_dir();

    if($sTmpDir && !($sTmpDir[0]==='/' || $sTmpDir[1]===':')){
        $sTmpDir=__DIR__ . '/' . $sTmpDir;
    }
    if(!is_writable($sTmpDir)){
        echo 'WARNING: directory is not writable: '.$sTmpDir.' - check write access or set a new value for "tmpdir"<br>';
        $oLog->add(__FUNCTION__ . '() - directory is not writable: '.$sTmpDir.' - check write access or set a new value for tmpdir', 'error');
    }
    $oLog->add(__FUNCTION__ . '() - set temp dir '.$sTmpDir);
    return $sTmpDir;
}


/**
 * get array with update infos 
 * Array
 * (
 *     [flag_update] => 
 *     [message] => OK: this is the latest version.
 *     [clientversion] => 2.00.03
 *     [release] => stable
 *     [latest_version] => 2.00.03
 *     [download] => https://sourceforge.net/projects/pimpapachestat/files/latest/download
 * )
 * 
 * @global array $aCfg      configuration settings
 * @global array $aEnv      environment
 * @global array $aLangTxt  language specific texts
 * @global array $oLog      logger class
 * @return array
 */
function getUpdateInfos($bForce = false){
    global $aCfg;
    global $aEnv;
    global $aLangTxt;    
    global $oLog;
    $sUrlCheck = str_replace(" ", "%20", $aEnv['links']['update']['check']['url']);
    // $sTarget = getTempdir() . '/checkupdate_' . md5($sUrlCheck) . '.tmp';
    $sTarget = getTempdir() . '/checkupdate.tmp';
    $bExec = true;
    $iTtl = (int) $aCfg["checkupdate"];
    
    // defaults:
    $sLatestUrl=(stripos($aEnv["project"]["version"], "beta")) 
            ? $aEnv["links"]["update"]['downloadbeta']['url']
            : $aEnv["links"]["update"]['download']['url']
            ;

    $aDefault=array(
        'flag_update'=>false,
        'message'=>'Request failed.',
        'latest_version'=>'unknown',
        'download'=>$sLatestUrl
    );
    
    // 
    if ($bForce) {
        $bExec = true;
        $oLog->add(__FUNCTION__ . " last exec: override: force parameter was found");
    } else if (file_exists($sTarget)) {
        $bExec = false;
        $iAge = time() - filemtime($sTarget);
        if ($iAge > $iTtl) {
            $bExec = true;
        }
        $oLog->add(__FUNCTION__ . " last exec: " . $iAge . " s ago - timer is $iTtl");
    } else {
        $oLog->add(__FUNCTION__ . " last exec: never (touchfile was not found)");
    }

    if ($bExec) {
        $oLog->add(__FUNCTION__ . " fetching $sUrlCheck ...");
        $sResult = httpGet($sUrlCheck);
        if (!$sResult) {
            $sResult = ' <span class="version-updateerror">' . $aLangTxt['versionError'] . '</span>';
            $oLog->add(__FUNCTION__ . " unable to check version.");
            $aUpdateInfos=array();
        } else {
            $oLog->add(__FUNCTION__ . " <pre>$sResult</pre>");
            
            if (!@file_put_contents($sTarget, $sResult)){
                echo "WARNING: unable to write file [$sTarget] - This slows down the performance.<br>";
                $oLog->add(__FUNCTION__ . " unable to write file [$sTarget]", "error");
            }
            $aUpdateInfos = json_decode($sResult, 1);
        }
    } else {
        $oLog->add(__FUNCTION__ . " reading cache $sTarget ...");
        $sResult = file_get_contents($sTarget);
        $aUpdateInfos = json_decode($sResult, 1);
    }
    
    $oLog->add(__FUNCTION__ . " <pre>".print_r($aUpdateInfos, 1)."</pre>");
    return array_merge($aDefault, $aUpdateInfos);
}
/**
 * check for an update of the product
 * @global array $aCfg      configuration settings
 * @global array $aEnv      environment
 * @global array $aLangTxt  language specific texts
 * @global array $oLog      logger class
 * @param bool  $bForce  force check and ignore ttl
 * @return type
 */
function checkUpdate($bForce = false) {
    global $aCfg;
    global $aEnv;
    global $aLangTxt;
    global $oLog;
    $iTtl = (int) $aCfg["checkupdate"];

    // if the user does not want an update check then respect it
    if (!$iTtl && !$bForce) {
        $oLog->add(__FUNCTION__ . ": is disabled");
        return '<a href="'
            .(isset($_SERVER['REQUEST_URI']) && !strstr($_SERVER['REQUEST_URI'], '/admin/') ? './admin/' : '' )
            .'?action=update&skin=' . $aEnv['active']['skin'] . '&lang=' . $aEnv['active']['lang'] . '"'
            . ' class="button"'
            . '>' . $aLangTxt['versionManualCheck'] . '</a>'
            ;
    }
    $aUpdateInfos=getUpdateInfos($bForce);

    $sVersion = $aUpdateInfos['latest_version'];
    $bHasUpdate = $aUpdateInfos['flag_update'];
    if ($bHasUpdate) {
        $sUrl = getNewQs()
                . '&lang=' . $aEnv['active']['lang']
                . '&skin=' . $aEnv['active']['skin']
                . '&action=update'
        ;
        $sResult = ' <span class="version-updateavailable" title="' . $aUpdateInfos['message'] . '">'
                    . '<a href="' . $sUrl . '">'
                    . sprintf($aLangTxt['versionUpdateAvailable'], $sVersion)
                    . '</a>'
                . '</span>';
    } else {
        $sResult = ' <span class="version-uptodate" title="' . $aUpdateInfos['message'] . '">'
                . $aLangTxt['versionUptodate']
                . '</span>';
    }

    return '<div id="checkversion">' . $sResult . '</div>';
}

/**
 * render head section of html page
 * @global array $aEnv      environment
 * @param type $aLangTxt
 * @return string
 */
function getHtmlHead($aLangTxt) {
    global $aEnv;
    
    require_once(__DIR__ . '/classes/cdnorlocal.class.php');
    
    $sVendorUrl=(strpos($_SERVER['REQUEST_URI'], '/admin/') ? '.' : '') . './vendor/';
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
    $sHeader = '<script>var aLang=' . json_encode($aLangJs) . ';</script>' . "\n"

        // jQuery
        . '<script src="' . $oCdn->getFullUrl($oCdn->getLibRelpath('jquery')."/jquery.min.js") . '"></script>' . "\n"

        // datatables
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
        . '<link href="' . $oCdn->getFullUrl($oCdn->getLibRelpath('font-awesome').'/css/all.min.css') . '" rel="stylesheet">'

        // Chart.js
        . '<script src="' . $oCdn->getFullUrl($oCdn->getLibRelpath('Chart.js').'/Chart.min.js') . '" type="text/javascript"></script>'
    ;
    return $sHeader;
}
