<?php
if (!isset($adminindex)){
    die("Abort.");
}

    require_once(__DIR__ . '/../../classes/cdnorlocal-admin.class.php');
    
    $sVendorUrl=(strpos($_SERVER['REQUEST_URI'], '/admin/?') ? '.' : '') . './vendor/';
    $oCdn = new axelhahn\cdnorlocaladmin(array(
        'vendordir'=>__DIR__ . '/../../vendor', 
        'vendorurl'=>$sVendorUrl, 
        'debug'=>0
    ));
    $oCdn->setLibs($aEnv['vendor']);
    
    // --- donwload or delete a library?
    $sLib2download=(array_key_exists('download', $_GET))?$_GET['download']:'';
    $sLib2delete=(array_key_exists('delete', $_GET))?$_GET['delete']:'';
    $sVersion2delete=(array_key_exists('version', $_GET))?$_GET['version']:'';
    
    $sHtml='
        <div class="subh3">
            <div class="hintbox">' . $aLangTxt["AdminHintVendor"] . '</div>'
            
            . ($sLib2delete.$sLib2download ? '<a href="?'. getNewQs(array('delete'=>'', 'download'=>'')).'" class="btn btn-default">OK</a>' : '')

            . '<table class="dataTable table-hover">'
            . '<thead>'
            . '<tr>'
                . '<th>'.$aLangTxt["AdminVendorLib"].'</th>'
                . '<th>'.$aLangTxt["AdminVendorVersion"].'</th>'
                . '<th>'.$aLangTxt["AdminVendorRemote"].'</th>'
                . '<th>'.$aLangTxt["AdminVendorLocal"].'</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody>'
            ;
    
    foreach($oCdn->getLibs(true) as $sLibname=>$aLib){
        
        // --- download
        if ($sLib2download && $aLib['lib']===$sLib2download && !$aLib['islocal']){
            // $sHtml.='downloading '.$sLib2download.'...<br>';
            $oCdn->downloadAssets($sLib2download, $aLib['version']);
            echo "<script>window.setTimeout('location.href=\"?&action=vendor\"', 20);</script>";
            $oCdn->setLibs($aEnv['vendor']);
        }
        // --- delete
        if ($sLib2delete && $aLib['lib']===$sLib2delete && $aLib['islocal']){
            // $sHtml.='deleting '.$sLib2delete.'...<br>';
            $oCdn->delete($sLib2delete, $sVersion2delete);
            echo "<script>window.setTimeout('location.href=\"?&action=vendor\"', 20);</script>";
            $oCdn->setLibs($aEnv['vendor']);
        }
        $sHtml.='<tr>'
                . '<td><strong>'
                    .$aCfg['icons']['adminvendor']
                    .$aLib['lib']
                .'</strong></td>'
                .'<td>'
                    .$aLib['version']
                    .(isset($aLib['isunused']) && $aLib['isunused'] ? ' ('.$aLangTxt['AdminVendorLibUnused'].')' : '')
                .'</td>'
                .'<td>'
                .($aLib['islocal']
                
                    ? '</td><td><button onclick="location.href=\''. getNewQs(array('delete'=>$aLib['lib'], 'version'=>$aLib['version'])).'\';" class="btn btn-danger"'
                        . ' title="'.$aLangTxt['ActionDeleteHint'].'"'
                        . '>'.$aCfg['icons']['actionDelete'].$aLangTxt['ActionDelete'].'</button></td>'
                        .'</td>'
                
                    : ''
                            .'<button onclick="location.href=\''. getNewQs(array('download'=>$aLib['lib'])).'\';" class="btn btn-success"'
                        . ' title="'.$aLangTxt['ActionDownloadHint'].'"'
                        . '>'.$aCfg['icons']['actionDownload'].$aLangTxt['ActionDownload'].'</a></td><td></td>'
                )
                // .'<br>'
                .'</tr>'
                ;
    }
    $sHtml.='</tbody></table>';
    
    $iCount=count($oCdn->getLibs());
    $iCountLocal=count($oCdn->getFilteredLibs(array('islocal'=>1,'isunused'=>0)));
    $iCountUnused=count($oCdn->getFilteredLibs(array('islocal'=>1,'isunused'=>1)));
    
    echo (($iCount && $iCount===$iCountLocal)
            ? sprintf($aLangTxt["AdminVendorLibAllLocal"], $iCount)
            : sprintf($aLangTxt["AdminVendorLibLocalinstallations"], $iCount, $iCountLocal)
    ).'<br>'
    .($iCountUnused ? sprintf($aLangTxt["AdminVendorLibDelete"], $iCountUnused).'<br>' : '')
    .$sHtml;
    // echo 'Libs:<br><pre>'. print_r($oCdn->getLibs(),1). '</pre>---<br>';    
