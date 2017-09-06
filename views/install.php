<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: INSTALL
 */

$sUser=$_POST['username'];
$sContent='';
$sForm='<form class="form-horizontal" action="?" method="POST">'
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
    . '<button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> '.$aLangTxt['ActionOK'].'</button>'
    . '<div style="clear: both"></div>'
    . '</form>'
; 
if(is_array($_POST) && count($_POST)){
    if ($_POST['username']
        && $_POST['pw1']
        && $_POST['pw2']
        && $_POST['pw1']===$_POST['pw2']
    ){
        $dummy=$oCfg->get("config_user");
        $oCfg->set(array(
            'auth'=>array(
                'user'=>$_POST['username'],
                'password'=>md5($_POST['pw1']),
            )
        ));
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
                $aLangTxt['lblHelplblInitialSetup'].
                '<br><br>'
                .$sContent
        );
