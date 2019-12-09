<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: LOGIN
 */

// remove menu items
unset($aEnv["links"]["servers"]);
unset($aEnv["links"]["reload"]);
unset($aEnv["links"]["views"]);
unset($aEnv["links"]["viewsadmin"]);

$sUser=($_POST && isset($_POST['username'])) ? $_POST['username'] : false;

if (!isset($_SERVER['HTTPS'])){
    $oMsg->add($aLangTxt['error-no-ssl'], 'error');
}

$sHtml=''
        .(isset($_SESSION['lastUser']) && $_SESSION['lastUser']
            ? 
                '<div class="hintbox">' . $aLangTxt['lblLoginIsAuthenticated'] . '</div>'
                .'<br><a href="?logout" class="btn btn-danger">'
                    . $aCfg['icons']['logout'] . sprintf($aLangTxt['lblLoginDoLogout'], $_SESSION['lastUser'])
                .'</a>'
            : 
                '<div class="hintbox">' . $aLangTxt['lblLoginHint'] . '</div>'
                . '<form class="form-horizontal" action="?'.$_SERVER['QUERY_STRING'].'" method="POST">'
                . '<div class="form-group">'
                    . '<label class="col-sm-2">'.$aLangTxt['lblUsername'].'</label>'
                    . '<div class="col-sm-3">'
                        . '<input class="form-control" name="username" type="text" value="'.$sUser.'" placeholder="">'
                    . '</div>'
                . '</div>'
                . '<div class="form-group">'
                    . '<label class="col-sm-2">'.$aLangTxt['lblPassword'].'</label>'
                    . '<div class="col-sm-3">'
                        . '<input class="form-control" name="password" type="password" value="" placeholder="">'
                    . '</div>'
                . '</div>'

                . '<button class="btn btn-primary" type="submit"    ><i class="fas fa-check"></i> '.$aLangTxt['ActionLogin'].'</button>'
                . '<div style="clear: both"></div>'
            . '</form>'
        )
;
$content=
        $oDatarenderer->themeBox(''
                // $aCfg['icons']['help-doc'] .' '
                . $aLangTxt['lblLogin'],

                $sHtml
        );
