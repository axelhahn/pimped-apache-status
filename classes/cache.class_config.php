<?php
/* 
 * CUSTOMIZE AhCache
 */

// set a custom cache direcory
// $this->_sCacheDir="/tmp/ahcache";

// if you have a large number of items to cache:
// you can limit the files per cache subdir in an indirect way
// The cachefile is a md5 hash. you can set a number of max characters
// i.e.
// cachefile     2f9ac50fd254ea4c6462105cb91ee14a
// with value 3: 2f9/ac5/0fd/254/ea4/c64/621/05c/b91/ee1/4a.cacheclass2
// with value 8: 2f9ac50f/d254ea4c/6462105c/b91ee14a.cacheclass2
// $this->_sCacheDirDivider=8;
