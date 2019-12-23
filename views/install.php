<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: INSTALL
 */

$sUser=($_POST && isset($_POST['username'])) ? $_POST['username'] : false;
$sContent='';
$sDummyUser='nouserprotection';
$aTC = array();
if(file_exists(__DIR__ . '/../config/config_user.php')){
    $aTC[] = array(
        'tab'=>$aLangTxt['lblInitialSetupTab0'],
        'content'=>$aLangTxt['lblHelplblInitialSetupTab0']
    );
}
if (!isset($_SERVER['HTTPS'])){
    $oMsg->add($aLangTxt['error-no-ssl'], 'error');
}

$aTC[] = array(
    'tab'=>$aLangTxt['lblInitialSetupTab1'],
    'content'=>$aLangTxt['lblHelplblInitialSetupTab1']
        . '<br><br>'
        . '<form class="form-horizontal" action="?" method="POST">'
        . '<div class="form-group">'
            . '<label class="col-sm-2">'.$aLangTxt['lblUsername'].'</label>'
            . '<div class="col-sm-3">'
                . '<input class="form-control" name="username" type="text" value="'.$sUser.'" placeholder="">'
            . '</div>'
        . '</div>'
        . '<div class="form-group">'
            . '<label class="col-sm-2">'.$aLangTxt['lblPassword'].'</label>'
            . '<div class="col-sm-3">'
                . '<input class="form-control" name="pw1" type="password" value="" placeholder="">'
            . '</div>'
        . '</div>'
        . '<div class="form-group">'
            . '<label class="col-sm-2">'.$aLangTxt['lblRepeatPassword'].'</label>'
            . '<div class="col-sm-3">'
                . '<input class="form-control" name="pw2" type="password" value="" placeholder="">'
            . '</div>'
        . '</div>'
    . '<button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> '.$aLangTxt['ActionOK'].'</button>'
    . '<div style="clear: both"></div>'
    . '</form>'
    ,
);
$aTC[] = array(
    'tab'=>$aLangTxt['lblInitialSetupTab2'],
    'content'=>$aLangTxt['lblHelplblInitialSetupTab2']
        . '<br><br>'
        . '<form class="form-horizontal" action="?" method="POST">'
                . '<input name="username" type="hidden" value="'.$sDummyUser.'">'
                . '<input name="pw1" type="hidden" value="" >'
                . '<input name="pw2" type="hidden" value="" >'
        . '<button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> '.$aLangTxt['lblInitialSetupTab2'].'</button>'
    . '</form>'
);

$sForm=(is_array($aUserCfg) && count($aUserCfg)) 
    ? $aLangTxt['lblInitialSetupAbort'] // Sorry, the initial setup was executed already. 
    : $sOldConfig . $oDatarenderer->renderTabbedContent($aTC);
; 

if(is_array($_POST) && count($_POST)){
    if ($_POST['username']
        && (
            ($_POST['pw1']
            && $_POST['pw2']
            && $_POST['pw1']===$_POST['pw2']
            )
            || ($_POST['username']===$sDummyUser && !$_POST['pw1'] && !$_POST['pw2'])
        )
    ){
        $dummy=$oCfg->getFullConfig("config_user");
        $aUsersetup=array(
            'auth'=>array(
                'user'=>$_POST['username']
            )
        );
        if ($_POST['pw1']){
            $aUsersetup['auth']['password']=md5($_POST['pw1']);
        } else {
            $aUsersetup['auth']=false;
        }
        $oCfg->set($aUsersetup);
        $oMsg->add($aLangTxt['lblInitialSetupSaved'], 'success');
        $sContent.=$aLangTxt['lblInitialSetupSaved']. '<br><br><a href="?" class="btn btn-primary">'.$aLangTxt['ActionContinue'].'</a>';
    } else {
        // data were posted but something is missing / pw do not match
        $oMsg->add($aLangTxt['lblInitialSetupSaveFailed'], 'error');
        $sContent.=$sForm;
    }
} else {
    $sContent.=$sForm;
}

// ----------------------------------------------------------------------
// Output
// ----------------------------------------------------------------------

$content=
        $oDatarenderer->themeBox(
                $aCfg['icons']['help-doc'] .' '. $aLangTxt['lblInitialSetup'],
                $sContent
        );
