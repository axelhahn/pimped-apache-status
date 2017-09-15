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
$sDirBS = $sSelfURL . '/javascript/bootstrap3';
$sHead = '<link rel="stylesheet" type="text/css" href="' . $sSelfURL . '/templates/' . basename(dirname(__FILE__)) . '/style.css" media="screen">'
        /*
        . '<link href="' . $sDirBS . '/css/bootstrap.min.css" rel="stylesheet">'
        . '<link href="' . $sDirBS . '/css/bootstrap-theme.min.css" rel="stylesheet">'
        . '<script src="' . $sDirBS . '/js/bootstrap.min.js" type="text/javascript"></script>'
        . '<script src="' . $sSelfURL . '/javascript/knob/jquery.knob.min.js" type="text/javascript"></script>'
         * 
         */
        // . '<script src="' . $sSelfURL . '/javascript/raphaeljs/raphael.min.js" type="text/javascript"></script>'
        // . '<script src="' . $sSelfURL . '/javascript/morrisjs/morris.min.js" type="text/javascript"></script>'
        ;

// add a meta refresh tag if needed
if ($aEnv["active"]["reload"]) {
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

// TODO
// ob_start();
$oLog->add(__FILE__ . ' start ');
// TODO
// $content = ob_get_contents();
// ob_end_clean();

// $oMsg->add('I am a beta version', 'info');
// $oMsg->add('Error while showing error message', 'error');
$sBody = '
    
    <header class="main-header">
    <!-- Logo -->
    <a href="#" class="logo">
      <span class="logo-mini"></span>
      <span class="logo-lg"></span>
    </a>
    
    <nav class="navbar navbar-static-top">
    
        <!-- Sidebar toggle button
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
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
          </ul>
          
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            ' . (array_key_exists("servers", $aEnv['links']) ? '
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'
                . $aCfg['icons']['group'] . $aLangTxt['menuGroup']
                . ' <span>' . $aEnv['active']['group']
                . ($aEnv['active']['servers'] ? ' -&rsaquo; ' . $aEnv['active']['servers'] : '')
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
        <li><a href="?"><span style="font-size:150%; white-space: normal;">'
                // . $aCfg['icons']['title'] 
                . $aEnv["project"]["title"]
                . '
                </span>
                <br>
                <span>v' . $aEnv["project"]["version"] . '</span><br>
                <span id="checkversion"></span><br>
            </a>
        </li>
        </ul>
        
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
      
        
        <li class="header">MAIN NAVIGATION</li>
        '
        .$oDatarenderer->renderLI($aEnv["links"]["views"])
        .'
            

        <li class="header">ADMIN</li>
        '
        .$oDatarenderer->renderLI($aEnv["links"]["viewsadmin"])
        .'
        <!-- 
        <li class="header">DUMMIES BELOW</li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
          </a>
        </li>
        

        <li class="header">DUMMIES BELOW</li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../../index.html"><i class="fa fa-circle-o"></i> Dashboard v1</a></li>
            <li><a href="../../index2.html"><i class="fa fa-circle-o"></i> Dashboard v2</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-files-o"></i>
            <span>Layout Options</span>
            <span class="pull-right-container">
              <span class="label label-primary pull-right">4</span>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../layout/top-nav.html"><i class="fa fa-circle-o"></i> Top Navigation</a></li>
            <li><a href="../layout/boxed.html"><i class="fa fa-circle-o"></i> Boxed</a></li>
            <li><a href="../layout/fixed.html"><i class="fa fa-circle-o"></i> Fixed</a></li>
            <li><a href="../layout/collapsed-sidebar.html"><i class="fa fa-circle-o"></i> Collapsed Sidebar</a></li>
          </ul>
        </li>
        <li>
          <a href="../widgets.html">
            <i class="fa fa-th"></i> <span>Widgets</span>
            <span class="pull-right-container">
              <small class="label pull-right bg-green">Hot</small>
            </span>
          </a>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-pie-chart"></i>
            <span>Charts</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../charts/chartjs.html"><i class="fa fa-circle-o"></i> ChartJS</a></li>
            <li><a href="../charts/morris.html"><i class="fa fa-circle-o"></i> Morris</a></li>
            <li><a href="../charts/flot.html"><i class="fa fa-circle-o"></i> Flot</a></li>
            <li><a href="../charts/inline.html"><i class="fa fa-circle-o"></i> Inline charts</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-laptop"></i>
            <span>UI Elements</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../UI/general.html"><i class="fa fa-circle-o"></i> General</a></li>
            <li><a href="../UI/icons.html"><i class="fa fa-circle-o"></i> Icons</a></li>
            <li><a href="../UI/buttons.html"><i class="fa fa-circle-o"></i> Buttons</a></li>
            <li><a href="../UI/sliders.html"><i class="fa fa-circle-o"></i> Sliders</a></li>
            <li><a href="../UI/timeline.html"><i class="fa fa-circle-o"></i> Timeline</a></li>
            <li><a href="../UI/modals.html"><i class="fa fa-circle-o"></i> Modals</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-edit"></i> <span>Forms</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../forms/general.html"><i class="fa fa-circle-o"></i> General Elements</a></li>
            <li><a href="../forms/advanced.html"><i class="fa fa-circle-o"></i> Advanced Elements</a></li>
            <li><a href="../forms/editors.html"><i class="fa fa-circle-o"></i> Editors</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-table"></i> <span>Tables</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../tables/simple.html"><i class="fa fa-circle-o"></i> Simple tables</a></li>
            <li><a href="../tables/data.html"><i class="fa fa-circle-o"></i> Data tables</a></li>
          </ul>
        </li>
        <li>
          <a href="../calendar.html">
            <i class="fa fa-calendar"></i> <span>Calendar</span>
            <span class="pull-right-container">
              <small class="label pull-right bg-red">3</small>
              <small class="label pull-right bg-blue">17</small>
            </span>
          </a>
        </li>
        <li>
          <a href="../mailbox/mailbox.html">
            <i class="fa fa-envelope"></i> <span>Mailbox</span>
            <span class="pull-right-container">
              <small class="label pull-right bg-yellow">12</small>
              <small class="label pull-right bg-green">16</small>
              <small class="label pull-right bg-red">5</small>
            </span>
          </a>
        </li>
        <li class="treeview active">
          <a href="#">
            <i class="fa fa-folder"></i> <span>Examples</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="invoice.html"><i class="fa fa-circle-o"></i> Invoice</a></li>
            <li><a href="profile.html"><i class="fa fa-circle-o"></i> Profile</a></li>
            <li><a href="login.html"><i class="fa fa-circle-o"></i> Login</a></li>
            <li><a href="register.html"><i class="fa fa-circle-o"></i> Register</a></li>
            <li><a href="lockscreen.html"><i class="fa fa-circle-o"></i> Lockscreen</a></li>
            <li><a href="404.html"><i class="fa fa-circle-o"></i> 404 Error</a></li>
            <li><a href="500.html"><i class="fa fa-circle-o"></i> 500 Error</a></li>
            <li class="active"><a href="blank.html"><i class="fa fa-circle-o"></i> Blank Page</a></li>
            <li><a href="pace.html"><i class="fa fa-circle-o"></i> Pace Page</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-share"></i> <span>Multilevel</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>
            <li>
              <a href="#"><i class="fa fa-circle-o"></i> Level One
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="#"><i class="fa fa-circle-o"></i> Level Two</a></li>
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Level Two
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Level Three</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Level Three</a></li>
                  </ul>
                </li>
              </ul>
            </li>
            <li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>
          </ul>
        </li>
        <li><a href="../../documentation/index.html"><i class="fa fa-book"></i> <span>Documentation</span></a></li>
        <li class="header">LABELS</li>
        <li><a href="#"><i class="fa fa-circle-o text-red"></i> <span>Important</span></a></li>
        <li><a href="#"><i class="fa fa-circle-o text-yellow"></i> <span>Warning</span></a></li>
        <li><a href="#"><i class="fa fa-circle-o text-aqua"></i> <span>Information</span></a></li>
        
        -->
      </ul>
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
            $sTiles=$sTiles ? '<div class="box box-solid"><div class="box-body"><div id="divtiles">'.$sTiles.'</div></div></div>' :'';
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

$sTabNavi = isset($sTabNavi) ? $sTabNavi : $oDatarenderer->renderTabs($aEnv["links"]["views"]);

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

