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
    
    $sHtml='
        <div class="subh3">
            <div class="hintbox">' . $aLangTxt["AdminHintVendor"] . '</div>'
            
            . ($sLib2delete.$sLib2download ? '<a href="?'. getNewQs(array('delete'=>'', 'download'=>'')).'" class="btn btn-default">OK</a>' : '')

            . '<table class="dataTable table-hover">'
            . '<thead>'
            . '<tr>'
                . '<th>'.$aLangTxt["AdminVendorLib"].'</th>'
                . '<th>'.$aLangTxt["AdminVendorVersion"].'</th>'
                . '<th>'.$aLangTxt["AdminVendorLocal"].'</th>'
                . '<th>'.$aLangTxt["AdminVendorRemote"].'</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody>'
            ;
    
    foreach($oCdn->getLibs() as $aLib){
        
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
            $oCdn->delete($sLib2delete, $aLib['version']);
            echo "<script>window.setTimeout('location.href=\"?&action=vendor\"', 20);</script>";
            $oCdn->setLibs($aEnv['vendor']);
        }
        
        $sHtml.='<tr>'
                . '<td><strong>'
                    .$aCfg['icons']['adminvendor']
                    .$aLib['lib']
                .'</strong></td>'
                .'<td>'.$aLib['version'].'</td>'
                .'<td>'
                .($aLib['islocal']
                
                    ? '<a href="'. getNewQs(array('delete'=>$aLib['lib'])).'" class="btn btn-default">'.$aCfg['icons']['vendorLocal'].' delete</a></td>'
                        .'</td><td></td>'
                
                    : '</td><td>'
                            .'<a href="'. getNewQs(array('download'=>$aLib['lib'])).'" class="btn btn-default">'.$aCfg['icons']['vendorCDN'].'download</a></td>'
                )
                // .'<br>'
                .'</tr>'
                ;
    }
    $sHtml.='</tbody></table>';
    
    echo $sHtml;
    // echo 'Libs:<br><pre>'. print_r($oCdn->getLibs(),1). '</pre>---<br>';    
