<?php

namespace axelhahn;

/**
 * 
 * CDN OR LOCAL
 * use source from a CDN cdnjs.cloudflare.com or local folder?
 * you need just this class for your projects
 *
 * @example 
 * 
 * load a library from CDN
 * <code>
 * $oCdn->new axelhahn\cdnorlocal();
 * echo $oCdn->getHtmlInclude("jquery/3.2.1/jquery.min.js");
 * </code>
 * 
 * TODO:
 * support jsdelivr, i.e.
 * https://cdn.jsdelivr.net/npm/vis@4.21.0/dist/vis.min.js
 * AND/ OR
 * https://unpkg.com/
 * 
 * @version 1.0.13
 * @author Axel Hahn
 * @link https://www.axel-hahn.de
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package cdnorlocal
 */
class cdnorlocal
{

    protected $sVersion = '1.0.13';

    /**
     * flag to show debugging infos (used in _wd method [write debug])
     * @var boolean
     */
    var $_bDebug = false;

    /**
     * local vendor dir ... if a lib was downloaded with admin
     * @var string
     */
    var $sVendorDir = false;

    /**
     * local vendor dir ... if a lib was downloaded with admin
     * @var string
     */
    var $sCdnMetadir = '.cdnmetadata';

    /**
     * local vendor url ... if a lib was downloaded with admin
     * this is the prefix for linking in html documents
     * @var string
     */
    var $sVendorUrl = false;

    /**
     * url prefix of CDNs
     * @var array
     */
    var $aCdnUrls = [
        'cdnjs.cloudflare.com' => [
            'about' => '',
            'url' => 'https://cdnjs.cloudflare.com/ajax/libs/[PKG]/[VERSION]/[FILE]',
            'urlLatest' => 'https://cdnjs.cloudflare.com/ajax/libs/[PKG]/[VERSION]/[FILE]',
        ],
        /*
        'cdn.jsdelivr.net'=>[
            'about'=>'',
            'url'=>'https://cdn.jsdelivr.net/npm/[PKG]@[VERSION]/[FILE]',
        ],
        'unpkg.com'=>[
            'about'=>'',
            'url'=>'https://unpkg.com/[PKG]@[VERSION]/[FILE]',
        ],
         */
    ];
    protected $_sCdn = false;

    /**
     * array of libs
     * @var array
     */
    var $_aLibs = [];

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
    public function __construct($aOptions = false)
    {

        if (is_array($aOptions)) {
            if (isset($aOptions['debug'])) {
                $this->setDebug($aOptions['debug']);
            }
            if (isset($aOptions['vendorrelpath'])) {
                $this->setVendorWithRelpath($aOptions['vendorrelpath']);
            }
            if (isset($aOptions['vendordir'])) {
                $this->setVendorDir($aOptions['vendordir']);
            }
            if (isset($aOptions['vendorurl'])) {
                $this->setVendorUrl($aOptions['vendorurl']);
            }
        }
        if (!$this->sVendorDir) {
            $this->setVendorUrl('/vendor');
            $this->setVendorDir($_SERVER['DOCUMENT_ROOT'] . '/vendor');
        }
        // $this->_sCdn=array_key_first($this->aCdnUrls);
        reset($this->aCdnUrls);
        $this->_sCdn = key($this->aCdnUrls);
    }

    /**
     * write debug output if the flag was set
     * @param string  $sText  message to show
     * @return boolean
     */
    protected function _wd($sText)
    {
        if ($this->_bDebug) {
            echo "DEBUG " . __CLASS__ . " - " . $sText . "<br>\n";
        }
        return true;
    }

    /**
     * dump current values
     * @return boolean
     */
    public function dump()
    {
        echo '
        <h2>Dump ' . __CLASS__ . '</h2>

        <h3>Basic values</h3>
        <ul>
            <li>Version: <span class="value">' . $this->getVersion() . '</span></li>
            <li>
                Vendor DIR: <span class="value">' . $this->sVendorDir . '</span>
                (' . (is_dir($this->sVendorDir) ? 'OK, exists' : 'Does not exist (yet)') . ')
            </li>
            <li>
                Vendor Metadata cache: <span class="value">' . $this->sVendorDir . '/' . $this->sCdnMetadir . '</span>
                (' . (is_dir($this->sVendorDir . '/' . $this->sCdnMetadir) ? 'OK, exists' : 'Does not exist (yet)') . ')
            </li>
            <li>Vendor URL: <span class="value">' . $this->sVendorUrl . '</span>
            </li>
            <li>current CDN: <span class="value">' . $this->_sCdn . '</span></li>
        </ul>

        <h3>Libraries</h3>
        <pre>this->getLibs(true) = ' . print_r($this->getLibs(true), 1) . '</pre>
        ';

        return true;
    }

    // ----------------------------------------------------------------------
    // 
    // getter and setter for single libs
    // 
    // ----------------------------------------------------------------------


    /**
     * return the local filename (maybe it does not exist)
     * 
     * @param string $sRelUrl  relative url of css/ js file (i.e. "jquery/3.2.1/jquery.min.js")
     * @return string
     */
    protected function _getLocalfilename($sRelUrl)
    {
        return $this->sVendorDir . '/' . $sRelUrl;
    }
    /**
     * get a filename for a json info file to store/ read metadata of a single library
     * 
     * @param  string  $sLibAndVersion  name of library + version, eg "jquery__3.2.1"
     * @return string
     */
    protected function _getLibMetaFile($sLibAndVersion)
    {
        $sDir = $this->_getLocalfilename('') . '/' . $this->sCdnMetadir;
        // if (!is_dir($sDir)) {
        //     mkdir($sDir);
        // }
        return $sDir . '/' . $sLibAndVersion . '.json';
    }

    /**
     * return the local filename if it exists
     * 
     * @param string $sRelUrl  relative url of css/ js file (i.e. "jquery/3.2.1/jquery.min.js")
     * @return string|bool
     */
    public function getLocalfile($sRelUrl)
    {
        return file_exists($this->_getLocalfilename($sRelUrl))
            ? $this->_getLocalfilename($sRelUrl)
            : false;
    }

    /**
     * set a CDN to deliver sources; returns true if the CDN is supported;
     * returns false if CDN is not supported
     * 
     * @see getCdns() to get a list of supported CDNs
     * 
     * @param string $sNewCdn
     * @return boolean
     */
    public function setCdn($sNewCdn)
    {
        if (isset($this->aCdnUrls[$sNewCdn])) {
            $this->_sCdn = $sNewCdn;
            return true;
        }
        return false;
    }

    /**
     * set a vendor url to use as link for libraries
     * 
     * @param string  $sNewValue  new url
     * @return string
     */
    public function setDebug($sNewValue)
    {
        $this->_wd(__METHOD__ . "($sNewValue)");
        return $this->_bDebug = $sNewValue;
    }
    /**
     * set a vendor dir to scan libraries as relative path to the class
     * 
     * @param string  $sNewValue  new local dir; relative to the class file
     * @return string
     */
    public function setVendorWithRelpath($sRelpath)
    {
        $this->_wd(__METHOD__ . "($sRelpath)");
        $this->setVendorDir(__DIR__ . '/' . $sRelpath);
        $this->setVendorUrl($sRelpath);
        return true;
    }
    /**
     * set a vendor dir to scan libraries as full path
     * 
     * @param string  $sNewValue   new local dir; absolute path
     * @param boolean $bMustExist  optional flag: ensure that the directory exists
     * @return string
     */

    public function setVendorDir($sNewValue, $bMustExist = false)
    {
        $this->_wd(__METHOD__ . "($sNewValue)");
        if (!file_exists($sNewValue) && $bMustExist) {
            die(__CLASS__ . ' ' . __METHOD__ . ' - ERROR: directory [' . $sNewValue . '] does not exist.');
        }
        return $this->sVendorDir = $sNewValue;
    }

    /**
     * set a vendor url to use as link for libraries
     * 
     * @param string  $sNewValue  new url
     * @return string
     */
    public function setVendorUrl($sNewValue)
    {
        $this->_wd(__METHOD__ . "($sNewValue)");
        return $this->sVendorUrl = $sNewValue;
    }

    // ----------------------------------------------------------------------
    // 
    // getter and setter for libs
    // 
    // ----------------------------------------------------------------------

    /**
     * add a library into lib stack
     * @param string $sReldir  relative dir and version (i.e. "jquery/3.2.1")
     * @param string $sFile    optional file (behind relpath)
     * @return boolean
     */
    public function addLib($sReldir, $sFile = false)
    {
        $this->_wd(__METHOD__ . "($sReldir,$sFile)");
        if (!isset($this->_aLibs[$sReldir])) {
            $this->_wd(__METHOD__ . " add $sReldir");
            $aTmp = preg_split('#\/#', $sReldir);
            $this->_aLibs[$sReldir] = [
                'lib' => $aTmp[0],
                'version' => $aTmp[1],
                'relpath' => $sReldir,
                'islocal' => !!$this->getLocalfile($sReldir),
                'isunused' => false,
                'files' => [],
            ];
        } else {
            $this->_wd(__METHOD__ . " SORRY $sReldir was added already");
        }
        if ($sFile) {
            $this->_aLibs[$sReldir]['files'][$sFile] = [
                'islocal' => !!$this->getLocalfile($sReldir . '/' . $sFile)
            ];
        }
        ksort($this->_aLibs);
        $this->_wd(__METHOD__ . " ... " . print_r($this->_aLibs, 1));
        return true;
    }

    /**
     * get array with a flat list of supported CDNs
     * @return array
     */
    public function getCdns()
    {
        return array_keys($this->aCdnUrls);
    }
    /**
     * get array with a flat list of supported CDNs
     * @return array
     */
    public function getCurrentCdn()
    {
        return $this->_sCdn;
    }
    /**
     * return array of all libs filtered by criteria
     * 
     * @example 
     * 
     *   get used libs that are local:
     *   <code>$oCdn->getFilteredLibs(['islocal'=>1]);</code>
     * 
     *   get used libs that are loaded from CDN:
     *   <code>$oCdn->getFilteredLibs(['islocal'=>0]);</code>
     * 
     *   get unused libs that are still local (and can be deleted)
     *   <code>$oCdn->getFilteredLibs(['islocal'=>1, 'isunused'=>1])</code>
     * 
     * @param array  $aFilter  array with filter items containing these keys:
     *                         - islocal   true|false; default is false
     *                         - isunused  true|false; default is false
     * @return array
     */
    public function getFilteredLibs($aFilter = [])
    {
        $this->_wd(__METHOD__ . "()");
        $aReturn = [];
        foreach (['islocal', 'isunused'] as $sKey) {
            $aFilter[$sKey] = isset($aFilter[$sKey]) ? $aFilter[$sKey] : false;
        }
        foreach ($this->getLibs($aFilter['isunused']) as $sLibKey => $aItem) {
            $bAdd = true;
            foreach (['islocal', 'isunused'] as $sFilterKey) {
                $bAdd = $bAdd && ($aFilter[$sFilterKey] == $aItem[$sFilterKey]);
            }
            if ($bAdd) {
                $aReturn[$sLibKey] = $aItem;
            }
        }
        return $aReturn;
    }
    /**
     * return all libs from lib stack; with enabled flag entries in local 
     * vendor cache will be added to show the versions that can be deleted
     * (detectable by subkey "isunused" => true)
     * 
     * @param boolean  $bDetectUnused  flag: detect unused local libs
     * @return array
     */
    public function getLibs($bDetectUnused = false)
    {
        $this->_wd(__METHOD__ . "()");
        $aReturn = $this->_aLibs;
        if ($bDetectUnused) {
            foreach (glob($this->sVendorDir . '/*') as $sDir) {
                $sMyLib = basename($sDir);
                foreach (glob($this->sVendorDir . '/' . $sMyLib . '/*') as $sVersiondir) {
                    $sMyVersion = basename($sVersiondir);
                    if (!isset($aReturn[$sMyLib . '/' . $sMyVersion]) || $aReturn[$sMyLib . '/' . $sMyVersion]) {
                        $aReturn[$sMyLib . '/' . $sMyVersion] = [
                            'lib' => $sMyLib,
                            'version' => $sMyVersion,
                            'relpath' => $sMyLib . '/' . $sMyVersion,
                            'islocal' => 1,
                            'isunused' => !isset($this->_aLibs[$sMyLib . '/' . $sMyVersion]),
                        ];
                    }
                }
            }
            ksort($aReturn);
        }
        return $aReturn;
    }

    /**
     * find item with a value and return other value
     * @param string $sScanItem    item to search (one of lib|version|relpath)
     * @param $sReldir$sScanValue  needed value
     * @param $sReldir$sReturnKey  return key (one of lib|version|relpath)
     * @return varia
     */
    public function _getLibItem($sScanItem, $sScanValue, $sReturnKey)
    {
        $this->_wd(__METHOD__ . "($sScanItem, $sScanValue, $sReturnKey)");
        foreach ($this->_aLibs as $sRelpath => $aLibdata) {
            if ($aLibdata[$sScanItem] === $sScanValue) {
                return $aLibdata[$sReturnKey];
            }
        }
        return false;
    }

    /**
     * get the (first) version of a lib in the lib stack
     * @param string  $sLib  name of the library (i.e. "jquery"; relpath without version)
     * @return string
     */
    public function getLibVersion($sLib)
    {
        return $this->_getLibItem('lib', $sLib, 'version');
    }

    /**
     * get the (first) version of a lib in the lib stack
     * @param string  $sLib  name of the library (i.e. "jquery"; relpath without version)
     * @return string
     */
    public function getLibRelpath($sLib)
    {
        return $this->_getLibItem('lib', $sLib, 'relpath');
    }

    /**
     * get version of current cdnorlocal class
     * @return string
     */
    public function getVersion()
    {
        return $this->sVersion;
    }

    /**
     * set an array of lib items to the lib 
     * @param array  $aLibs  array of relpath (i.e. "jquery/3.2.1")
     * @return boolean
     */
    public function setLibs($aLibs)
    {
        $this->_wd(__METHOD__ . "([array])");
        if (!is_array($aLibs)) {
            return false;
        }
        $this->_aLibs = [];
        foreach ($aLibs as $sReldir) {
            $this->addLib($sReldir);
        }
        return true;
    }

    // ----------------------------------------------------------------------
    // 
    // rendering
    // 
    // ----------------------------------------------------------------------

    /**
     * get array with parts from a relurl
     * - package
     * - version
     * - file (relative to lib root)
     * - part of filename for metadata
     */
    protected function _splitRelUrl($sRelUrl)
    {
        $aTmp = preg_match_all('#^(.*)/(.*)/(.*)$#U', $sRelUrl, $aMatches);
        if (!count($aMatches) === 4) {
            return false;
        }
        return [
            'pkg'      => $aMatches[1][0],
            'version'  => $aMatches[2][0],
            'file'     => $aMatches[3][0],
            'metafile' => $aMatches[1][0] . '__' . $aMatches[2][0], // pkg + __ + version
        ];
    }

    public function getFullCdnUrl($sRelUrl, $sCdn = false)
    {
        if (!$sCdn) {
            $sCdn = $this->_sCdn;
        }
        $aSplits = $this->_splitRelUrl($sRelUrl);
        $sTemplate = isset($this->aCdnUrls[$sCdn]['url']) ? $this->aCdnUrls[$sCdn]['url'] : false;
        if (!$aSplits || !$sTemplate) {
            return false;
        }
        return str_replace(
            ['[PKG]',         '[VERSION]',         '[FILE]'],
            [$aSplits['pkg'], $aSplits['version'], $aSplits['file']],
            $this->aCdnUrls[$sCdn]['url']
        );
    }

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
    public function getFullUrl($sRelUrl)
    {
        return ($this->getLocalfile($sRelUrl)
            ? $this->sVendorUrl . '/' . $sRelUrl
            : $this->getFullCdnUrl($sRelUrl)
        );
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
    function getHtmlInclude($sRelUrl, $sCecksum = '')
    {
        $sUrl = $this->getFullUrl($sRelUrl);
        $ext = pathinfo($sRelUrl, PATHINFO_EXTENSION);
        if (!$sCecksum) {
            $aInfos = $this->_splitRelUrl($sRelUrl);
            $sLibAndVersion = $aInfos['metafile'];

            $sLibCachefile = $this->_getLibMetaFile($sLibAndVersion);
            if (file_exists($sLibCachefile)) {
                // extract filename in the lib
                // $sRelfileInLib=str_replace($sLibAndVersion.'/', '', $sRelUrl);
                $sRelfileInLib = $aInfos['file'];
                // echo "$sLibCachefile exists<br>";
                $aVersionMetadata = json_decode(file_get_contents($sLibCachefile), 1);
                $sCecksum = isset($aVersionMetadata['sri'][$sRelfileInLib]) ? $aVersionMetadata['sri'][$sRelfileInLib] : '';
            }
        }

        $sSecurity = ($sCecksum ? 'integrity="' . $sCecksum . '" ' : '')
            . 'crossorigin="anonymous" referrerpolicy="no-referrer"';
        switch ($ext) {
            case 'css':
                return '<link rel="stylesheet" type="text/css" href="' . $sUrl . '" '
                    . $sSecurity
                    . ' />';
            case 'js':
                return '<script src="' . $sUrl . '" '
                    . $sSecurity
                    . '></script>';
            default:
                return "<!-- ERROR: I don't know (yet) how to handle extension [$ext] ... to include $sRelUrl; You can use getFullUrl('$sRelUrl'); -->";
        }
    }
}
