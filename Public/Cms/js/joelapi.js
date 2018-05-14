/*Joel后台功能插件插件，基于BUI框架
	Auth:Joel;
	desc:为框架重用公共方法;
	不对参数做处理,有问题自己检查;
*/
;
jQuery.Joel = { 
	/*
		Joel.help方法
	*/
	help:function() {
		var funarr=new Array;
		funarr=["$.Joel.ajax","$.Joel.loading","$.Joel.buiAlert","$.Joel.buiDialog","$.Joel.buiForm","$.Joel.uploadImg"];
		var output='';
		$.each(funarr, function(key,val){output+="<p><a href='#' onClick='"+val+"()'>"+val+"</a></p>";});
		var header='<h4>Joel.BaseAPI.Beta1.0版本全函数测试</h4>';
		var copyright='<p class="auxiliary-text" style="text-align:right">展望文化传媒有限公司 By Joel</p>';
		output=header+output+copyright;
		$.Joel.buiAlert(output,false,'warning',false);
	}, //帮助方法结束
	
	/*
		Joel.Ajax方法
	*/
	ajax:function(type,url,data,callok,callerr) {
		//if(type==null || url==null || data==null || callback==null){alert('Joel.ajax参数不齐全!\n用法：$.Joel.ajax(type,url,data,callback)\n{\n type:提交类型[GET/POST]\n url:提交地址[URL]\n data:提交数据[数组]\n callback:是否返回数据[TRUE/FALSE]\n}');return false} 
		var callok=callok?callok:function(){};
		var callerr=callerr?callerr:function(){};
		$.ajax({
				type:type, 
				url:url,
				data:data, 
				global: false,
				dataType: "json",
				beforeSend:$.Joel.loading(),//执行ajax前执行loading函数.直到success 
				success:function (info) {$.Joel.loading();if(info.status){$.Joel.alert('success',info.msg);callok();}else{$.Joel.alert('danger',info.msg);callerr();}}, //成功时执行Response函数
				error: function (info){alert('操作失败，请重试或检查网络连接！')}//失败时调用函数
		}) 
	},//Joel.ajax方法结束
	
	/*
		Joel.loding方法
	*/
	loading:function() {
		$('#Joel-loading-wrap').toggle();
		$('#Joel-loading').toggle();
	}, //Joel.loding方法结束
	
	/*
		Joel.alert方法
	*/
	alert:function(type,msg,callok,callerr) {
		Notify(msg, 'top-right', '5000', type, 'fa-bolt', true);
		if(callok){callok();}
		if(callerr){callerr();}
	}, //Joel.alert方法结束
	
	/*
		Joel.confirm方法
	*/
	confirm:function(msg,callok,callerr) {
		var msg=msg?msg:"确认执行此操作?";
		var callok=callok?callok:function(){};
		var callerr=callerr?callerr:function(){};
		bootbox.confirm(msg, function (result) {
			  if (result) {
                    callok();
                }else{
                	callerr();
                }
        });
	}, //Joel.alert方法结束
}; //插件结束