<?php
if (!isset($adminindex)){
    die("Abort." . __FILE__);
}
$sHtml = '';

/*
$sHtml .= "<h3>Work in progress</h3>file: " . __FILE__ . "<p>Update feature is disabled here so far. It would destroy a beta installation.</p>";
if (!strpos($_SERVER["SERVER_NAME"], "axel-hahn.de")){
    echo $sHtml;
    return true;
}
 * 
 */

require_once __DIR__ . '/../../classes/ahwi-installer.class.php';
$sApproot=dirname(dirname(__DIR__));
// $sApproot=dirname(dirname(__DIR__)).'/test-update';


$sZipfile = getTempdir() . '/__pimpapachestat-latest.zip';
$sTargetPath = $sApproot;

/*
$sLatestUrl=(stripos($aEnv["project"]["version"], "beta")) 
        ? $aEnv["links"]["update"]['downloadbeta']['url']
        : $aEnv["links"]["update"]['download']['url']
        ;
*/

$aUpdateInfos=getUpdateInfos();
$sLatestUrl=$aUpdateInfos['download'];

$oInstaller=new ahwi(array(
    'product'=>'dummy',
    'source'=>$sLatestUrl,
    'installdir'=>$sApproot,
    'tmpzip'=>$sZipfile,
    'checks'=>array(
        'phpversion'=>'5.3',
        'phpextensions'=>array('curl')
    ),
));

if (!array_key_exists('doinstall', $_GET)) {
    // ------------------------------------------------------------
    // step 1: welcome
    // ------------------------------------------------------------
    $aUpdateInfos=getUpdateInfos(true);
    $sHtml .= '<h4 id="h3' . md5('update') . '">'. $aLangTxt["lblUpdate"] . '</h4>'
            . '<div class="subh3">'
            . '<div class="hintbox">'
            . ($aUpdateInfos['flag_update']
                ? $aLangTxt['lblUpdateNewerVerionAvailable'].'<br>'
                : $aLangTxt['lblUpdateNoNewerVerionAvailable'].'<br>'
                )
            . '</div>'
            . sprintf($aLangTxt["lblUpdateHints"], $sLatestUrl)
            . sprintf($aLangTxt['lblUpdateInstalldir'], $oInstaller->getInstalldir())
            . '</div>'
            . '<a href="' . getNewQs(array('doinstall' => 'download')) . '"'
            . ' class="btn btn-default"'
            . '>' . $aLangTxt["lblUpdateContinue"] . '</a>'
            ;
    
} else {
    $sOutput='';
    switch ($_GET['doinstall']) {
        
        // ------------------------------------------------------------
        // step 2: download 
        // ------------------------------------------------------------
        case 'download':
            $sHtml .= '<h4 id="h3' . md5('update') . '">'. $aLangTxt["lblUpdate"] . '</h4>'
                    . '<div class="subh3">';

            if (file_exists($sZipfile)) {
                unlink($sZipfile);
            }
            
            ob_start();
            $bDownload=$oInstaller->download(false);
            $sOutput.=str_replace("\n", "<br>", ob_get_contents());
            ob_end_clean();
            if($bDownload){
                $sHtml.='<br><strong>'.$aLangTxt['lblUpdateDonwloadDone'].'</strong><br><br>'
                        . sprintf($aLangTxt['lblUpdateInstalldir'], $oInstaller->getInstalldir())
                        . '</div><a href="' . getNewQs(array('doinstall' => 'unzip')) . '"'
                        . ' class="btn btn-default"'
                        . '>' . $aLangTxt["lblUpdateContinue"] . '</a>';
            } else {
                $sHtml.=$aLangTxt['lblUpdateDonwloadFailed'] . '</div>';
            }
            break;
            
        // ------------------------------------------------------------
        // step 3: unzip downloaded file
        // ------------------------------------------------------------
        case 'unzip':
            
            $sHtml .= '<h4 id="h3' . md5('update') . '">'. $aLangTxt["lblUpdate"] . '</h4>'
                    . '<div class="subh3">'
                    . sprintf($aLangTxt['lblUpdateUnzipFile'], $sZipfile, $sTargetPath) 
                    . '<br><br>';
            
            ob_start();
            $bInstall=$oInstaller->install();
            $sOutput.=str_replace("\n", "<br>", ob_get_contents());
            ob_end_clean();
            
            if ($bInstall){
                $sHtml.=$aLangTxt['lblUpdateUnzipOK'] . '</div>'
                    . '<a href="../?"'
                        . ' class="btn btn-default"'
                        . '>' . $aLangTxt["lblUpdateContinue"] . '</a>';
            } else {
                $sHtml.=$aLangTxt['lblUpdateUnzipFailed'] . '</div>';
            }
            break;
        /*
        case 'postunzip':
            $content = '<h3 id="h4' . md5($sServer) . '">' . $aLangTxt["lblUpdate"] . '</h4>'
                    . '<div class="h3">';
            $content.='</div>';
            break;
        */
        default:
            break;
    }
    $sHtml.=$sOutput ? '<br><br>'.$aLangTxt['lblUpdateOutput'].':<br><pre class="output">'.$sOutput.'</pre>' : '';
}

echo $sHtml;
