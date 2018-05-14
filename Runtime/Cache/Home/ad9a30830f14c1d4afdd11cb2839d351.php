<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
	<head>
		<title>鹤乡农都</title>
		<title>鹤乡农都</title>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<link rel="stylesheet" href="/Public/home/css/nongdu.css" />
<link rel="stylesheet" href="/Public/home/css/fenxiao.css" />
<link rel="stylesheet" href="/Public/home/css/swiper.min.css" />
<link rel="stylesheet" href="/Public/home/css/ui-dialog.css" />
<script src="/Public/home/js/jquery-1.11.3.min.js"></script>
<script src="/Public/home/js/dialog.js"></script>
<script src="/Public/home/js/swiper.jquery.min.js"></script>
<script src="/Public/home/js/scroll.js"></script>

	</head>

	<body style="background: #f5f5f5;">
		<div class="top_nav_box f_sont">
	<div class="wapper">
		<div class="fl nav_l">
			<?php if($_SESSION['HOME']['vipid'] != null): ?><span class="title">Hi,<?php echo ($_SESSION['HOME']['vip']['nickname']); ?>,欢迎光临<?php echo ($_SESSION['SHOPSET']['name']); ?>！</span><a href="<?php echo u('home/vip/logout');?>">退出</a>
				<?php else: ?>
				<a href="<?php echo u('home/vip/login');?>" style="margin-right: 0;">登录</a><span class="xiegan">/</span>
				<a href="<?php echo u('home/vip/login');?>">免费注册</a><?php endif; ?>
		</div>
		<div class="fr nav_r">
			<a href="<?php echo u('home/vip/index');?>"  target="_blank"><img src="/Public/home/img/top_nav02.png">会员中心</a>
			<!--<a href="<?php echo u('home/index/basket');?>"  target="_blank"><img src="/Public/home/img/top_nav04.png">购物车<span class="f_arial cor07">（<em id="basketnum"><?php echo ($basketnum); ?></em>）</span></a>-->
			<a href="javascript:void(0)"><img src="/Public/home/img/top_nav06.png">全国订购统一热线：<?php echo sysconfig('tel');?></a>
		</div>
	</div>
</div>
<div class="bg_corb" style="padding-bottom: 16px;">
	<div class="wapper">
		<div class="fl logo">
			<a href="<?php echo u('home/index/index');?>"><img src="/Public/home/img/logo2.jpg" width="379" height="89"></a>
		</div>
		<div class="fl qrcode-box" style="position: absolute;top:150px;z-index: 10000;display: none"><img src="/Public/home/img/erwei.jpg" style="width:300px"/></div>
		<div class="fr search_box">
			<div class="search">
				<input class="f_sont fl" type="text" placeholder="请输入搜索商品关键字..." id="search-head-txt" value="<?php echo ($s); ?>" />
				<button class=" cor02 f16 textc fl f_wei" id="btn-head-search">搜&nbsp;索</button>
			</div>
			<div class="hot_key f_sont">
                <?php if(is_array($searchwords)): $i = 0; $__LIST__ = $searchwords;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo u('home/index/catelist',array('s'=>$vo['text']));?>"><?php echo ($vo["text"]); ?></a><span>|</span><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
		</div>
	</div>
</div>
<script>

$('.logo').on('mousemove',function(){
	$('.qrcode-box').show(100);
});
$('.logo').on('mouseout',function(){
	$('.qrcode-box').hide(100);
})
	$('#search-head-txt').keyup(function(event) {
		if (event.keyCode == 13) {
			$('#btn-head-search').click();
		}
	});
	$('#btn-head-search').click(function(){
		var search=$('#search-head-txt').val();
		if(search.length>0){
			window.open("<?php echo u('index/catelist');?>/s/"+search);
		}
	})
</script>
		<!--导航条-->
		<div class="bg_corg">
	<div class="wapper f_wei f16 nav_main">
		<a href="javascript:void(0)" class="show-cate-btn"><span class="fl textc cor02"><img src="/Public/home/img/fenlei_03.png" class="fenleiimg"/>商品分类</span></a>
		<div class="cate-nav">
			<ul class="cate-nav-ul" style="margin-top: 0;">
				<?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li style="margin-bottom: 0;">
					<div class="cateA-title"><em class="A-em1"></em>
						<a target="_blank" href="<?php echo u('home/index/catelist',array('id'=>$vo['id']));?>"><?php echo ($vo["name"]); ?></a>
					</div>
					<div class="cateA-sub" style="display: none;">
						<?php if($cate[$key]['goods'] != null): ?><div class="leaf-items cor02">
								<?php if(is_array($vo['items'])): $i = 0; $__LIST__ = $vo['items'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vt): $mod = ($i % 2 );++$i;?><a href="<?php echo u('home/index/catelist',array('id'=>$vt['id']));?>" class="cate-item" target="_blank"><?php echo ($vt["name"]); ?>、</a><?php endforeach; endif; else: echo "" ;endif; ?>
							</div><?php endif; ?>
						<img src="<?php echo getPicUrl($vo['icon']);?>" width="350px" height="350px"/>
					</div>
				</li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
		</div>
	</div>
</div>
<script>
	$(".cate-nav-ul li").hover(function() {
			$(this).find(".cateA-title").attr("class", "cateA-title cateB-thisnav");
			$(this).find(".cateA-title").find("em").animate({
				margin: '-55px 10px 0 20px'
			}, 200);
			$(this).find(".cateA-sub").show();
			$(this).find(".cateA-subB").show();
		},
		function() {
			$(this).find(".cateA-title").attr("class", "cateA-title");
			$(this).find(".cateA-title").find("em").animate({
				margin: '0 10px 0 20px'
			}, 200);
			$(this).find(".cateA-sub").hide();
			$(this).find(".cateA-subB").hide();
		}
	);
	$('.show-cate-btn').click(function(){
		$('.cate-nav').slideToggle(50);
	});
	if('/Home/Index/index'.split('/')[3]!='index'){
		$('.cate-nav').hide();	
	}
</script>
		<!--轮播-->
		<div class="swiper-container" >
			<div class="swiper-wrapper">
				<?php if(is_array($adsdata)): $i = 0; $__LIST__ = $adsdata;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="swiper-slide"><img src="<?php echo ($vo["pic"]["imgurl"]); ?>" style="height: 500px;" /></div><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>
			<!-- Add Pagination -->
			<div class="swiper-pagination"></div>
			<!-- Add Arrows -->
			<div class="swiper-button-next">〉</div>
			<div class="swiper-button-prev">〈</div>
		</div> 
		<!--限时、新品、优惠、广告-->
		<div class="wapper">
			<div class="active-box fl">
				<ul class="limittitle f_wei">
					<?php if(is_array($labelsgoods)): $i = 0; $__LIST__ = $labelsgoods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li>
							<p class="limitbg" data-id='<?php echo ($vo["id"]); ?>'><?php echo ($vo["name"]); ?><span></span></p>
							<!--<p class="daosanjiao"><img src="/Public/home/img/daosanjiao_03.png" /></p>-->
						</li><?php endforeach; endif; else: echo "" ;endif; ?>
					<br style="clear: both;" />
				</ul>
				<?php if(is_array($labelsgoods)): $i = 0; $__LIST__ = $labelsgoods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><ul class="limitgood f_wei labelgoods<?php echo ($vo["id"]); ?>">
						<?php if(is_array($vo['goods'])): $i = 0; $__LIST__ = $vo['goods'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vt): $mod = ($i % 2 );++$i;?><li>
								<a href="<?php echo u('home/index/goods',array('id'=>$vt['id']));?>" target="_blank">
									<img src="<?php echo getPicUrl($vt['pic']);?>" width="194px" height="194px" />
									<div>
										<p class="smalllimitbg"></p>
										<p class="f16 cor08 shenlue"><?php echo ($vt["name"]); ?></p>
										<p class="cor01 f14 jiage"><span>¥<?php echo ($vt["price"]); ?></span> /市场价：¥<?php echo ($vt["oprice"]); ?></p>
									</div>
								</a>
							</li><?php endforeach; endif; else: echo "" ;endif; ?>
						<br style="clear: both;" />
					</ul><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>
			<div class="notice-box fl">
				<div class="title"><em>农都快报</em><a href="<?php echo u('home/vip/news');?>" target="_blank" class="fr">更多></a></div>
				<ul>
					<?php if(is_array($newdata)): $i = 0; $__LIST__ = $newdata;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><a href="<?php echo u('home/vip/news');?>"  target="_blank"><?php echo ($i); ?>.<?php echo ($vo["name"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
				</ul>
				
			</div>
		</div>
		<!--产品-->
		<div class="wapper">
			<?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($cate[$key]['goods'] != null): ?><a class="goodtitle" style="display: block;" href="<?php echo u('home/index/catelist',array('id'=>$vo['id']));?>" target="_blank">
						<span><?php echo ($vo["name"]); ?></span>
						<p class="f20 f_wei"><?php echo ($vo["summary"]); ?></p>
					</a>
					<ul class="liangyou">
						<?php if(is_array($cate[$key]['goods'])): $i = 0; $__LIST__ = array_slice($cate[$key]['goods'],0,20,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><li>
								<a href="<?php echo u('home/index/goods',array('id'=>$v['id']));?>" target="_blank">
									<img src="<?php echo ($v["picurl"]); ?>" width="194" height="194" />
									<p class="f16 cor08 shenlue" style="margin-top: 16px;"><?php echo ($v["name"]); ?></p>
									<p class="cor01 f14 jiage2"><span>¥<?php echo ($v["price"]); ?></span> /市场价：¥<?php echo ($v["oprice"]); ?></p>
								</a>
							</li><?php endforeach; endif; else: echo "" ;endif; ?>
						<br style="clear: both;" />
					</ul><?php endif; endforeach; endif; else: echo "" ;endif; ?>
			<br style="clear: both;" />
			</ul>
		</div>

		<!--尾部白色区域-->
<div class="bg_corb bor_top1" style="border-bottom: none;">
	<div class="wapper">
		<ul class="baoz flw">
			<li style="width: 260px;">
				<a><img src="/Public/home/img/bz01.jpg">
					<h3 class="f18">品质保障</h3>
					<p class="f16 cor01">品质护航 购物无忧</p>
				</a>
			</li>
			<li style="width: 285px;">
				<a><img src="/Public/home/img/bz02.jpg">
					<h3 class="f18">七天无理由退换货</h3>
					<p class="f16 cor01">为您提供售后无忧保障</p>
				</a>
			</li>
			<li style="width: 270px;">
				<a><img src="/Public/home/img/bz03.jpg">
					<h3 class="f18">特色服务体验</h3>
					<p class="f16 cor01">为您呈现不一样的服务</p>
				</a>
			</li>
			<li style="width: 176px;">
				<a class="non_bor" href="#"><img src="/Public/home/img/bz04.jpg">
					<h3 class="f18">帮助中心</h3>
					<p class="f16 cor01">您的购物指南</p>
				</a>
			</li>
		</ul>
		<ul class="nav_bot flw">
			<li class="f_sont" style="width: 225px;"><b class="f14 f_strong">购物指南</b><a href="<?php echo u('home/vip/joinfree');?>">免费注册</a><a href="<?php echo u('home/vip/index');?>">账户充值</a></li>
			<li class="f_sont" style="width: 225px;"><b class="f14 f_strong">商城保障</b><a href="<?php echo u('home/vip/invoice');?>">发票保障</a><a href="#">售后规则</a></li>
			<li class="f_sont" style="width: 225px;"><b class="f14 f_strong">支付方式</b><a href="<?php echo u('home/vip/payway');?>">支付说明</a></li>
			<li class="f_sont" style="width: 215px;"><b class="f14 f_strong">商家服务</b><a href="#">商家规则</a><a href="#">物流服务</a><a href="#">运营服务</a></li>
			<!--<li class="f_sont" style="width: 215px;"><b class="f14 f_strong">商家服务</b><a href="#">商家规则</a><a href="#">物流服务</a><a href="#">运营服务</a></li>-->
			<li class="f_sont"><b class="f14 f_strong">手机店铺</b>
				<p><img src="/Public/home/img/erwei.jpg"></p>
			</li>
		</ul>
	</div>
</div>
<!--尾部绿色区域-->
<div class="bg_corg" style="background-color: #6DC092;">
	<div class="wapper f_sont">
		<img src="/Public/home/img/footbg_03.png" class="footlogo fl" />
		<div class="sanjiao fl">
			<img src="/Public/home/img/sanjiao_03.png" />
		</div>
		<ul class="fl">
			<li>&copy; 2015 ALL RIGHTS RESERVED.</li>
			<li>本商店顾客个人信息将不会被泄露给其他任何机构和个人</li>
			<li>本商店LOGO和图片都已经申请保护，未经授权不得使用</li>
			<li>有任何购物问题请联系我们在线客服 | 电话：<?php echo sysconfig('tel');?> | 工作时间：<?php echo sysconfig('worktime');?></li>
			<li><?php echo sysconfig('copyright');?></li>
		</ul>
	</div>
</div>
<div class="help-box">
	<ul>
		<li>
			<a href="javascript:void(0)" class="gototop"></a>
		</li>
		<li>
			<a href="javascript:void(0)" class="kf"></a>
		</li>
		<li>
			<a href="<?php echo u('home/index/basket');?>" class="gw">
				<em id="basketnum"><?php echo ($basketnum); ?></em>
			</a>
		</li>
		<li>
			<a href="javascript:void(0)" class="wx"></a>
		</li>
		<li>
			<a href="javascript:join_favorite()" class="sc"></a>
		</li>
	</ul>
	<div class="kf-box" style="display: none;">
		<span>
			<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo sysconfig('kefuqq1');?>&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:<?php echo sysconfig('kefuqq1');?>:41" alt="售前一号" title="鹤乡农都网站"/></a>
			<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo sysconfig('kefuqq2');?>&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:<?php echo sysconfig('kefuqq2');?>:41" alt="售前二号" title="鹤乡农都网站"/></a>
			<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo sysconfig('kefuqq3');?>&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:<?php echo sysconfig('kefuqq3');?>:41" alt="售前三号" title="鹤乡农都网站"/></a>
		</span>
	</div>
	<div class="erwema-box" style="display: none;">
		<img src="/Public/home/img/erwei.jpg" />
	</div>
</div>
<style>
	.help-box {
		position: fixed;
		right: 0px;
		bottom: 50px;
		z-index: 1000;
	}
	
	.help-box ul {
		float: right;
	}
	
	.kf-box {
		float: left;
		border: 1px solid #F1F0F0;
		width: 80px;
		height: 66px;
		text-align: center;
		margin-top: 67px;
		margin-right: 2px;
	}
	
	.erwema-box {
		float: left;
		border: 1px solid #F1F0F0;
		text-align: center;
		margin-top: 134px;
		margin-right: 2px;
	}
	
	.erwema-box img {
		width: 132px;
		height: 132px;
	}
	
	.help-box ul li a {
		display: block;
		margin-bottom: 1px;
		width: 66px;
		height: 66px;
	}
	
	.gototop {
		background: url(/Public/home/img/gtop.png);
	}
	
	.kf {
		background: url(/Public/home/img/kf.png);
	}
	
	.gw {
		background: url(/Public/home/img/gw.png);
	}
	
	.wx {
		background: url(/Public/home/img/wx.png);
	}
	
	.sc {
		background: url(/Public/home/img/sc.png);
	}
	
	.kf:hover {
		background: url(/Public/home/img/kf2.png);
	}
	
	.gw:hover {
		background: url(/Public/home/img/gw2.png);
	}
	
	.wx:hover {
		background: url(/Public/home/img/wx2.png);
	}
	
	.sc:hover {
		background: url(/Public/home/img/sc2.png);
	}
	#basketnum{
		background-color: red;
		color: #fff;
		display: block;
		float: left;
		font-size: 1.5em;
		margin-top: 20px;
		margin-left: -.2em;
	}
</style>
<script src="/Public/home/js/commons.js"></script>
<script>
	$(function() {
		//当点击跳转链接后，回到页面顶部位置  
		$(".gototop").click(function() {
			$('body,html').animate({
				scrollTop: 0
			}, 1000);
			return false;
		});
		$(window).scroll(function() {
			var sct = $(window).scrollTop();
			var opa = sct / 500;
			$('.gototop').css('filter', 'alpha(opacity=' + (opa * 100) + ')');
			$('.gototop').css('-moz-opacity', opa);
			$('.gototop').css('-khtml-opacity', opa);
			$('.gototop').css('opacity', opa);
		});
		$(window).scroll();
		$('.kf').click(function() {
			$('.erwema-box').hide();
			$('.kf-box').toggle(100);
		});
		$('.wx').click(function() {
			$('.kf-box').hide();
			$('.erwema-box').toggle(100);
		});
	});
	var join_favorite = function() {
		var siteUrl = 'http://<?php echo ($_SERVER["HTTP_HOST"]); ?>';
		var siteName = '鹤乡农都';
		//捕获加入收藏过程中的异常       
		try {
			//判断浏览器是否支持document.all      
			if (document.all) {
				//如果支持则用external方式加入收藏夹                       
				window.external.addFavorite(siteUrl, siteName);
			} else if (window.sidebar) {
				//如果支持window.sidebar，则用下列方式加入收藏夹    
				window.sidebar.addPanel(siteName, siteUrl, '');
			}else{
				alert("加入收藏夹失败，请使用Ctrl+D快捷键进行添加操作!");
			}
		} catch (e) {
			alert("加入收藏夹失败，请使用Ctrl+D快捷键进行添加操作!");
		}
	}
</script>
		<script>
			 //轮播
			var swiper = new Swiper('.swiper-container', {
				pagination: '.swiper-pagination',
				paginationClickable: true,
				nextButton: '.swiper-button-next',
				prevButton: '.swiper-button-prev',
				autoplay: 2500,
				spaceBetween: 30
			});
			 //滚动新闻
			$("div.new_txt").myScroll({
				speed: 60,
				rowHeight: 27
			});
			 //标签tab
			$('.limitbg').click(function() {
				$('.limitbg').removeClass('active');
				$(this).addClass('active');
				$('.limitgood').hide();
				//			alert('.labelgoods'+$(this).data('id'));
				$('.labelgoods' + $(this).data('id')).show();
			});
			$('.limitbg').eq(0).click();
		</script>
		<script>
			(function($) {
			    $.fn.snow = function(options) {
			        var $flake = $('<i class="iconfont" id="flake" />').css({
			            'position': 'absolute',
			            'top': '-50px'
			        }).html('&#xe6e0'),//10052
			        documentHeight = $(document).height(),
			        documentWidth = $(document).width(),
			        defaults = {
			            minSize: 10,
			            maxSize: 20,
			            newOn: 500,
			            flakeColor: "#CCCCCC"
			        },
			        options = $.extend({}, defaults, options);
			        var interval = setInterval(function() {
			            var startPositionLeft = Math.random() * documentWidth - 100,
			            startOpacity = 0.5 + Math.random(),
			            sizeFlake = options.minSize + Math.random() * options.maxSize,
			            endPositionTop = documentHeight - 40,
			            endPositionLeft = startPositionLeft - 100 + Math.random() * 200,
			            durationFall = documentHeight * 10 + Math.random() * 5000;
			            $flake.clone().appendTo('body').css({
			                left: startPositionLeft,
			                opacity: startOpacity,
			                'font-size': sizeFlake,
			                color: options.flakeColor
			            }).animate({
			                top: endPositionTop,
			                left: endPositionLeft,
			                opacity: 0.5
			            },
			            durationFall, 'linear',
			            function() {
			                $(this).remove()
			            });
			        },
			        options.newOn);
			    };
			})(jQuery);
			$.fn.snow({
			    minSize: 5,
			    maxSize: 50,
			    newOn: 1000,
			    flakeColor: '#FFF'
			});
		</script>
	</body>

</html>