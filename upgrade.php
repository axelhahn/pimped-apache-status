<?php
/*
 * PIMPED APACHE-STATUS
 * 
 * CONVERTER for PHP config files (v 1.x) to new JSON (2.x)
 * 
 */

require_once './classes/confighandler.class.php';
$oCfg = new confighandler("internal-env");
global $aServergroups, $aCfg;
require_once './classes/cdnorlocal.class.php';
$oCdn=new axelhahn\cdnorlocal();


function renameOld($filename){
    $bakfile=dirname($filename).'/__2_delete_'.basename($filename);
    if (file_exists($bakfile)){
        echo "SKIP: <span class=\"file\">$bakfile</span> already exists. It seems the updater was executed already.<br>";
        return false;
    }
    if (file_exists($filename)){
        rename($filename, $bakfile);
    }
    if (file_exists($filename) || !file_exists($bakfile)){
        echo "<span class=\"error\">ERROR: $filename was not renamed to $bakfile.</span><br>";
    } else {
        echo "OK, <span class=\"file\">$filename</span> was renamed to <span class=\"file\">$bakfile</span>.<br>";
    }
    return true;
}
?>
<html>
    <head>
        <link href="<?php echo $oCdn->getFullUrl('font-awesome/4.7.0/css/font-awesome.min.css'); ?>" rel="stylesheet">
    </head>
</html>
<style>
    body{color:#333; background:#eee; background: linear-gradient(-10deg, #abc,#fff,#ccc) fixed; font-family: "arial";}
    body>div{margin: 1em 20% 3em; background: #f8f8f8; border: 2px solid #aaa; border-radius: 1em; box-shadow: 0 0 3em #888; padding: 1em 1em 3em;}
    a{color:#88c; padding: 0.3em; background: #e0e0ff ; border: 1px solid #ccc; text-decoration: none;}
    h1{color:#89c;}
    h2{color:#67a; margin: 2em 0 0;}
    .error{color:#a00;}
    .file{color:#970;}
</style>
<body>
    <div>
        <h1><i class="fa fa-magic" aria-hidden="true"></i> Pimped Apache Status :: Converter for config files</h1>
         This converter upgrades the config files from version 1.x to 2.x<br>
         It creates JSON files in config directory and renames the old configs.

        <h2>Loading old config</h2>
        <?php

        // ---------- load 
        if (!@include("config/config_user.php")) {
            echo "No user config <span class=\"file\">config/config_user.php</span> was found.<br>"
            . "Maybe the the converter was executed already.<br>";
        }else {
            echo "OK, <span class=\"file\">config/config_user.php</span> was read.";
            if(!isset($aCfg['auth']) || !count($aCfg['auth'])){
                    $aCfg['auth']=false;
            }

            // ---------- write
            echo "<h2>Writing new config</h2>";
            $oCfg->setCfgId("config_servers");
            $oCfg->set($aServergroups);
            echo "OK, <span class=\"file\">config/config_servers.json</span> was written.<br>";
            $oCfg->setCfgId("config_user");
            $oCfg->set($aCfg);
            echo "OK, <span class=\"file\">config/config_user.json</span> was written.<br>";
        }

            // ---------- remove old
            echo "<h2>Removing old config</h2>";
            // renameOld("config/config_default.php");
            renameOld("config/config_user.php");
            // renameOld("config/config_user_default.php");

        // ---------- done
        ?>

        <h2>Done</h2>
        go to Pimped Apache Status
            <a href="./"><i class="fa fa-tachometer" aria-hidden="true"></i> Monitoring</a>
            <a href="./admin/?"><i class="fa fa-cog"></i> Admin</a><br>
    </div>
</body>