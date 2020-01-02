#!/usr/bin/env php
<?php
/*
 * 
 * PIMPED APACHE-STATUS :: C L I  configurator
 * 
 */
require_once __DIR__ . '/../classes/cli.class.php';
require_once __DIR__ . '/../classes/configdata.class.php';
require_once __DIR__ . '/../classes/configserver.class.php';

// language texts "en"
global $aLangTxt; require_once __DIR__.'/../lang/en.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$bDebug=false;
$sCliDocsUrl='https://www.axel-hahn.de/docs/apachestatus/cli.htm';

// ----------------------------------------------------------------------
// read config data
// ----------------------------------------------------------------------

global $oCli;

$oCfg=new axelhahn\confighandler();
$oServer=new configServer();

$aEnv=$oCfg->getFullConfig("internal-env");
$aParamDefs=array(
    'label' => 'C L I  configurator :: Pimped Apache Status v'.$aEnv['project']['version'].' ('.$aEnv['project']['releasedate'].')',
    'description' => 'CLI tool to configure settings and servers',
    'params'=>array(
        'action'=>array(
            'short' => 'a',
            'value'=> CLIVALUE_REQUIRED,
            'pattern'=>'/^(create|read|update|delete)$/',
            'shortinfo' => 'Name of action',
            'description' => 'The action value is one of create|read|update|delete',
        ),
        'config'=>array(
            'short' => 'c',
            'value'=> CLIVALUE_OPTIONAL,
            // 'pattern'=>'/^...$/',
            'shortinfo' => 'The value is the name of config variable',
            'description' => 'It can be empty to show the complete config',
        ),
        'defaults'=>array(
            'short' => 'd',
            'value'=> CLIVALUE_OPTIONAL,
            // 'pattern'=>'/^...$/',
            'shortinfo' => 'The value is the name of config variable',
            'description' => 'It can be empty to show the complete config',
        ),
        'value'=>array(
            'short' => 'v',
            'value'=> CLIVALUE_REQUIRED,
            // 'pattern'=>'/^...$/',
            'shortinfo' => 'config value',
            'description' => 'Set a custum config value; for parameters --action create|update --config=VARNAME ',
        ),
        'group'=>array(
            'short' => 'g',
            'value'=> CLIVALUE_OPTIONAL,
            // 'pattern'=>'/.*/',
            'shortinfo' => 'The value is the name of group',
            'description' => 'The group is a name {string} to identify a set of servers. It can be empty to show all groups',
        ),
        'server'=>array(
            'short' => 's',
            'value'=> CLIVALUE_REQUIRED,
            'pattern'=>'/.*/',
            'shortinfo' => 'The value is the name of server',
            'description' => 'The server is a hostname {string}.',
        ),
        'status-url'=>array(
            'short' => 'u',
            'value'=> CLIVALUE_REQUIRED,
            'pattern'=>'/^http.*/',
            'shortinfo' => 'url',
            'description' => 'Url of the apache httpd server status page; for actions create and update.',
        ),
        'userpwd'=>array(
            'short' => 'p',
            'value'=> CLIVALUE_REQUIRED,
            'pattern'=>'/^.*\:.*/',
            'shortinfo' => 'user:password',
            'description' => 'User and password in syntax [username]:[password]; for actions create and update.',
        ),
        'newname'=>array(
            'short' => 'n',
            'value'=> CLIVALUE_REQUIRED,
            // 'pattern'=>'/.*/',
            'shortinfo' => 'new name for group or server',
            'description' => 'For action update only. Rename a given group or server.',
        ),
        'help'=>array(
            'short' => 'h',
            'value'=> CLIVALUE_NONE,
            'shortinfo' => 'show help',
            'description' => '',
        ),
    ),
);

$oCli=new axelhahn\cli($aParamDefs);


// ----------------------------------------------------------------------
// FUNCTIONS
// ----------------------------------------------------------------------
/**
 * prevent that root executes this script - requires php posix module on *nix
 */
function denyRoot(){
    if (function_exists("posix_getpwuid")) {
        $processUser = posix_getpwuid(posix_geteuid());
        wd("detected user: ".print_r($processUser, 1));
        if ($processUser['name'] == "root") {
            die("ERROR: Do not start the script as user root. Run it as the user of the application\n");
        }
    }
}

/**
 * get help text to a given config variable (or using first element before a ".")
 * @global array $aLangTxt  language texts (en)
 * @param string $sVarname
 * @return string|boolean
 */
function getCfgDescription($sVarname){
    global $aLangTxt;
    $aTmp=preg_split('/\\./', $sVarname);
    $sFirstKey=array_shift($aTmp);
    
    $sKey1='cfg-'.$sFirstKey;
    if(isset($aLangTxt[$sKey1])){
        return "; DESCRIPTION of [$sFirstKey]:\n; ".str_replace('<br>', "\n; ", $aLangTxt[$sKey1])."\n";
    }
    // return '; remark '.$sKey1." not found\n";
    return false;
}
/**
 * handle JSON response for groups and servers 
 * @param array   $aJson   result array of an action
 */
function handleJsonResult($aJson){
    global $oCli;
    if (isset($aJson['result']) && $aJson['result']===false) {
        quit($aJson['error']);
    }
    $oCli->color('cli');
    echo json_encode($aJson, JSON_PRETTY_PRINT)."\n";
    return true;
}

/**
 * quit with error message and exitcode <> 0
 * @param string  $sMessage  text to show
 * @param integer $iExit     optional: exitcode; default=1
 */
function quit($sMessage, $iExit=1){
    global $oCli;
    $oCli->color('error');
    echo "ERROR: $sMessage\n";
    $oCli->color('reset');
    exit($iExit);
}

/**
 * write debug output
 * @param type $s
 */
function wd($s){
    global $bDebug;
    echo $bDebug ? "DEBUG: $s\n" : '';
}
// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

if ($oCli->getvalue("help") ||!count($oCli->getopt())){
    $oCli->color('head');
    echo '; _________________________________________________________________________________________
;
;
;    '.$aParamDefs['label'].'
;
; _________________________________________________________________________________________
;
';
    $oCli->color('info');
    echo $oCli->showhelp();
    $sBase='php ./'.basename(__FILE__);
    echo '
SYNTAX:

  All commands are shown in long syntax for better reading.

  --- Handle default config (readonly) 

    READ
      show all:
      '.$sBase.' --action read --defaults

      show given item only:
      '.$sBase.' --action read --defaults=VARNAME
      REMARK: in VARNAME you can use . (dot character) as divider of subkeys
    
  --- Handle custom config

    CREATE
      create item:
      '.$sBase.' --action create --config=VARNAME --value [your value]
      REMARK: VARNAME must be a valid key in default data (see '.$sBase.' --action read --defaults)

    READ
      show all:
      '.$sBase.' --action read --config

      show given item only:
      '.$sBase.' --action read --config=VARNAME
  
    UPDATE
      update item:
      '.$sBase.' --action update --config=VARNAME --value [new value]

    DELETE
      delete item:
      '.$sBase.' --action update --config=VARNAME --value [new value]

  --- Handle groups and servers

    CREATE
      create group:
      '.$sBase.' --action create --group=NAME

      create a new server in an existing group:
      '.$sBase.' --action create --group=NAME --server=HOSTNAME \
        --status-url URL [--userpwd [user:password]]
      REMARK: The HOSTNAME is a label only.
              The URL is the server-status url of a system.
              --userpwd [user:password] is optional

    READ
      list groups:
      '.$sBase.' --action read --group

      list servers of a group:
      '.$sBase.' --action read --group=NAME

      list server settings:
      '.$sBase.' --action read --group=NAME --server=HOSTNAME

    UPDATE
      rename group:
      '.$sBase.' --action update --group=NAME --newname=NEW_NAME

      rename server name of a group:
      '.$sBase.' --action update --group=NAME --server=HOSTNAME \
        --newname=NEW_NAME

      update server settings:
      '.$sBase.' --action update --group=NAME --server=HOSTNAME \
        --status-url [new url] [--userpwd [new user:password]]

    DELETE
      delete a server in a group:
      '.$sBase.' --action delete --group=NAME --server=HOSTNAME

      delete a group:
      '.$sBase.' --action delete --group=NAME
      WARNING: this deletes a group even if it contains servers.

Output lines starting with ; (semikolon) are comments only.
See the docs for more details: '.$sCliDocsUrl.'
';
    exit(0);
}


$oCli->color('head');
echo '; '.$aParamDefs['label']."\n";
$oCli->color('reset');
denyRoot();

// ----- get prameters

if ($oCli->getvalue("config")===false 
        && $oCli->getvalue("defaults")===false
        && $oCli->getvalue("group")===false
){
    quit("ERROR: next to an action --config or --defaults or --group is required\n");
}

if ($oCli->getvalue("action")===false){
    echo "\nwhat shall we do ??\n";
    $oCli->read("action");
    $oCli->color('ok', 'OK, action is ['.$oCli->getvalue("action").']'."\n\n");
}
$sAction=$oCli->getvalue("action");


if ($oCli->getvalue("config")){
    // ----------------------------------------------------------------------
    // handle customized program settings
    // ----------------------------------------------------------------------
    $oCfg->configSet("config_user");

    $sVar=$oCli->getvalue("config");
    $sValue=$oCli->getvalue("value");
    $oCli->color('info');
    switch ($sAction){
        case 'create':
            $oCfg->configSet("internal-config_default");
            $oCli->color('error');
            $aDefault=$oCfg->get($sVar);
            $oCli->color('info');
            
            echo "; create custom var [$sVar]:\n"
                ."; default: ".json_encode($aDefault)."\n"
                ."; your value: ".json_encode($sValue)."\n"
                ;
            
            // your reach this point if a default of $sVar exists

            // FIXME: nicht abbrechen, wenn der Wert noch nicht als custom existiert
            $oCfg->configSet("config_user");
            if ($oCfg->keyExists($sVar)){
                quit("Creation aborted. The custom value already exists.\n; custom value is: ".json_encode($oCfg->get($sVar)));
            }
            if(is_array($aDefault) && !is_array($sValue)){
                quit("Creation abortet. The default value is an array. You must set a JSON in the --value parameter.\n");
            }
            $oCli->color('cli');
            echo ($oCfg->set($sValue, $sVar) ? 'OK': 'Failed') . "\n";
            // /FIXME
            
            break;
        case 'read':
            if($sVar===true){
                echo "; read all custom settings:\n";
                $oCli->color('cli');
                echo json_encode($oCfg->get(false), JSON_PRETTY_PRINT)."\n";
            } else {
                echo "; read custom settings [$sVar]:\n";
                $oCli->color('cli');
                echo json_encode($oCfg->get($sVar), JSON_PRETTY_PRINT)."\n";
            }
            break;
            
        case 'update':
            echo "; update custom var [$sVar]:\n";
            if ($oCfg->keyExists($sVar)){
                echo "; current custom value: ".json_encode($oCfg->get($sVar))."\n"
                    ."; new value: ".json_encode($sValue)."\n"
                    ;
                $oCli->color('cli');
                echo ($oCfg->set($sValue, $sVar) ? 'OK': 'Failed') . "\n";
            } else {
                quit("Update aborted. The custom variable does not exist (yet).");
            }
            break;
            
        case 'delete':
            echo "; delete custom var [$sVar]:\n";
            if ($oCfg->keyExists($sVar)){
                $oCli->color('cli');
                echo ($oCfg->delete($sVar) ? 'OK': 'Failed') . "\n";
            } else {
                quit("Delete aborted. The custom variable does not exist (yet).");
            }
            break;

        default:
            quit("Not implemented action: ".$sAction."\n");
            break;
    }
    $oCli->color('info');
    echo getCfgDescription($sVar);
}
elseif ($oCli->getvalue("defaults")){
    // ----------------------------------------------------------------------
    // handle default settings (readonly)
    // ----------------------------------------------------------------------
    $oCfg->configSet("internal-config_default");

    $sVar=$oCli->getvalue("defaults");
    $oCli->color('info');
    switch ($sAction){
        case 'read':
            if($sVar===true){
                echo "; read all default settings:\n";
                $oCli->color('cli');
                echo json_encode($oCfg->get(false), JSON_PRETTY_PRINT)."\n";
            } else {
                echo "; read default settings [$sVar]:\n";
                if ($oCfg->keyExists($sVar)){
                    $oCli->color('cli');
                    echo json_encode($oCfg->get($sVar), JSON_PRETTY_PRINT)."\n";
                } else {
                    quit("Read aborted. The variable does not exist.");
                }
            }
            break;
        default:
            quit("Wrong action. Default values are read only but you can override them by using a custom setting.\n --action add --config".($sVar ? '='.$sVar : '')."\n");
            break;
    }
    $oCli->color('info');    
    echo getCfgDescription($sVar);
} elseif ($oCli->getvalue("group")){
    // ----------------------------------------------------------------------
    // handle server config
    // ----------------------------------------------------------------------
    $sGroup=$oCli->getvalue("group");
    $sServer=$oCli->getvalue("server");

    $oCli->color('info');
    switch ($sAction){
        case 'create':
            if($sServer){
                // --------------------------------------------------
                // @example --action create --group=[name] --server [hostname] --status-url 'https://...'
                // --------------------------------------------------
                $sUrl=$oCli->getvalue("status-url");
                $sUserPwd=$oCli->getvalue("userpwd");
                $aItem=array(
                    'group'=>$sGroup,
                    'label'=>$sServer,
                    'status-url'=>$sUrl,
                    'userpwd'=>$sUserPwd,
                );
                echo "; create server [$sGroup] -> [$sServer]:\n"
                    ."; host data: ".json_encode($aItem)."\n"
                    ;
                handleJsonResult($oServer->addServer($aItem));
            } elseif($sGroup && $sGroup!==true){
                // --------------------------------------------------
                // @example --action create --group=[name]
                // --------------------------------------------------
                $aItem=array(
                    'label'=>$sGroup,
                );
                echo "; create group [$sGroup]:\n";
                handleJsonResult($oServer->addGroup($aItem));
            } else {
                quit("A group name is required. use --group='[Name]'\n");
            }
            break;
        case 'read':
            if($sServer){
                // --------------------------------------------------
                // @example --action read --group=[name] --server [hostname]
                // --------------------------------------------------
                echo "; read server [$sGroup] -> [$sServer]:\n";
                handleJsonResult($oServer->getServerDetails($sGroup, $sServer));
            } elseif($sGroup && $sGroup!==true){
                // --------------------------------------------------
                // @example --action read --group=[name]
                // --------------------------------------------------
                echo "; read servernames of group [$sGroup]:\n";
                handleJsonResult($oServer->getServers($sGroup));
                $oCli->color('info');
                echo "; Hint: use as additional param --server '[NAME]' to show its settings.\n";
            } else {
                echo "; read existing groups:\n";
                $oCli->color('cli');
                handleJsonResult($oServer->getGroups());
                $oCli->color('info');
                echo "; Hint: use as additional param --group='[NAME]' to list its servers.\n";
            }
            break;
        case 'update':
            $sNewName=$oCli->getvalue("newname");
            if($sServer){
                if (!$sNewName){
                    // --------------------------------------------------
                    // @example --action update --group=[name] --server [hostname] --status-url 'https://...' --userpwd [user:password]
                    // --------------------------------------------------
                    $sUrl=$oCli->getvalue("status-url");
                    $sUserPwd=$oCli->getvalue("userpwd");
                    $aItem=array(
                        'group'=>$sGroup,
                        'oldlabel'=>$sServer,
                        'label'=>$sServer,
                        'status-url'=>$sUrl,
                        'userpwd'=>$sUserPwd,
                    );
                    echo "; update server [$sGroup] -> [$sServer]:\n"
                        ."; host data: ".json_encode($aItem)."\n"
                        ;
                    handleJsonResult($oServer->setServer($aItem), JSON_PRETTY_PRINT);
                } else {
                    // --------------------------------------------------
                    // @example --action update --group=[name] --server [hostname] --newname [new-hostname]
                    // --------------------------------------------------
                    echo "; rename server [$sGroup] -> [$sServer]:\n"
                        ."; new hostname: ${sNewName}\n"
                        ;
                    $aItem=$oServer->getServerDetails($sGroup, $sServer);
                    if(!count($aItem)){
                        quit("A server [$sGroup] -> [$sServer] does not exist.\n");
                    }
                    $aItemNew=$oServer->getServerDetails($sGroup, $sNewName);
                    if(count($aItemNew)){
                        quit("A server [$sGroup] -> [$sNewName] already exists. Use a non existing server name.\n");
                    }
                    $aItem['oldlabel']=$sServer;
                    $aItem['label']=$sNewName;
                    $aItem['group']=$sGroup;
                    handleJsonResult($oServer->setServer($aItem));
                }
            } elseif($sGroup && $sGroup!==true){
                if (!$sNewName){
                    quit("Missing parameter --newname\n");
                }
                // --------------------------------------------------
                // @example --action update --group=[name] --newname [new-groupname]
                // --------------------------------------------------
                echo "; rename group [$sGroup]:\n"
                    ."; new groupname: ${sNewName}\n"
                    ;
                if(array_search($sNewName, $oServer->getGroups())!==false){
                    quit("The group ${sNewName} exists already. Use a non existing group name.\n");
                }
                $aItem['oldlabel']=$sGroup;
                $aItem['label']=$sNewName;
                handleJsonResult($oServer->setGroup($aItem));
            } else {
                quit("A group name and a server is required. use --group='[name]' --server='[hostname]'\n");
            }
            break;
        case 'delete':
            if($sServer){
                // --------------------------------------------------
                // @example --action delete --group=[name] --server [hostname]
                // --------------------------------------------------
                $aItem=array(
                    'group'=>$sGroup,
                    'oldlabel'=>$sServer,
                );
                echo "; delete server [$sGroup] -> [$sServer]:\n";
                handleJsonResult($oServer->deleteServer($aItem));
            } elseif($sGroup && $sGroup!==true){
                // --------------------------------------------------
                // @example --action delete --group=[name]
                // --------------------------------------------------
                $aItem=array(
                    'oldlabel'=>$sGroup,
                );
                echo "; delete group [$sGroup]:\n";
                handleJsonResult($oServer->deleteGroup($aItem));
            } else {
                quit("A group name and optional servername is required. use --group='[name]' --server '[hostname]'\n");
            }
            break;
        default:
            quit("Not implemented action: ".$sAction."\n");
            break;
    }    
}

$oCli->color('reset');
wd("finishing with status OK");
exit(0);
