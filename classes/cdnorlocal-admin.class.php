<?php

namespace axelhahn;

require_once 'cdnorlocal.class.php';

/**
 * use source from a CDN cdnjs.cloudflare.com or local folder?
 * 
 * admin functions to request API, download, read existing local downloads
 * This file is needed by admin/index.php only - NOT in your projects to publish
 *
 * @version 1.0.13
 * @author Axel Hahn
 * @link https://www.axel-hahn.de
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package cdnorlocal
 */
class cdnorlocaladmin extends cdnorlocal
{

    /**
     * url for cdnjs api request
     * @var string
     */
    var $sUrlApiPackage = 'https://api.cdnjs.com/libraries/%s';

    var $aLibs = [];

    // ----------------------------------------------------------------------
    // 
    // API requests
    // 
    // ----------------------------------------------------------------------

    /**
     * helper function - get an alement from API return
     * 
     * @param string  $sLibrary  name of the library to ask for
     * @param string  $sElement  item to read, i.e. description|homepage|author|...
     */
    protected function _getLibraryElement($sLibrary, $sElement)
    {
        // $this->_wd(__METHOD__ . "($sLibrary, $sElement)");
        if (!isset($this->aLibs[$sLibrary]['_cdn'])) {
            $this->getLibraryMetadata($sLibrary);
        }

        if (isset($this->aLibs[$sLibrary]['_cdn']->$sElement)) {
            // $this->_wd(__METHOD__ . " return stdObject");
            return $this->aLibs[$sLibrary]['_cdn']->$sElement;
        }
        if (is_array($this->aLibs[$sLibrary]['_cdn']) && isset($this->aLibs[$sLibrary]['_cdn'][$sElement])) {
            // $this->_wd(__METHOD__ . " return array");
            return $this->aLibs[$sLibrary]['_cdn'][$sElement];
        }
        return false;
    }



    /**
     * fetch metadata of a library from CDN API or local cache
     * (and put/ "cache" them to $this->aLibs[$sLibrary]['_cdn'])
     * 
     * @param string  $sLibrary  name of the library to ask for
     * @return array
     */
    public function getLibraryMetadata($sLibrary, $bRefresh = false)
    {
        $bHasDoneRefresh = false;
        ini_set("memory_limit", "-1");
        $this->_wd(__METHOD__ . "($sLibrary, $bRefresh)");
        if (!isset($this->aLibs[$sLibrary])) {
            $this->aLibs[$sLibrary] = [];
        }

        if (!isset($this->aLibs[$sLibrary]['_cdn'])) {

            $aJson = false;
            // if(!$bRefresh){
            //     $aJson=$this->_getLibraryMetadataFromCache($sLibrary);
            // } 
            if (!$aJson) {
                $bHasDoneRefresh = true;
                $sApiUrl = sprintf($this->sUrlApiPackage, $sLibrary);
                $this->_wd(__METHOD__ . "($sLibrary) fetch $sApiUrl");
                $aJson = json_decode(file_get_contents($sApiUrl));
            }
            // echo '<pre>'.print_r($aJson, 1).'</pre>';
            if ($aJson) {
                $this->aLibs[$sLibrary]['_cdn'] = $aJson;
                // if($bHasDoneRefresh && $this->isLocalLibrary($sLibrary)){
                //     $this->_putLibraryInfoFile($sLibrary);
                // }
            }
        }
        if (!isset($this->aLibs[$sLibrary]['_cdn'])) {
            $this->_wd(__METHOD__ . "($sLibrary) no _cdn");
            return false;
        }
        // echo '<pre>'; print_r($this->aLibs[$sLibrary]); die();
        return true;
    }

    /**
     * get get metainfos of a library from CDN API fith files and checksums
     * 
     * @param string  $sLibrary  name of the library
     * @param string  $sVersion  optional: version of the library (default: version from local config)
     * @return array
     */
    public function getLibraryMetainfos($sLibrary, $sVersion = false)
    {
        $this->_wd(__METHOD__ . "($sLibrary, $sVersion) start");
        if (!$sVersion) {
            // $sVersion=isset($this->aLibs[$sLibrary]['_cdn']->version) ? $this->aLibs[$sLibrary]['_cdn']->version : false;
            $sVersion = $this->getLibraryLatestVersion($sLibrary);
            $this->_wd(__METHOD__ . ' version: ' . $sVersion);
        }

        $sLibCachefile = $this->_getLibMetaFile($sLibrary . '__' . $sVersion);
        if (!file_exists($sLibCachefile)) {
            $sApiUrl = sprintf($this->sUrlApiPackage, "$sLibrary/$sVersion/");
            $this->_wd(__METHOD__ . "($sLibrary, $sVersion) fetch $sApiUrl");
            $aJson = json_decode(file_get_contents($sApiUrl), 1);

            if (is_array($aJson)) {
                $this->_wd(__METHOD__ . "($sLibrary, $sVersion) store $sLibCachefile");
                if(!is_dir(dirname($sLibCachefile))){
                    mkdir(dirname($sLibCachefile));
                }
                file_put_contents($sLibCachefile, json_encode($aJson, JSON_PRETTY_PRINT));
            }
        } else {
            $this->_wd(__METHOD__ . "($sLibrary, $sVersion) read cache $sLibCachefile");
            $aJson = json_decode(file_get_contents($sLibCachefile), 1);
        }

        return $aJson;
    }

    /**
     * get author of a library
     * 
     * @param string  $sLibrary  name of the library
     * @return string
     */
    public function getLibraryAuthor($sLibrary)
    {

        $sReturn = $this->_getLibraryElement($sLibrary, 'author');
        if (isset($sReturn->name)) {
            return $sReturn->name
                . (isset($sReturn->url) ? '; <a href="' . $sReturn->url . '" target="_blank">' . $sReturn->url . '</a>' : '');
        }
        return $sReturn;
    }


    /**
     * get description of a library
     * 
     * @param string  $sLibrary  name of the library
     * @return string
     */
    public function getLibraryDescription($sLibrary)
    {
        return htmlentities($this->_getLibraryElement($sLibrary, 'description'));
    }

    /**
     * get filename of a library
     * 
     * @param string  $sLibrary  name of the library
     * @return string
     */
    public function getLibraryFilename($sLibrary)
    {
        return $this->_getLibraryElement($sLibrary, 'filename');
    }

    /**
     * get homepage of a library
     * 
     * @param string  $sLibrary  name of the library
     * @return string
     */
    public function getLibraryHomepage($sLibrary)
    {
        return $this->_getLibraryElement($sLibrary, 'homepage');
    }

    /**
     * get array with keywords of a library
     * 
     * @param string  $sLibrary  name of the library
     * @return array
     */
    public function getLibraryKeywords($sLibrary)
    {
        return $this->_getLibraryElement($sLibrary, 'keywords');
    }
    /**
     * get array with licenses of a library
     * 
     * @param string  $sLibrary  name of the library
     * @return array
     */
    public function getLibraryLicenses($sLibrary)
    {
        $aLicenses = $this->_getLibraryElement($sLibrary, 'licenses');
        if (!$aLicenses) {
            $aLicenses = [];
        }
        $sLicense = $this->_getLibraryElement($sLibrary, 'license');
        if ($sLicense) {
            $aLicenses[] = $sLicense;
        }
        return $aLicenses;
    }

    /**
     * get latest version of a library
     * 
     * @param string  $sLibrary  name of the library
     * @return string
     */
    public function getLibraryLatestVersion($sLibrary)
    {
        return $this->_getLibraryElement($sLibrary, 'version');
    }
    /**
     * get name of a library
     * 
     * @param string  $sLibrary  name of the library
     * @return string
     */
    public function getLibraryName($sLibrary)
    {
        return $this->_getLibraryElement($sLibrary, 'name');
    }

    /**
     * get all versions of a library from CDN API
     * 
     * @param string  $sLibrary  name of the library to ask for
     * @param integer $iMax      optional: max count of return values; default: all
     * @return array
     */
    public function getLibraryVersions($sLibrary, $iMax = false)
    {
        $this->getLibraryMetadata($sLibrary);
        $aReturn = [];
        if ($this->aLibs[$sLibrary]['_cdn']) {
            $iCount = 0;
            foreach ($this->aLibs[$sLibrary]['_cdn']->versions as $sVersion) {
                $iCount++;
                if (!$iMax || $iCount <= $iMax) {
                    array_unshift($aReturn, $sVersion);
                }
            }
        }
        return $aReturn;
    }

    /**
     * check if a library was downloaded locally or not
     * 
     * @param  string  $sLibrary  name of the library
     * @param  string  $sVersion  optional version (default: none)
     * @return boolean
     */
    public function isLocalLibrary($sLibrary, $sVersion = false)
    {
        $sRelUrl = $sLibrary . ($sVersion ? '/' . $sVersion : '');
        $sLocaldir = $this->_getLocalfilename($sRelUrl);
        return (is_dir($sLocaldir));
    }

    // ----------------------------------------------------------------------
    // 
    // download library to local vendor dir
    // 
    // ----------------------------------------------------------------------

    /**
     * http download of assets
     * source
     * https://stackoverflow.com/questions/21362362/file-get-contents-from-multiple-url
     * 
     * @param array  $aAssets  array with array with keys "url" + "file"
     */
    protected function _httpGet($aAssets)
    {
        // cURL multi-handle
        $mh = curl_multi_init();

        // This will hold cURLS requests for each file
        $requests = [];

        $options = [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_USERAGENT      => 'cdnorlocal downloader (php curl)',
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        ];

        //Corresponding filestream array for each file
        $fstreams = [];

        foreach ($aAssets as $aItem) {

            $sRemotefile = $aItem['url'];
            $sLocalfile = $aItem['file'];
            $sFileId = $aItem['file'];

            if (!is_dir(dirname($sLocalfile))) {
                mkdir(dirname($sLocalfile), 0755, 1);
            }

            // Add initialized cURL object to array
            $requests[$sFileId] = curl_init($sRemotefile);

            // Set cURL object options
            curl_setopt_array($requests[$sFileId], $options);

            // Open a filestream for each file and assign it to corresponding cURL object
            $fstreams[$sFileId] = fopen($sLocalfile, 'w');
            curl_setopt($requests[$sFileId], CURLOPT_FILE, $fstreams[$sFileId]);

            // Add cURL object to multi-handle
            curl_multi_add_handle($mh, $requests[$sFileId]);
        }

        // Do while all request have been completed
        do {
            curl_multi_exec($mh, $active);
        } while ($active > 0);

        // Collect all data here and clean up
        foreach ($requests as $sFileId => $request) {

            //$returned[$key] = curl_multi_getcontent($request); // Use this if you're not downloading into file, also remove CURLOPT_FILE option and fstreams array
            curl_multi_remove_handle($mh, $request); //assuming we're being responsible about our resource management
            curl_close($request);                    //being responsible again.  THIS MUST GO AFTER curl_multi_getcontent();
            fclose($fstreams[$sFileId]);
        }

        curl_multi_close($mh);
    }


    /**
     * get a filename for a json info file to store metadata of a single library
     * 
     * @param  string  $sLibrary  name of library
     * @return string
     */
    protected function _getInfoFilename($sLibrary)
    {
        return $this->_getLocalfilename('') . '/' . $this->sCdnMetadir . '/' . $sLibrary . '.json';
        // return $this->_getLocalfilename($sLibrary).'/.ht_info_cdnorlocal.json';
    }

    /**
     * get a filename for a json info file to store metadata of all libraries
     * 
     * @return string
     */
    protected function _getLocalLibsFile()
    {
        return $this->_getLocalfilename('') . '/' . $this->sCdnMetadir . '/my_libs.json';
        // return $this->_getLocalfilename('').'/.ht_info_all_libs_cdnorlocal.json';
    }

    /**
     * store fresh metadata of all downloaded libraries and versions
     * 
     * @return boolean
     */
    protected function _putLocalLibsFile()
    {
        $sJsonfile = $this->_getLocalLibsFile();
        return file_put_contents($sJsonfile, json_encode($this->_reReadLibdirs(), JSON_PRETTY_PRINT));
    }


    /**
     * get fresh data with reading  local vendor dir and find downloaded 
     * classes and versions
     * 
     * @return array
     */
    protected function _reReadLibdirs()
    {
        $sLocaldir = $this->_getLocalfilename('');
        $aReturn = [];
        if (is_dir($sLocaldir)) {
            foreach (glob($sLocaldir . '*', GLOB_ONLYDIR) as $sLibdir) {
                if (is_dir($sLibdir)) {
                    $sLib2test = basename($sLibdir);
                    foreach (glob($sLibdir . '/*', GLOB_ONLYDIR) as $sVersiondir) {
                        if (!preg_match('/_in_progressXXXX/', $sVersiondir)) {
                            if (!isset($aReturn[$sLib2test])) {
                                $aReturn[$sLib2test] = [];
                            }
                            $aReturn[$sLib2test][] = basename($sVersiondir);
                            $this->getLibraryMetainfos($sLib2test, basename($sVersiondir));
                            krsort($aReturn[$sLib2test]);
                        }
                    }
                }
            }
        }
        ksort($aReturn);
        return $aReturn;
    }

    /**
     * recursively remove a directory
     * 
     * @param string  $dir  name of the directory
     */
    private function _rrmdir($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->_rrmdir($file);
            } else {
                unlink($file);
            }
        }
        if (is_dir($dir)) {
            return rmdir($dir);
        }
        return false;
    }

    /**
     * delete a version of a local library 
     * 
     * @param  string  $sLibrary   name of library
     * @param  string  $sVersion   version to delete
     * @param  bool    $bIsUnused  flag: is lib unused (if true then the metafile will be deleted)
     * @return type
     */
    public function delete($sLibrary, $sVersion, $bIsUnused = true)
    {

        $sRelUrl = $sLibrary . '/' . $sVersion;
        $sLocaldir = $this->_getLocalfilename($sRelUrl);

        if ($this->_rrmdir($sLocaldir)) {
            // try: delete the lib directory if it was the last version
            @rmdir($this->_getLocalfilename($sLibrary));
            if ($bIsUnused) {
                unlink($this->_getLibMetaFile($sLibrary . '__' . $sVersion));
            }
        }

        // refresh local view
        $this->_putLocalLibsFile();

        return !is_dir($sLocaldir);
    }

    /**
     * download all files of a given library.
     * 
     * @param  string  $sLibrary  name of library
     * @param  string  $sVersion  version number
     * @param  bool    $bForceDownload  optional flag: if true a download will be repeated even if the local copy exists
     * @return boolean
     */
    public function downloadAssets($sLibrary, $sVersion, $bForceDownload = false)
    {
        $sRelUrl = $sLibrary . '/' . $sVersion;
        $sLocaldir = $this->_getLocalfilename($sRelUrl);

        // this version was downloaded already
        if (is_dir($sLocaldir)) {
            return false;
        }
        $sTmpdir = $sLocaldir . '_in_progress';
        $bAllDownloaded = true;

        $iMaxFiles = 50;
        $iFilesLeft = 0;
        $aAssetDataFromCdn = $this->getLibraryMetainfos($sLibrary, $sVersion);

        $iFilesTotal = isset($aAssetDataFromCdn['files']) ? count($aAssetDataFromCdn['files']) : 0;
        if (!$iFilesTotal) {
            return false;
        }
        // $this->_putLibraryInfoFile($sLibrary);

        // --- get the first N files...
        $aDownloads = [];
        foreach ($aAssetDataFromCdn['files'] as $sFilename) {
            if (!file_exists($sTmpdir . '/' . $sFilename) || !filesize($sTmpdir . '/' . $sFilename)) {
                if (count($aDownloads) < $iMaxFiles) {
                    $aDownloads[] = [
                        'url' => $this->getFullCdnUrl($sRelUrl . '/' . $sFilename),
                        'file' => $sTmpdir . '/' . $sFilename,
                    ];
                }
            }
        }
        // --- get the first N files...
        $this->_httpGet($aDownloads);

        // --- verify if all was downloaded
        foreach ($aAssetDataFromCdn['files'] as $sFilename) {
            if (!file_exists($sTmpdir . '/' . $sFilename)) {
                $iFilesLeft++;
                $this->_wd(__METHOD__ . ' file still missing: ' . $sTmpdir . '/' . $sFilename);
                $bAllDownloaded = false;
            }
        }

        // --- move tmpdir to final dir ... on incomplete downloads try to continue
        if ($bAllDownloaded && is_dir($sTmpdir)) {
            if (is_dir($sLocaldir)) {
                $this->_wd(__METHOD__ . ' removing ' . $sLocaldir);
                $this->_rrmdir($sLocaldir);
            }
            $this->_wd(__METHOD__ . ' move ' . $sTmpdir . ' to ' . $sLocaldir);
            rename($sTmpdir, $sLocaldir);

            // file_put_contents($this->_getLibMetaFile($sLibrary.'__'.$sVersion), json_encode($aAssetDataFromCdn, JSON_PRETTY_PRINT));
        } else {
            $this->_wd(__METHOD__ . ' download not complete ???' . $bAllDownloaded ? ' all files were downloaded' : 'missing some files.');
            echo "downloading " . count($aDownloads) . " files per request..."
                . "<br>$iFilesLeft of " . $iFilesTotal . " still left - " . round(($iFilesTotal - $iFilesLeft) / $iFilesTotal * 100) . "%<br>"
                . 'Please wait ... or <a href="?">abort</a>'
                . "<script>window.setTimeout('location.reload();', 2000);</script>";
            die();
        }
        if ($iFilesTotal) {
            $this->_putLocalLibsFile();
        }
        echo "<script>window.setTimeout('location.reload();', 20);</script>";

        return true;
    }


    // ----------------------------------------------------------------------
    // 
    // get local directories
    // 
    // ----------------------------------------------------------------------

    // Comparison function
    protected function _orderByNewestVersion($a, $b)
    {
        $this->_wd(__METHOD__ . "($a, $b)");
        if ($a == $b) {
            return 0;
        }
        return version_compare($a, $b, '<');
    }

    /**
     * get an array with all downloaded libs and its versions
     * 
     * @param boolean  $bForceRefresh  force refresh
     * @return array
     */
    public function getLocalLibs($bForceRefresh = false)
    {
        $sJsonfile = $this->_getLocalLibsFile();
        if (!file_exists($sJsonfile) || $bForceRefresh) {
            $this->_putLocalLibsFile();
        }
        if (!file_exists($sJsonfile)) {
            return false;
        }
        $aData = json_decode(file_get_contents($sJsonfile), 1);
        if (!$aData || !is_array($aData) || !count($aData)) {
            return false;
        }
        foreach ($aData as $sLib => $aVersions) {
            uasort($aVersions, [$this, '_orderByNewestVersion']);
            $aData[$sLib] = $aVersions;
        }
        return $aData;
    }
    // ----------------------------------------------------------------------
    // 
    // search with cdnjs API
    // 
    // ----------------------------------------------------------------------

    /**
     * search for a library with CDN API
     * 
     * @param string  $sLibrary  name of the library to ask for
     * @return object
     */
    public function searchLibrary($sLibrary)
    {
        $this->_wd(__METHOD__ . "($sLibrary)");
        $sApiUrl = sprintf($this->sUrlApiPackage, '?search=' . str_replace(' ', '+', $sLibrary) . '&fields=version,description');
        $this->_wd(__METHOD__ . " fetch $sApiUrl");
        $aJson = json_decode(file_get_contents($sApiUrl));

        return $aJson;
    }
}
