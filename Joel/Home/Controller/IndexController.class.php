<?php
namespace Home\Controller;
use Home\Controller\BaseController;
/**
 * 商城首页
 */
class IndexController extends BaseController {
	/**
	 *
	 */
	public function _initialize() {
		parent::_initialize();
		//获取分类菜单
		$m = M('shop_cate');
		$menu = $m -> where('pid=0') -> order('sorts') -> select();
		foreach ($menu as $k => $v) {
			$items = $m -> where('pid=' . $v['id']) -> order('sorts') -> select();
			$menu[$k]['items'] = $items;
		}
		//购物车
		$basketnum = M('Shop_basket') -> where(array('sid' => 0, 'vipid' => $_SESSION['HOME']['vipid'])) -> sum('num');
//		dump(M('Shop_basket')->getLastSql());
		$this -> assign('basketnum', $basketnum?$basketnum:0);
		$this -> assign('menu', $menu);
		//会员信息
		$vipinfo = M('vip') -> where('id=' . $_SESSION["HOME"]["vipid"]) -> find();
		$_SESSION['HOME']['vip'] = $vipinfo;
		$this -> assign('vipinfo', $vipinfo);
        //搜索关键字
        $searchwords=M('search_words')->order('times desc')->limit('0,4')->select();
        $this->assign('searchwords',$searchwords);
	}

	/**
	 * 首页
	 */
	public function index() {
		$m = M('shop_cate');
		$mg = M('shop_goods');
		$cate = $m -> where('pid=0') -> order('sorts') -> select();
		foreach ($cate as $k => $v) {
			$items = $m -> where('pid=' . $v['id']) -> order('sorts') -> select();
			$cate[$k]['goods'] = $mg -> where('cid in (' . $v['soncate'] . $v['id'] . ') and status=1')->order('sorts desc') -> select();
			//获取图片
			foreach ($cate[$k]['goods'] as $key => $vo) {
				$pic = $this -> getPic($vo['pic']);
				$cate[$k]['goods'][$key]['picurl'] = $pic['imgurl'];
			}
		}
		$this -> assign('cate', $cate);
		//获取新闻信息
		$mn = M('news');
		$msglist = $mn -> order('ctime desc') -> limit(0) -> select();
		foreach ($msglist as $key => $value) {
			$temppic = $this -> getPic($value['pic']);
			$msglist[$key]['picurl'] = $temppic['imgurl'];
		}
		$this -> assign('newdata', $msglist);
		//根据标签获取商品
		//获取标签
		$labels = M('shop_label') -> order('id desc') -> limit(3) -> select();
		foreach ($labels as $k => $v) {
			$labels[$k]['goods']=$mg -> where("lid like '%," . $v['id'] . ",%' or lid like '" . $v['id'] . "%' and status=1") -> limit(4) ->order('sorts desc') -> select();
		}
		$this -> assign('labelsgoods', $labels);
		//获取轮播图片
		$adsdata=M('shop_ads')->where('ispc=1')->select();
		foreach ($adsdata as $k => $v) {
			$adsdata[$k]['pic']=$this->getPic($v['pic']);
		}
		$this -> assign('adsdata', $adsdata);
		$this -> display();
	}

	/**
	 * 商品详情
	 */
	public function goods() {
		$m = M('shop_cate');
		$mg = M('shop_goods');
		$cate = $m -> where('pid=0') -> order('sorts') -> select();
		foreach ($cate as $k => $v) {
			$items = $m -> where('pid=' . $v['id']) -> order('sorts') -> select();
			$cate[$k]['goods'] = $mg -> where('cid in (' . $v['soncate'] . $v['id'] . ') and status=1')->order('sorts desc') -> select();
			//获取图片
			foreach ($cate[$k]['goods'] as $key => $vo) {
				$pic = $this -> getPic($vo['pic']);
				$cate[$k]['goods'][$key]['picurl'] = $pic['imgurl'];
			}
		}
		$this -> assign('cate', $cate);
		$id = I('id');
		//历史访问保存3条
		if ($id) {
			$m = M('shop_goods');
			$goodsinfo = $m -> where('id=' . $id . ' and status=1') -> find();
			$goodsinfo['pic'] = $this -> getPic($goodsinfo['pic']);
			$goodsinfo['album'] = $this -> getAlbum($goodsinfo['album']);
			$goodsinfo['content'] = htmlspecialchars_decode($goodsinfo['content']);
			$this -> assign('goods', $goodsinfo);
			//访问记录
			if ($goodsinfo['issku'] == 1) {
				$this -> _getSku($goodsinfo);
			}
			$this -> setViewHistoryInSession($id);
			$this->assign('pagetitle',$goodsinfo['name']);
			$this -> display();
		} else {
			$this -> error('商品信息无法获取~');
		}
	}

	/**
	 * 访问记录
	 */
	private function setViewHistoryInSession($id) {
		$max = 4;
		if ($id) {
			$tmep_array = $_SESSION['history_goodsids'] ? $_SESSION['history_goodsids'] : array();
			if (!in_array($id, $tmep_array) || $tmep_array == null) {
				array_push($tmep_array, $id);
			}
			if (count($tmep_array) > $max) {
				array_splice($tmep_array, 0, count($tmep_array) - $max);
			}
			$_SESSION['history_goodsids'] = $tmep_array;
			$m = M('shop_goods');
			$historydata = $m -> where('id in(' . implode(',', $tmep_array) . ')') -> select();
			$this -> assign('historydata', $historydata);
		}
	}

	/**
	 * 获取SKU属性
	 */
	private function _getSku($goodsinfo) {
		if ($goodsinfo['skuinfo']) {
			$skuinfo = unserialize($goodsinfo['skuinfo']);
			$skm = M('Shop_skuattr_item');
			foreach ($skuinfo as $k => $v) {
				$checked = explode(',', $v['checked']);
				$attr = $skm -> field('path,name') -> where('pid=' . $v['attrid']) -> select();
				foreach ($attr as $kk => $vv) {
					$attr[$kk]['checked'] = in_array($vv['path'], $checked) ? 1 : '';
				}
				$skuinfo[$k]['allitems'] = $attr;
			}
			$this -> assign('skuinfo', $skuinfo);
		} else {
			$this -> diemsg(0, '此商品还没有设置SKU属性！');
		}
		$skuitems = M('Shop_goods_sku') -> field('sku,skuattr,price,num,hdprice,hdnum') -> where(array('goodsid' => $goodsinfo['id'], 'status' => 1)) -> select();
		if (!$skuitems) {
			$this -> diemsg(0, '此商品还未生成SKU!');
		}
		$skujson = array();
		foreach ($skuitems as $k => $v) {
			$skujson[$v['sku']]['sku'] = $v['sku'];
			$skujson[$v['sku']]['skuattr'] = $v['skuattr'];
			$skujson[$v['sku']]['price'] = $v['price'];
			$skujson[$v['sku']]['num'] = $v['num'];
			$skujson[$v['sku']]['hdprice'] = $v['hdprice'];
			$skujson[$v['sku']]['hdnum'] = $v['hdnum'];
		}
		$this -> assign('skujson', json_encode($skujson));

	}

	public function orderMake() {
		if (IS_POST) {
			$morder = M('Shop_order');
			$data = I('post.');
			$data['items'] = stripslashes(htmlspecialchars_decode($data['items']));
			
			$data['ispay'] = 0;
			$data['status'] = 1;
			//订单成功，未付款
			$data['ctime'] = time();
			$data['payprice'] = $data['totalprice'];
			//代金券流程
			if ($data['djqid']) {
				$mcard = M('Vip_card');
				$djq = $mcard -> where('id=' . $data['djqid']) -> find();
				if (!$djq) {
					$this -> error('通讯失败！请重新尝试支付！');
				}
				if ($djq['usetime']) {
					$this -> error('此代金券已使用！');
				}
				$djq['status'] = 2;
				$djq['usetime'] = time();
				$rdjq = $mcard -> save($djq);
				if (FALSE === $rdjq) {
					$this -> error('通讯失败！请重新尝试支付！');
				}
				//修改支付价格
				$data['payprice'] = $data['totalprice'] - $djq['money'];
			}
			//邮费逻辑
			$data['payprice']=$data['payprice']+$data['yf'];
			if ($data['payprice'] < 0) {
				$data['payprice'] = 0;
			}
			$re = $morder -> add($data);
			if ($re) {
				$old = $morder -> where('id=' . $re) -> setField('oid', date('YmdHis') . '-' . $re);
				if (FALSE !== $old) {
					//后端日志
					$mlog = M('Shop_order_syslog');
					$dlog['oid'] = $re;
					$dlog['msg'] = '订单创建成功';
					$dlog['type'] = 1;
					$dlog['ctime'] = time();
					$rlog = $mlog -> add($dlog);
					//清空购物车
					$rbask = M('Shop_basket') -> where(array('sid' => $data['sid'], 'vipid' => $data['vipid'])) -> delete();
					
					$this -> redirect(U('index/pay/', array('sid' => $data['sid'], 'orderid' => $re)));
				} else {
					$old = $morder -> delete($re);
					$this -> error('订单生成失败！请重新尝试！', U('home/index/orderDetail', array('orderid' => $re)));
				}
			} else {
				//可能存在代金券问题
				$this -> error('订单生成失败！请重新尝试！', U('home/index/orderDetail', array('orderid' => $re)));
			}

		} else {
			//非提交状态
			$sid = $_GET['sid'] <> '' ? $_GET['sid'] : 0;
			//$this -> diemsg(0, '缺少SID参数');
			//sid可以为0
			$lasturl = $_GET['lasturl'];
			//?$_GET['lasturl']:$this->diemsg(0, '缺少LastURL参数');
			$basketlasturl = base64_decode($lasturl);
			$basketurl = U('index/basket', array('sid' => $sid, 'lasturl' => $lasturl));
			$backurl = base64_encode($basketurl);
			$basketloginurl = U('Vip/login', array('backurl' => $backurl));
			$re = $this -> checkLogin($backurl);
			//保存当前购物车地址
			$this -> assign('basketurl', $basketurl);
			//保存登陆购物车地址
			$this -> assign('basketloginurl', $basketloginurl);
			//保存购物车前地址
			$this -> assign('basketlasturl', $basketlasturl);
			//保存lasturlencode
			//保存购物车加密地址，用于OrderMaker正常返回
			$this -> assign('lasturlencode', $lasturl);
			$this -> assign('sid', $sid);
			//已登陆
			$m = M('Shop_basket');
			$mgoods = M('Shop_goods');
			$msku = M('Shop_goods_sku');
			$cache = $m -> where(array('sid' => $sid, 'vipid' => $_SESSION['HOME']['vipid'])) -> select();
			//错误标记
			$errflag = 0;
			//等待删除ID
			$todelids = '';
			//totalprice
			$totalprice = 0;
			//totalnum
			$totalnum = 0;
//			$allmy=1;
			$heavyall=0;
			foreach ($cache as $k => $v) {
				//sku模型
				$goods = $mgoods -> where('id=' . $v['goodsid']) -> find();
				$pic = $this -> getPic($goods['pic']);
				if ($v['sku']) {
					//取商品数据
					if ($goods['issku'] && $goods['status']) {
						$map['sku'] = $v['sku'];
						$sku = $msku -> where($map) -> find();
						if ($sku['status']) {
							if ($sku['num']) {
								//调整购买量
								$cache[$k]['goodsid'] = $goods['id'];
								$cache[$k]['skuid'] = $sku['id'];
								$cache[$k]['name'] = $goods['name'];
								$cache[$k]['skuattr'] = $sku['skuattr'];
								$cache[$k]['num'] = $v['num'] > $sku['num'] ? $sku['num'] : $v['num'];
								$cache[$k]['price'] = $sku['price'];
								$cache[$k]['total'] = $v['num'] * $sku['price'];
								$cache[$k]['pic'] = $pic['imgurl'];
								$cache[$k]['ismy'] = $goods['ismy'];
								$totalnum = $totalnum + $cache[$k]['num'];
								$totalprice = $totalprice + $cache[$k]['price'] * $cache[$k]['num'];
								//总重计算
								if($goods['ismy']==0){
									$heavyall=$heavyall+$sku['heavy']*$v['num'];
								}
							} else {
								//无库存删除
								$todelids = $todelids . $v['id'] . ',';
								unset($cache[$k]);

							}
						} else {
							//下架删除
							$todelids = $todelids . $v['id'] . ',';
							unset($cache[$k]);
						}
					} else {
						//下架删除
						$todelids = $todelids . $v['id'] . ',';
						unset($cache[$k]);
					}

				} else {
					if ($goods['status']) {
						if ($goods['num']) {
							//调整购买量
							$cache[$k]['goodsid'] = $goods['id'];
							$cache[$k]['skuid'] = 0;
							$cache[$k]['name'] = $goods['name'];
							$cache[$k]['skuattr'] = $sku['skuattr'];
							$cache[$k]['num'] = $v['num'] > $goods['num'] ? $goods['num'] : $v['num'];
							$cache[$k]['price'] = $goods['price'];
							$cache[$k]['total'] = $v['num'] * $goods['price'];
							$cache[$k]['pic'] = $pic['imgurl'];
							$cache[$k]['ismy'] = $goods['ismy'];
							$totalnum = $totalnum + $cache[$k]['num'];
							$totalprice = $totalprice + $cache[$k]['price'] * $cache[$k]['num'];
							//总重计算
							if($goods['ismy']==0){
								$heavyall=$heavyall+$goods['heavy']*$v['num'];
							}
						} else {
							//无库存删除
							$todelids = $todelids . $v['id'] . ',';
							unset($cache[$k]);
						}
					} else {
						//下架删除
						$todelids = $todelids . $v['id'] . ',';
						unset($cache[$k]);
					}
				}
			}
			$this->assign('heavyall',$heavyall);
			if ($todelids) {
				$rdel = $m -> delete($todelids);
				if (!$rdel) {
					$this -> error('购物车获取失败，请重新尝试！');
				}
			}
			//将商品列表
			sort($cache);
			$allitems = serialize($cache);
			$this -> assign('allitems', $allitems);
			//VIP信息
			$vipadd = I('vipadd');
			if ($vipadd) {
				$vip = M('Vip_address') -> where('id=' . $vipadd ) -> select();
			} else {
				$vip = M('Vip_address') -> where('vipid=' . $_SESSION['HOME']['vipid']) -> select();
			}
			foreach ($vip as $k => $v) {
				$ptemp=M('location_province')->where('id='.$v['province'])->find();
				$vip[$k]['provtext']=$ptemp['name'];
			}
			
			$this -> assign('vip', $vip);
			//可用代金券
			$mdjq = M('Vip_card');
			$mapdjq['type'] = 2;
			$mapdjq['vipid'] = $_SESSION['HOME']['vipid'];
			$mapdjq['status'] = 1;
			//1为可以使用
			$mapdjq['usetime'] = 0;
			$mapdjq['etime'] = array('gt', time());
			$mapdjq['usemoney'] = array('lt', $totalprice);
			$djq = $mdjq -> field('id,money') -> where($mapdjq) -> select();
			$this -> assign('djq', $djq);
			//是否可以用余额支付
			$vipinfo = M('user') -> where() -> find();
			$useryue = $vipinfo['money'];
			$isyue = $vipinfo['money'] - $totalprice >= 0 ? 0 : 1;
			$this -> assign('isyue', $isyue);
			//
			$vipinfo = M('vip') -> where('id=' . $_SESSION['HOME']['vipid']) -> find();
			$this -> assign('vipinfo', $vipinfo);
			//	$this -> assign('ntime', $ntime);
			$this -> assign('cache', $cache);
			$this -> assign('totalprice', $totalprice);
			$this -> assign('totalnum', $totalnum);
			//查询快递区域
			$pr=M('location_province')->select();
			$this->assign('prov',$pr);
			$this -> display();
		}

	}

	/**
	 * 详情
	 */
	public function orderDetail() {
		$sid = I('sid') ? I('sid') : 0;
		if (IS_POST) {
			//修改支付方式
			if (!I('orderid')) {
				$this -> diemsg(0, '订单不存在');
			}
			if (!I('paytype')) {
				$this -> error('请选择支付方式');
			} else {
				$re = M('shop_order') -> save(array('id' => I('orderid'), 'paytype' => I('paytype')));
				$this -> redirect(U('index/pay/', array('sid' => $sid, 'orderid' => I('orderid'))));
			}
		} else {
			$orderid = I('orderid') <> '' ? I('orderid') : $this -> diemsg(0, '缺少ORDERID参数');
			$bkurl = U('home/index/orderDetail', array('sid' => $sid, 'orderid' => $orderid));
			$backurl = base64_encode($bkurl);
			$loginurl = U('home/Vip/login', array('backurl' => $backurl));
			$re = $this -> checkLogin($backurl);
			//已登陆
			$m = M('Shop_order');
			$vipid = $_SESSION['HOME']['vipid'];
			$vipinfo = M('vip') -> where('id=' . $vipid) -> find();
			$map['sid'] = $sid;
			$map['id'] = $orderid;
			$cache = $m -> where($map) -> find();
			if (!$cache) {
				$this -> diemsg('此订单不存在!');
			}
			$cache['items'] = unserialize($cache['items']);
			//order日志
			$mlog = M('Shop_order_log');
			$log = $mlog -> where('oid=' . $cache['id']) -> select();
			$this -> assign('log', $log);
			if (!$cache['status'] == 1) {
				//是否可以用余额支付
				$useryue = $vipinfo['money'];
				$isyue = $vipinfo['money'] - $cache['payprice'] >= 0 ? 0 : 1;
				$this -> assign('isyue', $isyue);
			}
			$this -> assign('cache', $cache);
			//代金券调用
			if ($cache['djqid']) {
				$djq = M('Vip_card') -> where('id=' . $cache['djqid']) -> find();
				$this -> assign('djq', $djq);
			}
			$this -> display();
		}
	}

	//订单取消
	public function orderCancel() {
		$sid = I('sid') <> '' ? I('sid') : 0;
		//sid可以为0
		$orderid = I('orderid') <> '' ? I('orderid') : $this -> diemsg(0, '缺少ORDERID参数');
		$bkurl = U('home/index/orderDetail', array('sid' => $sid, 'orderid' => $orderid));
		$backurl = base64_encode($bkurl);
		$loginurl = U('home/Vip/login', array('backurl' => $backurl));
		$re = $this -> checkLogin($backurl);
		//已登陆
		$m = M('Shop_order');
		$map['sid'] = $sid;
		$map['id'] = $orderid;
		$cache = $m -> where($map) -> find();
		if (!$cache) {
			$info['status'] = 0;
			$info['msg'] = '此订单不存在!';
		} else if ($cache['status'] != 1) {
			$info['status'] = 0;
			$info['msg'] = '只有未付款订单可以取消！';
		} else {
			$re = $m -> where($map) -> setField('status', 0);
			if ($re) {
				//订单取消只有后端日志
				$mslog = M('Shop_order_syslog');
				$dlog['oid'] = $cache['id'];
				$dlog['msg'] = '订单取消';
				$dlog['type'] = 0;
				$dlog['ctime'] = time();
				$rlog = $mslog -> add($dlog);
				$info['status'] = 1;
				$info['msg'] = '订单取消成功！';

			} else {
				$info['status'] = 0;
				$info['msg'] = '订单取消失败,请重新尝试！';
			}
		}
		$this -> ajaxReturn($info);
	}

	//购物车
	public function basket() {
		$sid = I('sid') <> '' ? I('sid') : 0;
		//$this->diemsg(0, '缺少SID参数');//sid可以为0
		//	$re = $this -> checkLogin($backurl);
		//保存当前购物车地址
		//$this -> assign('basketurl', $basketurl);
		//保存登陆购物车地址
		//$this -> assign('basketloginurl', $basketloginurl);
		////保存购物车前地址
		//$this -> assign('basketlasturl', $basketlasturl);
		//保存购物车加密地址，用于OrderMaker正常返回
		//		$this->assign('lasturlencode',$lasturl);
		//已登陆
		$m = M('Shop_basket');
		$mgoods = M('Shop_goods');
		$msku = M('Shop_goods_sku');
		//		$returnurl=base64_decode($lasturl);
		//$this -> assign('returnurl', $returnurl);
		$cache = $m -> where(array('sid' => $sid, 'vipid' => $_SESSION['HOME']['vipid'])) -> select();
		//错误标记
		$errflag = 0;
		//等待删除ID
		$todelids = '';
		//totalprice
		$totalprice = 0;
		//totalnum
		$totalnum = 0;
		foreach ($cache as $k => $v) {
			//sku模型
			$goods = $mgoods -> where('id=' . $v['goodsid']) -> find();
			$pic = $this -> getPic($goods['pic']);
			if ($v['sku']) {
				//取商品数据
				if ($goods['issku'] && $goods['status']) {
					$map['sku'] = $v['sku'];
					$sku = $msku -> where($map) -> find();
					if ($sku['status']) {
						if ($sku['num']) {
							//调整购买量
							$cache[$k]['name'] = $goods['name'];
							$cache[$k]['skuattr'] = $sku['skuattr'];
							$cache[$k]['num'] = $v['num'] > $sku['num'] ? $sku['num'] : $v['num'];
							$cache[$k]['price'] = $sku['price'];
							$cache[$k]['total'] = $sku['num'];
							$cache[$k]['pic'] = $pic['imgurl'];
							$cache[$k]['totelmoney'] = $cache[$k]['num'] * $cache[$k]['price'];
							$totalnum = $totalnum + $cache[$k]['num'];
							$totalprice = $totalprice + $cache[$k]['price'] * $cache[$k]['num'];
						} else {
							//无库存删除
							$todelids = $todelids . $v['id'] . ',';
							unset($cache[$k]);

						}
					} else {
						//下架删除
						$todelids = $todelids . $v['id'] . ',';
						unset($cache[$k]);
					}
				} else {
					//下架删除
					$todelids = $todelids . $v['id'] . ',';
					unset($cache[$k]);
				}
			} else {
				if ($goods['status']) {
					if ($goods['num']) {
						//调整购买量
						$cache[$k]['name'] = $goods['name'];
						$cache[$k]['skuattr'] = $sku['skuattr'];
						$cache[$k]['num'] = $v['num'] > $goods['num'] ? $goods['num'] : $v['num'];
						$cache[$k]['price'] = $goods['price'];
						$cache[$k]['total'] = $goods['num'];
						$cache[$k]['pic'] = $pic['imgurl'];
						$cache[$k]['totelmoney'] = $cache[$k]['num'] * $cache[$k]['price'];
						$totalnum = $totalnum + $cache[$k]['num'];
						$totalprice = $totalprice + $cache[$k]['price'] * $cache[$k]['num'];
					} else {
						//无库存删除
						$todelids = $todelids . $v['id'] . ',';
						unset($cache[$k]);
					}
				} else {
					//下架删除
					$todelids = $todelids . $v['id'] . ',';
					unset($cache[$k]);
				}
			}
		}
		if ($todelids) {
			$rdel = $m -> delete($todelids);
			if (!$rdel) {
				$this -> error('购物车获取失败，请重新尝试！');
			}
		}
		$this -> assign('cache', $cache);
		$this -> assign('totalprice', $totalprice);
		$this -> assign('totalnum', $totalnum);
		$this -> display();
	}

	//购物车库存检测
	public function checkbasket() {
		if (IS_AJAX) {
			$sid = $_GET['sid'] ? $_GET['sid'] : '0';
			//前端必须保证登陆状态
			$vipid = $_SESSION['HOME']['vipid'];
			if (!$vipid) {
				$info['status'] = 3;
				$info['msg'] = '登陆已超时，2秒后自动跳转登陆页面！';
				$this -> ajaxReturn($info);
			}
			$arr = $_POST;
			if ($sid == '') {
				$info['status'] = 0;
				$info['msg'] = '未获取SID参数';
				$this -> ajaxReturn($info);
			}
			if (!$arr) {
				$info['status'] = 0;
				$info['msg'] = '未获取数据，请重新尝试';
				$this -> ajaxReturn($info);
			}
			$m = M('Shop_basket');
			$mgoods = M('Shop_goods');
			$msku = M('Shop_goods_sku');
			$data = $m -> where(array('sid' => $sid, 'vipid' => $_SESSION['HOME']['vipid'])) -> select();
			foreach ($data as $k => $v) {
				$goods = $mgoods -> where('id=' . $v['goodsid']) -> find();
				if ($v['sku']) {
					$sku = $msku -> where(array('sku' => $v['sku'])) -> find();
					if ($sku && $sku['status'] && $goods && $goods['issku'] && $goods['status']) {
						$nownum = $arr[$v['id']];
						if ($sku['num'] - $nownum >= 0) {
							//保存购物车新库存
							if ($nownum <> $v['num']) {
								$v['num'] = $nownum;
								$rda = $m -> save($v);
							}
						} else {
							$info['status'] = 2;
							$info['msg'] = '存在已下架或库存不足商品！';
							$this -> ajaxReturn($info);
						}

					} else {
						$info['status'] = 2;
						$info['msg'] = '存在已下架或库存不足商品！';
						$this -> ajaxReturn($info);
					}
				} else {
					if ($goods && $goods['status']) {
						$nownum = $arr[$v['id']];
						if ($goods['num'] - $nownum >= 0) {
							//保存购物车新库存
							if ($nownum <> $v['num']) {
								$v['num'] = $nownum;
								$rda = $m -> save($v);
							}
						} else {
							$info['status'] = 2;
							$info['msg'] = '存在已下架或库存不足商品！';
							$this -> ajaxReturn($info);
						}

					} else {
						$info['status'] = 2;
						$info['msg'] = '存在已下架或库存不足商品！';
						$this -> ajaxReturn($info);
					}
				}
			}
			$info['status'] = 1;
			$info['msg'] = '商品库存检测通过，进入结算页面！';
			$this -> ajaxReturn($info);
		} else {
			$this -> diemsg(0, '禁止外部访问！');
		}
	}

	//添加购物车
	public function addtobasket() {
		if (IS_AJAX) {
			$m = M('Shop_basket');
			$data = I('post.');
			if (!$data) {
				$info['status'] = 0;
				$info['msg'] = '未获取数据，请重新尝试';
				$this -> ajaxReturn($info);
			}
			//区分SKU模式
			if ($data['sku']) {
				$old = $m -> where(array('sid' => $data['sid'], 'vipid' => $data['vipid'], 'sku' => $data['sku'])) -> find();
				if ($old) {
					$old['num'] = $old['num'] + $data['num'];
					$rold = $m -> save($old);
					if ($rold === FALSE) {
						$info['status'] = 0;
						$info['msg'] = '添加购物车失败，请重新尝试！';
					} else {
						$total = $m -> where(array('sid' => $data['sid'], 'vipid' => $data['vipid'])) -> sum('num');
						$info['total'] = $total;
						$info['status'] = 1;
						$info['msg'] = '添加购物车成功！';
					}
				} else {
					$rold = $m -> add($data);
					if ($rold) {
						$total = $m -> where(array('sid' => $data['sid'], 'vipid' => $data['vipid'])) -> sum('num');
						$info['total'] = $total;
						$info['status'] = 1;
						$info['msg'] = '添加购物车成功！';
					} else {
						$info['status'] = 0;
						$info['msg'] = '添加购物车失败，请重新尝试！';
					}
				}
			} else {
				$old = $m -> where(array('sid' => $data['sid'], 'vipid' => $data['vipid'], 'goodsid' => $data['goodsid'])) -> find();
				if ($old) {
					$old['num'] = $old['num'] + $data['num'];
					$rold = $m -> save($old);
					if ($rold === FALSE) {
						$info['status'] = 0;
						$info['msg'] = '添加购物车失败，请重新尝试！';
					} else {
						$total = $m -> where(array('sid' => $data['sid'], 'vipid' => $data['vipid'])) -> sum('num');
						$info['total'] = $total;
						$info['status'] = 1;
						$info['msg'] = '添加购物车成功！';
					}
				} else {
					$rold = $m -> add($data);
					if ($rold) {
						$total = $m -> where(array('sid' => $data['sid'], 'vipid' => $data['vipid'])) -> sum('num');
						$info['total'] = $total;
						$info['status'] = 1;
						$info['msg'] = '添加购物车成功！';
					} else {
						$info['status'] = 0;
						$info['msg'] = '添加购物车失败，请重新尝试！';
					}
				}
			}
			$this -> ajaxReturn($info);
		} else {
			$this -> diemsg(0, '禁止外部访问！');
		}
	}

	//删除购物车
	public function delbasket() {
		if (IS_AJAX) {
			$id = I('id');
			if (!$id) {
				$info['status'] = 0;
				$info['msg'] = '未获取ID参数,请重新尝试！';
				$this -> ajaxReturn($info);
			}
			$m = M('Shop_basket');
			$re = $m -> where('id=' . $id) -> delete();
			if ($re) {
				$info['status'] = 1;
				$info['msg'] = '删除成功，更新购物车状态...';

			} else {
				$info['status'] = 0;
				$info['msg'] = '删除失败，自动重新加载购物车...';
			}
			$this -> ajaxReturn($info);
		} else {
			$this -> diemsg(0, '禁止外部访问！');
		}
	}

	//清空购物车
	public function clearbasket() {
		if (IS_AJAX) {
			//前端必须保证登陆状态
			$vipid = $_SESSION['HOME']['vipid'];
			if (!$vipid) {
				$info['status'] = 3;
				$info['msg'] = '登陆已超时，2秒后自动跳转登陆页面！';
				$this -> ajaxReturn($info);
			}
			$m = M('Shop_basket');
			$re = $m -> where(array('vipid' => $vipid)) -> delete();
			if ($re) {
				$info['status'] = 2;
				$info['msg'] = '购物车已清空';
				$this -> ajaxReturn($info);
			} else {
				$info['status'] = 0;
				$info['msg'] = '购物车清空失败，请重新尝试！';
				$this -> ajaxReturn($info);
			}
		} else {
			$this -> diemsg(0, '禁止外部访问！');
		}
	}

	//立刻购买逻辑
	public function fastbuy() {
		if (IS_AJAX) {
			$m = M('Shop_basket');
			$data = I('post.');
			if (!$data) {
				$info['status'] = 0;
				$info['msg'] = '未获取数据，请重新尝试';
				$this -> ajaxReturn($info);
			}

			//	$this->ajaxReturn($info);
			//判定是否有库存
			//			if($data['sku']){
			//				$gd=M('Shop_goods_sku')->where('id='.$data['sku'])->find();
			//				if(!$gd['status']){
			//					$info['status']=0;
			//					$info['msg']='此产品已下架，请挑选其他产品！';
			//					$this->ajaxReturn($info);
			//				}
			//				if($gd['num']-$data['num']<0){
			//					$info['status']=0;
			//					$info['msg']='该属性产品缺货或库存不足，请调整购买量！';
			//					$this->ajaxReturn($info);
			//				}
			//			}else{
			//				$info['status']=0;
			//				$info['msg']='此产品已下架，请挑选其他产品！';
			//				$this->ajaxReturn($info);
			//				$gd=M('Shop_goods')->where('id='.$data['goodsid'])->find();
			//				if(!$gd['status']){
			//					$info['status']=0;
			//					$info['msg']='此产品已下架，请挑选其他产品！';
			//					$this->ajaxReturn($info);
			//				}
			//				if($gd['num']-$data['num']<0){
			//					$info['status']=0;
			//					$info['msg']='该产品缺货或库存不足，请调整购买量！';
			//					$this->ajaxReturn($info);
			//				}
			//			}
			//清除购物车
			$sid = 0;
			//前端必须保证登陆状态
			$vipid = $_SESSION['HOME']['vipid'];
			$re = $m -> where(array('sid' => $sid, 'vipid' => $vipid)) -> delete();
			//区分SKU模式
			if ($data['sku']) {
				$rold = $m -> add($data);
				if ($rold) {
					$info['status'] = 1;
					$info['msg'] = '库存检测通过！2秒后自动生成订单！';
				} else {
					$info['status'] = 0;
					$info['msg'] = '通讯失败，请重新尝试！';
				}
			} else {
				$rold = $m -> add($data);
				if ($rold) {
					$info['status'] = 1;
					$info['msg'] = '库存检测通过！2秒后自动生成订单！';
				} else {
					$info['status'] = 0;
					$info['msg'] = '通讯失败，请重新尝试！';
				}
			}
			$this -> ajaxReturn($info);
		} else {
			$this -> diemsg(0, '禁止外部访问！');
		}
	}

	//删除地址
	public function removeaddress() {
		if (IS_POST) {
			$m = M('vip_address');
			$data['id'] = I('id');
			$re = $m -> where('id='.I('id'))->delete();
			if ($re) {
				$info['status'] = 1;
				$info['msg'] = '删除成功';
			} else {
				$info['status'] = 0;
				$info['msg'] = '操作失败,请重试!';
			}
			$this -> ajaxReturn($info);
		}
	}

	//保存地址
	public function saveaddress() {
		if (IS_POST) {
			$data = I('data');
			$data['ctime'] = time();
			$data['status'] = 1;
			$data['vipid'] = $_SESSION['HOME']['vipid'];
			$m = M('vip_address');
			$re = $m -> add($data);
			if ($re) {
				$info['status'] = 1;
				$info['msg'] = '保存成功';
				$data['id'] = $m -> getLastInsID();
				$info['data'] = $data;
			} else {
				$info['status'] = 0;
				$info['msg'] = '操作失败,请重试!';
			}
			$this -> ajaxReturn($info);
		}
	}
    /**
    *搜索排行记录
    */
    private function searchhistory($searchword){
        if($searchword){
            $w=M('search_words')->where("text='".$searchword."'")->find();
            if($w){
               $w['times'] =$w['times']+1;
               M('search_words')->save($w);
            }else{
               M('search_words')->add(array('text'=>$searchword,'times'=>1,'status'=>1));
            }
        }
    }
	/**
	 * 分类
	 */
	public function catelist() {
		$id = I('id') ? I('id') : '0';
		$o = I('o') != 1 ? 'asc' : 'desc';
		$this -> assign('o', I('o') != 1 ? '2' : '1');
		$s = I('s') ? I('s') : '';
        $this->searchhistory($s);
		$b = I('b') ? I('b') : '1';
		$this -> assign('b', $b);
		$pageNo = I('p') ? I('p') : 1;
		$this -> assign('p', $pageNo);
		$pageSize = 12;
		$m = M('shop_cate');
		$mg = M('shop_goods');
		$map = 'status=1';
		$order = '';
		//分类
		if ($id != 0) {
			$ca = $m -> where('id=' . $id) -> find();
			$this -> assign('cate', $ca);
			$cids = $ca['soncate'] . $ca['id'];
			$map .= " and cid in (" . $cids . ")";
			$this->assign('pagetitle',$ca['name']);
		}
		//搜索
		if ($s != '') {
			$map .= " and name like '%" . $s . "%'";
			$this -> assign('s', $s);
			$this->assign('pagetitle',"搜索:'".$s."' 结果.");
		}
		if ($b == 1) {
			$order = 'sells' . " " . $o;
		} else if ($b == 2) {
			$order = 'id' . " " . $o;
		} else if ($b == 3) {
			$order = 'price' . " " . $o;
		}
		$goods = $mg -> where($map) -> page($pageNo, $pageSize) -> order($order) -> select();
		$goodsCount = $mg -> where($map) -> count();
		$this -> assign('goods', $goods);
		$re = $goodsCount / $pageSize;
		if (floor($re) < $re) {
			$count = floor($re) + 1;
		} else {
			$count = floor($re);
		}
		if ($goodsCount / $pageSize == floor($goodsCount / $pageSize)) {
			$maxpage = $goodsCount / $pageSize;
		} else {
			$maxpage = floor($goodsCount / $pageSize) + 1;
		}
		$this -> assign('hasnext', $pageNo == $maxpage);
		$this -> assign('count', $count + 1);
		$this -> display();
	}

	//订单支付
	public function pay() {
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$orderid = I('orderid') <> '' ? I('orderid') : $this -> diemsg(0, '缺少ORDERID参数');
		$type = I('type');
		//$bkurl=U('Wap/Shop/pay',array('sid'=>$sid,'orderid'=>$orderid,'type'=>$type));
		//$backurl=base64_encode($orderdetail);
		//$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		//$re=$this->checkLogin($backurl);
		//已登陆
		$m = M('Shop_order');
		$order = $m -> where('id=' . $orderid) -> find();
		if (!$order) {
			$this -> error('此订单不存在！');
		}
		if ($order['status'] <> 1) {
			$this -> error('此订单不可以支付！');
		}
		$paytype = I('type') ? I('type') : $order['paytype'];
		switch($paytype) {
			case 'money' :
				$mvip = M('Vip');
				$vip = $mvip -> where('id=' . $_SESSION['HOME']['vipid']) -> find();
				$pp = $vip['money'] - $order['payprice'];
				if ($pp >= 0) {
					$re = $mvip -> where('id=' . $_SESSION['HOME']['vipid']) -> setField('money', $pp);
					if ($re !== false) {
						$order['paytype'] = 'money';
						$order['ispay'] = 1;
						$order['paytime'] = time();
						$order['status'] = 2;
						$rod = $m -> save($order);
						if (FALSE !== $rod) {
							//=======================================
							$SET = M('Set') -> find();
							$items = unserialize($order['items']);
							$itemsname = '';
							foreach ($items as $k => $v) {
								$itemsname .= $v['name'] . ',';
							}
							unset($items);
							//							$tp=new \bb\template();
							//							$array=array(
							//								'url'=>$SET['wxurl'].U('wap/shop/orderDetail',array('orderid'=>$order['id'])),
							//								'name'=>$order['vipname'],
							//								'ordername'=>rtrim($itemsname,','),
							//								'orderid'=>$order['oid'],
							//								'money'=>$order['payprice'],
							//								'date'=>date("Y-m-d H:i:s",time())
							//							);
							//							$templatedata=$tp->enddata('orderok',$order['vipopenid'],$array);	//组合模板数据
							//							$options['appid']= self::$_wxappid;
							//							$options['appsecret']= self::$_wxappsecret;
							//							$wx = new \Joel\wx\Wechat($options);
							//							$wx->sendTemplateMessage($templatedata);	//发送模板
							//=======================================
							//销量计算-只减不增
							$rsell = $this -> doSells($order);
							//前端日志
							$mlog = M('Shop_order_log');
							$dlog['oid'] = $order['id'];
							$dlog['msg'] = '余额-付款成功';
							$dlog['ctime'] = time();
							$rlog = $mlog -> add($dlog);
							//后端日志
							$mlog = M('Shop_order_syslog');
							$dlog['type'] = 2;
							$rlog = $mlog -> add($dlog);
							$this -> success('余额付款成功！', U('Home/Vip/order'));

							//首次支付成功自动变为微米
							//							if($vip && !$vip['isfx']){
							//								$rvip=$mvip->where('id='.$_SESSION['WAP']['vipid'])->setField('isfx',1);
							//								$data_msg['pids']=$_SESSION['WAP']['vipid'];
							//								$data_msg['title']="您成功升级为的分销商！";
							//								$data_msg['content']="欢迎成为分销商，开启一个新的旅程！";
							//								$data_msg['ctime']=time();
							//								$rmsg=M('vip_message')->add($data_msg);
							//							}

							//代收佣金计算-只减不增
							//$rds=$this->doDs($order);

							//==通知该会员的上级==即将得到的佣金=========================================================
							//$ds=M('fx_dslog')->where(array('oid'=>$order['id']))->find();		//查找将获得的佣金
							//$vipuser=M('vip')->where(array('id'=>$ds['to']))->find(); //查找他的上级获取上级的openid
							//$SET=M('Set')->find();
							//							$tp=new \bb\template();
							//							$array=array(
							//								'url'=>$SET['wxurl'].U('wap/fx/dslog'),
							//								'name'=>$ds['toname'],		//上级名字
							//								'fromname'=>$ds['fromname'],	//下级级名字
							//								'ordername'=>rtrim($itemsname,','),
							//								'money'=>$ds['fxprice'],		//商品价格
							//								'yj'=>$ds['fxyj']			//分销的佣金
							//							);
							//							$openid=$vipuser['openid'];		//发给的人
							//							$templatedata=$tp->enddata('collection',$openid,$array);	//组合模板数据
							//							$wx->sendTemplateMessage($templatedata);	//发送模板
							//=============================================================

						} else {
							//后端日志
							$mlog = M('Shop_order_syslog');
							$dlog['oid'] = $order['id'];
							$dlog['msg'] = '余额付款失败';
							$dlog['type'] = -1;
							$dlog['ctime'] = time();
							$rlog = $mlog -> add($dlog);
							$this -> error('余额付款失败！请联系客服！', U('home/index/orderDetail', array('orderid' => $order['id'])));
						}

					} else {
						//后端日志
						$mlog = M('Shop_order_syslog');
						$dlog['oid'] = $order['id'];
						$dlog['msg'] = '余额付款失败';
						$dlog['type'] = -1;
						$dlog['ctime'] = time();
						$this -> error('余额支付失败，请重新尝试！', U('home/index/orderDetail', array('orderid' => $order['id'])));
					}
				} else {
					$this -> error('余额不足，请使用其它方式付款！', U('home/index/orderDetail', array('orderid' => $order['id'])));
				}
				break;
			case 'alipay' :
				$this -> redirect(U('/Home/Alipay/pay', array('price' => $order['payprice'], 'oid' => $order['oid'])));
				break;
			case 'wxpay' :
				$this -> redirect(U('/Home/Index/wxpay', array('oid' => $order['id'])));
				break;
			case 'paypalpay' :
				$this -> redirect(U('/Home/Paypal/pay', array('oid' => $order['id'])));
				break;
			default :
				$this -> error('支付方式未知！');
				break;
		}

	}

	public function wxpay() {
		$oid = I('oid');
		if (!$oid) {
			$this -> error('参数不全！');
		}
		$order = M('Shop_order') -> where('id=' . $oid) -> find();
		if (!$order) {
			$this -> error('此订单不存在！');
		}
		if ($order['ispay']) {
			$this -> redirect(U('/Home/Vip/order'));
		}
		$this -> assign('oid', $oid);
		$this -> display();
	}

	public function getwxcode() {
		$set = M('Set') -> find();
		$oid = I('oid');
		$order = M('Shop_order') -> where('id=' . $oid) -> find();
		if ($order['wxcode'] && time() - $order['wxcodetime'] < 400) {
			$url = $order['wxcode'];
		} else {
			$posturl = "https://api.mch.weixin.qq.com/pay/unifiedorder";
			$options['appid'] = $set['wxappid'];
			$options['mch_id'] = $set['wxmchid'];
			$options['body'] = $set['wxname'];
			$options['attach'] = $order['oid'];
			$options['out_trade_no'] = $order['id'] . '-' . time();
			$options['total_fee'] = $order['payprice'] * 100;
			$options['time_start'] = date("YmdHis");
			$options['time_expire'] = date("YmdHis", time() + 600);
			$options['goods_tag'] = $order['id'] . '-' . $order['vipid'];
			$options['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . '/Wxpay/ndsk/';
			$options['trade_type'] = "NATIVE";
			$options['product_id'] = $order['id'];
			$options['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
			$options['nonce_str'] = $this -> getNonceStr();
			$options['sign'] = $this -> MakeSign($options, $set['wxmchkey']);
			$xml = $this -> ToXml($options);
			$response = $this -> postXmlCurl($xml, $posturl);
			$result = $this -> FromXml($response);
			$url = $result["code_url"];
			$order['wxcode'] = $result["code_url"];
			$order['wxcodetime'] = time();
			$rod = M('Shop_order') -> save($order);
		}
		$QR = new \Joel\QRcode();
		//$QR::png($url);
		$QR::png($url, FALSE, 'L', 9);
	}

	//销量计算
	private function doSells($order) {
		$mgoods = M('Shop_goods');
		$msku = M('Shop_goods_sku');
		$mlogsell = M('Shop_syslog_sells');
		//封装dlog
		$dlog['oid'] = $order['id'];
		$dlog['vipid'] = $order['vipid'];
		$dlog['vipopenid'] = $order['vipopenid'];
		//$dlog['vipunionid']=$order['vipunionid'];
		$dlog['vipname'] = $order['vipname'];
		$dlog['ctime'] = time();
		$items = unserialize($order['items']);
		$tmplog = array();
		foreach ($items as $k => $v) {
			//销售总量
			$dnum = $dlog['num'] = $v['num'];
			if ($v['skuid']) {
				$rg = $mgoods -> where('id=' . $v['goodsid']) -> setDec('num', $dnum);
				$rg = $mgoods -> where('id=' . $v['goodsid']) -> setInc('sells', $dnum);
				$rg = $mgoods -> where('id=' . $v['goodsid']) -> setInc('dissells', $dnum);
				$rs = $msku -> where('id=' . $v['skuid']) -> setDec('num', $dnum);
				$rs = $msku -> where('id=' . $v['skuid']) -> setInc('sells', $dnum);
				//sku模式
				$dlog['goodsid'] = $v['goodsid'];
				$dlog['goodsname'] = $v['name'];
				$dlog['skuid'] = $v['skuid'];
				$dlog['skuattr'] = $v['skuattr'];
				$dlog['price'] = $v['price'];
				$dlog['num'] = $v['num'];
				$dlog['total'] = $v['total'];
			} else {
				$rg = $mgoods -> where('id=' . $v['goodsid']) -> setDec('num', $dnum);
				$rg = $mgoods -> where('id=' . $v['goodsid']) -> setInc('sells', $dnum);
				$rg = $mgoods -> where('id=' . $v['goodsid']) -> setInc('dissells', $dnum);
				//纯goods模式
				$dlog['goodsid'] = $v['goodsid'];
				$dlog['goodsname'] = $v['name'];
				$dlog['skuid'] = 0;
				$dlog['skuattr'] = 0;
				$dlog['price'] = $v['price'];
				$dlog['num'] = $v['num'];
				$dlog['total'] = $v['total'];
			}
			array_push($tmplog, $dlog);
		}
		if (count($tmplog)) {
			$rlog = $mlogsell -> addAll($tmplog);
		}
		return true;
	}

	/**
	 * 将xml转为array
	 * @param string $xml
	 * @throws WxPayException
	 */
	public function FromXml($xml) {
		if (!$xml) {
			//die("xml数据异常！");
		}
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$re = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $re;
	}

	/**
	 * 输出xml字符
	 * @throws WxPayException
	 **/
	public function ToXml($obj) {
		if (!is_array($obj) || count($obj) <= 0) {
			die("数组数据异常！");
		}

		$xml = "<xml>";
		foreach ($obj as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			} else {
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}
		$xml .= "</xml>";
		return $xml;
	}

	public function getNonceStr($length = 32) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

	/**
	 * 格式化参数格式化成url参数
	 */
	public function ToUrlParams($obj) {
		$buff = "";
		foreach ($obj as $k => $v) {
			if ($k != "sign" && $v != "" && !is_array($v)) {
				$buff .= $k . "=" . $v . "&";
			}
		}

		$buff = trim($buff, "&");
		return $buff;
	}

	/**
	 * 生成签名
	 * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
	 */
	public function MakeSign($obj, $key) {
		//签名步骤一：按字典序排序参数
		ksort($obj);
		$string = $this -> ToUrlParams($obj);
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=" . $key;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}

	/**
	 * 	作用：以post方式提交xml到对应的接口url
	 */
	public function postXmlCurl($xml, $url, $second = 30) {
		//初始化curl
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOP_TIMEOUT, $second);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		curl_close($ch);
		//返回结果
		if ($data) {
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error" . "<br>";
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}
	/**
	 * 计算邮费
	 * post请求
	 * prid 省份ID
	 * heavy 重量
	 * return 邮费
	 */
	public function getpostage(){
		$prid=I('prid');
		$heavy=I('heavy');
		$totalprice=I('totalprice');
		$info['money']=$this->_getpostage($prid,$heavy,$totalprice);
		$this->ajaxReturn($info);
	}
	/**
	 * 计算邮费
	 * prid 省份ID
	 * heavy 重量
	 * return 邮费
	 */
	private function _getpostage($prid,$heavy,$totalprice){
		if(!$_SESSION['SHOPSET']['isyf']){
			return 0;
		}
		//获取所属区域的信息
		$area=M('express_area')->where("provids like '%|".$prid."|%'")->find();
		//江浙沪包邮
//		dump($area);
		if($area['topmoney']){			
//			dump($totalprice);
			if($totalprice>=$area['topmoney']){
				$yf=0;
			}else{
				//计算邮费价格
				$heavylist=array_filter(explode(',', $area['heavylist']));
				$moneylist=array_filter(explode(',', $area['moneylist']));
				$yf=0;
				$isin=true;
				foreach ($heavylist as $k => $v) {
					if($isin){
						if($heavylist[$k-1]){
							$yf=$yf+($heavylist[$k]-$heavylist[$k-1])*$moneylist[$k];
						}else{
							$yf=$yf+$heavylist[$k]*$moneylist[$k];
						}
					}
					if($heavy<=$v){
						$isin=false;
					}
				}
				if($isin){
					$yf=$yf+round($heavy-end($heavylist)+0.5)*end($moneylist);
				}
			}
			return $yf;
		}else{
//		$freepost=array(9,10,11);
//		if(in_array($prid,$freepost)){
//			
//		}

			//计算邮费价格
			$heavylist=array_filter(explode(',', $area['heavylist']));
			$moneylist=array_filter(explode(',', $area['moneylist']));
			$yf=0;
			$isin=true;
			foreach ($heavylist as $k => $v) {
				if($isin){
					if($heavylist[$k-1]){
						$yf=$yf+($heavylist[$k]-$heavylist[$k-1])*$moneylist[$k];
					}else{
						$yf=$yf+$heavylist[$k]*$moneylist[$k];
					}
				}
				if($heavy<=$v){
					$isin=false;
				}
			}
			if($isin){
				$yf=$yf+round($heavy-end($heavylist)+0.5)*end($moneylist);
			}
			return $yf;
		}
	}
}
