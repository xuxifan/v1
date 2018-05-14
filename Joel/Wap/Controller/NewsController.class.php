<?php
// 本类由系统自动生成，仅供测试用途
namespace Wap\Controller;
use Wap\Controller\BaseNewsController;
class NewsController extends BaseNewsController {
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	
	//1.25 zxg 添加新闻列表页面-->
	//新闻列表
	public function newslist(){
		//已登陆
		$m=M('news');
		$mf=M('News_form');
		$istg=I('istg');
		$limit=10;
		$page=I('page')?I('page'):1;
		$map['type']='wap';
		$map['istg']=$istg;
		$cache=$m->where(array('type'=>'wap','istg'=>I('istg')))->page($page,$limit)->order('ctime desc')->select();
		foreach($cache as $k=>$v){
			$ta=$this->getPic($cache[$k]['pic']);
			$cache[$k]['imgurl']=$ta['imgurl'];
			if($istg==1){
				// 计算该活动的标明人数
				$cache[$k]['formnum']=$mf->where("newsid=".$v['id'])->count();
			}
		}

		//分页
		$totalcount=$m->where(array('type'=>'wap'))->count();
		$totalpage=ceil($totalcount/$limit);
		$this->assign('totalpage',$totalpage);
		$this->assign('page',$page);
		$this->assign('cache',$cache);
		$this->assign('istg',$istg);
		$this->display('vip_newslist');
	}
	//新闻详情
	public function newsdetail(){
		//追入分享特效
		self::$SET=$_SESSION['SET']=$this->checkSet($uid);
		self::$_wxappid=self::$SET['wxappid'];
		self::$_wxappsecret=self::$SET['wxappsecret'];
		$options['appid']= self::$_wxappid;
		$options['appsecret']= self::$_wxappsecret;
		$wx = new \Joel\wx\Wechat($options);
		//生成JSSDK实例
		$opt['appid']= self::$_wxappid;
		$opt['token']=$wx->checkAuth();
		$opt['url']="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$jssdk=new \Joel\wx\Jssdk($opt);
		$jsapi=$jssdk->getSignPackage();
		if(!$jsapi){
			die('未正常获取数据！');
		}
		$this->assign('jsapi',$jsapi);
		//已登陆
		$m=M('news');
		$logmap['newsid']=$id=$_GET['id'];
		$m->query("UPDATE `joel_news` SET `read`=`read`+1 where( `id` = ".$id.")");
		$cache=$m->where(array('id'=>$id))->find();
		$cache['formnum']=M('News_form')->where("newsid=".$id)->count();
		$ta=$this->getPic($cache['pic']);
		$imgurl=$ta['imgurl'];
		if($cache['istg']) {
			$vn=M("Vip_news");
			$l=M("News_log");
			// 判断该会员是否为员工
			$map['openid']=$_SESSION['WAP']['vip']['openid'];
			$re=$vn->where($map)->find();
			$vipnewsid=I('vipnewsid');
			if($re) {
				// 如果该会员为员工
				$vipnewsid=$logmap['pid']=$re['id'];
				// 遍历信息
			} else {
				// 如果该会员不是员工
				// 不是通过分享途径进入的则拦截
				if(empty($vipnewsid)) {
					$this->redirect(U('wap/vip/index'));
				}
				// vipnewsid
				$logmap['pid']=$vipnewsid;
				// 判断该用户是否已经阅读过次文章
				$logmap['openid']=$_SESSION['WAP']['vip']['openid'];
				$ree=$l->where($logmap)->find();
				if($ree) {
					// 如果已经阅读过
					unset($logmap['openid']);
				} else {
					// 如果没有阅读过
					// 阅读量增加
					$a=$vn->where("id=".$vipnewsid)->find();
					$a['sumnum']++;
					$vn->save($a);
					// 录入阅读日志
					$data['newsid']=$id;
					$data['pid']=$vipnewsid;
					$data['headimg']=$_SESSION['WAP']['vip']['headimgurl'];
					$data['nickname']=$_SESSION['WAP']['vip']['nickname'];
					$data['openid']=$_SESSION['WAP']['vip']['openid'];
					$data['ctime']=time();
					$l->add($data);
				}
				$re=$vn->where("id=".$vipnewsid)->find();
			}
			$log=$l->where($logmap)->order('ctime desc')->select();
			$this->assign('newslog',$log);
			$this->assign('vipnewsid',$vipnewsid);
			$this->assign('staff',$re);
		}
		$this->assign('imgurl',$imgurl);
		$this->assign('cache',$cache);
		$this->display('vip_newsdetail');
	}
	//1.25 zxg 添加新闻列表页面-->

	public function newsform(){
		if (IS_AJAX) {
			$data=I("post.");
			$data['ctime']=time();
			$re=M("News_form")->add($data);
			if ($re) {
				$info['status']=1;
				$info['info']="添加信息成功";
			} else {
				$info['status']=0;
				$info="添加信息失败";
			}
			$this->ajaxReturn($info);
		}
	}
}
