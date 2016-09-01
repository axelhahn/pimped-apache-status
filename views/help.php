<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: HELP
 */

// ----------------------------------------------------------------------
// Legende
// ----------------------------------------------------------------------
    $s='';
    foreach ($oDatarenderer->getCssClasses() as $sGroup=>$aData){
        
        $s.= '<br><strong>'.$aLangTxt["cmtLegend".$sGroup].'</strong><br><br><div style="margin: 0 30% 0 20px; ">';
        foreach($aData as $key=>$aProperties){
            $sComment="key " . $key;
            if (array_key_exists("cmtStatus".$key, $aLangTxt))$sComment=$aLangTxt["cmtStatus".$key];
            if (array_key_exists("cmtRequest".$key, $aLangTxt))$sComment=$aLangTxt["cmtRequest".$key];
            if (array_key_exists("cmtexectime".$key, $aLangTxt))$sComment=$aLangTxt["cmtexectime".$key];
            
            $s.='<div class="'.$aProperties["class"].'">'.$sComment;
            if ($key=="warning" || $key=="critical"){
                $s.=" (&gt;".$aCfg["execTimeRequest"][$key]."s)";
            }
            $s.='</div>';
        }
        $s.='</div><br>';
    }

// ----------------------------------------------------------------------
// Output
// ----------------------------------------------------------------------

$content='
        <h3>'.$aCfg['icons']['help-doc'] .' '. $aLangTxt['lblHelpDoc'].'</h3>
        <div class="subh3">
            <div class="hintbox">'. 
                $aLangTxt['lblHintHelpDoc'].
            '</div>'.
            $aLangTxt['lblHelpDocContent'].
            '<ul>' . $oDatarenderer->renderLI($aEnv["links"]["project"]) . '</ul>'.
        '
            <p>'.
                $aLangTxt['lblHelpBookmarklet'].
                $oDatarenderer->genBookmarklet().'
            </p>
        </div>
        <br>
        
        <h3>'.$aCfg['icons']['help-color'] .' '. $aLangTxt['lblHelpColors'].'</h3>
        <div class="subh3">
            <div class="hintbox">'. 
                $aLangTxt['lblHintHelpColors'].
            '</div>'.
            $s.
        '</div>
            
        <h3>'.$aCfg['icons']['help-thanks'] .' '. $aLangTxt['lblHelpThanks'].'</h3>
        <div class="subh3">
            <div class="hintbox">'. 
                $aLangTxt['lblHintHelpThanks'].
            '</div>'
        . 
            $aLangTxt['lblHelpThanksContent']
        .'</div>'
        ;
