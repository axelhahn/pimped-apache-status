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

function showItem($sIconClass, $sVal='', $sMore=''){
    return '<div class="serveritem">'
        . '<strong>'
            . '<span><i class="'.$sIconClass.'"></i></span><br>'
            . ($sVal ? $sVal : ' ')
        . '</strong><br>'
        . $sMore
    . '</div>';
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
            . showItem(
                    'far fa-flag',
                    '',
                    $aLangTxt['lblUtilizationTrafficVersion'].':<br>'
                    . '<span class="value">'.$aSrvStatus[$sHost]['status']['Server Version'].'</span><br><br>'
                    . $aLangTxt['lblUtilizationTrafficMPM'].':<br>'
                    . '<span class="value">'.$aSrvStatus[$sHost]['status']['Server MPM'].'</span><br><br>'
                    )

            . showItem(
                    'far fa-calendar',
                    '',
                    $aLangTxt['lblUtilizationTrafficUptime'].':<br>'
                    . '<span class="value">'.$aSrvStatus[$sHost]['status']['Server uptime'].'</span>'
                   )
            . showItem(
                    'fas fa-exchange-alt',
                    $aSrvStatus[$sHost]['status']['Total accesses'],
                    $aLangTxt['lblUtilizationTrafficTotalAccesses'].'<br><br>'
                    . $aLangTxt['lblUtilizationTrafficAvgAccesses'].'<br>'
                    . '<span class="value">'.$aSrvStatus[$sHost]['counter']['requests/sec'].'/ sec</span>'
                   )
            . showItem(
                    'far fa-clock',
                    (isset($aSrvStatus[$sHost]['counter']['ms/request']) && $aSrvStatus[$sHost]['counter']['ms/request']) ? $aSrvStatus[$sHost]['counter']['ms/request'] : '-',
                    'ms/ request'
                   )
            . showItem(
                    'fas fa-cloud-upload-alt',
                    $aSrvStatus[$sHost]['status']['Total Traffic'],
                    $aLangTxt['lblUtilizationTrafficTotalTraffic'].'<br><br>'
                    . $aLangTxt['lblUtilizationTrafficAvgTraffic'].'<br>'
                    . '<span class="value">'.$aSrvStatus[$sHost]['status']['size/sec'].'/ sec</span>'
                   )
            . showItem(
                    'fas fa-grip-horizontal',
                    $aSrvStatus[$sHost]['counter']['slots_total'],
                    $aLangTxt['thWorkerTotal'].'<br><br>'
                    . $aLangTxt['thWorkerActive'].': <span class="value"><strong>'.$aSrvStatus[$sHost]['counter']['requests_active'].'</strong></span><br>'
                    . $aLangTxt['thWorkerWait']  .': <span class="value">'.$aSrvStatus[$sHost]['counter']['requests_waiting'].'</span><br>'
                    . $aLangTxt['thWorkerUnused'].': <span class="value">'.$aSrvStatus[$sHost]['counter']['slots_unused'].'</span><br>'
                    . '<br>'
                    .  $oDatarenderer->renderWorkersBar($aSrvStatus, $sHost, '100%', '2em')
                    )


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
