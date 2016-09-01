<?php
if (!isset($adminindex)){
    die("Abort.");
}

require_once '../classes/configserver.class.php';
global $oCS;
$oCS=new configServer();

$sHtml='';


// ----------------------------------------------------------------------
// ACTIONS
// ----------------------------------------------------------------------

if($sAppAction){
    $aResult=array();
    switch ($sAppAction){
        
        case 'addgroup':
            $sValue=$_POST['label'];
            $aResult=$oCS->addGroup($_POST);
            break;
        case 'deletegroup':
            $aResult=$oCS->deleteGroup($_POST);
            break;
        case 'updategroup':
            $aResult=$oCS->setGroup($_POST);
            break;
        
        case 'addserver':
            $aResult=$oCS->addServer($_POST);
            break;
        case 'deleteserver':
            $aResult=$oCS->deleteServer($_POST);
            break;
        case 'updateserver':
            $aResult=$oCS->setServer($_POST);
            break;
        
        default:
            $oMsg->add("SKIP: action $sAppAction is not implemented (yet).", 'error');
    }

    if(count($aResult)){
        $sLabel=  array_key_exists('label', $_POST) ? $_POST['label'] : $_POST['oldlabel'];
        if ($aResult['result']){
            $oMsg->add(sprintf($aLangTxt['AdminMessageServer-'.$sAppAction .'-ok'], $sLabel) , 'success');
        } else {
            $oMsg->add(sprintf($aLangTxt['AdminMessageServer-'.$sAppAction .'-error'], $sLabel), 'error');
            // $oMsg->add('ERROR: '. $sAppAction . ' - '.$aResult['error'].' - data: ' . print_r($_POST, 1), 'error');
        }
    }
    /*
    $sMarkId='divfrm-'.md5($sGroup ).'-'.md5($sId);
    $sHtml.='<style>#'.$sMarkId.'{border-left: 2px solid #fc2;}</style>';
    */
}


        
// ----------------------------------------------------------------------
// OUTPUT
// ----------------------------------------------------------------------

$sHtml.='<h3>'.$aLangTxt['AdminLblServers'].'</h3>'
        . '<div class="subh3">' 
        . '<div class="hintbox">'
        . $aLangTxt['AdminHintServers']
        . '</div>'
        ;


if(count($oCS->getGroups())){
    $sDivNewGroup='divAddGroup';
    $sHtml.=''
        . '<button class="btn btn-success" onclick="$(\'#'.$sDivNewGroup.'\').slideToggle();">'
            . '<i class="fa fa-plus"></i> '. $aLangTxt['ActionAddServergroup']
        . '</button>'
        . '<br>'
        . '<div class="divServergroup">'

            . '<div id="'.$sDivNewGroup.'" class="divNew">'
            . '<p>'.$aLangTxt['AdminServersLblAddGroup'].'</p>'
            . $oCS->renderFormGroup().'<br>'
            . '</div><br>'
            ;
            foreach($oCS->getGroups() as $sGroup){
                $sDivNew='divAddServer' . md5($sGroup);
                $sHtml.=''
                        . $oCS->renderFormGroup($sGroup).'<br>'

                        . '<div style="margin-left: 3%" class="">'
                        . '<button class="btn btn-success" onclick="$(\'#'.$sDivNew.'\').slideToggle();">'
                            . '<i class="fa fa-plus"></i> '. $aLangTxt['ActionAddServer']
                        . '</button>'
                        . '<br>'

                        . '<div id="'.$sDivNew.'" class="divNew">'
                        . '<p>'.$aLangTxt['AdminServersLblAddServer'].'</p>'
                        . $oCS->renderFormServer($sGroup).'<br>'
                        . '</div><br>'
                        ;

                $aServers=$oCS->getServers($sGroup);
                if(count($aServers)){
                    foreach ($aServers as $sId){
                        $sHtml.=$oCS->renderFormServer($sGroup, $sId);
                    }
                }
                $sHtml.='</div><br><br><br>';
            }
        $sHtml.='</div>';
        $sGroup=(array_key_exists('group', $_POST)&&$_POST['group']) ? $_POST['group'] : false;
        $sLabel=(array_key_exists('label', $_POST)&&$_POST['label']) ? $_POST['label'] : false;
        if($sGroup){
            $sHtml.="\n\n".'<script>'
                . '$(function() {
                    $(\'#'.$oCS->getDivId($sGroup).'\').addClass("lastsave");
                    $(\'#'.$oCS->getDivId($sLabel).'\').addClass("lastsave");
                    $(\'#'.$oCS->getDivId($sGroup, $sLabel).'\').addClass("lastsave");
                    });'
                . '</script>';
        }
    }
    $sHtml.='</div>';
        
        
echo $sHtml;