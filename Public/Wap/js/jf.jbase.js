var A_MONTH=2592000000;Function.prototype.method=function(b,a){if(!this.prototype[b]){this.prototype[b]=a;}else{}return this;};String.method("trim",function(){return this.replace(/^\s+|\s$/g,"");});var diff_platform=function(b){function a(c){if(typeof c==="function"){c();}}if((navigator.userAgent.match(/(Android)/i))){a(b.android);}else{if((navigator.userAgent.match(/(iPhone|iPod|ios|iPad)/i))){a(b.ios);}else{if((navigator.userAgent.match(/(Windows phone)/i))){a(b.wp);}else{a(b.others);}}}};var asyncLoadJS=function(a,c){var b=document.createElement("script");b.type="text/javascript";b.src=a;document.getElementsByTagName("head")[0].appendChild(b);b.onload=b.onreadystatechange=function(){if(!this.readyState||this.readyState=="loaded"||this.readyState=="complete"){if(typeof c==="function"){c();}}};};var touch=function(b,c,a){if(!a){a=false;}if(b&&c){b.addEventListener("touchstart",function(d){d.target.focus();d.stopPropagation();},a);b.addEventListener("touchmove",function(d){d.target.setAttribute("moved","true");},a);b.addEventListener("touchend",function(d){d.target.blur();if(d.target.getAttribute("moved")!=="true"){c(d);}else{d.target.setAttribute("moved","false");}},a);}};var getElesByKlsName=function(f,e){f=f?f:document.body;if(f.getElementsByClassName){return f.getElementsByClassName(e);}else{var d=[];var b=f.getElementsByTagName("*");for(var c=0,a=b.length;c<a;c++){if(b[c].getAttribute){if(b[c].getAttribute("className").indexOf(e)!==-1){d.push(b[c]);}else{}}else{}}return d;}};var getQuerySting=function(){var h=(location.search.length)?location.search.substring(1):"";var d={};var b=h.split("&");var f=null,c=null,g=null;if(b){for(var e=0,a=b.length;e<a;e++){f=b[e].split("=");c=decodeURIComponent(f[0]);g=decodeURIComponent(f[1]);d[c]=g;}}else{}return d;};var txtToJson=function(txt){var j={};if(txt){try{j=JSON.parse(txt);}catch(e){try{j=eval("("+txt+")");}catch(ee){}}}else{}return j;};var ajax=function(f){var e={createXhr:function(){var j;if(window.XMLHttpRequest){j=new XMLHttpRequest();}else{try{j=new ActiveXObject("Microsoft.XMLHTTP");}catch(i){try{j=new ActiveXObject("Msxml2.XMLHTTP");}catch(h){}}}return j;},obj2Body:function(j){var h="";if(j){for(var i in j){if(j.hasOwnProperty(i)){h+="&"+i+"="+j[i]+"";}else{}}}else{}return h.replace(/^\&/,"");},abortReq:function(h){if(h){h.abort();}}};var g=e.createXhr();var d=null;if(g){g.open(f.method,f.url,true);g.onreadystatechange=function(){if(g.readyState===4){if(d){clearTimeout(d);}if(g.status===200){f.succFunc(g.responseText);}else{f.failFunc(g.responseText);}if(f.dialogFlag){var i=document.getElementById("d_wall"),h=document.getElementById("d_wrap");if(i){i.style.display="none";}if(h){h.style.display="none";}}}else{if(g.readyState===3){}else{}}};if(f.method.toUpperCase()==="GET"){g.send(null);}else{if(f.method.toUpperCase()==="POST"){var b=f.data?e.obj2Body(f.data):"";g.setRequestHeader("Content-type","application/x-www-form-urlencoded");g.send(b);}else{}}if(f.timeout){var c=f.timeout.millisecond||30000,a=f.timeout.callback||function(){};d=setTimeout(function(){e.abortReq(g);a();},c);}}else{}};var collectLog=function(b,a,c){ajax({method:"GET",url:"/api/v2/weixinapi/collect_log?openid="+b+"&phone="+a+c});};var setCookie=function(a,b,c){var d=new Date();if(!c){d.setTime(d.getTime()+A_MONTH);}else{d.setTime(d.getTime()+c);}document.cookie=a+"="+escape(b)+";expires="+d.toGMTString();};var getCookie=function(b){var a,c=new RegExp("(^| )"+b+"=([^;]*)(;|$)");a=document.cookie.match(c);if(a){return unescape(a[2]);}else{return null;}};var delCookie=function(a){var b=getCookie(a);if(b){document.cookie=a+"="+b+";expires="+new Date(0).toGMTString();}else{}};var clearCookies=function(){var b=document.cookie.match(/[^ =;]+(?=\=)/g);if(b){for(var a=b.length;a--;){document.cookie=b[a]+"=0;expires="+new Date(0).toGMTString();}}};