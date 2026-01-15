<?php

/*
 * PIMPED APACHE-STATUS
 * DEFAULT template
 * 
 */


// ======================================================================
// html header
// ======================================================================
// default CSS and JS
$sHead = '<link rel="stylesheet" type="text/css" href="' . $sSelfURL . '/templates/' . basename(dirname(__FILE__)) . '/style.min.css" media="screen">'
        ;

// add a meta refresh tag if needed
if ($aEnv["active"]["reload"] && !($adminindex??false)) {
    $sHead.='<meta http-equiv="refresh" content="' . $aEnv["active"]["reload"] . '">';
}

// ======================================================================
// 
// generate content
// 
// ======================================================================
// ----------------------------------------------------------------------
// first I draw menu bar on top. It contains
// - Project and version in a H1 tag
// - top right:
//     - driopdown menus to select reload timer, skin and language
// - a dropdown to select a server or servergroup
// - a reload button
// ----------------------------------------------------------------------

$oLog->add(__FILE__ . ' start ');
$sUrlHome=(isset($_SERVER['REQUEST_URI']) && strstr($_SERVER['REQUEST_URI'], '/admin/') ? './../?' : '?' );
$sBody = '
    
    <header class="main-header">
    <!-- Logo -->
    <a href="#" class="logo">
      <span class="logo-mini"></span>
      <span class="logo-lg"></span>
    </a>
    
    <nav class="navbar navbar-static-top">
    
        <!-- Sidebar toggle button
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </a>
        -->
      
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          
        </div>
        
          <ul class="nav navbar-nav navbar-right" style="margin-right: 0.5em;">
            <li>
            ' . (array_key_exists("servers", $aEnv['links']) ? '
                <a href="#"
                    onclick="location.reload();" 
                >' . $aCfg['icons']['refresh'] . '<span>' . date("H:i:s")
                . '</span> ' . $aLangTxt['lblReload'] . '
                </a>
            </li>
                ' : ''
        ) . (array_key_exists("reload", $aEnv["links"]) ? '
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'
                . $aLangTxt['menuReload']
                . ' <span>' . ($aEnv['active']['reload'] ? $aEnv['active']['reload'] . 's' : '-') . '</span> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                     ' . $oDatarenderer->renderLI($aEnv["links"]["reload"]) . '
                    </ul>
                </li>
                ' : ''
        ) . '
            ' . (array_key_exists("skins", $aEnv["links"]) ? '
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'
                . $aCfg['icons']['skin'] . $aLangTxt['menuSkin']
                . ' <span>' . $aEnv['active']['skin'] . '</span> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                     ' . $oDatarenderer->renderLI($aEnv["links"]["skins"]) . '
                    </ul>
                </li>
                ' : ''
        ) . '
            ' . (array_key_exists("lang", $aEnv["links"]) ? '
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'
                . $aCfg['icons']['lang'] . $aLangTxt['menuLang']
                . ' <span>' . $aEnv['active']['lang'] . '</span> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                     ' . $oDatarenderer->renderLI($aEnv["links"]["lang"]) . '
                    </ul>
                </li>
                ' : ''
        ) . '
            ' . (isset($_SESSION['lastUser']) && $_SESSION['lastUser'] ? '
                <li class="dropdown">
                    <a href="?logout">'. $aCfg['icons']['logout'] . $_SESSION['lastUser'].'</a>
                </li>
                ' : ''
        ) . '
          </ul>
          
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            ' . (array_key_exists("servers", $aEnv['links']) ? '
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'
                . $aCfg['icons']['group'] . $aLangTxt['menuGroup']
                . ' <span>' . $aEnv['active']['group']
                . ($aEnv['active']['servers'] ? ' -&rsaquo; ' . $aCfg['icons']['server'].' '.$aEnv['active']['servers'] : '')
                . '</span> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu" id="serverlist">'
                        . $oDatarenderer->renderLI($aEnv["links"]["servers"]) . '
                    </ul>
                </li>
                ' : ''
        ) . '
          
              
          </ul>
        </div><!--/.nav-collapse -->
      
    </nav>
    </header>
    
  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar" style="padding-top: 0; z-index: 1050;">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <ul class="sidebar-menu">
        <li class="treeview">
            <a href="'.$sUrlHome.'"><span style="font-size:150%; white-space: normal;">'
                // . $aCfg['icons']['title'] 
                . $aEnv["project"]["title"]
                . '
                </span>
                <br>
                <span>v' . $aEnv["project"]["version"] . '</span><br>
            </a>
        </li>
        <li class="treeview">
            <span id="checkversion"></span>
        </li>
        </ul>
        
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
      
        '.(isset($aEnv["links"]["views"]) && count($aEnv["links"]["views"])
            ? '<li class="header">'.$aLangTxt['menuHeaderMonitoring'].'</li>'
                .$oDatarenderer->renderLI($aEnv["links"]["views"])
            : ''
        )
        .(isset($aEnv["links"]["viewsadmin"]) && count($aEnv["links"]["viewsadmin"])
            ? '<li class="header">'.$aLangTxt['menuHeaderConfig'].'</li>'
                .$oDatarenderer->renderLI($aEnv["links"]["viewsadmin"])
            : ''
        )
        .'
      </ul>
      <div id="divgotop">
        <a href="#" class="scroll-link">'.$aCfg['icons']['gotop'].$aLangTxt['gotop'].'</a>
      </div>
      
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- =============================================== -->


  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
  
    <!-- Content Header (Page header) -->
    
    <!--
    <section class="content-header">
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Examples</a></li>
        <li class="active">Blank page</li>
      </ol>
    </section>
    -->


    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      
        
        ';
            // ----------------------------------------------------------------------
            // add a DIV with the tiles
            // ----------------------------------------------------------------------

            $sTiles='';
            global $aSrvStatus;
            if ($aSrvStatus && is_array($aSrvStatus) && count($aSrvStatus)) {

                $oLog->add(__FILE__ . ' start tiles');
                foreach ($oDatarenderer->getValidTiles() as $sTilename) {
                    $sTiles.=$oDatarenderer->renderTile($sTilename);
                }
                $oLog->add(__FILE__ . ' tiles done');
            }
            $sTiles=$sTiles ? '<br><div class="row">'.$sTiles.'</div>' :'';
            $sBody.=$sTiles.'
        
      <!-- /.box -->
      
          ';
        // ----------------------------------------------------------------------
        // add Startup-Logs if any exists
        // ----------------------------------------------------------------------
        $sBody.=$oMsg->render();
        $sBody.='


        <!-- CONTENT -->
          '.$content.'
        <!-- /CONTENT -->
      
      
    </section>

    <!-- /.content -->
    

  </div>
  <!-- /.content-wrapper -->


    <span id="h3menu"></span> 
    ';





// ----------------------------------------------------------------------
// add a DIV with the content
// 
// - a menu for the views with tabs 
// - a DIV wih main content
// - a DIV with a link to jump to top of page
// ----------------------------------------------------------------------

$sTabNavi = isset($sTabNavi) ? $sTabNavi : 
        (isset($aEnv["links"]["views"]) 
            ? $oDatarenderer->renderTabs($aEnv["links"]["views"])
            : ''
        )
        ;

/*
if (array_key_exists("views", $aEnv["links"])) {
    $sBody.='
        <div id="divmainbody">
            ' . $sTabNavi .
            '<div id="divmaincontent">
                <!--
                    <h2>' 
                    // . $aLangTxt["view_" . $aEnv["active"]["view"] . "_label"] 
                    . '</h2>
                -->

                <div class="subh2">' .
                    $content .
               '</div>
            </div>
        </div>
        <div id="divgotop">
            <a href="#"> ^ <span>' . $aLangTxt['lblLink2Top'] . '</span></a>
        </div>
        ';
}
*/


// ======================================================================
// put header and body to the page object
// ======================================================================
$oPage->setHeader($sHead);
$oPage->setContent($sBody);
$oLog->add(__FILE__ . ' done');

