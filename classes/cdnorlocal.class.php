<?php

namespace axelhahn;

/**
 * 
 * CDN OR LOCAL
 * use source from a CDN cdnjs.cloudflare.com or local folder?
 * you need just this class for your projects
 *
 * @example 
 * $oCdn->new axelhahn\cdnorlocal();
 * echo $oCdn->getHtmlInclude("jquery/3.2.1/jquery.min.js");
 * 
 * @version 1.0
 * @author Axel Hahn
 * @link https://www.axel-hahn.de
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package cdnorlocal
 */
class cdnorlocal {
    
    /**
     * flag to show debugging infos (used in _wd method [write debug])
     * @var boolean
     */
    var $_bDebug=false;
    
    /**
     * local vendor dir ... if a lib was downloaded with admin
     * @var string
     */
    var $sVendorDir=false;
    
    /**
     * local vendor url ... if a lib was downloaded with admin
     * this is the prefix for linking in html documents
     * @var string
     */
    var $sVendorUrl=false;
    
    /**
     * url prefix of CDNJS
     * @var string
     */
    var $sCdnUrl='https://cdnjs.cloudflare.com/ajax/libs';
    

    // ----------------------------------------------------------------------
    // 
    // INIT
    // 
    // ----------------------------------------------------------------------
    
    /**
     * constructor
     * @param  array  $aOptions with possible keys 
     *                  - debug
     *                  - vendordir & vendorurl
     *                  - vendorrelpath
     */
    public function __construct($aOptions=false) {
        
        if(is_array($aOptions)){
            if(array_key_exists('debug', $aOptions)){
                $this->setDebug($aOptions['debug']);
            }
            if(array_key_exists('vendordir', $aOptions)){
                $this->setVendorDir($aOptions['vendordir'], 1);
            }
            if(array_key_exists('vendorurl', $aOptions)){
                $this->setVendorUrl($aOptions['vendorurl']);
            }
            if(array_key_exists('vendorrelpath', $aOptions)){
                $this->setVendorWithRelpath($aOptions['vendorrelpath']);
            }
        }
        if(!$this->sVendorDir){
            $this->setVendorUrl('/vendor');
            $this->setVendorDir($_SERVER['DOCUMENT_ROOT'].'/vendor');
        }
    }

    /**
     * write debug output if the flag was set
     * @param string  $sText  message to show
     */    
    protected function _wd($sText){
        if ($this->_bDebug){
            echo "DEBUG " . __CLASS__ . " - " . $sText . "<br>\n";
        }
    }
    
    
    // ----------------------------------------------------------------------
    // 
    // getter and setter
    // 
    // ----------------------------------------------------------------------

    
    /**
     * return the local filename (maybe it does not exist
     * 
     * @param string $sRelUrl  relative url of css/ js file (i.e. "jquery/3.2.1/jquery.min.js")
     * @return string
     */
    protected function _getLocalfilename($sRelUrl){
        return $this->sVendorDir.'/'.$sRelUrl;
    }
    
    /**
     * return the local filename if it exists
     * 
     * @param string $sRelUrl  relative url of css/ js file (i.e. "jquery/3.2.1/jquery.min.js")
     * @return type
     */
    public function getLocalfile($sRelUrl){
        return file_exists($this->_getLocalfilename($sRelUrl))
            ? $this->_getLocalfilename($sRelUrl)
            : false;
            ;
    }
    

    /**
     * set a vendor url to use as link for libraries
     * 
     * @param string  $sNewValue  new url
     * @return string
     */
    public function setDebug($sNewValue){
        $this->_wd(__METHOD__ . "($sNewValue)");
        return $this->_bDebug=$sNewValue;
    }
    /**
     * set a vendor dir to scan libraries
     * 
     * @param string  $sNewValue  new local dir
     * @return string
     */
    public function setVendorWithRelpath($sRelpath){
        $this->_wd(__METHOD__ . "($sRelpath)");
        $this->setVendorDir(__DIR__ . '/'.$sRelpath);
        $this->setVendorUrl($sRelpath);
        return true;
    }
    /**
     * set a vendor dir to scan libraries
     * 
     * @param string  $sNewValue   new local dir
     * @param boolean $bMustExist  optional flag: ensure that the directory exists
     * @return string
     */
    
    public function setVendorDir($sNewValue, $bMustExist=false){
        $this->_wd(__METHOD__ . "($sNewValue)");
        if(!file_exists($sNewValue) && $bMustExist){
            die(__CLASS__ . ' ' . __METHOD__ . ' - ERROR: directory ['.$sNewValue.'] does not exist.');
        }
        return $this->sVendorDir=$sNewValue;
    }

    /**
     * set a vendor url to use as link for libraries
     * 
     * @param string  $sNewValue  new url
     * @return string
     */
    public function setVendorUrl($sNewValue){
        $this->_wd(__METHOD__ . "($sNewValue)");
        return $this->sVendorUrl=$sNewValue;
    }
    
    
    // ----------------------------------------------------------------------
    // 
    // rendering
    // 
    // ----------------------------------------------------------------------
    


    /**
     * get full url based on relative filename. It returns the url of
     * local directory if it exists or a url of CDNJS 
     * 
     * To use a local path a library must exist. Default vendor dir
     * is '[webroot]/vendor' and vendor url is '/vendor'
     * Use the setVendor* functions to override them.
     * 
     * @see setVendorDir()
     * @see setVendorUrl()
     * or
     * @see setVendorWithRelpath()
     * 
     * @param string $sRelUrl  relative url of css/ js file (i.e. "jquery/3.2.1/jquery.min.js")
     * @return type
     */
    public function getFullUrl($sRelUrl){
        return ($this->getLocalfile($sRelUrl)
                ? $this->sVendorUrl
                : $this->sCdnUrl
                ).'/'.$sRelUrl;
        
    }
    
    /**
     * get html code to include a css or js file (kind of lazy function)
     * Remark: other file extensions are not supported
     * Use the method getFullUrl() to get a full url and then embed it directly
     * 
     * @see getFullUrl()
     * 
     * @param string $sRelUrl  relative url of css/ js file (i.e. "jquery/3.2.1/jquery.min.js")
     * @return string
     */
    function getHtmlInclude($sRelUrl){
        $sUrl=$this->getFullUrl($sRelUrl);
        $ext = pathinfo($sRelUrl, PATHINFO_EXTENSION);
        switch ($ext){
            case 'css': 
                return '<link rel="stylesheet" type="text/css" href="'.$sUrl.'">';
            case 'js': 
                return '<script src="'.$sUrl.'"></script>';
            default:
                return "<!-- ERROR: I don't know (yet) how to handle extension [$ext] ... to include $sRelUrl; You can use getFullUrl('$sRelUrl'); -->";
        }
    }
    
    
}
