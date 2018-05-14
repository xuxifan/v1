<?php
namespace Home\Controller;
use Home\Controller\BaseController;
class VipController extends BaseController {

	public function _initialize() {
		//你可以在此覆盖父类方法
		parent::_initialize();
		$passlist = array('login', 'logout', 'getqrcode', 'checkwclogin', 'agreement','joinfree','invoice','payway');
		//不检测登陆状态的操作
		$check = in_array(ACTION_NAME, $passlist);
		//		dump($_SESSION);
		if (!$check) {
			if (!isset($_SESSION['HOME']['vipid'])) {
				$this -> error('登陆已超时,请重新登陆~', U('Home/Vip/login'));
			}
		}
		//
		$vipid = $_SESSION['HOME']['vipid'];
		$m = M('vip');
		$vipinfo = $m -> where('id=' . $vipid) -> find();
		$_SESSION['HOME']['vip'] = $vipinfo;
		$this -> assign('vipinfo', $vipinfo);
		//购物车
		$basketnum = M('Shop_basket') -> where(array('sid' => 0, 'vipid' => $_SESSION['HOME']['vipid'])) -> sum('num');
		$this -> assign('basketnum', $basketnum?$basketnum:0);
		$this -> assign('time', time());
		$this -> assign('an', 'menu' . ACTION_NAME . implode("", I('')));
		//获取新闻消息
		//1.25 zxg PC端新闻添加标签-->
		$news = M('news') -> limit(20)->where(array('type'=>'pc')) -> order('ctime desc') -> select();
		foreach ($news as $k => $v) {
			$news[$k]['pic']=$this->getPic($v['pic']);
		}
		$this -> assign('news', $news);
         //搜索关键字
        $searchwords=M('search_words')->order('times desc')->limit('0,4')->select();
        $this->assign('searchwords',$searchwords);
	}

	public function index() {
		//订单信息
		$vipid = $_SESSION['HOME']['vipid'];
		$m = M('shop_order');
		$dfk = $m -> where('vipid=' . $_SESSION['HOME']['vipid'] . ' and status=1') -> count();
		$dsh = $m -> where('vipid=' . $_SESSION['HOME']['vipid'] . ' and status in (2,3)') -> count();
		$this -> assign('dfk', $dfk);
		$this -> assign('dsh', $dsh);
		$map['vipid'] = $vipid;
		$cache = $m -> where($map) -> order('ctime desc') -> select();
		if ($cache) {
			foreach ($cache as $k => $v) {
				if ($v['items']) {
					$cache[$k]['items'] = unserialize($v['items']);
				}
			}
		}
		$this -> assign('cache', $cache);
		//我的佣金
		$myj = M('fx_dslog');
		unset($map);
		$map['to'] = $vipid;
		$map['status'] = 1;
		$yjdata = $myj -> where($map) -> limit(8) -> order('ctime desc') -> select();
		$this -> assign('yjdata', $yjdata);
		//签到
		$mvl = M('vip_log');
		$signlog = $mvl -> where("EVENT LIKE '%会员签到-连续%天%' and vipid=" . $vipid . ' and ctime>=' . strtotime(date("Y-m-d", strtotime("-1 day")))) -> order('ctime DESC') -> select();
		if ($signlog) {
			preg_match_all('/\d+/', $signlog[0]['event'], $maxsigntime);
			$this -> assign('maxsigntime', $maxsigntime[0][0]);
		} else {
			$this -> assign('maxsigntime', 0);
		}
		//网站推广链接
		$homeshare = 'http://' . $_SERVER['HTTP_HOST'] . '/Home/Index/index/ppid/' . $_SESSION['HOME']['vipid'] . '/';
		$this -> assign('homeshare', $homeshare);
		$this -> display();
	}

	public function getlevel($exp) {
		$data = M('vip_level') -> order('exp') -> select();
		if ($data) {
			$level;
			foreach ($data as $k => $v) {
				if ($k + 1 == count($data)) {
					if ($exp >= $data[$k]['exp']) {
						$level['levelid'] = $data[$k]['id'];
						$level['levelname'] = $data[$k]['name'];
					}
				} else {
					if ($exp >= $data[$k]['exp'] && $exp < $data[$k + 1]['exp']) {
						$level['levelid'] = $data[$k]['id'];
						$level['levelname'] = $data[$k]['name'];
					}
				}
			}
		} else {
			return utf8error('会员等级未定义！');
		}
		return $level;
	}

	/**
	 * 签到
	 */
	public function sign() {
		$this -> checkLogin();
		$vipid = $_SESSION['HOME']['vipid'];
		$sign_score = explode(',', $_SESSION['HOME']['sign_score']);
		$sign_exp = explode(',', $_SESSION['HOME']['sign_exp']);
		$vip = M('vip') -> where(array('id' => $vipid)) -> find();
		$d1 = date_create(date('Y-m-d', $vip['signtime']));
		$d2 = date_create(date('Y-m-d', time()));
		$diff = date_diff($d1, $d2);
		$late = $diff -> format("%a");
		//判断是否签到过
		if ($late < 1) {
			$info['status'] = 0;
			$info['msg'] = "您今日已经签过到了！";
			$this -> ajaxReturn($info);
		}
		//正常签到累计流程
		if ($late >= 1 && $late < 2) {
			$vip['sign'] = $vip['sign'] ? $vip['sign'] : 0;
			//防止空值

			$data_vip['sign'] = $vip['sign'] + 1;
			//签到次数+1
			//积分
			if ($data_vip['sign'] >= count($sign_score)) {
				$score = $sign_score[count($sign_score) - 1];
			} else {
				$score = $sign_score[$data_vip['sign']];
			}
			//经验
			if ($data_vip['sign'] >= count($sign_exp)) {
				$exp = $sign_exp[count($sign_exp) - 1];
			} else {
				$exp = $sign_exp[$data_vip['sign']];
			}
		} else {
			$data_vip['sign'] = 0;
			//签到次数置零
			$score = $sign_score[0] ? $sign_score[0] : 0;
			$exp = $sign_exp[0] ? $sign_exp[0] : 0;
		}
		$data_vip['score'] = array('exp', 'score+' . $score);
		$data_vip['exp'] = array('exp', 'exp+' . $exp);
		$data_vip['signtime'] = time();
		$data_vip['cur_exp'] = array('exp', 'cur_exp+' . $exp);
		$level = $this -> getlevel($vip['cur_exp'] + $exp);
		$data_vip['levelid'] = $level['levelid'];
		$m = M('Vip');
		$r = $m -> where(array('id' => $vipid)) -> save($data_vip);
		if ($r !== false) {
			//增加签到日志
			$data_log['ip'] = get_client_ip();
			$data_log['vipid'] = $vipid;
			$data_log['event'] = '会员签到-连续' . ($data_vip['sign'] + 1) . '天';
			$data_log['score'] = $score;
			$data_log['exp'] = $exp;
			$data_log['type'] = 2;
			$data_log['ctime'] = time();
			M('vip_log') -> add($data_log);
			$info['status'] = 1;
			$info['msg'] = "签到成功！";
			$data_log['levelname'] = $level['levelname'];
			$info['data'] = $data_log;
		} else {
			$info['status'] = 0;
			$info['msg'] = "签到失败！" . $r;
		}
		$this -> ajaxReturn($info);
	}

	public function getqrcode() {
		$uuid = session_id();
		$ppid = $_SESSION['ppid'] ? $_SESSION['ppid'] : 0;
		$set = M('Set') -> find();
		$url = 'http://'.$_SERVER['HTTP_HOST']. '/Wap/Shop/wclogin/uuid/' . $uuid . '/ppid/' . $ppid . '/';
		//dump($url);
		$QR = new \Joel\QRcode();
		//$QR::png($url);
		$QR::png($url, FALSE, 'L', 9);
	}

	public function login() {
		if ($_SESSION['HOME']['vipid']) {
			$this -> redirect(U('Home/Vip/index/'));

		} else {
			$this -> display();
		}

	}

	public function logout() {
		$vipid = $_SESSION['HOME']['vipid'];
		$ppid = $_SESSION['ppid'];
		session(null);
		if ($ppid) {
			$_SESSION['ppid'] = $ppid;
		}
		M('wclogin') -> where("vipid=" . $vipid . " or ssid='" . session_id() . "'") -> delete();
		$this -> redirect(U('Home/Index/index'));
	}

	/**
	 * 地址
	 */
	public function address() {
		$vipid = $_SESSION['HOME']['vipid'];
		if (IS_POST) {
			$data = I('post.');
			if ($data['id']) {
				//修改
				$re = M('vip_address') -> save($data);
			} else {
				//新增
				$data['vipid'] = $vipid;
				$data['ctime'] = time();
				$re = M('vip_address') -> add($data);
			}
			if ($re !== false) {
				$info['status'] = 1;
				$info['msg'] = '操作成功';
			} else {
				$info['status'] = 1;
				$info['msg'] = '操作失败';
			}
			$this -> ajaxReturn($info);
		} else {
			$data = M('vip_address') -> where('vipid=' . $vipid ) -> select();
			foreach ($data as $k => $v) {
				$p=M('location_province')->where('id='.$v['province'])->find();
				$data[$k]['provtext']=$p['name'];
			}
			$this -> assign('data', $data);
			$prov=M('location_province')->select();
			$this->assign('prov',$prov);
			$this -> display();
		}
	}

	/**
	 *提现操作
	 */
	public function tx() {
		$backurl = base64_encode('/home/vip/index');
		$this -> checkLogin($backurl);
		$vipid = $_SESSION['HOME']['vipid'];
		$m = M('vip');
		$vip = $m -> where('id=' . $vipid) -> find();
		$this -> assign('vip', $vip);
		if (IS_POST) {
			$mtx = M('vip_tx');
			$post = I('post.');
			if (!$post['txprice']) {
				$info['status'] = 0;
				$info['msg'] = '提现佣金不能为空！';
			}
			if ($post['txprice'] < $_SESSION['VIPSET']['tx_money']) {
				$info['status'] = 0;
				$info['msg'] = '提现佣金不得少于' . $_SESSION['VIPSET']['tx_money'] . '个！';
			}
			if ($post['txprice'] > $vip['money']) {
				$info['status'] = 0;
				$info['msg'] = '您的佣金不足！';
			}
			$vip['money'] = $vip['money'] - $post['txprice'];
			$rvip = $m -> save($vip);
			if (FALSE !== $rvip) {
				$post['vipid'] = $vipid;
				$post['txsqtime'] = time();
				$post['status'] = 1;
				$r = $mtx -> add($post);
				if ($r) {
					$data_msg['pids'] = $vipid;
					$data_msg['title'] = "您的" . $post['txprice'] . "佣金提现申请已成功提交！会在三个工作日内审核完毕并发放！";
					$data_msg['content'] = "提现订单编号：" . $r . "<br><br>提现申请数量：" . $post['txprice'] . "<br><br>提现申请时间：" . date('Y-m-d H:i', time()) . "<br><br>提现申请将在三个工作日内审核完成，如有问题，请联系客服！";
					$data_msg['ctime'] = time();
					$rmsg = M('vip_message') -> add($data_msg);
					$info['status'] = 1;
					$info['msg'] = '提现申请成功！';
				} else {
					$data_msg['pids'] = $vipid;
					$data_msg['title'] = "您的" . $post['txprice'] . "佣金提现申请已成功提交！会在三个工作日内审核完毕并发放！";
					$data_msg['content'] = "提现订单编号：" . $r . "<br><br>提现申请数量：" . $post['txprice'] . "<br><br>提现申请时间：" . date('Y-m-d H:i', time()) . "<br><br>佣金余额已扣除，但未成功生成提现订单，凭此信息联系客服补偿损失！";
					$data_msg['ctime'] = time();
					$rmsg = M('vip_message') -> add($data_msg);
					$info['status'] = 0;
					$info['msg'] = '佣金余额扣除成功，但未成功生成提现申请，请联系客服！';
				}
			} else {
				$info['status'] = 0;
				$info['msg'] = '提现申请失败！请重新尝试！';
			}
			$this -> ajaxReturn($info);
		} else {
			$data = M('vip') -> where('id=' . $vipid) -> find();
			$this -> assign('data', $data);
			$this -> display();
		}
	}

	//提现资料
	public function bankinfo() {
		$backurl = base64_encode('/home/vip/tx');
		$this -> checkLogin($backurl);
		$vipid = $_SESSION['HOME']['vipid'];
		if (IS_POST) {
			$m = M('vip');
			$post = I('post.');
			$r = $m -> where("id=" . $vipid) -> save($post);
			if ($r !== FALSE) {
				$info['status'] = 1;
				$info['msg'] = '保存成功!';
			} else {
				$info['status'] = 0;
				$info['msg'] = '操作失败,请重试~';
			}
			$this -> ajaxReturn($info);
		} else {
			$data = M('vip') -> where('id=' . $vipid) -> find();
			$this -> assign('data', $data);
			$this -> display();
		}
	}

	/**
	 */
	public function card() {
		$vipid = $_SESSION['HOME']['vipid'];
		$m = M('vip_card');
		$pageSize = 6;
		$pageNo = I('p') ? I('p') : 1;
		$map = '1=1 ';
		$map = 'vipid=' . $vipid;
		$list = $m -> where($map) -> order('ctime desc') -> page($pageNo . ',' . $pageSize) -> select();
		//		dump($list);
		$this -> assign('list', $list);
		$count = $m -> where($map) -> count();
		$Page = new \Think\Page($count, $pageSize);
		$show = $Page -> show();
		$this -> assign('page', $show);
		$this -> assign('p', $pageNo);
		$this -> assign('all', $count);
		$this -> display();

	}

	/**
	 */
	public function help() {
		$this -> display();
	}

	/**
	 */
	public function info() {
		$this -> display();
	}

	/**
	 */
	public function money() {
		$pageSize = 8;
		$pageNo = I('p') ? I('p') : 1;
		$vipid = $_SESSION['HOME']['vipid'];
		$vipinfo = M('vip') -> where('id=' . $vipid) -> find();
		$this -> assign('vipinfo', $vipinfo);
		$stime = strtotime(date('Y-m-d', strtotime("last month")));
		$etime = strtotime(date('Y-m-d', strtotime('+1 day')));
		$m = M('shop_syslog_sells');
		$map = 'ctime >=' . $stime . ' and ctime <' . $etime . ' and vipid=' . $vipid;
		$list = $m -> where($map) -> order('ctime desc') -> page($pageNo . ',' . $pageSize) -> select();
		$this -> assign('list', $list);
		$count = $m -> where($map) -> count();
		$Page = new \Think\Page($count, $pageSize);
		$show = $Page -> show();
		$this -> assign('page', $show);
		$this -> assign('p', $pageNo);
		$this -> assign('stime', $stime);
		$this -> assign('etime', $etime);
		$this -> display();
	}

	/**
	 */
	public function news() {

		$this -> display();
	}
	public function joinfree(){
		$this -> display();
	}
	/**
	 */
	public function order() {
		$pageSize = 5;
		$pageNo = I('p') ? I('p') : 1;
		$m = M('shop_order');
		$map = '1=1 ';
		$vipid = $_SESSION['HOME']['vipid'];
		$map = ' vipid=' . $vipid . ' and status<>0';
		if (I('s')) {
			$map = $map . ' and status=' . I('s');
		}
		if (I('search')) {
			$map = $map . " and oid='" . I('search') . "'";
		}
		$list = $m -> where($map) -> order('ctime desc') -> page($pageNo . ',' . $pageSize) -> select();
		if ($list) {
			foreach ($list as $k => $v) {
				if ($v['items']) {
					$list[$k]['items'] = unserialize($v['items']);
				}
			}
		}
		$this -> assign('list', $list);
		$count = $m -> where($map) -> count();
		$Page = new \Think\Page($count, $pageSize);
		$show = $Page -> show();
		$this -> assign('page', $show);
		$this -> assign('s', I('s'));
		$this -> assign('search', I('search'));
		$this -> assign('p', $pageNo);
		$all = $m -> where('status<>0 and vipid=' . $vipid) -> count();
		$this -> assign('all', $all ? $all : 0);

		$count = $m -> where('status=1 and vipid=' . $vipid) -> count();
		$this -> assign('s1', $count);
		$count = $m -> where('status=2 and vipid=' . $vipid) -> count();
		$this -> assign('s2', $count);
		$count = $m -> where('status=3 and vipid=' . $vipid) -> count();
		$this -> assign('s3', $count);
		$count = $m -> where('status=5 and vipid=' . $vipid) -> count();
		$this -> assign('s5', $count);
		$this -> display();
	}

	//确认收货
	public function orderOK() {
		$sid = I('sid') <> '' ? I('sid') : 0;
		//$this->diemsg(0, '缺少SID参数');//sid可以为0
		$orderid = I('orderid') <> '' ? I('orderid') : $this -> diemsg(0, '缺少ORDERID参数');
		$bkurl = U('index/vip/order', array('sid' => $sid, 'orderid' => $orderid));
		$backurl = base64_encode($bkurl);
		$loginurl = U('index/Vip/login', array('backurl' => $backurl));
		$re = $this -> checkLogin($backurl);
		//已登陆
		$m = M('Shop_order');
		$map['sid'] = $sid;
		$map['id'] = $orderid;
		$cache = $m -> where($map) -> find();
		if (!$cache) {
			$info['status'] = 0;
			$info['msg'] = '此订单不存在!';
		} else {
			if ($cache['status'] <> 3) {
				$info['status'] = 0;
				$info['msg'] = '只有待收货订单可以确认收货！';
			} else {
				$cache['etime'] = time();
				//交易完成时间
				$cache['status'] = 5;
				if (true == 1) {
					$this -> _fxLogic($cache['vipid'], $cache['payprice']);
					$mlog = M('Shop_order_log');
					$dlog['oid'] = $cache['id'];
					$dlog['msg'] = '确认收货,交易完成。';
					$dlog['ctime'] = time();
					$rlog = $mlog -> add($dlog);
					//后端日志
					$mlog = M('Shop_order_syslog');
					$dlog['oid'] = $cache['id'];
					$dlog['msg'] = '交易完成-会员点击';
					$dlog['type'] = 5;
					$dlog['paytype'] = $cache['paytype'];
					$dlog['ctime'] = time();
					$rlog = $mlog -> add($dlog);
					$cache['status'] = 5;
					if ($m -> save($cache)) {
						$info['status'] = 1;
						$info['msg'] = '交易已完成，感谢您的支持！';
					} else {
						$info['status'] = 0;
						$info['msg'] = '确认收货失败，请重新尝试！';
					}

				} else {
					//后端日志
					$mlog = M('Shop_order_syslog');
					$dlog['oid'] = $cache['id'];
					$dlog['msg'] = '确认收货失败';
					$dlog['type'] = -1;
					$dlog['paytype'] = $cache['paytype'];
					$dlog['ctime'] = time();
					$rlog = $mlog -> add($dlog);
					$info['status'] = 0;
					$info['msg'] = '确认收货失败，请重新尝试！';
				}
			}
		}
		$this -> ajaxReturn($info);
	}

	//订单退货
	public function orderTuihuo() {
		$sid = I('sid') <> '' ? I('sid') : 0;
		//$this->diemsg(0, '缺少SID参数');//sid可以为0
		$orderid = I('orderid') <> '' ? I('orderid') : $this -> diemsg(0, '缺少ORDERID参数');
		$bkurl = U('home/vip/order', array('sid' => $sid, 'orderid' => $orderid));
		$backurl = base64_encode($bkurl);
		$loginurl = U('home/Vip/login', array('backurl' => $backurl));
		$re = $this -> checkLogin($backurl);
		//已登陆
		$m = M('Shop_order');
		$vipid = $_SESSION['HOME']['vipid'];
		$vipinfo = $m -> where('id=' . $vipid) -> find();
		$map['sid'] = $sid;
		$map['id'] = $orderid;
		$cache = $m -> where($map) -> find();
		if (!$cache) {
			$this -> diemsg('此订单不存在!');
		}
		$cache['items'] = unserialize($cache['items']);

		$this -> assign('cache', $cache);
		//代金券调用
		if ($cache['djqid']) {
			$djq = M('Vip_card') -> where('id=' . $cache['djqid']) -> find();
			$this -> assign('djq', $djq);
		}
		$this -> display();
	}

	//订单取消
	public function orderTuihuoSave() {
		$sid = I('sid') <> '' ? I('sid') : 0;
		//$this->diemsg(0, '缺少SID参数');//sid可以为0
		$orderid = I('orderid') <> '' ? I('orderid') : $this -> diemsg(0, '缺少ORDERID参数');
		$bkurl = U('home/vip/orderTuihuo', array('sid' => $sid, 'orderid' => $orderid));
		$backurl = base64_encode($bkurl);
		$loginurl = U('home/Vip/login', array('backurl' => $backurl));
		$re = $this -> checkLogin($backurl);
		//已登陆
		$m = M('Shop_order');
		$map['sid'] = $sid;
		$map['id'] = $orderid;
		$cache = $m -> where($map) -> find();
		if (!$cache) {
			$info['msg'] = "此订单不存在!";
			$info['status'] = 0;
		} else {
			if ($cache['status'] <> 3) {
				$info['msg'] = "只有待收货订单可以办理退货！";
				$info['status'] = 0;
			} else {
				$data = I('post.');
				$cache['status'] = 4;
				$cache['tuihuoprice'] = $data['tuihuoprice'];
				$cache['tuihuokd'] = $data['tuihuokd'];
				$cache['tuihuokdnum'] = $data['tuihuokdnum'];
				$cache['tuihuomsg'] = $data['tuihuomsg'];
				//退货申请时间
				$cache['tuihuosqtime'] = time();
				$re = $m -> where($map) -> save($cache);
				if ($re) {
					//后端日志
					$mlog = M('Shop_order_log');
					$mslog = M('Shop_order_syslog');
					$dlog['oid'] = $cache['id'];
					$dlog['msg'] = '申请退货';
					$dlog['ctime'] = time();
					$rlog = $mlog -> add($dlog);
					$dlog['type'] = 4;
					$rslog = $mslog -> add($dlog);
					$info['msg'] = "申请退货成功！请等待工作人员审核！";
					$info['status'] = 1;
				} else {
					$info['msg'] = "申请退货失败,请重新尝试！";
					$info['status'] = 0;
				}
			}

		}

		$this -> ajaxReturn($info);
	}

	//本人的佣金结算   msq 2015-09-12
	protected function _fxLogic($id, $money) {
		//$id = 5;
		//$money = 10000;

		//会员自身的奖金
		$m = M('vip');
		$m2 = M('fx_level');
		$vipData = $m -> where('id=' . $id) -> find();
		//会员数据
		$fxLevelData = $m2 -> where('level=' . $vipData['fx_level']) -> find();
		//会员等级数据
		//佣金的计算
		if ($fxLevelData['month_amount'] != 0) {
			//判断当月，当月第一次消费没有佣金
			$month = strtotime(date('Y-m', time()) . '-01');
			$nextMonth = strtotime('+1 month', $month);
			if ($vipData['month'] >= $month && $vipData['month'] < $nextMonth) {
				//消费金融不满足条件
				if ($fxLevelData['month_amount'] <= $vipData['month_money']) {
					$commission = $money * $fxLevelData['ratio'];
					//佣金
				}
				//回写用户数据
				$vipData['month'] = time();
				$vipData['month_money'] = $vipData['month_money'] + $money;
			} else {
				//回写用户数据
				//$vipData['month'] = time();
				$vipData['month_money'] = $money;
			}
		} else {
			$commission = $money * $fxLevelData['ratio'];
			//佣金
			//回写用户数据
			$vipData['month'] = time();
			$vipData['month_money'] = $money;
		}
		//保存会员购物后的月份和当月累计金额
		unset($vipData['month']);
		unset($vipData['month_money']);
		$m -> save($vipData);

		//有佣金
		if ($commission) {
			//写入用户
			$this -> _getTc($id, $commission, '佣金');
			//计算上级提佣
			$arr = explode('-', $vipData['path']);
			//上级id数组
			$count = count($arr);
			//上级层数
			//普通佣金计算 上1层
			$upOneId = array_slice($arr, -1, 1);

			if ($upOneId) {
				$this -> _getTc($upOneId[0], $money * 0.1, '佣金');
			}
			//普通佣金计算 上2层
			$upTwoId = array_slice($arr, -2, 1);
			if ($upTwoId) {
				$this -> _getTc($upTwoId[0], $money * 0.05, '佣金');
			}
			//取20层内外国人
			for ($i = 0; $i <= 20; $i++) {
				if ($i < $count) {
					$endId = array_pop($arr);
					$iswg = $m -> where('id=' . $endId) -> getField('iswg');
					if ($iswg == 1) {
						$newArr[] = $endId;
					}
				}
			}

			//上级的等级
			$wgUpLevel = 0;
			//上级的佣金
			$wgUpMoney = 0;
			foreach ($newArr as $k => $val) {
				$wgVipData = $m -> where('id=' . $val) -> find();
				//外国人会员数据
				$wgFxLevelData = $m2 -> where('level=' . $wgVipData['fx_level']) -> find();
				//外国人等级数据

				//计算外国人和购买人的等级差
				if ($wgFxLevelData['level'] > $wgUpLevel) {
					if ($wgFxLevelData['level'] > $fxLevelData['level']) {
						$wgCommission = ($wgFxLevelData['ratio'] - $fxLevelData['ratio']) * $money - $wgUpMoney;
						//写入会员数据
						if ($wgCommission) {
							$this -> _getTc($val, $wgCommission, '极差奖');
						}
						//写入缓存数据
						$wgUpLevel = $wgFxLevelData['level'];
						$wgUpMoney = $wgUpMoney + $wgCommission;
					}
				} else {
					break;
				}
			}
		}
	}

	/**
	 */
	public function score() {
		$this -> display();
	}

	/**
	 */
	public function team() {
		$vipid = $_SESSION['HOME']['vipid'];
		$m = M('vip');
		$data = $m -> where('id=' . $vipid) -> find();
		$fxlv = M('Fx_level') -> where('level=' . $data['fx_level']) -> find();
		$this -> assign('fxlv', $fxlv);
		//第一层
		$datafirst=$m->where("pid = ".$vipid)->order('ctime desc')->limit(50)->select();
		$countfirst=$m->where("pid = ".$vipid)->count();
		$this->assign('datafirst',$datafirst);
		$this->assign('countfirst',$countfirst);
		//第二层
		$datasecond=$m->where("path LIKE '%-".$vipid."-%' and plv=".($data['plv']+2))->order('ctime desc')->limit(50)->select();
		$countsecond=$m->where("path LIKE '%-".$vipid."-%' and plv=".($data['plv']+2))->count();
		$this->assign('datasecond',$datasecond);
		$this->assign('countsecond',$countsecond);
		//下线总数
		$this->assign('count',$countfirst+$countsecond);
		//已关注下线
		$countsub=$m->where("(path LIKE '%-".$vipid."-%' and plv=".($data['plv']+2).") or (pid = ".$vipid.") and subscribe=1")->count();
		$this->assign('countsub',$countsub);
		//已购买下线
		$d=$m->where("(path LIKE '%-".$vipid."-%' and plv=".($data['plv']+2).") or (pid = ".$vipid.")")->select();
		$ids='0';
		foreach ($d as $k => $v) {
			$ids=$ids.",".$v['id'];
		}
		$buy=M('shop_order')->where("vipid in (".$ids.")")->group('vipid')->select();
		$this->assign('countbuy',count($buy));
		//历史总佣金
		$mapfirst['pid']=$data['id'];
		$firstsub=$m->field('id')->where($mapfirst)->select();
		$subarr="";
		foreach($firstsub as $k=>$v){
			$subarr=$subarr.$v['id'].',';
		}
		$subarr=array_filter(explode(',', $subarr));
		$shopset=M('Shop_set')->find();
		$vipfx1rate=$shopset['vipfx1rate'];
		if(count($subarr) && $vipfx1rate){
			$maporder['ispay']=1;
			$maporder['status']=array('in','2,3');
			$maporder['vipid']=array('in',$subarr);
			$total=M('Shop_order')->where($maporder)->sum('payprice');
			$total=$total?$total:0;
			$fx1total=$total*($vipfx1rate/100);
			$data['fxmoney']=number_format($fx1total,2);
		}else{
			$data['fxmoney']=0.00;
		}
		$maptx['vipid']=$data['id'];
		$maptx['status']=1;
		$txtotal=M('Vip_tx')->where($maptx)->sum('txprice');
		if($txtotal>0){
			$data['txmoney']=number_format($txtotal,2);
		}else{
			$data['txmoney']=number_format(0,2);
		}
		$this -> assign('data', $data);
		$this -> display();
	}

	public function yj() {
		$pageSize = 8;
		$pageNo = I('p') ? I('p') : 1;
		$vipid = $_SESSION['HOME']['vipid'];
		$vipinfo = M('vip') -> where('id=' . $vipid) -> find();
		$this -> assign('vipinfo', $vipinfo);
		$stime = strtotime(date('Y-m-d', strtotime("last month")));
		$etime = strtotime(date('Y-m-d', strtotime('+1 day')));
		$m = M('fx_dslog');
		$map = 'ctime >=' . $stime . ' and ctime <' . $etime  . ' and `to`=' . $vipid;
		$list = $m -> where($map) -> order('ctime desc') -> page($pageNo . ',' . $pageSize) -> select();
		$this -> assign('list', $list);
		$count = $m -> where($map) -> count();
		$Page = new \Think\Page($count, $pageSize);
		$show = $Page -> show();
		$this -> assign('page', $show);
		$this -> assign('p', $pageNo);
		$this -> assign('stime', $stime);
		$this -> assign('etime', $etime);
		$this -> display();
	}

	public function problem() {
		$this -> display();
	}

	public function remotewclogin() {
		$cache = M('Wclogin') -> where(array('ssid' => session_id())) -> find();
		$_SESSION['HOME']['vipid'] = $cache['vipid'];
		if ($cache['vipid']) {
			$info['status'] = 1;
			//$info['msg']="登录成功！";
		} else {
			$info['status'] = 0;
			//$info['msg']="登录成功！";
		}
		$this -> ajaxReturn($info);
	}

	/**
	 * 关于我们
	 */
	public function aboutus() {
		$this -> display();
	}

	/**
	 * 关于我们
	 */
	public function contact() {
		$this -> display();
	}

	/**
	 * 商务合作
	 */
	public function business() {
		$this -> display();
	}

	/**
	 * 物流
	 */
	public function logistics() {
		$this -> display();
	}

	/**
	 * 协议
	 */
	public function agreement() {
		$this -> display();
	}

	/**
	 * 未登录版
	 */
	public function agreement1() {
		$this -> display();
	}

	/**
	 * 支付方式
	 */
	public function payway() {
		$this -> display();
	}
	/**
	 * 发票说明
	 */
	public function invoice() {
		$this -> display();
	}

	//二维码
	public function getmyqrcode() {
		$set = M('Set') -> find();
		$vipid = $_SESSION['HOME']['vipid'];
		$url = $set['wxurl'] . '/Wap/Shop/index/ppid/' . $vipid . '/';
		$set = M('Set') -> find();
		$QR = new \Joel\QRcode();
		$QR::png($url, FALSE, 'L', 9);
	}
	public function getczqrcode(){
		$vipid = $_SESSION['HOME']['vipid'];
		$url = "http://".$_SERVER['HTTP_HOST'] . '/wap/vip/cz';
		$set = M('Set') -> find();
		$QR = new \Joel\QRcode();
		$QR::png($url, FALSE, 'L', 9);
	
	}
	//APP二维码
	public function getmyappcode() {
		$set = M('Set') -> find();
		$vipid = $_SESSION['HOME']['vipid'];
		$url = $set['wxurl'] . '/Home/Download/index/ppid/' . $vipid . '/';
		$uuid = session_id();
		$set = M('Set') -> find();
		//dump($url);
		$QR = new \Joel\QRcode();
		//$QR::png($url);
		$QR::png($url, FALSE, 'L', 9);
	}

	//上级佣金的计算 msq
	private function _getTc($id, $money, $event) {
		$m = M('vip');
		$vipData = $m -> where('id=' . $id) -> find();
		$vipData['money'] = $vipData['money'] + $money;
		$re = $m -> save($vipData);
		//写入日志
		if ($re || $id == 0) {
			$this -> _vipLogMoney($id, $event, '1', $money);
		} else {
			$this -> ajaxReturn(array('status' => 0, 'msg' => '写入会员' . $id . '资金出错！'));
		}
	}

	//money日志 msq
	private function _vipLogMoney($id, $event, $active, $money) {
		$m = M('vip_log_money');
		$log['vipid'] = $id;
		$log['event'] = $event;
		$log['active'] = $active;
		$log['money'] = $money;
		$log['ctime'] = time();
		if ($id == 0) {
			$re = $this -> _toWxInfo($id, $money, $active);
			return true;
		} else {
			if ($m -> add($log)) {
				//发送微信模板消息
				$re = $this -> _toWxInfo($id, $money, $active);
				return true;

			} else {
				$this -> ajaxReturn(array('status' => 0, 'msg' => '写入会员' . $id . '资金日志出错！'));
			}
		}

	}

	//微信模板消息 msg
	private function _toWxInfo($id, $money, $active) {
		if ($id == 0) {
			return true;
		}
		$name = 'yuetx';
		//余额变动提醒
		$m = M('vip');
		$vip = $m -> where('id=' . $id) -> find();
		$data['name'] = $vip['name'];
		$data['money'] = ($active == 1) ? '+' . $money : '-' . $money;
		$data['total'] = $vip['money'];

		$openid = $vip['openid'];
		//发给的人
		$tp = new \bb\template();
		$templatedata = $tp -> enddata($name, $openid, $data);
		//组合模板数据
		$options['appid'] = self::$_wxappid;
		$options['appsecret'] = self::$_wxappsecret;
		$wx = new \Joel\wx\Wechat($options);
		$wx -> sendTemplateMessage($templatedata);
		//发送模板
		return TRUE;
	}

}
