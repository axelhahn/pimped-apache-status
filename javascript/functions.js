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
    var sMenuid = '.sidebar-menu>li.active>span.submenu';
    var sH3id = false;

    // menu animation
    // $('#sbright').hide() && window.setTimeout("$('#sbright').slideDown(400)", 50);

    var i = 0;
    $("h3").each(function () {
        sH3id = this.id ? this.id : "h3" + this.innerHTML.replace(/\W/g, '');
        if (!this.id)
            this.id = sH3id;
        if (this.id !== "h3menu") {
            i++;
            sHtml += '<li><a href="#' + sH3id + '" class="scroll-link"><i class="fas fa-angle-right"></i>&nbsp;' + this.innerHTML.replace(/(<([^>]+)>)/ig, "") + '</a></li>';
        }

    });
    if (i < 2) {
        sHtml = '';
        $(sMenuid).hide();
    } else {
        $(sMenuid).html('<ul class="treeview-menu" style="display: block;">' + sHtml + '</ul>');
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
            if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    $('html,body').animate({
                        scrollTop: target.offset().top - 30
                    }, 300);
                    return false;
                }
            }
        });
    });
}


/**
 * filter display of server list in the menu
 * 
 * @param {string} s  filtertext
 * @returns {undefined}
 */
function filterServer(s) {

    var iCount = 0;
    if (s === 'null') {
        s = '';
    }
    localStorage.setItem('apachestatusFilterServer', s);
    $('#serverlist ul li a').each(function () {
        if (!s || $(this).html().indexOf(s) >= 0) {
            iCount++;
            $(this).css("display", "block");
        } else {
            $(this).css("display", "none");
        }
    });

    // show count of hits
    $('#srvcounter').html(s ? iCount : '');

    // enable active server
    $('#serverlist ul li.active a').each(function () {
        $(this).css("display", "block");
    });

    // switch view between list and cascaded menu
    var bShowAsListmenu = iCount < 25;
    $('#serverlist>li ').each(function () {
        bShowAsListmenu ? $(this).removeClass("dropdown-submenu") : $(this).addClass("dropdown-submenu");
        $(this).attr("onmouseover", bShowAsListmenu ? "" : "$(this).addClass(\'open\');");
        $(this).attr("onmouseout", bShowAsListmenu ? "" : "$(this).removeClass(\'open\');");
    });

    // show count of items and filtered view
    $('#serverlist li ul').each(function () {
        bShowAsListmenu ? $(this).removeClass("dropdown-menu") : $(this).addClass("dropdown-menu");
        iCountItems = $(this).find("li>a").length;
        iCountVisibleItems = 0;
        oUL = $(this);
        $(oUL).find("li>a").each(function () {
            if ($(this).css("display") === "block") {
                iCountVisibleItems++;
            }
        });
        sInfotext = iCountVisibleItems != iCountItems
                ? iCountVisibleItems + '/ ' + iCountItems
                : iCountItems
                ;
        $(oUL).prev().children(".info").html(' (' + sInfotext + ')');
        $(oUL).prev().css("display", iCountVisibleItems ? "block" : "none");
    });
}

/**
 * initialize server filter 
 * @since 1.25
 * @returns {undefined}
 */
function initServerFilter() {
    var iServer = 0;
    $('#serverlist li').each(function () {
        iServer++;
    });
    var sFiltertext = localStorage.getItem('apachestatusFilterServer') + '';
    if (sFiltertext === undefined) {
        sFiltertext = '';
    }
    if (iServer > 5) {
        $('#serverlist >li>a').append('<span class="info" style="margin-right: 1em;">-</span>');
        var sHtml = '<li><form class="form-inline">\n\
            <div class="form-group" style="margin-bottom: 0.5em; min-width: 20em;">\n\
                <input type="text" id="esrvfilter" style="margin-left: 1em;" \n\
                    onkeypress="filterServer(this.value);" class="form-control" onkeydown="filterServer(this.value);" onkeyup="filterServer(this.value);" \n\
                    placeholder="' + aLang['srvFilterPlaceholder'] + '"\n\
                    value="' + sFiltertext + '" />\n\
                <span id="srvcounter"></span>\n\
            </div></form></li>';
        $('#serverlist').prepend(sHtml);
    }
    filterServer(sFiltertext);
}

/**
 * init progress bar in tiles 
 * @returns {undefined}
 */
function initTileProgress() {
    var sTilename = false;
    var aData = false;
    $(".progress-bar").each(function () {

        // div name of progress bar is id "progress-[server]-[tilename]" 
        // @see php class datarenderer->renderTile()
        sId = $(this).attr('id').replace(/^progress\-/, '');
        sSrv = sId.replace(/\-.*/, '');
        sTilename = sId.replace(/^.*\-/, '');
        if (sTilename) {
            var oCounter = new counterhistory(sSrv, sTilename);
            aData = oCounter.getLast(50);
            aDataLast = oCounter.getLast(1);
            currentVal = aDataLast['max'];
            if (aData) {
                $('#progress-' + sId).css('width', (currentVal / aData['max'] * 100) + '%');
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
    initTileProgress();
    $('body').append('<div id="' + sDivPlotter + '" class="plotter"></div>');
}


/**
 * handle tabs of a 2nd tab row
 * @param {type} id
 * @returns {Boolean}
 */
function showTab(id) {
    mydiv = '.subh2 ';
    $(mydiv + ' > h3').hide();
    $(mydiv + ' > .subh3').hide();
    $(mydiv + ' > ' + id).show();
    $(mydiv + ' > ' + id + ' + div.subh3').show();
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

    $('#' + sDivPlotter + ' .btnclose').hide();
    showGraph(sSrv, sVarname, sTitle);
    if (!sav) {
        bPlotterSticky = true;
        $('#' + sDivPlotter + ' .btnclose').show();
    }
}

/**
 * render bar chart with avg line using Chart.js
 * 
 * @param {string}  sDivPlotter
 * @param {string}  sSrv
 * @param {string}  sVarname
 * @param {integer} iCount
 * @param {strng}   sTitle
 * @returns {Boolean}
 */
function showGraphInline(sDivPlotter, sSrv, sVarname, iCount, sTitle, iMax) {
    var oCounter = new counterhistory(sSrv, sVarname);
    var aData = oCounter.getLast(iCount);

    var idCanvas = sDivPlotter + '-chart';
    if (!aData || !aData.data || !aData.data.length || aData['min'] === false) {
        // $('#' + sDivPlotter).html('No data (yet).');
        return false;
    }
    var sData = '',
            sAvg = '',
            sMax = '',
            sMin = '',
            aTimeAxis = [],
            sInfo = ''
            ;

    // get last data and create value arrays for the chart
    for (var i = (aData.data.length - 1); i >= 0; i--) {
        var aItem = aData.data[i];
        // sData+= (sData ? ', ' : '' ) + '{ "x": "'+aItem[0]+'", "y": ' + aItem[1]+ '}';
        aTimeAxis.push(new Date(aItem[0]));
        sData += (sData ? ', ' : '') + (aItem[1]/1 === aItem[1] ? aItem[1] : '"'+aItem[1]+'"');
        sAvg += (sAvg ? ', ' : '') + aData['avg'];
        sMax += (sMax ? ', ' : '') + (iMax ? iMax : aData['max']);
        sMin += (sMin ? ', ' : '') + aData['min'];
    }
    // console.log(sData);

    // create info box
    if (aLang) {
        sInfo += 'values: ' + aData.data.length + '<br>';
        sInfo += (aLang['statsCurrent']) ? aLang['statsCurrent'] + ': ' + aData['data'][0][1] + '<br>' : '';
        sInfo += (aLang['statsMin']) ? aLang['statsMin'] + ': ' + aData['min'] + '<br>' : '';
        sInfo += (aLang['statsMax']) ? aLang['statsMax'] + ': ' + aData['max'] + '<br>' : '';
        sInfo += (aLang['statsAvg']) ? aLang['statsAvg'] + ': ' + aData['avg'] + '<br>' : '';
    }

    // output
    $('#' + sDivPlotter).html('<div class="infos">' + sInfo + '</div><canvas id="' + idCanvas + '"></canvas>');
    var ctx = document.getElementById(idCanvas).getContext('2d');
    var myChart = new Chart(ctx, {

        type: 'bar',
        data: {
            labels: aTimeAxis,
            datasets: [
                {
                    type: 'line',
                    data: JSON.parse('[' + sAvg + ']'),
                    borderColor: '#e0a010',
                    borderWidth: 1,
                    borderDash: [3, 3],
                    fill: false,
                    radius: 0
                },
                {
                    type: 'line',
                    data: JSON.parse('[' + sMin + ']'),
                    borderColor: '#008000',
                    borderWidth: 1,
                    borderDash: [3, 3],
                    fill: false,
                    radius: 0
                },
                {
                    type: 'line',
                    data: JSON.parse('[' + sMax + ']'),
                    borderColor: '#ff0000',
                    borderWidth: 1,
                    borderDash: [3, 3],
                    fill: false,
                    radius: 0
                },
                {
                    type: 'bar',
                    label: sTitle,
                    data: JSON.parse('[' + sData + ']'),
                    backgroundColor: '#80d0f4',
                    lineTension: 0,
                    radius: 0
                }
            ]
        },
        options: {
            animation: {
                duration: 0
            },
            legend: {
                display: false
            },
            scales: {
                xAxes: [{
                        display: false
                    }],
                yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            max: (iMax ? iMax : aData['max'])
                        }
                    }]
            },
            title: {
                display: false,
                text: sTitle
            }
        }
    });

    return true;
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
    var sHtml = '<div class="btnclose" onclick="hideGraph(1);"> X </div>'
            + '<div class="title">' + sTitle + '</div>'
            + '<div id="hovergraph" class="graph">'
            + '</div>'
            ;

    $("#" + sDivPlotter).html(sHtml).show();
    if (!showGraphInline("hovergraph", sSrv, sVarname, 50, sTitle)) {
        $("#" + sDivPlotter).hide();
    }
}

/**
 * hide statistical graph and remove sticky flag
 * @returns {Boolean}
 */
function hideGraph(bUnsticky) {
    if (bUnsticky) {
        bPlotterSticky = false;
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
