<?php
if (!isset($adminindex)){
    die("Abort." . __FILE__);
}

$content = '<h3><i class="fa fa-flash"></i> Update</h3>'
        . '<div class="hintbox">'
        . $aLangTxt['AdminHintUpdates']
        . '</div>'
        . $oMsg->render()
        . '';

$content = '';

$sApproot=dirname(__DIR__);


$sZipfile = $sApproot . '/__pimpapachestat-latest.zip';
$sTargetPath = $sApproot;
$sLatestUrl=$aEnv["links"]["update"]['download']['url'];

if (!array_key_exists('doinstall', $_GET)) {
    // ------------------------------------------------------------
    // step 1: welcome
    // ------------------------------------------------------------
    $sUpdateInfo=checkUpdate(true);
    $content .= '<h3 id="h3' . md5('update') . '">'. $aLangTxt["lblUpdate"] . '</h3>'
            . '<div class="subh3"><br>'
            . (hasNewVersion($sUpdateInfo)
                ?' '.$aLangTxt['lblUpdateNewerVerionAvailable'].'<br>'
                :'<div class="hintbox">'.$aLangTxt['lblUpdateNoNewerVerionAvailable'].'</div>'
                )
            . '<br>'
            . sprintf($aLangTxt["lblUpdateHints"], $sLatestUrl)
            . '</div>'
            . '<a href="' . getNewQs(array('doinstall' => 'download')) . '"'
            . ' class="btn btn-default"'
            . '>' . $aLangTxt["lblUpdateContinue"] . '</a>';
} else {
    switch ($_GET['doinstall']) {
        
        // ------------------------------------------------------------
        // step 2: download 
        // ------------------------------------------------------------
        case 'download':
            $content .= '<h3 id="h3' . md5('update') . '">'. $aLangTxt["lblUpdate"] . '</h3>'
                    . '<div class="subh3">';


            if (file_exists($sZipfile)) {
                unlink($sZipfile);
            }
            
            $sLatestVersionUrl = httpFollowUrl($sLatestUrl);
			
			$content.= $sLatestUrl . '<br> --&gt; GET ' . $sLatestVersionUrl;
			$sData = httpGet($sLatestVersionUrl);
			$content.=' ('.strlen($sData) . ')<br>';
		
            if (strlen($sData) > 100000) {
                file_put_contents($sZipfile, $sData);
                if (file_exists($sZipfile)) {
                    $content.='<br><strong>'.$aLangTxt['lblUpdateDonwloadDone'].'</strong>'
                            . '</div><a href="' . getNewQs(array('doinstall' => 'unzip')) . '"'
                            . ' class="btn btn-default"'
                            . '>' . $aLangTxt["lblUpdateContinue"] . '</a>';
                } else {
                    $content.=$aLangTxt['lblUpdateDonwloadFailed'] . '</div>';
                }
            } else {
                $content.=$aLangTxt['lblUpdateDonwloadFailed'] . '</div>';
            }

            break;
            
        // ------------------------------------------------------------
        // step 3: unzip downloaded file
        // ------------------------------------------------------------
        case 'unzip':
            $content .= '<h3 id="h3' . md5('update') . '">'. $aLangTxt["lblUpdate"] . '</h3>'
                    . '<div class="subh3">';
            $content.=sprintf($aLangTxt['lblUpdateUnzipFile'], $sZipfile, $sTargetPath) . '<br><br>';
            
            $zip = new ZipArchive;
            
            $res = $zip->open($sZipfile);
            if ($res === TRUE) {
                // extract it to the path we determined above
                $zip->extractTo($sTargetPath);
                $zip->close();
                $content.=$aLangTxt['lblUpdateUnzipOK'] . '</div>'
                    . '<a href="?"'
                        . ' class="btn btn-default"'
                        . '>' . $aLangTxt["lblUpdateContinue"] . '</a>';
                unlink($sZipfile);
            } else {
                $content.=$aLangTxt['lblUpdateUnzipFailed'] . '</div>';
            }
            break;
        /*
        case 'postunzip':
            $content = '<h3 id="h3' . md5($sServer) . '">' . $aLangTxt["lblUpdate"] . '</h3>'
                    . '<div class="h3">';
            $content.='</div>';
            break;
        */
        default:
            break;
    }
}


// echo $content;