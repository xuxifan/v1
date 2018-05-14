var Util = (function(doc) {

    return {
        $ : function(id) {
            return doc.getElementById(id);
        },
        //占位，防止轮播，黄金区大幅度抖动,scale占屏幕的比例
        calcHeight : function (ele, w, h, scale) {
            ele.style.height = parseInt(Constants.CLIENT_W * h * scale / w) + "px";
        },

        extend : function(target, options) {
            for (name in options) {
                target[name] = options[name];
            }
            return target;
        },

        getNextElement : function (node){
            if(node.nextSibling.nodeType == 1){    //判断下一个节点类型为1则是“元素”节点
                return node.nextSibling;
            }
            if(node.nextSibling.nodeType == 3){      //判断下一个节点类型为3则是“文本”节点  ，回调自身函数
                return Util.getNextElement(node.nextSibling);
            }
            return null;
        },

        addMallLog : function (type, params, addParams) {

            if (addParams != undefined && addParams instanceof Object) {
                for (var i in addParams){
                    params[i] = addParams[i];
                }
            }

            var url = $root + "/" + type + ".htm?";
            for (var i in params){
                if (params[i] == undefined){
                    delete params[i];
                }else {
                    url += i + "=" + params[i] + "&";
                }
                //to do 去掉最后的&
            }
            if(url.lastIndexOf("&") == url.length - 1) {
                url = url.substr(0, url.lastIndexOf("&"))
            }
            ajax({
                method: "GET",
                url: url,
                succFunc: function (data) {
                    var da = txtToJson(data);
                    if (da.ret.code == 0) {

                    }
                },
                failFunc: function () {
                }
            });
        },
        addMallLog1 : function () {

            var url = "//10.128.8.100:8090/imall/addShareLog.htm?source=11&channel=2&businessId=3&shareTo=2&shareType=1";
            ajax({
                method: "GET",
                url: url,
                succFunc: function (data) {
                    var da = txtToJson(data);
                    if (da.ret.code == 0) {

                    }
                },
                failFunc: function () {
                }
            });
        },

        getUrlParams : function() {
            return {
                source : getQuerySting().source,
                channel : getQuerySting().channel2,
                businessId : getQuerySting().businessId,
                productId : getQuerySting().productId,
                productType : getQuerySting().productType,
                categoryId : getQuerySting().categoryId,
                entry : getQuerySting().entry,
                topicUrl : getQuerySting().topicUrl,
                topicName : getQuerySting().topicName,
                points : getQuerySting().points,
                dcqId : getQuerySting().dcqId,
                bannerSeq : getQuerySting().bannerSeq,
                moduleId : getQuerySting().moduleId,
                pageEntry : getQuerySting().pageEntry,
                shareTo : getQuerySting().shareTo,
                token : getQuerySting().token,
                lat : getQuerySting().lat,
                lng : getQuerySting().lng,
                shareType : getQuerySting().shareType
            };
        },

        getUrlParamsStr : function() {
            return location.search.length ? location.search.substring(1) : "";
        },

        redirectPage : function(pageName, addParams) {
            var url = "";
            if (addParams != undefined && addParams instanceof Object) {
                if (Util.getUrlParamsStr() == "") {
                    url = pageName + ".htm";
                } else {
                    url = pageName + ".htm?" + Util.getUrlParamsStr();
                }
                return Util.paramsObj2UrlStr(url + "&", addParams);
            } else {
                return pageName + ".htm" + (Util.getUrlParamsStr() == "" ? ""  : "?" + Util.getUrlParamsStr());
            }
        },
        paramsObj2UrlStr : function (url, addParams) {
            for (var i in addParams){
                if (addParams[i] == undefined){
                    delete addParams[i];
                }else {
                    url += i + "=" + addParams[i] + "&";
                }
            }
            if(url.lastIndexOf("&") == url.length - 1) {
                url = url.substr(0, url.lastIndexOf("&"))
            }
            return url;
        },
        dayNumOfMonth : function (year, month){
            return 32 - new Date(year, month, 32).getDay();
        },
        validityIsLastMonth : function(validity){
            var a = /^(\d{4}).(\d{2}).(\d{2})$/;
            if (!a.test(validity)) return false;
            var val = new Date(validity.replace(/\./g, "/")).getTime(),
                now = new Date().getTime();
            var thisMonth = new Date().getMonth() + 1,
                thisYear = new Date().getFullYear();
            var valDay = Math.abs(parseInt(val - now))/24/3600/1000;
            if (valDay == undefined) return false;
            valDay = Math.round(valDay);
            return valDay <= Util.dayNumOfMonth(thisYear, thisMonth);
        }
    }

})(document, undefined);
