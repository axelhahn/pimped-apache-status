<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * view: Utilisation of each server
 */

$iPlotterValues = 50;


// ----------------------------------------------------------------------
// functions
// ----------------------------------------------------------------------

function showHint($sMessage){
    return '<div class="hintbox">'.$sMessage.'</div><br>';
}

function showPlotter($sHost, $sCounterItem, $iPlotterValues, $sDescription, $sTitle, $iMax, $sCounterItem2=''){
    global $aCfg, $sJsOnReady;
    $sReturn='';
    static $iPlotterCounter;
    if(!isset($iPlotterCounter)){
        $iPlotterCounter=0;
    }
    $iPlotterCounter++;
    $sIdWorker='graphPlotter'.$iPlotterCounter;
    $sReturn.='<div class="plottterinlinewrapper chartsfortable">'
            . '<div class="header '.$aCfg['skin-color2'].'">'.$sTitle.'</div>'
            . '<br><div id="'.$sIdWorker.'" class="plottterinline"></div>'
            . '</div>'
            ;
    $sJsOnReady.='showGraphInline("'.$sIdWorker.'", "'.$sHost.'", "'.$sCounterItem.'", '.$iPlotterValues.', "'.$sDescription.'", '.$iMax.', "'.$sCounterItem2.'"); '."\n";
    return $sReturn;
}

// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

// tabbed content
$aTC = array();
if (count($aSrvStatus) > 0) {
    foreach ($aSrvStatus as $sHost => $aData) {
        $sWorker='';
        $sScoreBar='';
              
        $sOverview=''
            . '<h4>' .$aCfg['icons']['server'].' '. $sHost . '</h4>'

            // . '<pre>'.print_r($aSrvStatus[$sHost]['counter'], 1).'</pre>'
            . '<div class="serveritem">'
                . '<strong>'
                    . '<span><i class="far fa-flag"></i></span><br>'
                . '</strong><br>'
                . $aLangTxt['lblUtilizationTrafficVersion'].':<br>'
                . $aSrvStatus[$sHost]['status']['Server Version'].'<br><br>'
                . $aLangTxt['lblUtilizationTrafficMPM'].':<br>'
                . $aSrvStatus[$sHost]['status']['Server MPM'].'<br><br>'
            . '</div>'

            . '<div class="serveritem">'
                . '<strong>'
                    . '<span><i class="far fa-calendar"></i></span><br>'
                . '</strong><br>'
                . $aLangTxt['lblUtilizationTrafficUptime'].':<br>'
                . $aSrvStatus[$sHost]['status']['Server uptime']
            . '</div>'

            . '<div class="serveritem">'
                . '<strong>'
                    . '<span><i class="fas fa-exchange-alt"></i></span><br>'
                    . $aSrvStatus[$sHost]['status']['Total accesses']
                . '</strong><br>'
                . $aLangTxt['lblUtilizationTrafficTotalAccesses'].'<br><br>'
                . $aLangTxt['lblUtilizationTrafficAvgAccesses'].'<br>'
                . $aSrvStatus[$sHost]['counter']['requests/sec'].'/ sec'
            . '</div>'

            . '<div class="serveritem">'
                . '<strong>'
                    . '<span><i class="far fa-clock"></i></span><br>'
                    . $aSrvStatus[$sHost]['counter']['ms/request']
                . '</strong><br>'
                . 'ms/ request'
            . '</div>'

            . '<div class="serveritem">'
                . '<strong>'
                    . '<span><i class="fas fa-cloud-upload-alt"></i></span><br>'
                    . $aSrvStatus[$sHost]['status']['Total Traffic']
                . '</strong><br>'
                . $aLangTxt['lblUtilizationTrafficTotalTraffic'].'<br><br>'
                . $aLangTxt['lblUtilizationTrafficAvgTraffic'].'<br>'
                . $aSrvStatus[$sHost]['status']['size/sec'].'/ sec'
            . '</div>'

            . '<div class="serveritem">'
                . '<strong>'
                    . '<span><i class="fas fa-grip-horizontal"></i></span><br>'
                    . $aSrvStatus[$sHost]['counter']['slots_total']
                . '</strong><br>'
                . $aLangTxt['thWorkerTotal'].'<br><br>'
                . $aLangTxt['thWorkerActive'].': <strong>'.$aSrvStatus[$sHost]['counter']['requests_active'].'</strong><br>'
                . $aLangTxt['thWorkerWait']  .': '.$aSrvStatus[$sHost]['counter']['requests_waiting'].'<br>'
                . $aLangTxt['thWorkerUnused'].': '.$aSrvStatus[$sHost]['counter']['slots_unused'].'<br>'
                . '<br>'
                .  $oDatarenderer->renderWorkersBar($aSrvStatus, $sHost, '100%', '2em')
            . '</div>'

            . '<div style="clear: both;"></div>'
            ;
        
        // --------------------------------------------------------------------------------
        

        $fWarning=0.85;
        $fCritical=0.95;
        $fPartUnused=($aSrvStatus[$sHost]['counter']['slots_unused'] / $aSrvStatus[$sHost]['counter']['slots_total']);
        $fPartActive=($aSrvStatus[$sHost]['counter']['requests_active'] / $aSrvStatus[$sHost]['counter']['slots_total']);
        if($fPartUnused>$fWarning){
            if($fPartUnused>$fCritical){
                $sWorker.=showHint(sprintf($aLangTxt['lblUtilizationLowActivityCritical'],$aSrvStatus[$sHost]['counter']['slots_unused'],$aSrvStatus[$sHost]['counter']['slots_total']));
            } else {
                $sWorker.=showHint(sprintf($aLangTxt['lblUtilizationLowActivityWarning'],$aSrvStatus[$sHost]['counter']['slots_unused'],$aSrvStatus[$sHost]['counter']['slots_total']));
            }
        }
        if($fPartActive>$fWarning){
            if($fPartActive>$fCritical){
                $sWorker.=showHint(sprintf($aLangTxt['lblUtilizationHighActivityCritical'],$aSrvStatus[$sHost]['counter']['requests_active'],$aSrvStatus[$sHost]['counter']['slots_total']));
            } else {
                $sWorker.=showHint(sprintf($aLangTxt['lblUtilizationHighActivityWarning'],$aSrvStatus[$sHost]['counter']['requests_active'],$aSrvStatus[$sHost]['counter']['slots_total']));
            }
        }
        
        $sWorker.= ''
                . showPlotter($sHost, 'requests_active', $iPlotterValues, $aLangTxt['lblUtilizationWorkerProcessesActive'],  
                        sprintf($aLangTxt['lblUtilizationWorkerProcessesActiveTitleTotal'], $aSrvStatus[$sHost]['counter']['slots_total']),
                        $aSrvStatus[$sHost]['counter']['slots_total'],
                        'slots_busy'
                        )
                . '<div style="clear:both"></div>'
                ;
            
        
        // --------------------------------------------------------------------------------
        $aTC[] = array(
            'tab' => $oDatarenderer->renderHostTab($aSrvStatus, $sHost),
            'content' => $sOverview . $sWorker
        );
    }
}

$content = $oDatarenderer->themeBox(
    $aCfg['icons']['utilization.php'] . ' ' . $aLangTxt['view_utilization.php_label']
    , $oDatarenderer->renderTabbedContent($aTC)
    , $aLangTxt['lblHintHelpUtilization']
);
