/**
 * form=>json
 */
jQuery.prototype.serializeObject = function() {
	var obj = new Object();
	$.each(this.serializeArray(), function(index, param) {
		if (!(param.name in obj)) {
			obj[param.name] = param.value;
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
	if (dialog.getCurrent() != null) {
		dialog.getCurrent().close();
	}
	if (okfun == undefined) {
		okfun = function() {}
	}
	dialog({
		title: '提示',
		content: content,
		width: 320,
		ok: okfun,
		okValue: '确定'
	}).show();
};
/**
 * 询问框
 * @param {String} content
 * @param {Function} yesfun
 * @param {Function} nofun
 */
var confirm_msg = function(content, yesfun, nofun) {
		if (dialog.getCurrent() != null) {
			dialog.getCurrent().close();
		}
		if (yesfun == undefined) {
			yesfun = function() {}
		}
		if (nofun == undefined) {
			nofun = function() {}
		}
		dialog({
			title: '提示',
			content: content,
			width: 320,
			ok: yesfun,
			okValue: '是',
			cancel: nofun,
			cancelValue: '否'
		}).showModal();
	}
	/**
	 * 弹出HTML窗
	 * @param {String} html
	 * @param {Int} width
	 * @param {Int} height
	 * @param {Function} ok
	 * @param {String} okValue
	 * @param {Function} cancel
	 * @param {String} cancelValue
	 */
var alert_dialog = function(html, width, height, ok, okV, cancel, cancelV) {
	if (dialog.getCurrent() != null) {
		dialog.getCurrent().close();
	}
	if (ok == undefined) {
		ok = function() {}
	}
	if (cancel == undefined) {
		cancel = function() {}
	}
	dialog({
		title: 'message',
		content: html,
		width: width,
		height: height,
		ok: ok,
		okValue: okV,
		cancel: cancel,
		cancelValue: cancelV
	}).showModal();
};