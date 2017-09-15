<?php
if (!isset($adminindex)){
    die("Abort.");
}


    require_once(__DIR__ . '/../../classes/cdnorlocal.class.php');
    
    $sVendorUrl=(strpos($_SERVER['REQUEST_URI'], '/admin/?') ? '.' : '') . './vendor/';
    $oCdn = new axelhahn\cdnorlocal(array(
        'vendordir'=>__DIR__ . '/../../vendor', 
        'vendorurl'=>$sVendorUrl, 
        'debug'=>0
    ));
    $oCdn->setLibs($aEnv['vendor']);
    
    $sHtml='
        <div class="subh3">
            <div class="hintbox">' . $aLangTxt["AdminHintVendor"] . '</div>'
            ;
    
    foreach($oCdn->getLibs() as $aLib){
        $sHtml.='<strong>'
                    .$aCfg['icons']['adminvendor']
                    .$aLib['lib']
                .'</strong>'
                .' '
                .$aLib['version']
                .' ... '
                .($aLib['islocal']
                    ? $aCfg['icons']['vendorLocal']
                    : $aCfg['icons']['vendorCDN']
                )
                .'<br>'
                // .'<br>'
                ;
    }
    
    echo $sHtml;
    // echo 'Libs:<br><pre>'. print_r($oCdn->getLibs(),1). '</pre>---<br>';    
