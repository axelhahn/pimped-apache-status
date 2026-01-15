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
     * @var string
     */
    private string $sType = 'html';

    /**
     * array of strings for http response header
     * @var array
     */
    private array $aResponseHeader = [];

    /**
     * Replacements in the template
     * @var array
     */
    private array $aReplace = [
        '{{HEADER}}' => '',
        '{{CONTENT}}' => '',
        '{{FOOTER}}' => '',
        '{{JSONREADY}}' => '',
    ];

    /**
     * constructor (it does nothing)
     * @return void
     */
    public function __construct() {
        $this->setOutputtype();
    }

    /**
     * wrap on document ready instructions in jQuery style
     * @return string 
     */
    private function _finalizeJsOnReady(): string {
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
    public function getContent(): string {
        return $this->aReplace['{{CONTENT}}'];
    }
    
    /**
     * get current footer
     * @return string
     */
    public function getFooter(): string {
        return $this->aReplace['{{FOOTER}}'];
    }

    /**
     * get current header in response body
     * @return string
     */
    public function getHeader(): string {
        return $this->aReplace['{{HEADER}}'];
    }

    /**
     * get on ready javascript instructions
     * @return string
     */
    public function getJsOnReady(): string {
        return $this->aReplace['{{JSONREADY}}'];
    }
        
    
    /**
     * get output type
     * @return string
     */
    public function getOutputtype(): string {
        return $this->sType;
    }

    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------

    /**
     * add content
     * @param string $s  additional html code for the body
     * @return string
     */
    public function addContent(string $s): string {
        return $this->aReplace['{{CONTENT}}'] .= $s;
    }
    
    /**
     * add javascript for on ready execution
     * @param string $s  javascript code
     * @return string
     */
    public function addJsOnReady(string $s): string {
        return $this->aReplace['{{JSONREADY}}'] .= $s;
    }

    /**
     * add a http response header line
     * @param string $s
     * @return string
     */
    public function addResponseHeader(string $s): string {
        return $this->aResponseHeader[] = $s;
    }

    /**
     * set rel dir of an application
     * @param string $s  html code
     * @return string
     */
    public function setAppDir(string $s): string {
        return $this->aReplace['{{APPDIR}}'] = $s;
    }
    /**
     * set html body; it replaces old content
     * @param string $s  html code
     * @return string
     */
    public function setContent(string $s): string {
        return $this->aReplace['{{CONTENT}}'] = $s;
    }
    /**
     * set footer in html body; it replaces old content
     * @param string $s  html code
     * @return string
     */
    public function setFooter(string $s): string {
        return $this->aReplace['{{FOOTER}}'] = $s;
    }

    /**
     * set html header; it replaces old content
     * @param string $s  html code
     * @return string
     */
    public function setHeader(string $s): string {
        return $this->aReplace['{{HEADER}}'] = $s;
    }

    /**
     * set javascript code on ready; it replaces old content
     * @param string $s  javascript code
     * @return string
     */
    public function setJsOnReady(string $s): string {
        return $this->aReplace['{{JSONREADY}}'] = $s;
    }
    
    /**
     * set output type of response
     * @param string $sOutputType
     * @return string
     */
    public function setOutputtype(string $sOutputType = 'html'): string {
        return $this->sType = $sOutputType;
    }

    /**
     * set a (new) replacement
     * @param string  $s         search text, i.e. "{{EXAMPLE}}"
     * @param string  $sReplace  replacement
     * @return string
     */
    public function setReplacement(string $s, string $sReplace): string {
        return $this->aReplace[$s] = $sReplace;
    }


    // ----------------------------------------------------------------------
    // OUTPUT
    // ----------------------------------------------------------------------

    /**
     * send http reponse headers and built the response body
     * @return string
     */
    public function render(): string {
        $aS = []; // search
        $aR = []; // replace

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
