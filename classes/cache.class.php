<?php

/** 
 * --------------------------------------------------------------------------------<br>
 *          __    ______           __       
 *   ____ _/ /_  / ____/___ ______/ /_  ___ 
 *  / __ `/ __ \/ /   / __ `/ ___/ __ \/ _ \
 * / /_/ / / / / /___/ /_/ / /__/ / / /  __/
 * \__,_/_/ /_/\____/\__,_/\___/_/ /_/\___/ 
 *                                        
 * --------------------------------------------------------------------------------<br>
 * AXELS CACHE CLASS<br>
 * --------------------------------------------------------------------------------<br>
 * <br>
 * THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE <br>
 * LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR <br>
 * OTHER PARTIES PROVIDE THE PROGRAM ?AS IS? WITHOUT WARRANTY OF ANY KIND, <br>
 * EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED <br>
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE <br>
 * ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. <br>
 * SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY <br>
 * SERVICING, REPAIR OR CORRECTION.<br>
 * <br>
 * --------------------------------------------------------------------------------<br>
 * <br>
 * --- HISTORY:<br>
 * 2009-07-20  1.0   cache class on www.axel-hahn.de<br>
 * 2011-08-27  1.1   comments added; sCacheFile is private<br>
 * 2012-02-04  2.0   cache serialzable types; more methods, i.e.:<br>
 *                    - comparison of timestimp with a sourcefile<br>
 *                    - cleanup unused cachefiles<br>
 * 2012-05-15  2.1   isExpired() returns as bool; new method iExpired() to get <br>
 *                   expiration in sec<br>
 * 2014-02-27  2.2   - rename to AhCache<br>
 *                   - _cleanup checks with file_exists<br>
 * 2014-03-31  2.3   - added _setup() that to includes custom settings<br>
 *                   - limit number of files in cache directory<br>
 * 2019-11-24  2.4   - added getCachedItems() to get a filtered list of cache files<br>
 *                   - added remove file to make complete cache of a module invalid<br>
 *                   - rename var in cache.class_config.php to "$this->_sCacheDirDivider"<br>
 * 2019-11-26  2.5   - added getModules() to get a list of existing modules that stored<br>
 *                     a cached item<br>
 * 2021-09-28  2.6   added a simple admin UI; the cache class got a few new methods
 *                   - update: cleanup() now always deletes expired items
 *                   - update: dump() styles output as table
 *                   - added: getCurrentModule 
 *                   - added: deleteModule 
 *                   - added: loadCachefile
 *                   - added: removefileDelete
 *                   - added: setCacheId
 *                   - added: setModule
 * 2021-09-30  2.7   FIX: remove chdir() in _readCacheItem()
 * 2021-10-07  2.8   FIX: remove chdir() in _readCacheItem()
 *                   ADD reference file to expire a cache item
 *                   - added: getRefFile
 *                   - added: setRefFile
 *                   - update: dump, isExpired, isNewerThanFile, write
 *                   - update cache admin
 * 2023-03-17  2.9   FIX: harden _getAllCacheData to prevent PHP warnings
 * 2023-06-02  2.10  shorten code: defaults using ??; short array syntax
 * 2023-11-20  2.11  check data subkey before writing
 * 2024-07-22  2.12  WIP: add type declarations for PHP 8
 * 2024-12-15  2.13  compatible to PHP 8.4
 * --------------------------------------------------------------------------------<br>
 * compatible to PHP 8 ... 8.4<br>
 * --------------------------------------------------------------------------------<br>
 * @version 2.15
 * @author Axel Hahn
 * @link https://www.axel-hahn.de/docs/ahcache/index.htm
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package Axels Cache
 */
class AhCache
{

    /**
     * a module name string is used as relative cache path
     * @var string 
     */
    var string $sModule = '';

    /**
     * id of cachefile (filename will be generated from it)
     * @var string 
     */
    var string $sCacheID = '';

    /**
     * where to store all cache data - it can be outside docRoot. If it is 
     * below docRoot think about forbidding access
     * If empty it will be set in the constructor to [webroot]/~cache/
     * or $TEMP/~cache/ for CLI
     * @var string 
     */
    private string $_sCacheDir = '';

    /**
     * absolute filename of cache file
     * @var string 
     */
    private string $_sCacheFile = '';

    /**
     * divider to limit count of cachefiles
     * @var int 
     */
    private int $_sCacheDirDivider = 0;

    /**
     * fileextension for storing cachefiles (without ".")
     * @var string
     */
    private string $_sCacheExt = 'cacheclass2';

    /**
     * Expiration timestamp; 
     * It will be calculated with current time + ttl in the write() method
     * TTL can be read with getExpire()
     * @var integer
     */
    private int $_tsExpire = -1;

    /**
     * TTL (time to live) in s; 
     * TTL can be set in methods setTtl($iTtl) or write($data, $iTtl)
     * TTL can be read with getTtl()
     * @var integer
     */
    private int $_iTtl = -1;

    /**
     * cachedata and file infos of cachefile (returned array of php function stat)
     * @var array
     */
    private array $_aCacheInfos = [];

    /**
     * Full path to a cache remove file for the current module
     * @var string
     */
    private string $_sCacheRemovefile = '';

    /**
     * Reference file: a cache is outdated if the reference file is newer than
     * the cache item.
     * TTL can be set in methods setRefFile($sFile) or write($data, $iTtl, $sFile)
     * TTL can be read with getRefFile()
     * @var string
     */
    private string $_sRefFile = '';

    // ----------------------------------------------------------------------
    // constructor
    // ----------------------------------------------------------------------

    /**
     * Constructor
     * @param  string  $sModule   name of module or app that uses the cache
     * @param  string  $sCacheID  cache-id (must be uniq within a module; used to generate filename of cachefile)
     */
    public function __construct(string $sModule = ".", string $sCacheID = '.')
    {
        $this->setModule($sModule, $sCacheID);
    }

    // ----------------------------------------------------------------------
    // private funtions
    // ----------------------------------------------------------------------

    // ----------------------------------------------------------------------
    /**
     * init
     * - load custom config from cache.class_config.php 
     * - set a cache
     * - set remove file (if does not exist)
     *
     * @return bool
     */
    private function _setup(): bool
    {
        if (!$this->sModule) {
            die("ERROR: no module was given.<br>");
        }
        if (!$this->sCacheID) {
            die("ERROR: no id was given.<br>");
        }
        $sCfgfile = "cache.class_config.php";
        if (file_exists(__DIR__ . "/" . $sCfgfile)) {
            include (__DIR__ . "/" . $sCfgfile);
        }
        if (!$this->_sCacheDir) {
            if (getenv("TEMP"))
                $this->_sCacheDir = str_replace("\\", "/", getenv("TEMP"));
            if ($_SERVER['DOCUMENT_ROOT'])
                $this->_sCacheDir = $_SERVER['DOCUMENT_ROOT'];
            if (!$this->_sCacheDir)
                $this->_sCacheDir = ".";
            $this->_sCacheDir .= "/~cache";
        }
        $this->_sCacheRemovefile = $this->_sCacheDir . '/' . $this->sModule . '/__remove_me_to_make_caches_invalid__';
        if (!file_exists($this->_sCacheRemovefile)) {
            if (!is_dir($this->_sCacheDir . '/' . $this->sModule)) {
                if (!mkdir($this->_sCacheDir . '/' . $this->sModule, 0750, true)) {
                    die("ERROR: unable to create directory " . $this->_sCacheDir . '/' . $this->sModule);
                }
            }
            touch($this->_sCacheRemovefile);
        }
        return true;
    }

    // ----------------------------------------------------------------------
    /**
     * private function _getAllCacheData() - read cachedata and its meta infos
     * @since 2.0
     * @return     array  array with data, file stat
     */
    private function _getAllCacheData(): array|bool
    {
        if (!$this->_sCacheFile) {
            return false;
        }
        $this->_aCacheInfos = [];
        $aTmp = $this->_readCacheItem($this->_sCacheFile);
        if (is_array($aTmp) && count($aTmp)) {
            $this->_aCacheInfos['data'] = $aTmp['data'] ?? false;
            $this->_iTtl = $aTmp['iTtl'] ? $aTmp['iTtl'] : -1;
            $this->_tsExpire = $aTmp['tsExpire'] ? $aTmp['tsExpire'] : -1;

            $this->_sRefFile = $aTmp['sRefFile'] ?? false;
            $this->_aCacheInfos['stat'] = stat($this->_sCacheFile);

            // @see loadCachefile: it sets module + id to false
            $this->sModule = $this->sModule ? $this->sModule : $aTmp['module'];
            $this->sCacheID = $this->sCacheID ? $this->sCacheID : $aTmp['cacheid'];
        }
        return $this->_aCacheInfos;
    }

    /**
     * read a raw cache item and return it as hash
     *
     * @param string  $sFile  filename with full path or relative to cache base path
     * @return array|boolean
     */
    private function _readCacheItem(string $sFile): array|bool
    {
        $sFull = file_exists($sFile) ? $sFile : $this->_sCacheDir . '/' . $sFile;
        if (file_exists($sFull)) {
            return unserialize(file_get_contents($sFull));
        }
        return false;
    }

    // ----------------------------------------------------------------------
    /**
     * private function _getCacheFilename() - get full filename of cachefile
     * @return     string  full filename of cachefile
     */
    private function _getCacheFilename(): string
    {
        $sMyFile = md5($this->sCacheID);
        if ($this->_sCacheDirDivider && $this->_sCacheDirDivider > 0) {
            $sMyFile = preg_replace('/([0-9a-f]{' . $this->_sCacheDirDivider . '})/', "$1/", $sMyFile);
        }
        $sMyFile .= "." . $this->_sCacheExt;
        $sMyFile = str_replace("/.", ".", $sMyFile);
        // $this->_sCacheFile = $this->_sCacheDir . "/" . $this->sModule . "/" . md5($this->sCacheID) . "." . $this->_sCacheExt;
        $this->_sCacheFile = $this->_sCacheDir . "/" . $this->sModule . "/" . $sMyFile;
        return $this->_sCacheFile;
    }

    // ----------------------------------------------------------------------
    // public funtions
    // ----------------------------------------------------------------------

    /**
     * helper function - remove empty cache directories up to module cache dir
     *
     * @param string    $sDir
     * @param boolean   $bShowOutput   flag: show output? default: false (=no output)
     * @return boolean
     */
    private function _removeEmptyCacheDir(string $sDir, bool $bShowOutput = false): bool
    {
        // echo $bShowOutput ? __METHOD__."($sDir)<br>\n" : '';
        if ($sDir > $this->_sCacheDir . "/" . $this->sModule) {
            echo $bShowOutput ? "REMOVE DIR [$sDir] ... " : '';
            if (is_dir($sDir)) {
                if (rmdir($sDir)) {
                    chdir($this->_sCacheDir);
                    echo $bShowOutput ? "OK<br>\n" : '';
                    usleep(20000); // 0.02 sec
                    $this->_removeEmptyCacheDir(dirname($sDir), $bShowOutput);
                } else {
                    echo $bShowOutput ? "failed.<br>\n" : '';
                    return false;
                }
            } else {
                echo $bShowOutput ? "skip: not a directory.<br>\n" : '';
            }
            return true;
        }
        return false;
    }

    // ----------------------------------------------------------------------
    // public funtions
    // ----------------------------------------------------------------------

    // ----------------------------------------------------------------------
    /**
     * Cleanup cache directory; It deletes all outdated cache items of current module.
     * Additionally you can delete all cachefiles older than n seconds (because
     * there are cache items that are not based on a TTL).
     * Other filetypes in the cache directory won't be touched.
     * Empty directories will be deleted.
     * 
     * Only the directory of the initialized module/ app will be deleted.
     * $o=new Cache("my-app"); $o->cleanup(60*60*24*3); 
     * 
     * To delete all cachefles of all modules you can use
     * $o=new Cache(); $o->cleanup(0); 
     * 
     * @since 2.0
     * @param int       $iSec          max age of cachefile; older cachefiles will be deleted
     * @param boolean   $bShowOutput   flag: show output? default: false (=no output)
     * @return     boolean
     */
    public function cleanup(int $iSec = 0, bool $bShowOutput = false): bool
    {
        // quick and dirty
        $aFilter = ['lifetimeBelow' => 0];
        if (!$iSec === false) {
            $aFilter['ageOlder'] = $iSec;
        }
        $aData = $this->getCachedItems(false, $aFilter);
        echo $bShowOutput ? 'CLEANUP  ' . count($aData) . " files\n" : '';
        if ($aData) {
            $aFiles = array_keys($aData);
            rsort($aFiles);
            foreach (array_keys($aData) as $sFile) {
                echo $bShowOutput ? 'DELETE ' . $sFile . "<br>\n" : '';
                unlink($sFile);
                $this->_removeEmptyCacheDir(dirname($sFile), $bShowOutput);
            }
        }
        return true;
    }

    // ----------------------------------------------------------------------
    /**
     * get an array with cached data elements
     * @since 2.4
     *
     * @param string  $sDir     full path of cache dir; default: false (auto detect cache dir)
     * @param array   $aFilter  filter; valid keys are
     *                          - ageOlder         integer  return items that are older [n] sec
     *                          - lifetimeBelow    integer  return items that expire in less [n] sec (or outdated)
     *                          - lifetimeGreater  integer  return items that expire in more than [n] sec
     *                          - ttlBelow         integer  return items with ttl less than [n] sec
     *                          - ttlGreater       integer  return items with ttl more than [n] sec
     *                          no filter returns all cached entries
     * @return array|boolean
     */
    public function getCachedItems(string $sDir = '', array $aFilter = []): array|bool
    {
        $aReturn = [];
        $sDir = $sDir ? $sDir : $this->_sCacheDir . "/" . $this->sModule;
        if (!file_exists($sDir)) {
            // echo "\t Directory does not exist - [$sDir]";
            return false;
        }
        if (!($d = dir($sDir))) {
            // echo "\t Cannot open directory - [$sDir]</ul></li></ul>";
            return false;
        }
        while ($entry = $d->read()) {
            $sEntry = $sDir . "/" . $entry;
            if (is_dir($sEntry) && $entry != '.' && $entry != '..') {
                $aReturn = array_merge($aReturn, $this->getCachedItems($sEntry, $aFilter));
            }

            if (file_exists($sEntry)) {
                $ext = pathinfo($sEntry, PATHINFO_EXTENSION);
                $ext = substr($sEntry, strrpos($sEntry, '.') + 1);

                $exts = explode(".", $sEntry);
                $n = count($exts) - 1;
                $ext = $exts[$n];

                if ($ext == $this->_sCacheExt) {

                    $aData = $this->_readCacheItem($sEntry);
                    unset($aData['data']);

                    $aData['_lifetime'] = $aData['tsExpire'] - date('U');
                    $aData['_age'] = date('U') - filemtime($sEntry);
                    $aData['_lifetime'] = filemtime($sEntry) > filemtime($this->_sCacheRemovefile) ? $aData['tsExpire'] - date('U') : -1;

                    $bAdd = false;

                    if (isset($aFilter['ageOlder']) && ($aData['_age'] > $aFilter['ageOlder'])) {
                        $bAdd = true;
                    }
                    if (isset($aFilter['lifetimeBelow']) && ($aData['_lifetime'] < $aFilter['lifetimeBelow'])) {
                        $bAdd = true;
                    }
                    if (isset($aFilter['lifetimeGreater']) && ($aData['_lifetime'] > $aFilter['lifetimeGreater'])) {
                        $bAdd = true;
                    }
                    if (isset($aFilter['ttlBelow']) && ($aData['iTtl'] < $aFilter['ttlBelow'])) {
                        $bAdd = true;
                    }
                    if (isset($aFilter['ttlGreater']) && ($aData['iTtl'] > $aFilter['ttlGreater'])) {
                        $bAdd = true;
                    }

                    if (!is_array($aFilter) || !count($aFilter)) {
                        $bAdd = true;
                    }

                    if ($bAdd) {
                        $aReturn[$sEntry] = $aData;
                    }
                }
            }
        }
        return $aReturn;
    }

    // ----------------------------------------------------------------------
    /**
     * get currently activated module
     * @since 2.6
     * @return string
     */
    public function getCurrentModule(): string
    {
        return $this->sModule;
    }

    // ----------------------------------------------------------------------
    /**
     * get current cache id
     * @since 2.6
     * @return string
     */
    public function getCurrentId(): string
    {
        return $this->sCacheID;
    }

    // ----------------------------------------------------------------------
    /**
     * get a flat array of module names that saved a cache item already
     * @since 2.5
     * 
     * @return array
     */
    public function getModules(): array
    {
        $aReturn = [];
        foreach (glob($this->_sCacheDir . '/*') as $sEntry) {
            if (is_dir($sEntry)) {
                $aReturn[] = basename($sEntry);
            }
        }
        return $aReturn;
    }

    // ----------------------------------------------------------------------
    /**
     * delete a single cache item if it exist.
     * It returns true if a cache item was deleted. It returns false if it
     * does not exist (yet) or the deletion failed.
     * @return     boolean
     */
    public function delete(): bool
    {
        if (!file_exists($this->_sCacheFile)) {
            return false;
        }
        if (unlink($this->_sCacheFile)) {
            $this->_aCacheInfos['data'] = false;
            $this->_aCacheInfos['stat'] = false;
            return true;
        }
        return false;
    }
    // ----------------------------------------------------------------------
    /**
     * delete all existing cached items of the set module
     * Remark: this method should be used in an admin interface or cronjob only.
     * It makes a recursive filesystem scan and is quite slow.
     * 
     * @since 2.6
     * @param boolean   $bShowOutput   flag: show output? default: false (=no output)
     * @return     boolean
     */
    public function deleteModule(bool $bShowOutput = false): bool
    {
        if (!$this->sModule) {
            return false;
        }
        $this->cleanup(0, $bShowOutput);
        $this->removefileDelete();
        return rmdir($this->_sCacheDir . "/" . $this->sModule);
    }

    // ----------------------------------------------------------------------
    /**
     * public function dump() - dump variables of cache class
     * @return     true
     */
    public function dump(): bool
    {
        $sReturn = '';
        $sReturn .= "<table>
        <!--<strong>" . __METHOD__ . "()<br></strong>-->
        <tr><td><strong>module: </strong></td><td>" . $this->sModule . "</td></tr>
        <tr><td><strong>ID: </strong></td><td>" . $this->sCacheID . "</td></tr>
        <tr><td><strong>filename: </strong></td><td>" . $this->_sCacheFile . "</td></tr>
        ";
        if (file_exists($this->_sCacheFile)) {
            $sReturn .= "
            <tr><td><strong>size: </strong></td><td>" . filesize($this->_sCacheFile) . " byte</td></tr>
            <tr><td><strong>created: </strong></td><td>" . filemtime($this->_sCacheFile) . " (" . date("d.m.y - H:i:s", filemtime($this->_sCacheFile)) . ")</td></tr>
            <tr><td><strong>ttl: </strong></td><td>" . $this->getTtl() . " s</td></tr>
            " . ($this->getTtl() < 0
                ? ''
                : "<tr><td><strong>expires: </strong></td><td>" . $this->getExpire() . " (" . date("d.m.y - H:i:s", $this->getExpire()) . ") "
                . ($this->iExpired() > 0 ? '<span class="outdated">' . $this->iExpired() . ' s EXPIRED</span>' : ' ... <span class="ok">' . -$this->iExpired() . " s left") . "</td></tr>"
            )
                . "<tr><td><strong>age: </strong></td><td>" . $this->getAge() . " s</td></tr>"
                . "<tr><td><strong>reference file: </strong></td><td>" . ($this->getRefFile() ? $this->getRefFile() : 'NONE') . " </td></tr>"
                . "<br>
            </table>
            <strong>data in the cache:</strong>
            <pre>";
            $sReturn .= htmlentities(print_r($this->_aCacheInfos, 1));
            $sReturn .= "</pre><hr>";
        } else {
            $sReturn .= "</table>Cache file does not exist (yet).<br>
            Maybe the _sDivider was changed in another cache instance.<br>
            ";
        }
        echo $sReturn;
        return true;
    }

    // ----------------------------------------------------------------------
    /**
     * public function getCacheAge() - get age in seconds of exisiting cachefile
     * @return     int  age in seconds; -1 if cachefiles does not exist
     */
    public function getAge(): int
    {
        if (!isset($this->_aCacheInfos['stat'])) {
            $this->_getAllCacheData();
        }
        if (!isset($this->_aCacheInfos['stat'])) {
            return -1;
        }
        return date("U") - $this->_aCacheInfos['stat']['mtime'];
    }

    // ----------------------------------------------------------------------
    /**
     * public function getExpire() - get TS of cache expiration
     * @since 2.0
     * @return     int  unix ts of cache expiration
     */
    public function getExpire(): int
    {
        return $this->_tsExpire;
    }

    // ----------------------------------------------------------------------
    /**
     * public function getRefFile() - get reference file that invalidates the
     * cache item
     * @since 2.8
     * @return  string  filename of reference file
     */
    public function getRefFile(): string
    {
        return $this->_sRefFile;
    }

    // ----------------------------------------------------------------------
    /**
     * public function getTtl() - get TTL of cache in seconds
     * @since 2.0
     * @return     int  get ttl of cache
     */
    public function getTtl(): int
    {
        return $this->_iTtl;
    }


    // ----------------------------------------------------------------------
    /**
     * public function isExpired() - cache expired? To check it 
     * you must use ttl while writing data, i.e.
     * $oCache->write($sData, [$iTtl, [RefFile]]);
     * A cache item is expired if
     *   - the module remove file is newer
     *   - if a ttl was set: the min. ttl  
     * @since 2.0
     * @return     bool  cache is expired?
     */
    public function isExpired(): bool
    {
        // cache data already exist? $this->_aCacheInfos is set by constructor 
        // in the read method
        if (!isset($this->_aCacheInfos['data'])) {
            return true;
        }
        // check if remove file was touched
        $iAgeOfCache = $this->getAge();
        if ($iAgeOfCache > (date("U") - filemtime($this->_sCacheRemovefile))) {
            return true;
        }
        // check if cahe item is expired
        if ($this->_tsExpire && (date("U") > $this->_tsExpire)) {
            return true;
        }
        // check timestamp of reference file (if one was set)
        return !$this->isNewerThanFile();
    }
    // ----------------------------------------------------------------------
    /**
     * public function iExpired() - get time in seconds when cachefile expires
     * you must use ttl while writing data, i.e.
     * $oCache->write($sData, $iTtl);
     * @since 2.1
     * @return     int  expired time in seconds; negative if cache is not expired
     */
    public function iExpired(): int|bool
    {
        if (!$this->_tsExpire) {
            return true;
        }
        return date("U") - $this->_tsExpire;
    }

    // ----------------------------------------------------------------------
    /**
     * function isNewerThanFile($sRefFile) - is the cache (still) newer than
     * a reference file? This function returns difference of mtime of both
     * files.
     * @since 2.0
     * @param   string   $sRefFile  local filename
     * @return  integer  time in sec how much the cache file is newer; negative if reference file is newer
     */
    public function isNewerThanFile(string|null $sRefFile = null): int|bool
    {
        if (is_null($sRefFile)) {
            $sRefFile = $this->_sRefFile;
        }
        if (!$sRefFile || !file_exists($sRefFile)) {
            return true;
        }
        if (!isset($this->_aCacheInfos['stat'])) {
            return false;
        }

        $aTmp = stat($sRefFile);
        $iTimeRef = $aTmp['mtime'];

        //echo $this->_sCacheFile."<br>".$this->_aCacheInfos['stat']['mtime']."<br>".$iTimeRef."<br>".($this->_aCacheInfos['stat']['mtime'] - $iTimeRef);
        return $this->_aCacheInfos['stat']['mtime'] - $iTimeRef;
    }

    // ----------------------------------------------------------------------
    /**
     * load cache item from a given file - this is like reverse engineering 
     * by reading data file; needed for a admin interface only
     * 
     * @since 2.6
     * @param string  $sFile  filename with full path
     * @return boolean
     */
    public function loadCachefile(string $sFile): bool
    {
        $this->_sCacheFile = false;
        $this->sModule = false;
        $this->sCacheID = false;
        if (file_exists($sFile)) {
            $this->_sCacheFile = $sFile;
            $this->_getAllCacheData();
            // reverse engineered _sCacheDirDivider
            $sRelfile = str_replace($this->_sCacheDir . "/" . $this->sModule, '', $this->_sCacheFile);
            $sTmp = preg_replace('#^\/([0-9a-f]*)([/\.].*)#', '$1', $sRelfile);
            $this->_sCacheDirDivider = strlen($sTmp);
            return true;
        }
        return false;
    }

    // ----------------------------------------------------------------------
    /**
     * public function getCacheData() - read cachedata if it exist
     * @return     mixed  cachedata or false if cache does not exist
     */
    public function read(): mixed
    {
        if (!isset($this->_aCacheInfos['data'])) {
            $this->_getAllCacheData();
        }
        if (!isset($this->_aCacheInfos['data'])) {
            return false;
        }
        return $this->_aCacheInfos['data'];
    }

    // ----------------------------------------------------------------------
    /**
     * delete module based remove file
     *
     * @since 2.6
     * @return boolean
     */
    public function removefileDelete(): bool
    {
        if (file_exists($this->_sCacheRemovefile)) {
            return unlink($this->_sCacheRemovefile);
        }
        return false;
    }
    // ----------------------------------------------------------------------
    /**
     * make all cache items invalid by touching the remove file
     *
     * @since 2.6
     * @return boolean
     */
    public function removefileTouch(): bool
    {
        return touch($this->_sCacheRemovefile);
    }
    // ----------------------------------------------------------------------
    /**
     * public function setData($data) - set cachedata into cache object
     * data can be any serializable type, like string, array or object
     * Remark: You additionally need to call the write() method to store data in the filesystem
     * @since 2.0
     * @param      mixed  $data  data to store in cache
     * @return   boolean
     */
    public function setData(mixed $data): bool
    {
        $this->_aCacheInfos['data'] = $data;
        return true;
    }

    /**
     * set new cache id; it keeps current module
     * @param  string  $sCacheID  cache-id (must be uniq within a module; used to generate filename of cachefile)
     *                            adding the cache id is recommendet, otherwise
     *                            you addiionally need to call setCacheId()
     * @return boolean
     */
    public function setCacheId(string $sCacheID): bool
    {
        $this->sCacheID = $sCacheID;

        $this->_setup();

        $this->_getCacheFilename();
        $this->read();

        return true;
    }
    /**
     * set module
     * @param  string  $sModule   name of module or app that uses the cache
     * @param  string  $sCacheID  optional: cache-id (must be uniq within a module; used to generate filename of cachefile)
     *                            adding the cache id is recommendet, otherwise
     *                            you addiionally need to call setCacheId()
     * @return boolean
     */
    public function setModule(string $sModule, string $sCacheID = ''): bool
    {
        $this->sModule = $sModule;
        return $this->setCacheId($sCacheID);
    }
    // ----------------------------------------------------------------------
    /**
     * public function setRefFile() - set a reference file that invalidates the
     * cache if the file is newer than the stored item
     * @since 2.8
     * @param   string   $sFile  filename of reference file with full path
     * @return  boolean
     */
    public function setRefFile(string $sFile): bool
    {
        $this->_sRefFile = $sFile;
        return true;
    }
    // ----------------------------------------------------------------------
    /**
     * public function setTtl() - set TTL of cache in seconds
     * You need to write the cache data to ap
     * Remark: You additionally need to call the write() method to store a new ttl value with 
     * data in the filesystem
     * @since 2.0
     * @param   int  $iTtl  ttl value in seconds
     * @return  int  get ttl of cache
     */
    public function setTtl(int $iTtl): int
    {
        return $this->_iTtl = $iTtl;
    }

    // ----------------------------------------------------------------------
    /**
     * public function touch() - touch cachefile if it exist
     * For cached data a new expiration based on existing ttl will be set
     * @return boolean 
     */
    public function touch(): bool
    {
        if (!file_exists($this->_sCacheFile)) {
            return false;
        }

        // touch der Datei reicht nicht mehr, weil tsExpire verloren ginge
        if (!$this->_iTtl) {
            $bReturn = touch($this->_sCacheFile);
        } else {
            $bReturn = $this->write();
        }

        $this->_getAllCacheData();

        return $bReturn;
    }

    // ----------------------------------------------------------------------
    /**
     * Write data into a cache. 
     * - data can be any serializable type, like string, array or object
     * - set ttl in s (from now); optional parameter
     * 
     * @param      mixed     $data      data to store in cache
     * @param      null|int  $iTtl      optional: time in s when content cache expires; default: null
     * @param      string    $sRefFile  optional: set a reference file that invalidates the cache if it is newer
     * @return     bool      success of write action
     */
    public function write(mixed $data = false, null|int $iTtl = null, string $sRefFile = ''): bool
    {
        if (!$this->_sCacheFile) {
            return false;
        }
        $sDir = dirname($this->_sCacheFile);
        if (!is_dir($sDir)) {
            if (!mkdir($sDir, 0750, true)) {
                die("ERROR: unable to create directory " . $sDir);
            }
        }

        if (!$data === false) {
            $this->setData($data);
        }

        if (!is_null($iTtl)) {
            $this->setTtl($iTtl);
        }
        if ($sRefFile) {
            $this->setRefFile($sRefFile);
        }

        $aTmp = [
            'iTtl' => $this->_iTtl,
            'sRefFile' => $this->_sRefFile,
            'tsExpire' => date("U") + $this->_iTtl,
            'module' => $this->sModule,
            'cacheid' => $this->sCacheID,
            'data' => isset($this->_aCacheInfos['data']) ? $this->_aCacheInfos['data'] : '',
        ];
        return file_put_contents($this->_sCacheFile, serialize($aTmp));
    }
}

// ----------------------------------------------------------------------
