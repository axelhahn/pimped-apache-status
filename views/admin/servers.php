<?php

if (!isset($adminindex)) {
    die("Abort.");
}

require_once '../classes/configserver.class.php';
global $oCS;
$oCS = new configServer();

$sHtml = '';


// ----------------------------------------------------------------------
// ACTIONS
// ----------------------------------------------------------------------

if ($sAppAction) {
    $aResult = array();
    switch ($sAppAction) {

        case 'addgroup':
            $sValue = $_POST['label'];
            $aResult = $oCS->addGroup($_POST);
            break;
        case 'deletegroup':
            $aResult = $oCS->deleteGroup($_POST);
            break;
        case 'updategroup':
            $aResult = $oCS->setGroup($_POST);
            break;

        case 'addserver':
            $aResult = $oCS->addServer($_POST);
            break;
        case 'deleteserver':
            $aResult = $oCS->deleteServer($_POST);
            break;
        case 'updateserver':
            $aResult = $oCS->setServer($_POST);
            break;

        default:
            $oMsg->add("SKIP: action $sAppAction is not implemented (yet).", 'error');
    }

    if (count($aResult)) {
        $sLabel = array_key_exists('label', $_POST) ? $_POST['label'] : $_POST['oldlabel'];
        if ($aResult['result']) {
            $oMsg->add(sprintf($aLangTxt['AdminMessageServer-' . $sAppAction . '-ok'], $sLabel), 'success');
        } else {
            $oMsg->add(sprintf($aLangTxt['AdminMessageServer-' . $sAppAction . '-error'], $sLabel), 'error');
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
?>
<script>

    function createSrvFilter(){
    var sHtml = '';
    var sOptions = '<option value="">-</option>';
    $('h3').each(function () {
        sOptions += '<option value="' + $(this).parent().attr("id") + '">' + $(this).html() + '</option>';
    });
    sHtml += '\n\
        <form class="form-inline"><div class="form-group">\n\
            <select id="selGroup" onchange="doFilter()">' + sOptions + '</select>\n\
            \n\
            <input type="text" id="eFilterServer"\n\
            onchange="doFilter()" onkeydown="doFilter()" onkeyup="doFilter()"\n\
            placeholder="'+aLang['srvFilterPlaceholder']+'" \n\
            value=""\n\
            >\n\
        </div></form>\n\
            ';
    $('#divServerFilter').html(sHtml);
    }

    function doFilter(){
    var iCount = 0;
    var s = $('#eFilterServer').val();
    var sGroup = $('#selGroup').val();
    
    if (sGroup){
        $('.divGroup').css("display", "none");
        $('#'+sGroup).css("display", "block");
    } else {
        $('.divGroup').css("display", "block");
    }
    
    $('.divServer').each(function () {
        if (!s || $(this).html().indexOf(s) >= 0){
        iCount++;
        $(this).css("display", "block");
        } else {
        $(this).css("display", "none");
    }

    });
    }

    // window.setTimeout("createSrvFilter();", 200);
</script>

<?php

if (!isset($_SERVER['HTTPS'])){
    $oMsg->add($aLangTxt['error-no-ssl'], 'error');
}

$sHtml.='<h4>' . $aLangTxt['AdminLblServers'] . '</h4>'
        . '<div class="subh3">'
        . '<div class="hintbox">'
        . $aLangTxt['AdminHintServers']
        . '</div>'
        . '<div id="divServerFilter"></div>'
        . '<br>'
;



// loop over groups
if (count($oCS->getGroups())) {
    // add a group
    
    $sHtml.=''
        . '<div class="divServergroup">'
            . $oCS->renderFormGroup() . '<br>'
        ;
    foreach ($oCS->getGroups() as $sGroup) {
        
        // add a server
        $sDivNew = 'divAddServer' . md5($sGroup);
        $sHtml.=''
                . $oCS->renderFormGroup($sGroup) . '<br>'
                . '<div style="margin-left: 3%" class="">'
                // . '<div id="' . $sDivNew . '" class="divNew">'
                . $oCS->renderFormServer($sGroup) . '<br>'
                // . '</div><br>'
        ;

        // show servers of the current group
        $aServers = $oCS->getServers($sGroup);
        if (count($aServers)) {
            foreach ($aServers as $sId) {
                $sHtml.=$oCS->renderFormServer($sGroup, $sId);
            }
        }
        $sHtml.='</div><br><br><br>';
    }
    
    // highlight saved items
    $sHtml.='</div>';
    $sGroup = (array_key_exists('group', $_POST) && $_POST['group']) ? $_POST['group'] : false;
    $sLabel = (array_key_exists('label', $_POST) && $_POST['label']) ? $_POST['label'] : false;
    if ($sGroup) {
        $sHtml.="\n\n" . '<script>'
                . '$(function() {
                    $(\'#' . $oCS->getDivId($sGroup) . '\').addClass("lastsave");
                    $(\'#' . $oCS->getDivId($sLabel) . '\').addClass("lastsave");
                    $(\'#' . $oCS->getDivId($sGroup, $sLabel) . '\').addClass("lastsave");
                    });'
                . '</script>';
    }
}
$sHtml.='</div>';


echo $sHtml;
