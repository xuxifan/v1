/**
 * form=>json
 */
jQuery.prototype.serializeObject=function(){
    var obj=new Object();  
    $.each(this.serializeArray(),function(index,param){  
        if(!(param.name in obj)){  
            obj[param.name]=param.value;  
        }  
    });  
    return obj;  
}; 
/**
 * 弹窗
 * @param {String} content
 * @param {Function} okfun
 */
var alert_msg = function(content, okfun) {
	$(this).createModal({
		background: "#000", //设定弹窗之后的覆盖层的颜色
		width: "600px", //设定弹窗的宽度
		height: "146px", //设定弹窗的高度
		resizable: true, //设定弹窗是否可以拖动改变大小
		move: true, //规定弹窗是否可以拖动
		bgClose: false, //规定点击背景是否可以关闭
		html: "<div class='modal-promot-mess'>" + content + "</div>" +
			"<p class='insure-btn-con'><span class='sure-btn'>确定</span><span class='cancel-btn modal-close'>关闭</span></p>"
	}, function() { //回调函数的方法
		if (okfun || okfun != undefined) {
			$(".sure-btn").click(function() {
				okfun();
				$('.cancel-btn').click();
			});
		} else {
			$(".sure-btn").click(function() {
				$('.cancel-btn').click();
			});
			$(".cancel-btn").hide()
		}
	});
};
/**
 * 弹出自定义HTML
 * @param {String} html
 * @param {int} w
 * @param {int} h
 */
var alert_win = function(html,w,h) {
	$(document).createModal({
		background: "#000", //设定弹窗之后的覆盖层的颜色
		width: w+"px", //设定弹窗的宽度
		height: h+"px", //设定弹窗的高度
		resizable: true, //设定弹窗是否可以拖动改变大小
		move: true, //规定弹窗是否可以拖动
		bgClose: false, //规定点击背景是否可以关闭
		html: html
	});
};