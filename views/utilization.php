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

function showPlotter($sHost, $sCounterItem, $iPlotterValues, $sDescription, $sTitle, $iMax){
    global $aCfg, $sJsOnReady;
    $sReturn='';
    static $iPlotterCounter;
    if(!isset($iPlotterCounter)){
        $iPlotterCounter=0;
    }
    $iPlotterCounter++;
    $sIdWorker='graphPlotter'.$iPlotterCounter;
    $sReturn.='<div class="plottterinlinewrapper">'
            . '<div class="header '.$aCfg['skin-color2'].'">'.$sTitle.'</div>'
            . '<br><div id="'.$sIdWorker.'" class="plottterinline"></div>'
            . '</div>'
            ;
    $sJsOnReady.='showGraphInline("'.$sIdWorker.'", "'.$sHost.'", "'.$sCounterItem.'", '.$iPlotterValues.', "'.$sDescription.'", '.$iMax.'); '."\n";
    return $sReturn;
}

    /**
     * return a widget
     * @param type $aOptions  hash with keys for all options
     *                          - bgcolor - icon color one of aqua|green|yellow|red
     *                          - color - icon color one of aqua|green|yellow|red
     *                          - icon
     *                          - text
     *                          - number
     *                          - progressvalue - 0..100
     *                          - progresstext  - text for progress
     * @return string
     */
    function getWidget($aOptions=array()){

        return '<div class="col-md-4">'
        . '<div class="info-box bg-'.$aOptions['bgcolor'].'">
            <span class="info-box-icon bg-'.$aOptions['color'].'">'.$aOptions['icon'].'</span>

            <div class="info-box-content">
              <span class="info-box-text">'.$aOptions['text'].'</span>
              <span class="info-box-number">'.$aOptions['number'].'</span>
            </div>
            '.
                (is_int($aOptions['progressvalue'])
                    ? '<div class="progress">
                            <div class="progress-bar" style="width: '.$aOptions['progressvalue'].'%"></div>
                        </div>
                        <span class="progress-description">'.$aOptions['progresstext'].'</span>
                        '
                    :'')
            .'
            <!-- /.info-box-content -->
        </div>
        </div>'
        ;
    }
// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

// tabbed content
$aTC = array();
if (count($aSrvStatus) > 0) {
    foreach ($aSrvStatus as $sHost => $aData) {
        
        $sOverview=''
            . '<h4>' .$aCfg['icons']['server'].' '. $sHost . '</h4>'
                
            . '<div class="hero">'
                . '<span class="srvlabel">Server version</span>: '.$aSrvStatus[$sHost]['status']['Server Version'].'<br>'
                . '<span class="srvlabel">Server uptime</span>: '.$aSrvStatus[$sHost]['status']['Server uptime'].'<br>'
                . '<span class="srvlabel">'.$aLangTxt['lblUtilizationTrafficTotalAccesses'].'</span>: '.$aSrvStatus[$sHost]['status']['Total accesses'].'<br>'
                . '<span class="srvlabel">'.$aLangTxt['lblUtilizationTrafficTotalTraffic'].'</span>: ' .$aSrvStatus[$sHost]['status']['Total Traffic'] .'<br>'
                . '<span class="srvlabel">'.$aLangTxt['lblUtilizationTrafficAvgAccesses'].'</span>: '  .$aSrvStatus[$sHost]['counter']['requests/sec'] .' /s<br>'
                . '<span class="srvlabel">'.$aLangTxt['lblUtilizationTrafficAvgTraffic'].'</span>: '   .$aSrvStatus[$sHost]['status']['size/sec']      .'/s<br>'
            . '</div>'
            ;
        
        // --------------------------------------------------------------------------------
        $sWorker='<br>'
                . '<h4>' . $aLangTxt['lblTable_status_workers'] . '</h4>';
        
        
        $sScoreBar='';
            $iProcesses = $aSrvStatus[$sHost]['counter']['slots_total'];
            $iSlotsUnused = $aSrvStatus[$sHost]['counter']['slots_unused'];
            $iActive = $aSrvStatus[$sHost]['counter']['requests_active'];
            $iWait = $aSrvStatus[$sHost]['counter']['requests_waiting'];

            $sScoreBar = $oDatarenderer->renderWorkersBar($aSrvStatus, $sHost, '100%', '4em');
            
            /*
                $aLangTxt['thWorkerTotal'] => $iProcesses,
                $aLangTxt['thWorkerActive'] => $iActive,
                $aLangTxt['thWorkerWait'] => $iWait,
                $aLangTxt['thWorkerUnused'] => $iSlotsUnused,
                */
            $sWorker.=$sScoreBar
                . '<br>'
                . '<span class="srvlabel">'.$aLangTxt['thWorkerTotal'] .'</span>: '.$iProcesses.'<br>'
                . '<span class="srvlabel">'.$aLangTxt['thWorkerActive'].'</span>: '.$iActive.'<br>'
                . '<span class="srvlabel">'.$aLangTxt['thWorkerWait']  .'</span>: '.$iWait.'<br>'
                . '<span class="srvlabel">'.$aLangTxt['thWorkerUnused'].'</span>: '.$iSlotsUnused.'<br>'
                . '<br>'
                ;
            
            
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
                /*
                . getWidget(array(
                    'bgcolor'=>'green',
                    // 'color'=>'green',
                    'icon'=>'<i class="fas fa-ticket-alt"></i>',
                    'text'=>$aLangTxt['lblUtilizationWorkerProcessesActiveTitle'],
                    'number'=>$iActive,
                    'progressvalue' => (int)($iActive/$iProcesses*100),
                    'progresstext' => '('.$iProcesses.') ... ',
                ))
                . getWidget(array(
                    'bgcolor'=>'green',
                    // 'color'=>'green',
                    'icon'=>'<i class="fas fa-ticket-alt"></i>',
                    'text'=>$aLangTxt['lblUtilizationWorkerProcessesActiveTitle'],
                    'number'=>($iProcesses - $iSlotsUnused),
                    'progressvalue' => (int)(($iProcesses - $iSlotsUnused)/$iProcesses*100),
                    'progresstext' => '('.$iProcesses.') ... ',
                ))
                 * 
                 */
                . showPlotter($sHost, 'requests_active', $iPlotterValues, $aLangTxt['lblUtilizationWorkerProcessesActive'],  $aLangTxt['lblUtilizationWorkerProcessesActiveTitle'], false)
                . showPlotter($sHost, 'slots_busy',      $iPlotterValues, $aLangTxt['lblUtilizationWorkerProcessesRunning'], $aLangTxt['lblUtilizationWorkerProcessesRunningTitle'], false)
                . showPlotter($sHost, 'requests_active', $iPlotterValues, $aLangTxt['lblUtilizationWorkerProcessesActive'],  
                        sprintf($aLangTxt['lblUtilizationWorkerProcessesActiveTitleTotal'], $aSrvStatus[$sHost]['counter']['slots_total']),
                        $aSrvStatus[$sHost]['counter']['slots_total']
                        )
                . showPlotter($sHost, 'slots_busy',      $iPlotterValues, $aLangTxt['lblUtilizationWorkerProcessesRunning'], 
                        sprintf($aLangTxt['lblUtilizationWorkerProcessesRunningTitleTotal'], $aSrvStatus[$sHost]['counter']['slots_total']),
                        $aSrvStatus[$sHost]['counter']['slots_total']
                        )
                . '<div style="clear:both"></div>'
                /*
                . '<br>'
                . '<h4>'.$aLangTxt['lblUtilizationTraffic'].'</h4>'
                . '<div class="hero">'
                    . '<span class="srvlabel">'.$aLangTxt['lblUtilizationTrafficTotalAccesses'].'</span>: '.$aSrvStatus[$sHost]['status']['Total accesses'].'<br>'
                    . '<span class="srvlabel">'.$aLangTxt['lblUtilizationTrafficTotalTraffic'].'</span>: ' .$aSrvStatus[$sHost]['status']['Total Traffic'] .'<br>'
                    . '<span class="srvlabel">'.$aLangTxt['lblUtilizationTrafficAvgAccesses'].'</span>: '  .$aSrvStatus[$sHost]['counter']['requests/sec'] .' /s<br>'
                    . '<span class="srvlabel">'.$aLangTxt['lblUtilizationTrafficAvgTraffic'].'</span>: '   .$aSrvStatus[$sHost]['status']['size/sec']      .'/s<br>'
                . '</div>'
                . '<br>'
                . showPlotter($sHost, 'requests/sec', $iPlotterValues, $aLangTxt['lblUtilizationTrafficAvgAccesses'], $aLangTxt['lblUtilizationTrafficAvgAccesses'])
                . showPlotter($sHost, 'size/sec',     $iPlotterValues, $aLangTxt['lblUtilizationTrafficAvgTraffic'],  $aLangTxt['lblUtilizationTrafficAvgTraffic'])
                . '<div style="clear:both"></div>'
                 */
                ;
            
        
        // --------------------------------------------------------------------------------
        $aTC[] = array(
            'tab' => $oDatarenderer->renderHostTab($aSrvStatus, $sHost),
            'content' => $sOverview . $sWorker
                /*
                . '<hr>'
                . 'DEBUG'
                . '<div class="console" style="font-family: \'lucida console\'; font-size: 80%;">'
                    // . utf8_encode($aData['orig']) 
                    . 'counter <pre>' . print_r($aSrvStatus[$sHost]['counter'], 1) . '</pre>' 
                    . 'status <pre>' . print_r($aSrvStatus[$sHost]['status'], 1) . '</pre>' 
                . '</div>'
                 */
        );
    }
}

$content = $oDatarenderer->themeBox(
    $aCfg['icons']['utilization.php'] . ' ' . $aLangTxt['view_utilization.php_label']
    , $oDatarenderer->renderTabbedContent($aTC)
    , $aLangTxt['lblHintHelpUtilization']
);
