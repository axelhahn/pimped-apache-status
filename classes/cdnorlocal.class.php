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
 * @version 1.0.14
 * @author Axel Hahn
 * @link https://www.axel-hahn.de
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package cdnorlocal
 * 
 * 2024-07-19  v1.0.14  WIP: php 8 only; declare variable types
 */
class cdnorlocal
{

    /**
     * Version number of this class
     * @var string
     */
    protected string $sVersion = '1.0.14';

    /**
     * flag to show debugging infos (used in _wd method [write debug])
     * @var boolean
     */
    protected bool $_bDebug = false;

    /**
     * local vendor dir ... if a lib was downloaded with admin
     * @var string
     */
    public string $sVendorDir = '';

    /**
     * local vendor dir ... if a lib was downloaded with admin
     * @var string
     */
    public string $sCdnMetadir = '.cdnmetadata';

    /**
     * local vendor url ... if a lib was downloaded with admin
     * this is the prefix for linking in html documents
     * @var string
     */
    protected string $sVendorUrl = '';

    /**
     * url prefix of CDNs
     * @var array
     */
    var array $aCdnUrls = [
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

    /**
     * Used CDN name ... a key of $this->aCdnUrls
     * @var string
     */
    protected string $_sCdn = '';

    /**
     * array of libs
     * @var array
     */
    protected array $_aLibs = [];

    // ----------------------------------------------------------------------
    // 
    // INIT
    // 
    // ----------------------------------------------------------------------

    /**
     * constructor
     * @param  array  $aOptions optional: options array with possible keys 
     *                          - debug
     *                          - vendordir & vendorurl
     *                          - vendorrelpath
     */
    public function __construct(array $aOptions = [])
    {

        if (count($aOptions)) {
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
     * Write debug output if the flag was set
     * @param string  $sText  message to show
     * @return boolean
     */
    protected function _wd(string $sText): bool
    {
        if ($this->_bDebug) {
            echo "DEBUG " . __CLASS__ . " - " . $sText . "<br>\n";
        }
        return true;
    }

    /**
     * Dump current values
     * @return boolean
     */
    public function dump(): bool
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
     * Get the local filename (maybe it does not exist)
     * 
     * @param string $sRelUrl  relative url of css/ js file (i.e. "jquery/3.2.1/jquery.min.js")
     * @return string
     */
    protected function _getLocalfilename(string $sRelUrl): string
    {
        return "$this->sVendorDir/$sRelUrl";
    }

    /**
     * get a filename for a json info file to store/ read metadata of a single library
     * 
     * @param  string  $sLibAndVersion  name of library + '__' + version, eg "jquery__3.2.1"
     * @return string
     */
    protected function _getLibMetaFile(string $sLibAndVersion): string
    {
        $sDir = $this->_getLocalfilename('') . '/' . $this->sCdnMetadir;
        return $sDir . '/' . $sLibAndVersion . '.json';
    }

    /**
     * Get the local filename if it exists.
     * It returns false if it does not exist
     * 
     * @param string $sRelUrl  relative url of css/ js file (i.e. "jquery/3.2.1/jquery.min.js")
     * @return bool|string
     */
    public function getLocalfile(string $sRelUrl): bool|string
    {
        return file_exists($this->_getLocalfilename($sRelUrl))
            ? $this->_getLocalfilename($sRelUrl)
            : false;
    }

    /**
     * Set a CDN to deliver sources; returns true if the CDN is supported;
     * returns false if CDN is not supported/ unknown
     * 
     * @see getCdns() to get a list of supported CDNs
     * 
     * @param string $sNewCdn
     * @return boolean
     */
    public function setCdn(string $sNewCdn): bool
    {
        if (isset($this->aCdnUrls[$sNewCdn])) {
            $this->_sCdn = $sNewCdn;
            return true;
        }
        return false;
    }

    /**
     * Enable / disable debug output
     * 
     * @param bool  $sNewValue  Flag to enable/ disable debugging; true = enabled
     * @return bool
     */
    public function setDebug(bool $sNewValue): bool
    {
        $this->_wd(__METHOD__ . "($sNewValue)");
        $this->_bDebug = $sNewValue;
        return true;
    }
    /**
     * Set a vendor dir to scan libraries as relative path to the class
     * 
     * @param string  $sNewValue  new local dir; relative to the class file
     * @return boolean
     */
    public function setVendorWithRelpath(string $sRelpath): bool
    {
        $this->_wd(__METHOD__ . "($sRelpath)");
        $this->setVendorDir(__DIR__ . '/' . $sRelpath);
        $this->setVendorUrl($sRelpath);
        return true;
    }

    /**
     * Set a vendor dir to scan libraries as full path. It is the basepath with all libs in subdirs.
     * 
     * @param string  $sNewValue   new local dir; absolute path
     * @param boolean $bMustExist  optional flag: ensure that the directory exists
     * @return string
     */
    public function setVendorDir(string $sNewValue, bool $bMustExist = false): string 
    {
        $this->_wd(__METHOD__ . "($sNewValue)");
        if (!file_exists($sNewValue) && $bMustExist) {
            die(__CLASS__ . ' ' . __METHOD__ . ' - ERROR: directory [' . $sNewValue . '] does not exist.');
        }
        return $this->sVendorDir = $sNewValue;
    }

    /**
     * Set a vendor url to use as link for libraries
     * 
     * @param string  $sNewValue  new url
     * @return string
     */
    public function setVendorUrl(string $sNewValue): string
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
     * Add a library into lib stack
     * @param string $sReldir  relative dir and version (i.e. "jquery/3.2.1")
     * @param string $sFile    optional file (behind relpath)
     * @return boolean
     */
    public function addLib(string $sReldir, string $sFile = ''): bool
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
     * Get array with a flat list of supported CDNs
     * @return array
     */
    public function getCdns(): array
    {
        return array_keys($this->aCdnUrls);
    }

    /**
     * Get name of currently set CDN
     * @return string
     */
    public function getCurrentCdn(): string
    {
        return $this->_sCdn;
    }

    /**
     * Get array of all libs filtered by criteria
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
     * @param array  $aFilter  optional array with filter items containing these keys:
     *                         - islocal   true|false; default is false
     *                         - isunused  true|false; default is false
     * @return array
     */
    public function getFilteredLibs(array $aFilter = []): array
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
     * Get all libs from lib stack; with enabled flag entries in local 
     * vendor cache will be added to show the versions that can be deleted
     * (detectable by subkey "isunused" => true)
     * 
     * @param boolean  $bDetectUnused  optional flag: detect unused local libs; default is false (=no)
     * @return array
     */
    public function getLibs(bool $bDetectUnused = false): array
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
     * Search in current libraries - it finds an item with given key and value
     * and can return other value.
     * It returns false if nothing was found.
     * 
     * @param string $sScanItem    item to search (one of lib|version|relpath)
     * @param string $sScanValue   needed value
     * @param string $sReturnKey   return key (one of lib|version|relpath)
     * @return bool|string
     */
    public function _getLibItem($sScanItem, $sScanValue, $sReturnKey): bool|string
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
     * Get the (first) version of a lib in the lib stack
     * @param string  $sLib  name of the library (i.e. "jquery"; relpath without version)
     * @return string
     */
    public function getLibVersion(string $sLib): string
    {
        return $this->_getLibItem('lib', $sLib, 'version');
    }

    /**
     * Get the relative path of a lib
     * @param string  $sLib  name of the library (i.e. "jquery"; relpath without version)
     * @return string
     */
    public function getLibRelpath(string $sLib): string
    {
        return $this->_getLibItem('lib', $sLib, 'relpath');
    }

    /**
     * Get version of current cdnorlocal class
     * @return string
     */
    public function getVersion(): string
    {
        return $this->sVersion;
    }

    /**
     * Set an array of lib items to the lib 
     * @param array  $aLibs  array of relpath (i.e. "jquery/3.2.1")
     * @return boolean
     */
    public function setLibs(array $aLibs): bool
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
     * It returns false if the relurl is not in the right format
     * 
     * @param  string  $sRelUrl  relurl of a file (i.e. "jquery/3.2.1/jquery.min.js")
     * @return bool|array
     */
    protected function _splitRelUrl(string $sRelUrl): bool|array
    {
        preg_match_all('#^(.*)/(.*)/(.*)$#U', $sRelUrl, $aMatches);
        if (!count($aMatches) === 4) {
            return false;
        }
        return [
            'pkg' => $aMatches[1][0],
            'version' => $aMatches[2][0],
            'file' => $aMatches[3][0],
            'metafile' => $aMatches[1][0] . '__' . $aMatches[2][0], // pkg + __ + version
        ];
    }

    /**
     * Get a full url to a CDN of a given relurl
     * @param string  $sRelUrl  relative path
     * @param string  $sCdn     optional: use another CDN key; default: current CDN
     * @return bool|string
     */
    public function getFullCdnUrl(string $sRelUrl, string $sCdn = ''): bool|string
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
            ['[PKG]', '[VERSION]', '[FILE]'],
            [$aSplits['pkg'], $aSplits['version'], $aSplits['file']],
            $this->aCdnUrls[$sCdn]['url']
        );
    }

    /**
     * Get full url based on relative filename. It returns the url of
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
     * @return bool|string
     */
    public function getFullUrl(string $sRelUrl): bool|string
    {
        return $this->getLocalfile($sRelUrl)
            ? "$this->sVendorUrl/$sRelUrl"
            : $this->getFullCdnUrl($sRelUrl)
        ;
    }

    /**
     * Get html code to include a css or js file (kind of lazy function)
     * Remark: other file extensions are not supported
     * Use the method getFullUrl() to get a full url and then embed it directly
     * 
     * @see getFullUrl()
     * 
     * @param string $sRelUrl  relative url of css/ js file (i.e. "jquery/3.2.1/jquery.min.js")
     * @param string $sCecksum optional checksum
     * @return string
     */
    function getHtmlInclude(string $sRelUrl, string $sCecksum = ''): string
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

        $sSecurity = ($sCecksum ? "integrity=\"$sCecksum\" " : '')
            . 'crossorigin="anonymous" referrerpolicy="no-referrer"'
            ;

        return match ($ext) {
            'css' => "<link rel=\"stylesheet\" type=\"text/css\" href=\"$sUrl\" $sSecurity/>",
            'js'  => "<script src=\"$sUrl\" $sSecurity></script>",
            default => "<!-- ERROR: I don't know (yet) how to handle extension [$ext] ... to include $sRelUrl; You can use getFullUrl('$sRelUrl'); -->",
        };
    }
}
