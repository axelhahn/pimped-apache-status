/* ======================================================================
 * Axels PIMPED APACHE SERVER STATUS
 * initial functions for a page
 ====================================================================== */

/**
 * scan h3 headers and draw on the top
 * @returns {undefined}
 */
function initDrawH3list() {
    var sHtml = '';
    var sMenuid = 'h3menu';
    var sH3id = false;

    // menu animation
    // $('#sbright').hide() && window.setTimeout("$('#sbright').slideDown(400)", 50);

    var i = 0;
    $("#divmaincontent h3").each(function () {
        sH3id = this.id ? this.id : "h3" + this.innerHTML.replace(/\W/g, '');
        if (!this.id)
            this.id = sH3id;
        if (this.id != "h3menu") {
            i++;
            sHtml += '<a href="#' + sH3id + '" class="scroll-link">' + this.innerHTML.replace(/(<([^>]+)>)/ig, "") + '</a>';
        }

    });
    if (i < 2) {
        sHtml = '';
        $('#' + sMenuid).hide();
    } else {
        sHtml = '<a href="#" class="scroll-link">^</a>' + sHtml;
        $('#' + sMenuid).html(sHtml);
        $('#' + sMenuid).fadeIn(100);
    }

}

/**
 * initialize soft scrolling for links with css class "scroll-link"
 * @see http://css-tricks.com/snippets/jquery/smooth-scrolling/
 * @returns {undefined}
 */
function initSoftscroll() {
    $(function () {
        // $('a[href*=#]:not([href=#])').click(function() {
        $('a.scroll-link').click(function () {
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    $('html,body').animate({
                        scrollTop: target.offset().top - 70
                    }, 300);
                    return false;
                }
            }
        });
    });
}


/**
 * filter display of server list in the menu
 * @returns {undefined}
 */
function filterServer(s){

    var iCount=0;
    
    localStorage.setItem('apachestatusFilterServer', s);
    $('#serverlist ul li a').each(function () {
        if (!s || $(this).html().indexOf(s)>=0){
            iCount++;
            $(this).css("display", "block" );
        } else {
            $(this).css("display", "none" );
        }
    });
    
    // show count of hits
    $('#srvcounter').html( s ? iCount : '' );
    
    // enable active server
    $('#serverlist ul li.active a').each(function () {
        $(this).css("display", "block" );
    });

    // switch view between list and cascaded menu
    var bShowAsListmenu=iCount<25;
    $('#serverlist>li ').each(function () {
        bShowAsListmenu ? $(this).removeClass("dropdown-submenu") : $(this).addClass("dropdown-submenu");
        $(this).attr("onmouseover", bShowAsListmenu ?  "" : "$(this).addClass(\'open\');" );
        $(this).attr("onmouseout",  bShowAsListmenu ?  ""  : "$(this).removeClass(\'open\');" );
    });
    
    // show count of items and filtered view
    $('#serverlist li ul').each(function () {
        bShowAsListmenu ? $(this).removeClass("dropdown-menu") : $(this).addClass("dropdown-menu");
        iCountItems=$(this).find("li>a").length;
        iCountVisibleItems=0;
        oUL=$(this);
        $(oUL).find("li>a").each(function () {
            if ($(this).css("display")=="block"){
                iCountVisibleItems++;
            }
        });
        sInfotext=iCountVisibleItems!=iCountItems
            ? iCountVisibleItems + '/ ' + iCountItems 
            : iCountItems
            ;
        $(oUL).prev().children(".info").html(' ('+sInfotext+')');
        $(oUL).prev().css("display", iCountVisibleItems ? "block" : "none");
    });
}
    
/**
 * initialize server filter 
 * @since 1.25
 * @returns {undefined}
 */
function initServerFilter(){
    var iServer=0;
    $('#serverlist li').each(function () {
         iServer++;
    });
    var sFiltertext=localStorage.getItem('apachestatusFilterServer');
    if(sFiltertext===undefined){
        sFiltertext='';
    }
    if (iServer>5){
        $('#serverlist >li>a').append('<span class="info" style="margin-right: 1em;">-</span>');
        var sHtml='<li><form class="form-inline">\n\
            <div class="form-group" style="margin-bottom: 0.5em; min-width: 20em;">\n\
                <input type="text" id="esrvfilter" style="margin-left: 1em;" \n\
                    onkeypress="filterServer(this.value);" class="form-control" onkeydown="filterServer(this.value);" onkeyup="filterServer(this.value);" \n\
                    placeholder="'+aLang['srvFilterPlaceholder']+'"\n\
                    value="'+sFiltertext+'" />\n\
                <span id="srvcounter"></span>\n\
            </div></form></li>';
        $('#serverlist').prepend(sHtml);
    }
    filterServer(sFiltertext);
}

/**
 * init knob in tiles 
 * @since 1.26
 * @returns {undefined}
 */
function initKnob(){
    var i=0;
    var iValue=false;
    var sTilename=false;
    var aData=false;
    var sFgColor=$('.tile .dial').css("color");
    var sBgColor=$('.tile .dial').css("background-color");
    $("#divtiles .dial").each(function () {
        i++;
        
        // tial name is the id "dial-[server]-[tilename]" 
        // @see php class datarenderer->renderTile()
        sId=$(this).attr('id').replace(/^dial\-/, '');
        sSrv=sId.replace(/\-.*/, '');
        sTilename=sId.replace(/^.*\-/, '');
        if (sTilename){
            iValue=$(this).val();
            var oCounter = new counterhistory(sSrv, sTilename);
            aData=oCounter.getLast(50);
            console.log(aData);
            if(aData){

                $(this).knob({
                    readOnly: true,
                    fgColor: sFgColor,
                    bgColor: sBgColor,
                    'max': aData['max'],
                    'width': '50px',
                    'height': '50px',
                    thickness: 0.4
                });

            }
        }
    });
}

/**
 * init page
 * @returns {undefined}
 */
function initPage() {
    initDrawH3list();
    initSoftscroll();
    initServerFilter();
    initKnob();
    $('body').append('<div id="' + sDivPlotter + '" class="plotter"></div>');
}


/**
 * handle tabs of a 2nd tab row
 * @param {type} id
 * @returns {Boolean}
 */
function showTab(id){
    mydiv='.subh2 ';
    $(mydiv + ' > h3').hide();
    $(mydiv + ' > .subh3').hide();
    $(mydiv + ' > '+id).show(); 
    $(mydiv + ' > '+id+' + div.subh3').show();
    $(mydiv + ' li a').blur();
    return false;
}


// ----------------------------------------------------------------------
// plotter @since v1.22
// ----------------------------------------------------------------------

var sDivPlotter = "divPlotter";
var bPlotterSticky = false;


/**
 * render statistical graph and make it sticky.
 * see datarenderer.class.php - function renderTile
 * @param {string} sSrv      serverlist
 * @param {string} sVarname  name of tile
 * @param {string} sTitle    title in popup
 * @returns {undefined}
 */
function stickyGraph(sSrv, sVarname, sTitle) {
    var sav = bPlotterSticky;
    bPlotterSticky = false;

    $('#'+sDivPlotter+' .btnclose').hide();
    showGraph(sSrv, sVarname, sTitle);
    if (!sav) {
        bPlotterSticky = true;
        $('#'+sDivPlotter+' .btnclose').show();
    }
}

/**
 * render statistical graph wih onmouseover or called by stickyGraph
 * see datarenderer.class.php - function renderTile
 * @param {string} sSrv      serverlist
 * @param {string} sVarname  name of tile
 * @param {string} sTitle    title in popup
 * @returns {undefined}
 */
function showGraph(sSrv, sVarname, sTitle) {
    if (bPlotterSticky) {
        return false;
    }
    var oCounter = new counterhistory(sSrv, sVarname);
    renderHistory(sDivPlotter, oCounter.getLast(50), sTitle);

}

/**
 * hide statistical graph and remove sticky flag
 * @returns {Boolean}
 */
function hideGraph(bUnsticky) {
    if(bUnsticky){
        bPlotterSticky=false;
    }
    if (bPlotterSticky) {
        return false;
    }
    $('#' + sDivPlotter).hide();
}

/**
 * helper function: return two digit value; if value is lower 10 then a "0"
 * will be added
 * @param {integer} iVal
 * @returns {String}
 */
function twodigits(iVal) {
    return iVal > 9 ? iVal : "0" + iVal;
}

/**
 * render statistical graph; called by showGraph
 * @param {string} sDivname  target div where to put the bars 
 * @param {array}  aData     data items with date and value
 * @param {string} sTitle    title in popup
 * @returns {Boolean}
 */
function renderHistory(sDivname, aData, sTitle) {

    if (!aData || !aData['data'] || aData['data'].length < 10 || !aData['max']) {
        return false;
    }

    var sHtml = '',
            sInfo = '',
            iDataHeight = 250,
            iDataWidth = 600,
            sClass = false,
            sHint = false,
            date = false;

    sHtml += '<div class="btnclose" onclick="hideGraph(1);"> X </div>'
            + '<div class="graph">'
            + '<div class="title">' + sTitle + '</div>'
            ;

    for (var i = aData['data'].length - 1; i >= 0; i--) {
        val = aData['data'][i][1] / 1;

        date = new Date(aData['data'][i][0]);
        iH = val / aData['max'] * iDataHeight;
        iW = iDataWidth / aData['data'].length;
        sClass = (i === 0) ? 'barcur' : '';

        sHint = val + "\n\n"
                + twodigits(date.getDate()) + "." + twodigits(date.getMonth() + 1) + "." + date.getFullYear()
                + "\n" + twodigits(date.getHours()) + ":" + twodigits(date.getMinutes()) + ":" + twodigits(date.getSeconds())
                ;

        sHtml += '<div class="barcontainer" style="width:' + iW + 'px; height: ' + iDataHeight + 'px" title="' + sHint + '">'
                + '<div class="bar ' + sClass + '" style="width:' + iW + 'px; height: ' + iH + 'px; margin-top:' + (iDataHeight - iH) + 'px; "> </div>'
                + '</div>';
    }
    ihAvg = aData['avg'] / aData['max'] * iDataHeight;
    ihCurrent = aData['data'][0][1] / aData['max'] * iDataHeight;

    if (aLang) {
        sInfo += (aLang['statsCurrent']) ? aLang['statsCurrent'] + ': ' + aData['data'][0][1] + '<br>' : '';
        sInfo += (aLang['statsMin']) ? aLang['statsMin'] + ': ' + aData['min'] + '<br>' : '';
        sInfo += (aLang['statsMax']) ? aLang['statsMax'] + ': ' + aData['max'] + '<br>' : '';
        sInfo += (aLang['statsAvg']) ? aLang['statsAvg'] + ': ' + aData['avg'] + '<br>' : '';
    }
    sHtml += ''
            + '<div class="avg" style="margin-top:' + (iDataHeight - ihAvg) + 'px; "> </div>'
            + '<div class="current" style="margin-top:' + (iDataHeight - ihCurrent) + 'px; "> </div>'
            + (sInfo ? '<div class="infos">' + sInfo + '</div>' : '')
            + '</div>';

    $("#" + sDivname).html(sHtml).show();
    return true;
}
