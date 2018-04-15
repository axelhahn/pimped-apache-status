/**
 * CLASS counterhistory
 * 
 * This file is part of the pimped apache status
 * tiles (see datarenderer class) add the current value as new 
 * item (timestamp and value). This class stores _iMaxItems value pairs.
 * 
 * @returns {Boolean}
 */
var counterhistory = function () {

    // ======================================================================
    // CONFIG
    // ======================================================================
    this.sServerIndex = false;
    this.sCounter = false;

    this._sDatavar = 'apachestatusHistory';
    this._aData = false;
    this._iMaxItems = 250;   


    // ======================================================================
    // 
    // "private" functions
    // 
    // ======================================================================

    /**
     * load stored Data
     * @returns {undefined}
     */
    this._dataLoad=function(){
        this._aData = JSON.parse(localStorage.getItem(this._sDatavar));
    };
    
    /**
     * save data to localStorage
     * @returns {undefined}
     */
    this._dataSave=function(){
        return localStorage.setItem(this._sDatavar, JSON.stringify(this._aData));
    };

    /**
     * cleanup counter data; it keeps max a given count of elements
     * @param {int} iItemsToKeep  
     * @returns {Boolean}
     */
    this._cleanup = function (iItemsToKeep) {
        if (!this._aData[this.sServerIndex][this.sCounter]){
            return false;
        }
        
        // reminder: in js aData is just a pointer
        var aData=this._aData[this.sServerIndex][this.sCounter];
        aData.splice((iItemsToKeep), aData.length);
        
        return true;
    };

    // ======================================================================
    // 
    // GETTER
    // 
    // ======================================================================
    
    /**
     * get the last n items of a counter; this functions returns an
     * object with keys min, max and data.
     * @param {int} iCount  max count of items to return
     * @returns {Object|counterhistory.getLast.aReturn|Boolean}
     */
    this.getLast = function (iCount) {
        if (!this._aData[this.sServerIndex]
            || !this._aData[this.sServerIndex][this.sCounter]
            || this._aData[this.sServerIndex][this.sCounter].length<1){
            return false;
        }
        var aReturn={
            'min':false,
            'max':false,
            'avg':false,
            'data':[]
        };
        var iSum=0;
        
        var aData=this._aData[this.sServerIndex][this.sCounter];
        var aItem=false;
        var val=false;
        if(aData.length<iCount){
            iCount=aData.length;
        }
        for (var i=0; i<iCount; i++){
            aItem=aData[i];
            val=aItem[1]/1;

            var er = /^-?[\.0-9]+$/;
            if (er.test(aItem[1])){
                if(!aReturn['min']){
                    aReturn['min']=val;
                }
                if(!aReturn['max']){
                    aReturn['max']=val;
                }
                if(val<aReturn['min']){
                    aReturn['min']=val;
                }
                if(val>aReturn['max']){
                    aReturn['max']=val;
                }
                iSum+=val;                
            }
            aReturn['data'].push(aItem);
        }
        aReturn['avg']=Math.round(iSum/iCount*1000)/1000;
        return aReturn;
    };
    
    // ======================================================================
    // 
    // SETTER
    // 
    // ======================================================================

    /**
     * add a new value with the current time
     * @param {integer|string} iValue  value
     * @returns {Boolean}
     */
    this.add = function (iValue) {
        var oDate = new Date();

        if (!this._aData) {
            this._aData = new Object();
        }
        if (!this._aData[this.sServerIndex]) {
            this._aData[this.sServerIndex] = new Object();
        }
        if (!this._aData[this.sServerIndex][this.sCounter]) {
            this._aData[this.sServerIndex][this.sCounter] = [];
        }

        // add new item on top of assoc array
        this._aData[this.sServerIndex][this.sCounter].unshift([oDate, iValue]);

        // store it
        this._cleanup(this._iMaxItems);
        this._dataSave();
        
        return true;
    };

    /**
     * set the name of a counter/ tile
     * @param {string} s  name of the tile
     * @returns {counterhistory@pro;sCounters}
     */
    this.setCounter = function (s) {
        return this.sCounter = s;
    };
    
    /**
     * set the server index (a serverlist or hash of it)
     * @param {string} s  name of the server index
     * @returns {counterhistory@pro;sServerIndexs}
     */
    this.setServerIndex = function (s) {
        return this.sServerIndex = s;
    };

    // ======================================================================
    // 
    // init
    // 
    // ======================================================================
    
    // get this._aData
    this._dataLoad();

    if (arguments) {
        if(arguments[0]){
            this.setServerIndex(arguments[0]);
        }
        if(arguments[1]){
            this.setCounter(arguments[1]);
        }
    }

    return true;

};