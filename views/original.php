<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: ORIGINAL server-status
 */


$content='';
$sJsOnReady.='
	//On Click Event
	$(".subh2 ul.nav li").click(function() {
		$(this.parentNode).find("li").removeClass("active"); //Remove any "active" class
		$(this).addClass("active"); //Add "active" class to selected tab
		return false;
	});
        
        $(".subh2 div ul.nav li a").filter(":first").trigger("click");
';


$sMenuServer='';
if (count($aSrvStatus)>1){
    foreach ($aSrvStatus as $sHost=>$aData){
        // $sMenuServer.='<li><a href="#" onclick="return showwebserver(\'#h3'.md5($sHost).'\');">'.$sHost.'</a></li>';
        
        $aTab[$sHost] = array(
            'url' => '#',
            'label' => $sHost,
            'onclick' => 'return showTab(\'#h3' . md5($sHost) . '\');',
        );

    }
    $content.= $oDatarenderer->renderTabs($aTab);
    // $content.='<div><ul class="tabs">'.$sMenuServer.'</ul></div><div style="clear: both"></div>';
}
foreach($aSrvStatus as $sServer=>$aData){
    $content.='<h3 id="h3'.md5($sServer).'">'.$sServer.'</h3><div class="subh3" style="font-family: \'lucida console\'; font-size: 80%;">'.
        '<div class="console">'.utf8_encode($aData['orig']).'</div>
        </div>';
}

// echo $content;
