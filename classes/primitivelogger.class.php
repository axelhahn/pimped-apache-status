<?php
/*
 * PIMPED APACHE-STATUS
 * primitive logger class
 * You can add log lines and show them with the render function
 *
 * @package pimped_apache_status
 * @author Axel Hahn
 */
class PrimitiveLogger {

    private $aLogs = array();

    /**
     * constructor
     * @return boolean (true)
     */
    public function __construct() {
        return $this->flush();
    }

    /**
     * add a log line
     * @param string $sMsg    message text
     * @param string $sLevel  loglevel like "info", "warning", "error" (css classname for output)
     * @return boolean
     */     
    public function add($sMsg, $sLevel='info') {
        return $this->aLogs[] = array(
            'level'=>$sLevel, 
            'msg'=>$sMsg
        );
    }
    
    /**
     * flush all logs
     * @return boolean
     */
    public function flush() {
        return $this->aLogs = array();
    }

    /**
    * print log as html and flush log entries
    * @return string
    */
    function render() {
        $sReturn = '';
        foreach ($this->aLogs as $aLogentry) {
            $sReturn.='<div class="' . $aLogentry['level'] . '"><span class="type">' . $aLogentry['level'] . ' </span>' . $aLogentry['msg'] . '</div>';
        }
        
        if ($sReturn)
            $sReturn='<div class="logs">' . $sReturn . '</div>';
        
        $this->flush();
        
        return $sReturn;
    }
}
