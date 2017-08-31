<?php

/**
 * PIMPED APACHE-STATUS
 * Page class
 * Render output page by replacing placeholders 
 *
 * @package pimped_apache_status
 * @author Axel Hahn
 */
class Page {

    /**
     * output type of content
     * @var array
     */
    private $sType = 'html';

    /**
     * array of strings for http response header
     * @var array
     */
    private $aResponseHeader = array();

    /**
     * Replacements in the template
     * @var array
     */
    private $aReplace = array(
        '{{HEADER}}' => '',
        '{{CONTENT}}' => '',
        '{{FOOTER}}' => '',
        '{{JSONREADY}}' => '',
    );

    /**
     * constructor (it does nothing)
     * @return boolean (true)
     */
    public function __construct() {
        $this->setOutputtype();
        return true;
    }

    /**
     * wrap on document ready instructions in jQuery style
     * @return type 
     */
    private function _finalizeJsOnReady() {
        return $this->aReplace["{{JSONREADY}}"] = '
            <script>
                $(document).ready(function() {
                    ' . $this->aReplace["{{JSONREADY}}"] . '
                } );
            </script>';
    }

    // ----------------------------------------------------------------------
    // GETTER
    // ----------------------------------------------------------------------

    /**
     * get current page content
     * @return string
     */
    public function getContent() {
        return $this->aReplace['{{CONTENT}}'];
    }
    
    /**
     * get current footer
     * @return type
     */
    public function getFooter() {
        return $this->aReplace['{{FOOTER}}'];
    }

    /**
     * get current header in response body
     * @return type
     */
    public function getHeader() {
        return $this->aReplace['{{HEADER}}'];
    }

    /**
     * get on ready javascript instructions
     * @return type¨
     */
    public function getJsOnReady() {
        return $this->aReplace['{{JSONREADY}}'];
    }
        
    
    /**
     * get output type
     * @return string
     */
    public function getOutputtype() {
        return $this->sType;
    }

    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------

    /**
     * add content
     * @param string $s  additional html code for the body
     * @return boolean
     */
    public function addContent($s) {
        return $this->aReplace['{{CONTENT}}'] .= $s;
    }
    
    /**
     * add javascript for on ready execution
     * @param string $s  javascript code
     * @return boolean
     */
    public function addJsOnReady($s) {
        return $this->aReplace['{{JSONREADY}}'] .= $s;
    }

    /**
     * add a http response header line
     * @param string $s
     * @return boolean
     */
    public function addResponseHeader($s) {
        return $this->aResponseHeader[] = $s;
    }

    /**
     * set rel dir of an application
     * @param string $s  html code
     * @return boolean
     */
    public function setAppDir($s) {
        return $this->aReplace['{{APPDIR}}'] = $s;
    }
    /**
     * set html body; it replaces old content
     * @param string $s  html code
     * @return boolean
     */
    public function setContent($s) {
        return $this->aReplace['{{CONTENT}}'] = $s;
    }
    /**
     * set footer in html body; it replaces old content
     * @param string $s  html code
     * @return boolean
     */
    public function setFooter($s) {
        return $this->aReplace['{{FOOTER}}'] = $s;
    }

    /**
     * set html header; it replaces old content
     * @param string $s  html code
     * @return boolean
     */
    public function setHeader($s) {
        return $this->aReplace['{{HEADER}}'] = $s;
    }

    /**
     * set javascript code on ready; it replaces old content
     * @param string $s  javascript code
     * @return boolean
     */
    public function setJsOnReady($s) {
        return $this->aReplace['{{JSONREADY}}'] = $s;
    }
    
    /**
     * set output type of response
     * @param string $sOutputType
     * @return boolean
     */
    public function setOutputtype($sOutputType = 'html') {
        return $this->sType = $sOutputType;
    }

    /**
     * set a (new) replacement
     * @param string  $s         search text, i.e. "{{EXAMPLE}}"
     * @param string  $sReplace  replacement
     * @return boolean
     */
    public function setReplacement($s, $sReplace) {
        return $this->aReplace[$s] = $sReplace;
    }


    // ----------------------------------------------------------------------
    // OUTPUT
    // ----------------------------------------------------------------------

    /**
     * send http reponse headers and built the response body
     * @return type
     */
    public function render() {
        $aS = array(); // search
        $aR = array(); // replace

        $this->_finalizeJsOnReady();

        foreach ($this->aReplace as $sSeach => $sReplace) {
            $aS[] = $sSeach;
            $aR[] = $sReplace;
        }

        $sTemplate = false;
        $sTplFile = dirname(__FILE__) . "/" . $this->sType . ".tpl.php";
        if (!file_exists($sTplFile)) {
            die("ERROR: template for type " . $this->sType . " was not found: $sTplFile");
        }

        $sTemplate = file_get_contents($sTplFile);
        if (!$sTemplate) {
            die("ERROR: template file $sTplFile is empty or could not be read.");
        }

        foreach ($this->aResponseHeader as $sHeader) {
            header($sHeader);
        }
        return str_replace($aS, $aR, $sTemplate);
    }

}
