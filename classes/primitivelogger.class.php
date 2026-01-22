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

    private $aLogs = [];

    /**
     * constructor
     * @return boolean (true)
     */
    public function __construct() {
        $this->flush();
    }

    /**
     * add a log line
     * @param string $sMsg    message text
     * @param string $sLevel  loglevel like "info", "warning", "error" (css classname for output)
     * @return array
     */     
    public function add($sMsg, $sLevel='info'): array {
        return $this->aLogs[] = [
            'level'=>$sLevel, 
            'msg'=>$sMsg
        ];
    }
    
    /**
     * flush all logs
     * @return array
     */
    public function flush(): array {
        return $this->aLogs = [];
    }

    /**
    * print log as html and flush log entries
    * @return string
    */
    public function render(): string {
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
