var Constants = (function(doc, dd) {

    return {
        DOC : doc,
        BODY : doc.getElementsByTagName("body")[0],
        CLIENT_W : document.documentElement.clientWidth,
        SHARE_DATA : {
            share_url: "http://pay.xiaojukeji.com/api/v2/weixinapi?page=middlepage",
            share_icon_url: "http://static.diditaxi.com.cn/activity/img-mall/share.jpg", // 分享的出去后所显示的图标的链接   ，如下图所示的2
            share_img_url: "http://static.diditaxi.com.cn/activity/img-mall/share.jpg", //分享的大图  （不必理会）
            share_title: "有滴币享福利，任性兑换不花钱", // 分享出去时所显示的标题             如下图所示的1
            share_content: 'DiDi商城天天上新品：水果零食、休闲购物、生活娱乐、鲜花礼品，好礼兑不停', // 分享出去时所显示的描述           如下图所示的3   （分享到朋友圈时描述是不会显示的）
            share_from: '滴滴出行' // 分享来源，非必填,默认为滴滴出行
        },
        DIALOG : new dd.dialog.Fn('<div class="loading-logo"></div>')
    }

})(document, dd, undefined);
