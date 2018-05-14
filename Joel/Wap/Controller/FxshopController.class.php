<?php
// 本类由系统自动生成，仅供测试用途
namespace Wap\Controller;
use Wap\Controller\BaseController;
class FxshopController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();		
		$shopset=M('Shop_set')->where('id=1')->find();
		if($shopset['pic']){
			$listpic=$this->getPic($shopset['pic']);
			$shopset['sharepic']=$listpic['imgurl'];
		}
		if($shopset){
			self::$WAP['shopset']=$_SESSION['WAP']['shopset']=$shopset;
		}else{
			$this->diemsg(0,'您还没有进行商城配置！');
		}
		if(empty($_SESSION['WAP']['sid'])){
			session(null);
			//$this->diemsg(0, '缺少SID参数，请尝试重新访问！');
		}
		//模板id
		$_SESSION['shop']['tplid']=$shopset['tplid'];		
		//初始化分类
		//JoelTree快速无限分类
		$field=array("id","pid","name","sorts","concat(path,'-',id) as bpath");
		$indexcate=joelTree(M('Shop_cate'), 0, $field);
		$this->assign('indexcate',$indexcate);
		// 作者：郑伊凡 2016-1-26 母版本 功能：用于前台判断是否开启身份验证
		$this->assign("ischeckid",$shopset['ischeckid']);
		// 作者：郑伊凡 2016-1-26 母版本 功能：用于前台判断是否开启身份验证
		
		//追入分享特效
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
	}

	// 作者：郑伊凡 2016-2-15 母版本 功能：首页自定义模版
	public function labellist(){

		$mlabel=M("shop_label");
		$m=M("Shop_goods");
		$f=M("vip_favorite");
		$label=$mlabel->select();
		foreach ($label as $k=>$v){
			$labelarr=$this->getPic($v['sppic']);
			$label[$k]['imgurl']=$labelarr['imgurl'];
			$map['lid']=$v['id'].',';
			$map['isgroup']=0;
			$map['iscut']=0;
			$map['status']=1;
			$label[$k]['goodsnum']=$m->where($map)->count();
			$re=$m->where($map)->select();
			$label[$k]['likenum']=0;
			foreach($re as $kk=>$vv){
				$label[$k]['likenum']+=$f->where("goodsid=".$vv['id'])->count();
			}
		}
		$this->assign('label',$label);
		$this->display('tpl1');
	}

	public function labelinfo()
	{
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		$m=M('Shop_goods');
		$mlabel=M('Shop_label');
		
		//追入分组ID
		$id=I('id')?I('id'):$this->error('缺少标签ID');		
		
		$label=$mlabel->where('id='.$id)->find();
		if($label){
			$labelarr=$this->getPic($label['sppic']);
			$label['imgurl']=$labelarr['imgurl'];
			$this->assign('label',$label);
		}else{
			$this->error('不存在此分类！');
		}
		$shopset=M('Shop_set')->find();
		$pagesize=$shopset['pagesize']?$shopset['pagesize']:5;
		$goodslist=$m->where('lid='.$id.' and status=1 and isgroup=0 and iscut=0')->order('sorts desc')->select();
		foreach($goodslist as $k=>$v){
			$ta=$this->getPic($goodslist[$k]['pic']);
			$goodslist[$k]['imgurl']=$ta['imgurl'];
		}
		$this->assign('catecache',$goodslist);
		$this->display();
	}
// 作者：郑伊凡 2016-2-15 母版本 功能：首页自定义模版

	public function index(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		//首页轮播图集
		$indexalbum=M('Fxs_ads')->where('sid='.$sid)->select();
		if(empty($indexalbum)){
			$indexalbum=M('Shop_ads')->select();
		}

		foreach($indexalbum as $k=>$v){
			$listpic=$this->getPic($v['pic']);
			$indexalbum[$k]['imgurl']=$listpic['imgurl'];
		}
		$this->assign('indexalbum',$indexalbum);
		
		$m=M('Shop_goods');
		$mlabel=M("shop_label");
		$f=M("vip_favorite");
		$indexcache=$mlabel->select();		
		switch ($_SESSION['shop']['tplid']) {
			case '1':
				$pageSize=self::$WAP['shopset']['pagesize']?self::$WAP['shopset']['pagesize']:8;
				$pageCount=1;
				foreach($indexcache as $k=>$v){
					$lpic=$this->getPic($v['lpic']);
					$indexcache[$k]['limgurl']=$lpic['imgurl'];			
					$listpic=$this->getPic($v['pic']);
					$indexcache[$k]['imgurl']=$listpic['imgurl'];
					$wap['lid']=$v['id'];
					$wap['status']='1';
					$wap['iscut']='0';
					$wap['isgroup']='0';
					$wap['isteg']="0";
					$list=$m->where($wap)->limit(($pageCount-1)*$pageSize,$pageSize)->order('sorts desc')->select();
					if($list){
						foreach($list as $kk=>$vv){
							$tlist=$this->getPic($vv['pic']);
							$list[$kk]['imgurl']=$tlist['imgurl'];
						}
					}
					$indexcache[$k]['goods']=$list;
					$indexcache[$k]['count']=$m->where($wap)->limit(($pageCount)*$pageSize+1,$pageSize)->count();
				}
				$this->assign('actname','fthome');
				$this->assign('indexcache',$indexcache);
				$this->display('index/'.$_SESSION['shop']['tplid']);
				break;
			case '2':
				foreach ($indexcache as $k=>$v){
					$labelarr=$this->getPic($v['sppic']);
					$indexcache[$k]['imgurl']=$labelarr['imgurl'];
					$map['lid']=$v['id'].',';
					$map['isgroup']=0;
					$map['iscut']=0;
					$map['status']=1;
					$indexcache[$k]['goodsnum']=$m->where($map)->count();
					$re=$m->where($map)->select();
					$indexcache[$k]['likenum']=0;
					foreach($re as $kk=>$vv){
						$indexcache[$k]['likenum']+=$f->where("goodsid=".$vv['id'])->count();
					}
				}
				$this->assign('actname','fthome');				
				$this->assign('label',$indexcache);
				$this->display('index/'.$_SESSION['shop']['tplid']);
				break;
			case '3':
				$pageSize=self::$WAP['shopset']['pagesize']?self::$WAP['shopset']['pagesize']:8;
				$pageCount=1;		
				foreach($indexcache as $k=>$v){
					$lpic=$this->getPic($v['lpic']);
					$indexcache[$k]['limgurl']=$lpic['imgurl'];			
					$listpic=$this->getPic($v['pic']);
					$indexcache[$k]['imgurl']=$listpic['imgurl'];
					
					$wap['lid']=$v['id'];
					$wap['status']='1';
					$wap['iscut']='0';
					$wap['isgroup']='0';
					$wap['isteg']="0";
					$list=$m->where($wap)->limit(($pageCount-1)*$pageSize,$pageSize)->order('sorts desc')->select();
					if($list){
						foreach($list as $kk=>$vv){
							$tlist=$this->getPic($vv['pic']);
							$list[$kk]['imgurl']=$tlist['imgurl'];
						}
					}
					$indexcache[$k]['goods']=$list;
					$indexcache[$k]['count']=$m->where($wap)->limit(($pageCount-1)*$pageSize,$pageSize)->count();
				}
				
				//底部导航
				$this->assign('actname','fthome');	
				$this->assign('indexcache',$indexcache);
				$this->display('index/'.$_SESSION['shop']['tplid']);
			break;
			default:
				$this->diemsg(0,'请选择模板');
				break;
		}
    }
	 //首页获取更多
	/* public function indexMore(){
			if(IS_AJAX){						
				$page=I('key');
				
				if(!$page){
					$info['status']=0;
					$info['msg']='请输入你要找的产品名称！';
					$this->ajaxReturn($info);
				}
				$m=M('Shop_goods');
				
				$map = array('like','%'.$page.'%');
				$goodslist=$m->where(array('name'=>$map))->order('id desc')->select();
				if($goodslist){
					foreach($goodslist as $k=>$v){
						$ta=$this->getPic($goodslist[$k]['pic']);
						$goodslist[$k]['imgurl']=$ta['imgurl'];
					}
					$info['status']=1;
					$info['msg']='加载成功！';
					$this->assign('new',$goodslist);
					$info['result']=$this->fetch();
				}else{
					$info['status']=0;
					$info['msg']='没有找到产品！';
				}
				$this->ajaxReturn($info);
			}else{
				$info['status']=0;
				$info['msg']='非法访问！';
				$this->ajaxReturn($info);
			}
			
	}*/
	
	public function search(){		
			//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		$m=M('Shop_goods');
		$search=I('search')?I('search'):$this->error('请输入产品名称查询！');
		$map['name']=array('like',"%".$search."%");
		$map['status']=1;
		$map['iscut']=0;
		$map['isgroup']=0;
		$goodslist=$m->where($map)->select();
		foreach($goodslist as $k=>$v){
			$ta=$this->getPic($goodslist[$k]['pic']);
			$goodslist[$k]['imgurl']=$ta['imgurl'];
		}
		$this->assign('catecache',$goodslist);
		$this->assign('search',$search);
		$this->display();
    }

	//产品分组
	public function group(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		$m=M('Shop_goods');
		$mgroup=M('Shop_group');
		
		//追入分组ID
		$id=I('id')?I('id'):$this->error('缺少分组ID');
		
		
		$tmplist=array();//临时数组
		$tmpgroup=$mgroup->where('id='.$id)->find();
		if($tmpgroup){
				$ta=$this->getPic($tmpgroup['pic']);
				$tmplist['img']=$ta['imgurl'];
				$tmpmap['id']=array('in',$tmpgroup['goods']);
				$tmplist['goods']=$m->where($tmpmap)->select();
				if($tmplist['goods']){
					foreach($tmplist['goods'] as $kk=>$vv){
						$ta=$this->getPic($vv['listpic']);
						$tmplist['goods'][$kk]['imgurl']=$ta['imgurl'];
					}
				}
		}
		//dump($tmplist);
		$this->assign('groupcache',$tmplist);
		
		$this->display();
    }
	
	//产品分类
	public function cate(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		$m=M('Shop_goods');
		$mcate=M('Shop_cate');		
		//追入分组ID
		$id=I('id')?I('id'):$this->error('缺少分类ID');		
		
		$cate=$mcate->where('id='.$id)->find();
		if($cate){
			$this->assign('cate',$cate);
		}else{
			$this->error('不存在此分类！');
		}
		$shopset=M('Shop_set')->find();
		$pagesize=$shopset['pagesize']?$shopset['pagesize']:6;
		$wap['cid']=$id;
		$wap['status']='1';
		$wap['iscut']='0';
		$wap['isgroup']='0';
		
		$goodslist=$m->where($wap)->order('id desc')->select();/*->page(1,$pagesize)*/
		$next=$m->where($wap)->page(2,$pagesize)->order('id desc')->select();
		if($next){
			$this->assign('nextpage',2);
		}		
		foreach($goodslist as $k=>$v){
			$ta=$this->getPic($goodslist[$k]['pic']);
			$goodslist[$k]['imgurl']=$ta['imgurl'];
		}
		
		//dump($tmplist);
		$this->assign('catecache',$goodslist);
		
		$this->display();
    }
	
	public function cateMore(){
		if(IS_AJAX){						
			$cid=I('cid');
			$page=I('page');
			if(!$cid){
				$info['status']=0;
				$info['msg']='未获取分类参数！';
				$this->ajaxReturn($info);
			}
			if(!$page){
				$info['status']=0;
				$info['msg']='未获取分页参数！';
				$this->ajaxReturn($info);
			}
			$shopset=M('Shop_set')->find();
			$pagesize=$shopset['pagesize']?$shopset['pagesize']:5;
			$m=M('Shop_goods');
			$goodslist=$m->where('cid='.$cid)->page($page,$pagesize)->order('id desc')->select();
			if($goodslist){
				foreach($goodslist as $k=>$v){
					$ta=$this->getPic($goodslist[$k]['pic']);
					$goodslist[$k]['imgurl']=$ta['imgurl'];
				}
				$info['status']=1;
				$info['msg']='加载成功！';
				$this->assign('catecache',$goodslist);
				$info['result']=$this->fetch();
			}else{
				$info['status']=0;
				$info['msg']='没有更多产品了！';
			}
			$this->ajaxReturn($info);
		}else{
			$info['status']=0;
			$info['msg']='非法访问！';
			$this->ajaxReturn($info);
		}
    }
	
	public function goods(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		$id=I('id')?I('id'):$this->diemsg(0,'缺少ID参数!');	
		//追入分享特效
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
		$m=M('Shop_goods');
		$cache=$m->where('id='.$id)->find();
		//读取标签
		foreach (explode(',',$cache['lid']) as $k=>$v) {
			$label[$k]=M('shop_label')->where('id='.$v)->getField('name');
		}
		$cache['label']=$label;
		if(!$cache){
			$this->diemsg(0,'不存在此商品！');
		}
		// 作者：郑伊凡 2016-1-20 母版本 功能：修复能购买下架产品的bug
		if(!$cache['status']){
			$this->diemsg(0,'此商品已下架！');
		}
		// 作者：郑伊凡 2016-1-20 母版本 功能：修复能购买下架产品的bug
		$this->assign('cache',$cache);
		if($cache['issku']){
			if($cache['skuinfo']){
				$skuinfo=unserialize($cache['skuinfo']);
				$skm=M('Shop_skuattr_item');
				foreach($skuinfo as $k=>$v){
					$checked=explode(',', $v['checked']);
					$attr=$skm->field('path,name')->where('pid='.$v['attrid'])->select();
					foreach($attr as $kk=>$vv){
						$attr[$kk]['checked']=in_array($vv['path'], $checked)?1:'';
					}
					$skuinfo[$k]['allitems']=$attr;
				}
				$this->assign('skuinfo',$skuinfo);				
			}else{
				$this->diemsg(0,'此商品还没有设置SKU属性！');
			}		
			$skuitems=M('Shop_goods_sku')->field('sku,skuattr,price,num,hdprice,hdnum')->where(array('goodsid'=>$id,'status'=>1))->select();
			if(!$skuitems){
				$this->diemsg(0,'此商品还未生成SKU!');
			}
			$skujson=array();
			foreach($skuitems as $k=>$v){
				$skujson[$v['sku']]['sku']=$v['sku'];
				$skujson[$v['sku']]['skuattr']=$v['skuattr'];
				$skujson[$v['sku']]['price']=$v['price'];
				$skujson[$v['sku']]['num']=$v['num'];
				$skujson[$v['sku']]['hdprice']=$v['hdprice'];
				$skujson[$v['sku']]['hdnum']=$v['hdnum'];
			}
			$this->assign('skujson',json_encode($skujson));
		}

		//绑定图集
		if($cache['album']){
			$joelalbum=$this->getAlbum($cache['album']);
			if($joelalbum){
				$this->assign('joelalbum',$joelalbum);
			}
		}
		//绑定图片
		if($cache['pic']){
			$joelpic=$this->getPic($cache['pic']);
			if($joelpic){
				$this->assign('joelpic',$joelpic);
			}
		}
		
		//绑定购物车数量
		$basketnum=M('Shop_basket')->where(array('sid'=>$sid,'vipid'=>self::$WAP['vipid']))->sum('num');
		$this->assign('basketnum',$basketnum);
		//绑定登陆跳转地址
		/*$backurl=base64_encode(U('Wap/fxshop/goods',array('id'=>$id)));
		$loginback=U('Wap/Vip/login',array('backurl'=>$backurl));
		$this->assign('loginback',$loginback);
		$this->assign('lasturl',$backurl);*/
		//验证是否收藏
		$this->assign('checkfav',$this->_checkfav($id));
		$this->display();
	}
	
	public function basket(){
		
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		/*重置链接
		$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$lasturl=I('lasturl')?I('lasturl'):$this->diemsg(0, '缺少LastURL参数');
		$basketlasturl=base64_decode($lasturl);
		$basketurl=U('Wap/Shop/basket',array('lasturl'=>$lasturl));		
		$backurl=base64_encode($basketurl);
		$basketloginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		$re=$this->checkLogin($backurl);
		//保存当前购物车地址
		$this->assign('basketurl',$basketurl);
		//保存登陆购物车地址
		$this->assign('basketloginurl',$basketloginurl);
		//保存购物车前地址
		$this->assign('basketlasturl',$basketlasturl);
		//保存购物车加密地址，用于OrderMaker正常返回
		$this->assign('lasturlencode',$lasturl);
		 */
		//已登陆
		$m=M('Shop_basket');
		$mgoods=M('Shop_goods');
		$msku=M('Shop_goods_sku');
		//$returnurl=base64_decode($lasturl);
		//$this->assign('returnurl',$returnurl);
		$cache=$m->where(array('sid'=>$sid,'vipid'=>$_SESSION['WAP']['vipid']))->select();
		//错误标记
		$errflag=0;
		//等待删除ID
		$todelids='';
		//totalprice
		$totalprice=0;
		//totalnum
		$totalnum=0;
		foreach($cache as $k=>$v){
			//sku模型
			$goods=$mgoods->where('id='.$v['goodsid'])->find();	
			$flag =$goods['lid'].','.$flag;		
			$pic=$this->getPic($goods['pic']);
			if($v['sku']){
				//取商品数据				
				if($goods['issku'] && $goods['status']){
					$map['sku']=$v['sku'];
					$sku=$msku->where($map)->find();
					if($sku['status']){
						if($sku['num']){
							//调整购买量
							$cache[$k]['name']=$goods['name'];
							$cache[$k]['skuattr']=$sku['skuattr'];
							$cache[$k]['num']=$v['num']>$sku['num']?$sku['num']:$v['num'];
							$cache[$k]['price']=$sku['price'];
							$cache[$k]['total']=$sku['num'];
							$cache[$k]['pic']=$pic['imgurl'];
							$lidarr =explode(',',$cache[$k]['lid']);
							$lid =substr($goods['lid'],0,strlen($goods['lid'])-1);
							$cache[$k]['labelname']=M('shop_label')->where('id='.$lid)->getField('name');	
							$totalnum=$totalnum+$cache[$k]['num'];
							$totalprice=$totalprice+$cache[$k]['price']*$cache[$k]['num'];
						}else{
							//无库存删除
							$todelids=$todelids.$v['id'].',';
							unset($cache[$k]);
							
						}
					}else{
						//下架删除
						$todelids=$todelids.$v['id'].',';
						unset($cache[$k]);
					}
				}else{
					//下架删除
					$todelids=$todelids.$v['id'].',';
					unset($cache[$k]);
				}
				
			}else{
				if($goods['status']){
					if($goods['num']){
						//调整购买量
						$cache[$k]['name']=$goods['name'];
						$cache[$k]['skuattr']=$sku['skuattr'];
						$cache[$k]['num']=$v['num']>$goods['num']?$goods['num']:$v['num'];
						$cache[$k]['price']=$goods['price'];
						$cache[$k]['total']=$goods['num'];
						$cache[$k]['pic']=$pic['imgurl'];
						//$lidarr =explode(',',$cache[$k]['lid']);
						$lid =substr($goods['lid'],0,strlen($goods['lid'])-1);
						$cache[$k]['labelname']=M('shop_label')->where('id='.$lid)->getField('name');
						$totalnum=$totalnum+$cache[$k]['num'];
						$totalprice=$totalprice+$cache[$k]['price']*$cache[$k]['num'];
					}else{
						//无库存删除
						$todelids=$todelids.$v['id'].',';
						unset($cache[$k]);						
					}
				}else{
					//下架删除
					$todelids=$todelids.$v['id'].',';
					unset($cache[$k]);
				}
			}
		}
		if($todelids){
			$rdel=$m->delete($todelids);
			if(!$rdel){
				$this->error('购物车获取失败，请重新尝试！');
			}	
		}
		
		//优惠标记
		$flagarr =explode(',',$flag);
		if(in_array('3',$flagarr) && (in_array('1',$flagarr) || in_array('2',$flagarr))){
			$flags =1;
		}else{
			$flags =2;
		}
		
		$this->assign('flag',$flags);
		
		$this->assign('cache',$cache);
		$this->assign('totalprice',$totalprice);
		$this->assign('totalnum',$totalnum);
		$this->display();
	}

	//添加购物车
	public function addtobasket(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		if(IS_AJAX){
			$m=M('Shop_basket');
			$data=I('post.');
			if(!$data){
				$info['status']=0;
				$info['msg']='未获取数据，请重新尝试';
				$this->ajaxReturn($info);
			}
			//区分SKU模式
			if($data['sku']){
				$old=$m->where(array('sid'=>$data['sid'],'vipid'=>$data['vipid'],'sku'=>$data['sku']))->find();
				if($old){
					$old['num']=$old['num']+$data['num'];
					$rold=$m->save($old);
					if($rold===FALSE){
						$info['status']=0;
						$info['msg']='添加购物车失败，请重新尝试！';
					}else{
						$total=$m->where(array('sid'=>$sid,'vipid'=>$data['vipid']))->sum('num');
						$info['total']=$total;
						$info['status']=1;
						$info['msg']='添加购物车成功！';
					}
				}else{
					$rold=$m->add($data);
					if($rold){
						$total=$m->where(array('sid'=>$sid,'vipid'=>$data['vipid']))->sum('num');
						$info['total']=$total;
						$info['status']=1;
						$info['msg']='添加购物车成功！';
					}else{						
						$info['status']=0;
						$info['msg']='添加购物车失败，请重新尝试！';
					}
				}
			}else{
				$old=$m->where(array('sid'=>$sid,'vipid'=>$data['vipid'],'goodsid'=>$data['goodsid']))->find();
				if($old){
					$old['num']=$old['num']+$data['num'];
					$rold=$m->save($old);
					if($rold===FALSE){
						$info['status']=0;
						$info['msg']='添加购物车失败，请重新尝试！';
					}else{
						$total=$m->where(array('sid'=>$sid,'vipid'=>$data['vipid']))->sum('num');
						$info['total']=$total;
						$info['status']=1;
						$info['msg']='添加购物车成功！';
					}
				}else{
					$rold=$m->add($data);
					if($rold){
						$total=$m->where(array('sid'=>$sid,'vipid'=>$data['vipid']))->sum('num');
						$info['total']=$total;
						$info['status']=1;
						$info['msg']='添加购物车成功！';
					}else{						
						$info['status']=0;
						$info['msg']='添加购物车失败，请重新尝试！';
					}
				}
			}
			$this->ajaxReturn($info);
		}else{
			$this->diemsg(0, '禁止外部访问！');
		}
	}
	//删除购物车
	public function delbasket(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		if(IS_AJAX){			
			$id=I('id');
			if(!$id){
				$info['status']=0;
				$info['msg']='未获取ID参数,请重新尝试！';
				$this->ajaxReturn($info);
			}		
			$m=M('Shop_basket');
			$re=$m->where('id='.$id)->delete();
			if($re){				
				$info['status']=1;
				$info['msg']='删除成功，更新购物车状态...';
				
			}else{
				$info['status']=0;
				$info['msg']='删除失败，自动重新加载购物车...';
			}	
			$this->ajaxReturn($info);	
		}else{
			$this->diemsg(0, '禁止外部访问！');
		}
	}
	//清空购物车
	public function clearbasket(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		if(IS_AJAX){
		
			//$sid=$_GET['sid'];
			//前端必须保证登陆状态
			$vipid=$_SESSION['WAP']['vipid'];
			if(!$vipid){
				$info['status']=3;
				$info['msg']='登陆已超时，2秒后自动跳转登陆页面！';
				$this->ajaxReturn($info);
			}
			if($sid==''){
				$info['status']=0;
				$info['msg']='未获取SID参数,请重新尝试！';
				$this->ajaxReturn($info);
			}		
			$m=M('Shop_basket');
			$re=$m->where(array('sid'=>$sid,'vipid'=>$vipid))->delete();
			if($re){				
				$info['status']=2;
				$info['msg']='购物车已清空';
				$this->ajaxReturn($info);
			}else{
				$info['status']=0;
				$info['msg']='购物车清空失败，请重新尝试！';
				$this->ajaxReturn($info);
			}		
		}else{
			$this->diemsg(0, '禁止外部访问！');
		}
	}
	//购物车库存检测
	public function checkbasket(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		if(IS_AJAX){		
			//$sid=$_GET['sid'];
			//前端必须保证登陆状态
			$vipid=$_SESSION['WAP']['vipid'];
			if(!$vipid){
				$info['status']=3;
				$info['msg']='登陆已超时，2秒后自动跳转登陆页面！';
				$this->ajaxReturn($info);
			}
			$arr=$_POST;
			if($sid==''){
				$info['status']=0;
				$info['msg']='未获取SID参数';
				$this->ajaxReturn($info);
			}
			if(!$arr){
				$info['status']=0;
				$info['msg']='未获取数据，请重新尝试';
				$this->ajaxReturn($info);
			}
			$m=M('Shop_basket');
			$mgoods=M('Shop_goods');
			$msku=M('Shop_goods_sku');
			$data=$m->where(array('sid'=>$sid,'vipid'=>$_SESSION['WAP']['vipid']))->select();
			foreach($data as $k=>$v){				
				$goods=$mgoods->where('id='.$v['goodsid'])->find();
				if($v['sku']){
					$sku=$msku->where(array('sku'=>$v['sku']))->find();
					if($sku && $sku['status'] && $goods && $goods['issku'] && $goods['status']){
						$nownum=$arr[$v['id']];
						if($sku['num']-$nownum>=0){
							//保存购物车新库存
							if($nownum<>$v['num']){
								$v['num']=$nownum;
								$rda=$m->save($v);
							}
						}else{
							$info['status']=2;
							$info['msg']='存在已下架或库存不足商品！';
							$this->ajaxReturn($info);
						}
						
					}else{
						$info['status']=2;
						$info['msg']='存在已下架或库存不足商品！';
						$this->ajaxReturn($info);
					}
				}else{
					if($goods && $goods['status']){
						$nownum=$arr[$v['id']];
						if($goods['num']-$nownum>=0){
							//保存购物车新库存
							if($nownum<>$v['num']){
								$v['num']=$nownum;
								$rda=$m->save($v);
							}
						}else{
							$info['status']=2;
							$info['msg']='存在已下架或库存不足商品！';
							$this->ajaxReturn($info);
						}
						
					}else{
						$info['status']=2;
						$info['msg']='存在已下架或库存不足商品！';
						$this->ajaxReturn($info);
					}
					
				}
			}
			$info['status']=1;
			$info['msg']='商品库存检测通过，进入结算页面！';
			$this->ajaxReturn($info);
		}else{
			$this->diemsg(0, '禁止外部访问！');
		}
	}

	//立刻购买逻辑
	public function fastbuy(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		if(IS_AJAX){
			$m=M('Shop_basket');
			$data=I('post.');
			if(!$data){
				$info['status']=0;
				$info['msg']='未获取数据，请重新尝试';
				$this->ajaxReturn($info);
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
			//$sid=0;
			//前端必须保证登陆状态
			$vipid=$_SESSION['WAP']['vipid'];
			$re=$m->where(array('sid'=>$sid,'vipid'=>$vipid))->delete();
			//区分SKU模式
			if($data['sku']){
				$rold=$m->add($data);
				if($rold){
						$info['status']=1;
						$info['msg']='库存检测通过！2秒后自动生成订单！';
					}else{						
						$info['status']=0;
						$info['msg']='通讯失败，请重新尝试！';
				}
			}else{
				$rold=$m->add($data);
				if($rold){
						$info['status']=1;
						$info['msg']='库存检测通过！2秒后自动生成订单！';
				}else{						
						$info['status']=0;
						$info['msg']='通讯失败，请重新尝试！';
				}
			}
			$this->ajaxReturn($info);
		}else{
			$this->diemsg(0, '禁止外部访问！');
		}
	}
	/******
	 *作者：郑伊凡
	 *时间：2016-1-26
	 *版本：母版本
	 *功能：当开启身份认证时，检测身份是否填写完毕
	 ******/
	public function checkidentify(){
		if(IS_AJAX){
			if($_SESSION['WAP']['shopset']['ischeckid']){
				// 如果开启身份验证
				$vip=M("Vip")->where(array("id"=>$_SESSION['WAP']['vipid']))->find();
				if($vip['isidentify']){
					$info['status']=1;
				}else{
					$info['status']=0;
					$info['msg']="您还未完善个人信息，请先填写信息！";
				}
				$this->ajaxReturn($info);
			}
		}
	}
	//Order逻辑
	public function orderMake(){
		// 作者：郑伊凡 2016-1-20 母版本 功能：防止用户入口进入
		if($_SESSION['WAP']['shopset']['ischeckid']){
			// 如果开启身份验证
			$vip=M("Vip")->where(array("id"=>$_SESSION['WAP']['vipid']))->find();
			if(!$vip['isidentify']){
				$this->error('您的身份尚未完善，请先填写信息！',U("wap/vip/info"),3);
			}
		}
		// 作者：郑伊凡 2016-1-20 母版本 功能：防止用户入口进入
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		/********提货方式    zxg   16.2.16******/
		$iszts=M('express_set')->find();
		$iszt=$iszts['iszt'];
		$isztcon=$iszts['content'];
		$this->assign('iszt',$iszt);
		$this->assign('isztcon',$isztcon);
		/********提货方式    zxg   16.2.16******/
		if(IS_POST){
			$morder=M('Shop_order');
			$data=I('post.');
			$data['items']=stripslashes(htmlspecialchars_decode($data['items']));
			$data['ispay']=0;
			$data['status']=1;//订单成功，未付款
			$data['ctime']=time();
			$data['payprice']=$data['totalprice'];
			//代金券流程
			if($data['djqid']){
				$mcard=M('Vip_card');
				$djq=$mcard->where('id='.$data['djqid'])->find();
				if(!$djq){
					$this->error('通讯失败！请重新尝试支付！');
				}
				if($djq['usetime']){
					$this->error('此代金券已使用！');
				}
				$djq['status']=2;
				$djq['usetime']=time();
				$rdjq=$mcard->save($djq);
				if(FALSE === $rdjq){
					$this->error('通讯失败！请重新尝试支付！');
				}
				//修改支付价格
				$data['payprice']=$data['totalprice']-$djq['money'];
			}
			//邮费逻辑
			$goods=unserialize($data['items']);
			$allmy=1;
			foreach ($goods as $k => $v) {
				if($v['ismy']==0){
					$allmy=0;
				}
			}

			// 如果用户选择自提
			if($data['tqtype']=="ziti"){
				$data['yf'] = 0;
			}else{
				if($allmy==1){
					$data['yf'] = 0;
				}else{
					if(self::$WAP['shopset']['isyf']){
						if($data['totalprice']>=self::$WAP['shopset']['yftop']){
							$data['yf']=0;
						}else{
							$data['yf']=self::$WAP['shopset']['yf'];
							$data['payprice']=$data['payprice']+$data['yf'];
						}
						
					}else{
						$data['yf']=0;
					}
				}
			}
			
			$re=$morder->add($data);
			if($re){
				$old=$morder->where('id='.$re)->setField('oid',date('YmdHis').'-'.$re);
				if(FALSE !== $old){
					//后端日志
					$mlog=M('Shop_order_syslog');
					$dlog['sid']=$sid;
					$dlog['oid']=$re;
					$dlog['msg']='订单创建成功';
					$dlog['type']=1;
					$dlog['ctime']=time();
					$rlog=$mlog->add($dlog);
					//清空购物车
					$rbask=M('Shop_basket')->where(array('sid'=>$data['sid'],'vipid'=>$data['vipid']))->delete();
					$this->success('订单创建成功，转向支付界面!',U('Wap/Fxshop/pay/',array('sid'=>$data['sid'],'orderid'=>$re)));
				}else{
					$old=$morder->delete($re);
					$this->error('订单生成失败！请重新尝试！');
				}
			}else{
				//可能存在代金券问题
				$this->error('订单生成失败！请重新尝试！');
			}
			
		}else{
			//追入渠道
			$sid=$_SESSION['WAP']['sid'];
			$this->assign('sid',$sid);
			//非提交状态
			//$sid=$_GET['sid']<>''?$_GET['sid']:$this->diemsg(0, '缺少SID参数');//sid可以为0			
			//$lasturl=$_GET['lasturl']?$_GET['lasturl']:$this->diemsg(0, '缺少LastURL参数');
			//$basketlasturl=base64_decode($lasturl);
			//$basketurl=U('Wap/Shop/basket',array('lasturl'=>$lasturl));	
			//dump(CONTROLLER_NAME);
			//dump($basketlasturl);
			//dump($basketurl);	
			//$backurl=base64_encode($basketurl);
			//$basketloginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
			//$re=$this->checkLogin($backurl);
			//保存当前购物车地址
			//$this->assign('basketurl',$basketurl);
			//保存登陆购物车地址
			//$this->assign('basketloginurl',$basketloginurl);
			//保存购物车前地址
			//$this->assign('basketlasturl',$basketlasturl);
			//保存lasturlencode
			//保存购物车加密地址，用于OrderMaker正常返回
			//$this->assign('lasturlencode',$lasturl);
			//$this->assign('sid',$sid);
			//清空临时地址
			unset($_SESSION['WAP']['orderURL']);
			//已登陆
			$m=M('Shop_basket');
			$mgoods=M('Shop_goods');
			$msku=M('Shop_goods_sku');
			$cache=$m->where(array('sid'=>$sid,'vipid'=>$_SESSION['WAP']['vipid']))->select();
			//错误标记
			$errflag=0;
			//等待删除ID
			$todelids='';
			//totalprice
			$totalprice=0;
			//单件邮费处理
			$yfprice=0;
			//totalnum
			$totalnum=0;
			// 作者：郑伊凡 2016-1-26 功能：ptg 拼团购模块移植
			$ids=I('ids');
			$ptgid=I('ptgid');
			$this->assign('ids',$ids);
			$this->assign('ptgid',$ptgid);
			// 作者：郑伊凡 2016-1-26 功能：ptg 拼团购模块移植
			//聚友杀逻辑
			$totaliscut=0;
			$totalcutlow=0;
			$totalcuttop=0;
			$totalcutmax=0;
			$allmy=1;
			foreach($cache as $k=>$v){
				//sku模型
				$goods=$mgoods->where('id='.$v['goodsid'])->find();	
				// 作者：郑伊凡 2016-1-26 功能：ptg 拼团购模块移植
				if(count($cache)==1){
					$this->assign('isgroup',$goods['isgroup']);
				}
				// 作者：郑伊凡 2016-1-26 功能：ptg 拼团购模块移植
				//邮费判断
				if($goods['ismy']==0){
					$allmy=0;
				}
				$flag =$goods['lid'].','.$flag;		
				$pic=$this->getPic($goods['pic']);
				if($v['sku']){
					//取商品数据				
					if($goods['issku'] && $goods['status']){
						$map['sku']=$v['sku'];
						$sku=$msku->where($map)->find();
						if($sku['status']){
							if($sku['num']){
								//调整购买量
								$cache[$k]['goodsid']=$goods['id'];
								$cache[$k]['skuid']=$sku['id'];
								$cache[$k]['name']=$goods['name'];
								$cache[$k]['skuattr']=$sku['skuattr'];
								$cache[$k]['num']=$v['num']>$sku['num']?$sku['num']:$v['num'];
								$cache[$k]['price']=$sku['price'];
								$cache[$k]['total']=$v['num']*$sku['price'];
								$cache[$k]['pic']=$pic['imgurl'];
								$totalnum=$totalnum+$cache[$k]['num'];
								$totalprice=$totalprice+$cache[$k]['price']*$cache[$k]['num'];
								if($goods['iscut']){
									$cache[$k]['iscut']=$goods['iscut'];
									$cache[$k]['cutlow']=$goods['cutlow'];
									$cache[$k]['cuttop']=$goods['cuttop'];
									$cache[$k]['cutmax']=$goods['cutmax'];
									$totaliscut=$totaliscut+$goods['iscut']*$cache[$k]['num'];
									$totalcutlow=$totalcutlow+$goods['cutlow']*$cache[$k]['num'];
									$totalcuttop=$totalcuttop+$goods['cuttop']*$cache[$k]['num'];
									$totalcutmax=$totalcutmax+$goods['cutmax']*$cache[$k]['num'];
								}
							}else{
								//无库存删除
								$todelids=$todelids.$v['id'].',';
								unset($cache[$k]);
								
							}
						}else{
							//下架删除
							$todelids=$todelids.$v['id'].',';
							unset($cache[$k]);
						}
					}else{
						//下架删除
						$todelids=$todelids.$v['id'].',';
						unset($cache[$k]);
					}
					
				}else{
					if($goods['status']){
						if($goods['num']){
							//调整购买量
							$cache[$k]['goodsid']=$goods['id'];
							$cache[$k]['skuid']=0;
							$cache[$k]['name']=$goods['name'];
							$cache[$k]['skuattr']=$sku['skuattr'];
							$cache[$k]['num']=$v['num']>$goods['num']?$goods['num']:$v['num'];
							// 作者：郑伊凡 2016-1-25 母版本 拼团购模块
							if($ptgid){
								$cache[$k]['price']=$goods['groupprice'];
							}else{
								$cache[$k]['price']=$goods['price'];
							}
							// $cache[$k]['total']=$v['num']*$goods['price'];
							$cache[$k]['total']=$v['num']*$cache[$k]['price'];
							// 作者：郑伊凡 2016-1-25 母版本 拼团购模块
							$cache[$k]['pic']=$pic['imgurl'];
							$totalnum=$totalnum+$cache[$k]['num'];
							$totalprice=$totalprice+$cache[$k]['price']*$cache[$k]['num'];
							if(!$goods['ismy']){
								$yfprice =$yfprice +$cache[$k]['price']*$cache[$k]['num'];
							}
							if($goods['iscut']){
									$cache[$k]['iscut']=$goods['iscut'];
									$cache[$k]['cutlow']=$goods['cutlow'];
									$cache[$k]['cuttop']=$goods['cuttop'];
									$cache[$k]['cutmax']=$goods['cutmax'];
									$totaliscut=$totaliscut+$goods['iscut']*$cache[$k]['num'];
									$totalcutlow=$totalcutlow+$goods['cutlow']*$cache[$k]['num'];
									$totalcuttop=$totalcuttop+$goods['cuttop']*$cache[$k]['num'];
									$totalcutmax=$totalcutmax+$goods['cutmax']*$cache[$k]['num'];
							}
							// 作者：郑伊凡 2016-1-26 功能：ptg--是否开启拼团购
								if($goods['isgroup']){
										$cache[$k]['isgroup']=$goods['isgroup'];
										$cache[$k]['groupmax']=$goods['groupmax'];
										$cache[$k]['groupprice']=$goods['groupprice'];
								}
						// 作者：郑伊凡 2016-1-26 功能：ptg--是否开启拼团购
						}else{
							//无库存删除
							$todelids=$todelids.$v['id'].',';
							unset($cache[$k]);						
						}
					}else{
						//下架删除
						$todelids=$todelids.$v['id'].',';
						unset($cache[$k]);
					}
				}
			}
			if($todelids){
				$rdel=$m->delete($todelids);
				if(!$rdel){
					$this->error('购物车获取失败，请重新尝试！');
				}	
			}
			//优惠标记
			$flagarr =explode(',',$flag);
			if(in_array('3',$flagarr)){
				$flags =1;
			}
			$this->assign('flag',$flags);
			
		
			
			
			//将商品列表
			sort($cache);
			$allitems=serialize($cache);
			$this->assign('allitems',$allitems);
			//VIP信息
			$vipadd=I('vipadd');
			if($vipadd){
				$vip=M('Vip_address')->where('id='.$vipadd)->find();
			}else{
				$vip=M('Vip_address')->where('vipid='.$_SESSION['WAP']['vipid'])->find();
			}
			$this->assign('vip',$vip);
			//可用代金券
			$mdjq=M('Vip_card');
			$mapdjq['type']=2;
			$mapdjq['vipid']=$_SESSION['WAP']['vipid'];
			$mapdjq['status']=1;//1为可以使用
			$mapdjq['usetime']=0;
			$mapdjq['etime']=array('gt',time());
			$mapdjq['usemoney']=array('lt',$totalprice);
			$djq=$mdjq->field('id,money')->where($mapdjq)->select();
			$this->assign('djq',$djq);
			//邮费逻辑
			if($allmy==1){
				$this->assign('isyf',1);
				$this->assign('yf',0);
			}else{
				if(self::$WAP['shopset']['isyf']){
					$this->assign('isyf',1);
					$yf=$totalprice>=self::$WAP['shopset']['yftop']?0:self::$WAP['shopset']['yf'];
					$this->assign('yf',$yf);
					$this->assign('yftop',self::$WAP['shopset']['yftop']);
				}else{
					$this->assign('isyf',1);
					$this->assign('yf',0);
				}
			}
			//是否可以用余额支付
			$useryue=$_SESSION['WAP']['vip']['money'];
			$isyue=$_SESSION['WAP']['vip']['money']-$totalprice>=0?0:1;
			$this->assign('isyue',$isyue);
			//
			$this->assign('ntime',$ntime);
			$this->assign('cache',$cache);
			$this->assign('totalprice',$totalprice);
			$this->assign('totalnum',$totalnum);
			//积分换算
			$this->assign('jfdk',self::$WAP['shopset']['jfdk']);
			$this->assign('jfdh',self::$WAP['shopset']['jfdh']);
			$dhscore=$totalprice*self::$WAP['shopset']['jfdk']*0.01*self::$WAP['shopset']['jfdh'];
			$this->assign('myscore',$_SESSION['WAP']['vip']['score']);
			$this->assign('dhscore',$dhscore);
			//聚友杀逻辑
			$this->assign('totaliscut',$totaliscut);
			$this->assign('totalcutlow',$totalcutlow);
			$this->assign('totalcuttop',$totalcuttop);
			$this->assign('totalcutmax',$totalcutmax);
			$jysmsg=array_filter(explode('##', self::$WAP['shopset']['jysmsg']));
			$this->assign('firstmsg',$jysmsg[0]);
			// 作者：郑伊凡 2016-1-26 母版本 功能：控制前台的聚友杀、拼团购
			$this->assign('isjys',self::$WAP['shopset']['isjys']);
			$this->assign('isptg',self::$WAP['shopset']['isptg']);
			// 作者：郑伊凡 2016-1-26 母版本 功能：控制前台的聚友杀、拼团购	
			$this->display();
		}
		
	}

	//Order 聚友杀
	public function orderJys(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		if(IS_POST){
			$morder=M('Shop_order');
			$data=I('post.');
			$data['items']=stripslashes(htmlspecialchars_decode($data['items']));
			$data['ispay']=0;
			$data['status']=1;//订单成功，未付款
			$data['ctime']=time();
			$data['payprice']=$data['totalprice'];
			//聚友杀模式
			$data['iscut']=1;
			$data['cutlow']=$data['cutlow'];
			$data['cuttop']=$data['cuttop'];
			$data['cutmax']=$data['cutmax'];
			//代金券流程
			if($data['djqid']){
				$mcard=M('Vip_card');
				$djq=$mcard->where('id='.$data['djqid'])->find();
				if(!$djq){
					$this->error('通讯失败！请重新尝试支付！');
				}
				if($djq['usetime']){
					$this->error('此代金券已使用！');
				}
				$djq['status']=2;
				$djq['usetime']=time();
				$rdjq=$mcard->save($djq);
				if(FALSE === $rdjq){
					$this->error('通讯失败！请重新尝试支付！');
				}
				//修改支付价格
				$data['payprice']=$data['totalprice']-$djq['money'];
			}
			//邮费逻辑
			// 如果用户选择自提
			if($data['tqtype']=="ziti"){
				$data['yf'] = 0;
			}else{
				if(self::$WAP['shopset']['isyf']){
					if($data['totalprice']>=self::$WAP['shopset']['yftop']){
						$data['yf']=0;
					}else{
						$data['yf']=self::$WAP['shopset']['yf'];
						$data['payprice']=$data['payprice']+$data['yf'];
					}
					
				}else{
					$data['yf']=0;
				}
			}
			$re=$morder->add($data);
			if($re){
				$old=$morder->where('id='.$re)->setField('oid',date('YmdHis').'-'.$re);
				if(FALSE !== $old){
//					$mlog=M('Shop_order_log');
//					$dlog['sid']=$sid;
//					$dlog['oid']=$cache['id'];
//					$dlog['msg']='订单开启聚友杀模式。';
//					$dlog['ctime']=time();
//					$rlog=$mlog->add($dlog);
					//后端日志
					$mlog=M('Shop_order_syslog');
					$dlog['sid']=$sid;
					$dlog['oid']=$re;
					$dlog['msg']='订单创建成功';
					$dlog['type']=1;
					$dlog['ctime']=time();
					$rlog=$mlog->add($dlog);
//					$mlog=M('Shop_order_syslog');
//					$dlog['sid']=$sid;
//					$dlog['oid']=$re;
//					$dlog['msg']='订单开启聚友杀模式';
//					$dlog['type']=1;
//					$dlog['ctime']=time();
//					$rlog=$mlog->add($dlog);
					//清空购物车
					$rbask=M('Shop_basket')->where(array('sid'=>$data['sid'],'vipid'=>$data['vipid']))->delete();
					//$this->success('订单创建成功，转向支付界面!',U('Wap/Fxshop/pay/',array('sid'=>$data['sid'],'orderid'=>$re)));
					//转向聚友杀首页
					$this->success('订单创建成功，转向聚友杀中心!',U('Wap/Jys/index/',array('sid'=>$data['sid'],'orderid'=>$re)));
				}else{
					$old=$morder->delete($re);
					$this->error('订单生成失败！请重新尝试！');
				}
			}else{
				//可能存在代金券问题
				$this->error('订单生成失败！请重新尝试！');
			}
			
		}else{
				$this->error('非法访问！');
		}
	}

	//订单地址跳转
	public function orderAddress(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);	
		//$sid=I('sid');
		$lasturlencode=I('lasturl');
		if($lasturlencode){
			$backurl=base64_decode($lasturlencode);
		}else{
			$backurl=U('Wap/Fxshop/orderMake');
		}
		$_SESSION['WAP']['orderURL']=$backurl;
		$this->redirect(U('Wap/Vip/address'));
	}
	
	//订单列表
	public function orderList(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);	
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$type=I('type')?I('type'):4;
		$this->assign('type',$type);
		$bkurl=U('Wap/Fxshop/orderList',array('type'=>$type));		
		//$backurl=base64_encode($bkurl);
		//$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		//$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('Shop_order');
		$vipid=$_SESSION['WAP']['vipid'];
		$map['sid']=$sid;
		$map['vipid']=$vipid;
		$map['iscut']='0';
		$map['isgroup']='0';
		$map['integpay']='0';
		switch($type){
			case '1':
				$map['status']=1;
				break;
			case '2':
				$map['status']=array('in','2,3');
				break;
			case '3':
				$map['status']=array('in','5,6');
				break;
			case '4':
				//全部
				$map['status']=array('neq','0');
				break;
			default:
				$map['status']=1;
				break;
		}
		$cache=$m->where($map)->order('ctime desc')->select();
		if($cache){
			foreach($cache as $k=>$v){
				if($v['items']){
					$cache[$k]['items']=unserialize($v['items']);
				}
			}	
		}
		$this->assign('cache',$cache);
		
		//高亮底导航
		$this->assign('actname','ftorder');
		$this->display();
	}
	
	//zxg   2016.2.19  拼团购  聚友杀订单
	public function ptgorderlist(){
		
		$sid=$_SESSION['WAP']['vip']['sid']<>''?$_SESSION['WAP']['vip']['sid']:$this->diemsg(0, '缺少SID参数');
		$type=I('type')?I('type'):4;
		$this->assign('type',$type);
		$bkurl=U('Wap/fxshop/orderList',array('sid'=>$sid,'type'=>$type));		
		$backurl=base64_encode($bkurl);
		$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		$re=$this->checkLogin($backurl);
		//已登陆
		$mo=M("Shop_order");
		$m=M('ptg_log');
		$shopg=M('shop_goods');
		$vipid=$_SESSION['WAP']['vipid'];
		$map['sid']=$sid;
		$map['vipid']=$vipid;
		$map['isgroup']=2;
		$cache=$mo->where($map)->order('ctime desc')->select();
		if($cache){
			foreach($cache as $k=>$v){
				$items=unserialize($v['items']);
				$goodsid=$items[0]['goodsid'];
				$cate1=$shopg->where(array('id'=>$goodsid))->find();
				$ta=$this->getPic($cate1['listpic']);
				$cache[$k]['listsrc']=$ta['imgurl'];
				if($v['ptgid'] && $v['ispay']==1){
					$ptg_log=$m->where("id=".$v['ptgid'])->find();
					$cache[$k]['groupmax']=$ptg_log['groupmax'];
					$cache[$k]['groupprice']=$ptg_log['groupprice'];
					$cache[$k]['status']=$ptg_log['status'];
				} else if(!$v['ispay']){
					$cache[$k]['groupmax']=$cate1['groupmax'];
					$cache[$k]['groupprice']=$cate1['groupprice'];
					// 未支付
					$cache[$k]['status']=0;
				}
			}	
		}
		$this->assign('cache',$cache);
		
		//高亮底导航
		$this->assign('actname','ftorder');
		$this->display();
	}

	public function jysorderlist(){
		
		$sid=$_SESSION['WAP']['vip']['sid']<>''?$_SESSION['WAP']['vip']['sid']:$this->diemsg(0, '缺少SID参数');
		$type=I('type')?I('type'):4;
		$this->assign('type',$type);
		$bkurl=U('Wap/Shop/orderList',array('sid'=>$sid,'type'=>$type));		
		$backurl=base64_encode($bkurl);
		$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('shop_order');
		$shopg=M('shop_goods');
		$vipid=$_SESSION['WAP']['vipid'];
		$map['sid']=$sid;
		$map['iscut']=array('in','1,2');
		$map['vipid']=$vipid;
		$cache=$m->where($map)->order('ctime desc')->select();
		if($cache){
			foreach($cache as $k=>$v){
				if($v['items']){
					$cache[$k]['items']=unserialize($v['items']);
				}
				$goodsid=$cache[$k]['items']['0']['goodsid'];
				$cate1=$shopg->where(array('id'=>$goodsid))->select();
				foreach($cate1 as $k1=>$v1){
					$ta=$this->getPic($v1['listpic']);
					$cate1[$k1]['listsrc']=$ta['imgurl'];
				}
				$cache[$k]['goods']=$cate1;
			}	
		}
		$this->assign('cache',$cache);
		
		//高亮底导航
		$this->assign('actname','ftorder');
		$this->display();
	}
	//zxg   2016.2.19  拼团购订单
	
	
	//订单详情
	//订单列表
	public function orderDetail(){
		//追入渠道
		$sid=$_SESSION['WAP']['vip']['sid'];
		$this->assign('sid',$sid);
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		//$bkurl=U('Wap/Fxshop/orderDetail',array('orderid'=>$orderid));		
		//$backurl=base64_encode($bkurl);
		//$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		//$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('Shop_order');
		$vipid=$_SESSION['WAP']['vipid'];
		$isptg=I('isptg');
		if($isptg){
			$map['oid']=$orderid;
			$this->assign('isptg',$isptg);
		}else{
			$map['id']=$orderid;
		}
		$cache=$m->where($map)->find();
		if(!$cache){
			$this->diemsg('此订单不存在!');
		}
		$cache['items']=unserialize($cache['items']);
		
		
		
		//order日志
		$mlog=M('Shop_order_log');
		$log=$mlog->where('oid='.$cache['id'])->select();
		$this->assign('log',$log);
		if(!$cache['status']==1){
			//是否可以用余额支付
			$useryue=$_SESSION['WAP']['vip']['money'];
			$isyue=$_SESSION['WAP']['vip']['money']-$cache['payprice']>=0?0:1;
			$this->assign('isyue',$isyue);
		}
		$this->assign('cache',$cache);
		//代金券调用
		if($cache['djqid']){
			$djq=M('Vip_card')->where('id='.$cache['djqid'])->find();
			$this->assign('djq',$djq);
		}
		//高亮底导航
		$this->assign('actname','ftorder');
		$this->display();
	}
	
	//订单取消
	public function orderCancel(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		//$bkurl=U('Wap/Fxshop/orderDetail',array('orderid'=>$orderid));		
		//$backurl=base64_encode($bkurl);
		//$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		//$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('Shop_order');
		$map['sid']=$sid;
		$map['id']=$orderid;
		$cache=$m->where($map)->find();
		if(!$cache){
			$this->diemsg(0,'此订单不存在!');
		}
		if($cache['status']<>1){
			$this->error('只有未付款订单可以取消！');
		}
		$re=$m->where($map)->setField('status',0);
		if($re){
			//订单取消只有后端日志
			$mslog=M('Shop_order_syslog');
			$dlog['oid']=$sid;
			$dlog['oid']=$cache['id'];
			$dlog['msg']='订单取消';
			$dlog['type']=0;
			$dlog['ctime']=time();
			$rlog=$mslog->add($dlog);
			$this->success('订单取消成功！');
		}else{
			$this->error('订单取消失败,请重新尝试！');
		}	
	}

	public function backnum($cache){
		$mgoods=M('Shop_goods');
		$msku=M('Shop_goods_sku');
		$items=unserialize($cache['items']);
		foreach($items as $k => $v){
			$goods=$mgoods->where('id='.$v['goodsid'])->find();
			//if($goods['status']){
				if($goods['issku']){
					if($v['sku']){
						$map['sku']=$v['sku'];
						$sku=$msku->where($map)->setInc('num',$v['num']);
					}		
				}else{
					//无sku
					$mgoods->where('id='.$v['goodsid'])->setInc('num',$v['num']);
				}	
			//}						
		}
	}
	
	//确认收货
	public function orderOK(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);	
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		//$bkurl=U('Wap/Fxshop/orderDetail',array('orderid'=>$orderid));		
		//$backurl=base64_encode($bkurl);
		//$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		//$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('Shop_order');
		$map['sid']=$sid;
		$map['id']=$orderid;
		$cache=$m->where($map)->find();
		if(!$cache){
			$this->diemsg(0,'此订单不存在!');
		}
		if($cache['status']<>3){
			$this->error('只有待收货订单可以确认收货！');
		}
		$cache['etime']=time();//交易完成时间
		$cache['status']=5;
		$rod=$m->save($cache);
		if(FALSE !== $rod){
			//修改会员账户金额、经验、积分、等级
			$data_vip['id']=$cache['vipid'];
			$data_vip['score']=array('exp','score+'.round($cache['payprice']*self::$WAP['vipset']['xf_score']/100));
			if (self::$WAP['vipset']['cz_exp']>0) {
				$data_vip['exp']=array('exp','exp+'.round($cache['payprice']*self::$WAP['vipset']['xf_exp']/100));
				$data_vip['cur_exp']=array('exp','cur_exp+'.round($cache['payprice']*self::$WAP['vipset']['xf_exp']/100));
				$level=$this->getLevel(self::$WAP['vip']['cur_exp']+round($cache['payprice']*self::$WAP['vipset']['xf_exp']/100));
				$data_vip['levelid']=$level['levelid'];
				//会员分销统计字段
				//会员合计支付
				$data_vip['total_buy']=$data_vip['total_buy']+$cache['payprice'];
				//会员购满多少钱变成分销商
				if(self::$WAP['shopset']['vipfxneed']<=$data_vip['total_buy']){
					$data_vip['isfx']=1;
				}
			}
			$re=M('vip')->save($data_vip);
			if (FALSE===$re) {
				$this->error('更新会员信息失败！');
			}
			//3层分销销商制佣金
			//分销佣金计算
			//$pid=$_SESSION['WAP']['vip']['pid'];
			$mvip=M('Fxs_user');
			$mfxlog=M('Fxs_syslog');
			$fxlog['sid']=$sid;
			$fxlog['oid']=$cache['id'];
			$fxlog['fxprice']=$fxprice=$cache['payprice']-$cache['yf'];
			$fxlog['ctime']=time();
			$fx1rate=self::$WAP['shopset']['fx1rate']/100;
			$fx2rate=self::$WAP['shopset']['fx2rate']/100;
			$fx3rate=self::$WAP['shopset']['fx3rate']/100;
			$fxtmp=array();//缓存3级数组
			if($sid){
				//第一层分销
				$fx1=$mvip->where('id='.$sid)->find();
				if($fx1rate){
					$fxlog['fxyj']=$fxprice*$fx1rate;
					$fx1['money']=$fx1['money']+$fxlog['fxyj'];
					$fx1['total_xxbuy']=$fx1['total_xxbuy']+1;//下线中购买产品总次数
					$fx1['total_xxyj']=$fx1['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
					$rfx=$mvip->save($fx1);					
					$fxlog['from']=$_SESSION['WAP']['vipid'];
					$fxlog['fromname']=$_SESSION['WAP']['vip']['nickname'];
					$fxlog['to']=$fx1['id'];
					$fxlog['toname']=$fx1['nickname'];
					if(FALSE!==$rfx){
						//佣金发放成功
						$fxlog['status']=1;
					}else{
						//佣金发放失败
						$fxlog['status']=0;
					}
					//单层逻辑					
					//$rfxlog=$mfxlog->add($fxlog);
					//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
					array_push($fxtmp,$fxlog);
				}
				//第二层分销
				if($fx1['pid']){
					$fx2=$mvip->where('id='.$fx1['pid'])->find();
					if($fx2rate){
						$fxlog['fxyj']=$fxprice*$fx2rate;
						$fx2['money']=$fx2['money']+$fxlog['fxyj'];
						$fx2['total_xxbuy']=$fx2['total_xxbuy']+1;//下线中购买产品人数计数
						$fx2['total_xxyj']=$fx2['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
						$rfx=$mvip->save($fx2);
						$fxlog['from']=$_SESSION['WAP']['vipid'];
						$fxlog['fromname']=$_SESSION['WAP']['vip']['nickname'];
						$fxlog['to']=$fx2['id'];
						$fxlog['toname']=$fx2['nickname'];
						if(FALSE!==$rfx){
							//佣金发放成功
							$fxlog['status']=1;
						}else{
							//佣金发放失败
							$fxlog['status']=0;
						}
						//单层逻辑
						//$rfxlog=$mfxlog->add($fxlog);
						//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
						array_push($fxtmp,$fxlog);
					}
				}
				//第三层分销
				if($fx2['pid']){
					$fx3=$mvip->where('id='.$fx2['pid'])->find();
					if($fx3rate){
						$fxlog['fxyj']=$fxprice*$fx3rate;
						$fx3['money']=$fx3['money']+$fxlog['fxyj'];
						$fx3['total_xxbuy']=$fx3['total_xxbuy']+1;//下线中购买产品人数计数
						$fx3['total_xxyj']=$fx3['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
						$rfx=$mvip->save($fx3);
						$fxlog['from']=$_SESSION['WAP']['vipid'];
						$fxlog['fromname']=$_SESSION['WAP']['vip']['nickname'];
						$fxlog['to']=$fx3['id'];
						$fxlog['toname']=$fx3['nickname'];
						if(FALSE!==$rfx){
							//佣金发放成功
							$fxlog['status']=1;
						}else{
							//佣金发放失败
							$fxlog['status']=0;
						}
						//单层逻辑
						//$rfxlog=$mfxlog->add($fxlog);
						//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
						array_push($fxtmp,$fxlog);
					}
				}
				//多层分销
				if(count($fxtmp)>=1){
					$refxlog=$mfxlog->addAll($fxtmp);
					if(!$refxlog){
						file_put_contents('Joel_fxs_error.txt','错误日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'错误纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
					}
				}
							
			}

			//三层VIP分销佣金计算
			$pid=$_SESSION['WAP']['vip']['pid'];
			$mvip=M('vip');
			$mfxlog=M('fx_syslog');
			$fxlog=array();//清空分销log
			$fxlog['sid']=$cache['sid'];
			$fxlog['oid']=$cache['id'];
			$fxlog['fxprice']=$fxprice=$cache['payprice']-$cache['yf'];
			$fxlog['ctime']=time();
			$fx1rate=self::$WAP['shopset']['vipfx1rate']/100;
			$fx2rate=self::$WAP['shopset']['vipfx2rate']/100;
			$fx3rate=self::$WAP['shopset']['vipfx3rate']/100;
			$fxtmp=array();//缓存3级数组
			if($pid){
				//第一层分销
				$fx1=$mvip->where('id='.$pid)->find();
				if($fx1['isfx'] && $fx1rate){
					$fxlog['fxyj']=$fxprice*$fx1rate;
					$fx1['money']=$fx1['money']+$fxlog['fxyj'];
					$fx1['total_xxbuy']=$fx1['total_xxbuy']+1;//下线中购买产品总次数
					$fx1['total_xxyj']=$fx1['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
					$rfx=$mvip->save($fx1);					
					$fxlog['from']=$_SESSION['WAP']['vipid'];
					$fxlog['fromname']=$_SESSION['WAP']['vip']['nickname'];
					$fxlog['to']=$fx1['id'];
					$fxlog['toname']=$fx1['nickname'];
					if(FALSE!==$rfx){
						//佣金发放成功
						$fxlog['status']=1;
					}else{
						//佣金发放失败
						$fxlog['status']=0;
					}
					
					//单层逻辑					
					//$rfxlog=$mfxlog->add($fxlog);
					//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
					array_push($fxtmp,$fxlog);
				}
				
				//第二层分销
				if($fx1['pid'] && $fx2rate){
					$fx2=$mvip->where('id='.$fx1['pid'])->find();
					if($fx2['isfx'] && $fx2rate){
						$fxlog['fxyj']=$fxprice*$fx2rate;
						$fx2['money']=$fx2['money']+$fxlog['fxyj'];
						$fx2['total_xxbuy']=$fx2['total_xxbuy']+1;//下线中购买产品人数计数
						$fx2['total_xxyj']=$fx2['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
						$rfx=$mvip->save($fx2);
						$fxlog['from']=$_SESSION['WAP']['vipid'];
						$fxlog['fromname']=$_SESSION['WAP']['vip']['nickname'];
						$fxlog['to']=$fx2['id'];
						$fxlog['toname']=$fx2['nickname'];
						if(FALSE!==$rfx){
							//佣金发放成功
							$fxlog['status']=1;
						}else{
							//佣金发放失败
							$fxlog['status']=0;
						}
						//单层逻辑
						//$rfxlog=$mfxlog->add($fxlog);
						//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
						array_push($fxtmp,$fxlog);
					}
				}
				//第三层分销
				if($fx2['pid'] && $fx3rate){
					$fx3=$mvip->where('id='.$fx2['pid'])->find();
					if($fx3['isfx'] && $fx3rate){
						$fxlog['fxyj']=$fxprice*$fx3rate;
						$fx3['money']=$fx3['money']+$fxlog['fxyj'];
						$fx3['total_xxbuy']=$fx3['total_xxbuy']+1;//下线中购买产品人数计数
						$fx3['total_xxyj']=$fx3['total_xxyj']+$fxlog['fxyj'];//下线贡献佣金
						$rfx=$mvip->save($fx3);
						$fxlog['from']=$_SESSION['WAP']['vipid'];
						$fxlog['fromname']=$_SESSION['WAP']['vip']['nickname'];
						$fxlog['to']=$fx3['id'];
						$fxlog['toname']=$fx3['nickname'];
						if(FALSE!==$rfx){
							//佣金发放成功
							$fxlog['status']=1;
						}else{
							//佣金发放失败
							$fxlog['status']=0;
						}
						//单层逻辑
						//$rfxlog=$mfxlog->add($fxlog);
						//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
						array_push($fxtmp,$fxlog);
					}
				}
				
				//三层VIP分销
				if(count($fxtmp)>=1){
						$refxlog=$mfxlog->addAll($fxtmp);
						if(!$refxlog){
							file_put_contents('Joel_vipfx_error.txt','错误日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'错误纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
						}
				}
				
			}
			
			
			$mlog=M('Shop_order_log');
			$dlog['sid']=$sid;
			$dlog['oid']=$cache['id'];
			$dlog['msg']='确认收货,交易完成。';
			$dlog['ctime']=time();
			$rlog=$mlog->add($dlog);
			
			//后端日志
			$mlog=M('Shop_order_syslog');
			$dlog['sid']=$sid;
			$dlog['oid']=$cache['id'];
			$dlog['msg']='交易完成-会员点击';
			$dlog['type']=5;
			$dlog['paytype']=$cache['paytype'];
			$dlog['ctime']=time();
			$rlog=$mlog->add($dlog);
			//==订单完成==给该用户的上级发送=获得的佣金=====================================================
			$ds=M('fx_syslog')->where(array('oid'=>$orderid))->find();		//查找将获得的佣金
			$vipuser=M('vip')->where(array('id'=>$ds['to']))->find(); //查找他的上级获取上级的openid
			$SET=M('Set')->find();
			
			$items=unserialize($cache['items']);
			$itemsname='';
			foreach($items as $k=>$v){
				$itemsname.=$v['name'].',';
			}
			$tp=new \bb\template();
			$array=array(
				'url'=>$SET['wxurl'].U('wap/fx/fxlog'),
				'name'=>$ds['toname'],		//上级名字
				'fromname'=>$ds['fromname'],		//下级级名字
				'ordername'=>rtrim($itemsname,','),
				'orderid'=>$cache['oid'],
				'money'=>$ds['fxprice'],		//商品价格
				'yj'=>$ds['fxyj']			//分销的佣金
			);
			$openid=$vipuser['openid'];		//发给的人
			$templatedata=$tp->enddata('getcommission',$openid,$array);	//组合模板数据
			$options['appid']= self::$_wxappid;
			$options['appsecret']= self::$_wxappsecret;
			$wx = new \Joel\wx\Wechat($options);
			$wx->sendTemplateMessage($templatedata);	//发送模板
			//====================================================================
			$this->success('交易已完成，感谢您的支持！');
		}else{
			//后端日志
			$mlog=M('Shop_order_syslog');
			$dlog['sid']=$sid;
			$dlog['oid']=$cache['id'];
			$dlog['msg']='确认收货失败';
			$dlog['type']=-1;
			$dlog['paytype']=$cache['paytype'];
			$dlog['ctime']=time();
			$rlog=$mlog->add($dlog);
			$this->error('确认收货失败，请重新尝试！');
		}
	}

	//订单退货
	public function orderTuihuo(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);	
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		//$bkurl=U('Wap/Fxshop/orderTuihuo',array('orderid'=>$orderid));		
		//$backurl=base64_encode($bkurl);
		//$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		//$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('Shop_order');
		$vipid=$_SESSION['WAP']['vipid'];
		$map['sid']=$sid;
		$map['id']=$orderid;
		$cache=$m->where($map)->find();
		if(!$cache){
			$this->diemsg('此订单不存在!');
		}
		$cache['items']=unserialize($cache['items']);
	
		$this->assign('cache',$cache);
		//代金券调用
		if($cache['djqid']){
			$djq=M('Vip_card')->where('id='.$cache['djqid'])->find();
			$this->assign('djq',$djq);
		}
		//高亮底导航
		$this->assign('actname','ftorder');
		$this->display();
	}
	
	//订单取消
	public function orderTuihuoSave(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);	
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		//$bkurl=U('Wap/Fxshop/orderTuihuo',array('orderid'=>$orderid));		
		//$backurl=base64_encode($bkurl);
		//$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		//$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('Shop_order');
		$map['sid']=$sid;
		$map['id']=$orderid;
		$cache=$m->where($map)->find();
		if(!$cache){
			$this->diemsg(0,'此订单不存在!');
		}
		if($cache['status']<>3){
			$this->error('只有待收货订单可以办理退货！');
		}
		$data=I('post.');
		$cache['status']=4;
		$cache['tuihuoprice']=$data['tuihuoprice'];
		$cache['tuihuokd']=$data['tuihuokd'];
		$cache['tuihuokdnum']=$data['tuihuokdnum'];
		$cache['tuihuomsg']=$data['tuihuomsg'];
		//退货申请时间
		$cache['tuihuosqtime']=time();
		$re=$m->where($map)->save($cache);
		if($re){
			//后端日志
			$mlog=M('Shop_order_log');
			$mslog=M('Shop_order_syslog');
			$dlog['sid']=$sid;
			$dlog['oid']=$cache['id'];
			$dlog['msg']='申请退货';
			$dlog['ctime']=time();
			$rlog=$mlog->add($dlog);
			$dlog['type']=4;
			$rslog=$mslog->add($dlog);
			$this->success('申请退货成功！请等待工作人员审核！');
		}else{
			$this->error('申请退货失败,请重新尝试！');
		}	
	}
	
	//订单支付
	public function pay(){
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		//$sid=I('sid')<>''?I('sid'):$this->diemsg(0, '缺少SID参数');//sid可以为0
		$orderid=I('orderid')<>''?I('orderid'):$this->diemsg(0, '缺少ORDERID参数');
		$type=I('type');
		//$bkurl=U('Wap/Fxshop/pay',array('sid'=>$sid,'orderid'=>$orderid,'type'=>$type));		
		//$backurl=base64_encode($orderdetail);
		//$loginurl=U('Wap/Vip/login',array('backurl'=>$backurl));
		//$re=$this->checkLogin($backurl);
		//已登陆
		$m=M('Shop_order');
		$order=$m->where('id='.$orderid)->find();
		if(!$order){
			$this->error('此订单不存在！');
		}
		if($order['status']<>1){
			$this->error('此订单不可以支付！');
		}
		$paytype=I('type')?I('type'):$order['paytype'];
		switch($paytype){
			case 'money':
				$mvip=M('Vip');
				$vip=$mvip->where('id='.$_SESSION['WAP']['vipid'])->find();
				$pp=$vip['money']-$order['payprice'];
				if($pp>=0){
					$re=$mvip->where('id='.$_SESSION['WAP']['vipid'])->setField('money',$pp);
					if($re){
						$order['paytype']='money';
						$order['ispay']=1;
						$order['paytime']=time();
						$order['status']=2;
						//聚友杀关闭 
						if($order['iscut']){
							$order['iscut']=2;
						}
						$rod=$m->save($order);
						if(FALSE !== $rod){
							//==付款成功-发送模板消息通知该会员==============================
							$SET=M('Set')->find();
							$items=unserialize($order['items']);
							$itemsname='';
							foreach($items as $k=>$v){
								$itemsname.=$v['name'].',';
							}
							$tp=new \bb\template();
							$array=array(
								'url'=>$SET['wxurl'].U('wap/fxshop/orderDetail',array('orderid'=>$order['id'])),
								'name'=>$order['vipname'],
								'ordername'=>rtrim($itemsname,','),
								'orderid'=>$order['oid'],
								'money'=>$order['payprice'],
								'date'=>date("Y-m-d H:i:s",time())
							);
							$templatedata=$tp->enddata('orderok',$order['vipopenid'],$array);	//组合模板数据
							$options['appid']= self::$_wxappid;
							$options['appsecret']= self::$_wxappsecret;
							$wx = new \Joel\wx\Wechat($options);
							$wx->sendTemplateMessage($templatedata);	//发送模板
							//============================================================
							//销量计算-只减不增
							$rsell=$this->doSells($order);
							//前端日志
							$mlog=M('Shop_order_log');
							$dlog['sid']=$sid;
							$dlog['oid']=$order['id'];
							$dlog['msg']='余额-付款成功';
							$dlog['ctime']=time();
							$rlog=$mlog->add($dlog);
							//后端日志
							$mlog=M('Shop_order_syslog');
							$dlog['type']=2;
							$rlog=$mlog->add($dlog);
							if($order['iscut'] && !$order['isgroup']){
								$this->success('余额付款成功！',U('Wap/jys/index',array('sid'=>$sid,'orderid'=>$order['id'],'isptg'=>"jys")));
							}else{
								$this->success('余额付款成功！',U('Wap/Fxshop/orderList',array('type'=>'2')));
							}
							//代收花生米计算-只减不增
							$rds=$this->doDs($order);
							
							//==通知该会员的上级==即将得到的佣金=========================================================
							$ds=M('fx_dslog')->where(array('oid'=>$order['id']))->find();		//查找将获得的佣金
							$vipuser=M('vip')->where(array('id'=>$ds['to']))->find(); //查找他的上级获取上级的openid
							$SET=M('Set')->find();
							$tp=new \bb\template();
							$array=array(
								'url'=>$SET['wxurl'].U('wap/fx/dslog'),
								'name'=>$ds['toname'],		//上级名字
								'fromname'=>$ds['fromname'],		//下级级名字
								'ordername'=>rtrim($itemsname,','),
								'money'=>$ds['fxprice'],		//商品价格
								'yj'=>$ds['fxyj']			//分销的佣金
							);
							$openid=$vipuser['openid'];		//发给的人
							$templatedata=$tp->enddata('collection',$openid,$array);	//组合模板数据
							$wx->sendTemplateMessage($templatedata);	//发送模板
							//=============================================================
							
						}else{
							//后端日志
							$mlog=M('Shop_order_syslog');
							$dlog['sid']=$sid;
							$dlog['oid']=$order['id'];
							$dlog['msg']='余额付款失败';
							$dlog['type']=-1;
							$dlog['ctime']=time();
							$rlog=$mlog->add($dlog);
							$this->error('余额付款失败！请联系客服！');
						}
						
					}else{
						//后端日志
						$mlog=M('Shop_order_syslog');
						$dlog['sid']=$sid;
						$dlog['oid']=$order['id'];
						$dlog['msg']='余额付款失败';
						$dlog['type']=-1;
						$dlog['ctime']=time();
						$this->error('余额支付失败，请重新尝试！');
					}
				}else{
					$this->error('余额不足，请使用其它方式付款！');
				}
				break;
			case 'alipaywap':
				$this->redirect(U('/Home/Alipaywap/pay',array('sid'=>$sid,'price'=>$order['payprice'],'oid'=>$order['oid'])));
				break;
			case 'wxpay':
				$_SESSION['wxpaysid']=$_SESSION['WAP']['vip']['sid'];
				$_SESSION['wxpayopenid']=$_SESSION['WAP']['vip']['openid'];//追入会员openid
				$this->redirect(U('/Home/Wxpay/pay',array('oid'=>$order['oid'])));
			break;
			default:
				$this->error('支付方式未知！');
				break;
		}
		
	}

	//销量计算
	private function doSells($order){
		$mgoods=M('Shop_goods');
		$msku=M('Shop_goods_sku');
		$mlogsell=M('Shop_syslog_sells');
		//封装dlog
		$dlog['oid']=$order['id'];
		$dlog['vipid']=$order['vipid'];
		$dlog['vipopenid']=$order['vipopenid'];
		$dlog['vipname']=$order['vipname'];
		$dlog['ctime']=time();
		$items=unserialize($order['items']);
		$tmplog=array();
		foreach($items as $k=>$v){
			//销售总量
			$dnum=$dlog['num']=$v['num'];
			if($v['skuid']){
				$rg=$mgoods->where('id='.$v['goodsid'])->setDec('num',$dnum);
				$rg=$mgoods->where('id='.$v['goodsid'])->setInc('sells',$dnum);
				$rg=$mgoods->where('id='.$v['goodsid'])->setInc('dissells',$dnum);
				$rs=$msku->where('id='.$v['skuid'])->setDec('num',$dnum);
				$rs=$msku->where('id='.$v['skuid'])->setInc('sells',$dnum);
				//sku模式
				$dlog['goodsid']=$v['goodsid'];
				$dlog['goodsname']=$v['name'];
				$dlog['skuid']=$v['skuid'];
				$dlog['skuattr']=$v['skuattr'];
				$dlog['price']=$v['price'];
				$dlog['num']=$v['num'];
				$dlog['total']=$v['total'];
			}else{
				$rg=$mgoods->where('id='.$v['goodsid'])->setDec('num',$dnum);
				$rg=$mgoods->where('id='.$v['goodsid'])->setInc('sells',$dnum);
				$rg=$mgoods->where('id='.$v['goodsid'])->setInc('dissells',$dnum);
				//纯goods模式
				$dlog['goodsid']=$v['goodsid'];
				$dlog['goodsname']=$v['name'];
				$dlog['skuid']=0;
				$dlog['skuattr']=0;
				$dlog['price']=$v['price'];
				$dlog['num']=$v['num'];
				$dlog['total']=$v['total'];
			}
			array_push($tmplog,$dlog);
		}
		if(count($tmplog)){
			$rlog=$mlogsell->addAll($tmplog);
		}
		return true;
	}

	//代收花生米计算
	public function doDs($order){
			//分销佣金计算
			$vipid=$order['vipid'];
			$mvip=M('vip');
			$vip=$mvip->where('id='.$vipid)->find();
			if(!$vip && !$vip['pid']){
				return FALSE;
			}
			//初始化 
			$pid=$vip['pid'];
			$mfxlog=M('fx_dslog');
			$shopset=M('Shop_set')->find();//追入商城设置
			$fxlog['oid']=$order['id'];
			$fxlog['fxprice']=$fxprice=$order['payprice']-$order['yf'];
			$fxlog['ctime']=time();
			$fx1rate=$shopset['vipfx1rate']/100;
			$fxtmp=array();//缓存3级数组
			if($pid){
				//第一层分销
				$fx1=$mvip->where('id='.$pid)->find();
				if($fx1['isfx'] && $fx1rate){
					$fxlog['fxyj']=$fxprice*$fx1rate;				
					$fxlog['from']=$vip['id'];
					$fxlog['fromname']=$vip['nickname'];
					$fxlog['to']=$fx1['id'];
					$fxlog['toname']=$fx1['nickname'];
					$fxlog['status']=1;
					//单层逻辑					
					//$rfxlog=$mfxlog->add($fxlog);
					//file_put_contents('Joel_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
					array_push($fxtmp,$fxlog);
				}
				
				//多层分销
				if(count($fxtmp)>=1){
					$refxlog=$mfxlog->addAll($fxtmp);
					if(!$refxlog){
						file_put_contents('Joel_fx_error.txt','错误日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'错误纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
					}
				}
												
			}
	return true;
	//逻辑完成
	}

	//添加到收藏夹
	public function addfav(){
		if(IS_POST){
			$vipid = self::$WAP['vipid'];
			$data=I('post.');
			$data['ctime']=time();
			$data['vipid']=$vipid;
			$m=M('vip_favorite');
			$r=$m->add($data);
			if($r){
				$info['status']=1;
				$info['msg']='ok';
			}else{
				$info['status']=0;
				$info['msg']='收藏失败,请重试~';
			}
			$this->ajaxReturn($info);
		}
	}
	//移除收藏
	public function removefav(){
		if(IS_POST){
			$vipid = self::$WAP['vipid'];
			$data=I('post.');
			$data['vipid']=$vipid;
			$m=M('vip_favorite');
			$r=$m->where($data)->delete();
			if($r){
				$info['status']=1;
				$info['msg']='ok';
			}else{
				$info['status']=0;
				$info['msg']='操作失败,请重试~';
			}
			$info['sql']=$m->getLastSql();
			$this->ajaxReturn($info);
		}
	}
	//验证是否收藏
	private function _checkfav($id){
		$vipid = self::$WAP['vipid'];
		$m=M('vip_favorite');
		return $m->where(array('vipid'=>$vipid,'goodsid'=>$id))->count();
	}

	//获取标签
	public function getlabel()
	{
		$mlabel=M("shop_label");
		$label=$mlabel->field("id,name")->select();
		$this->assign("label",$label);
	}
	public function label()
	{
		//追入渠道
		$sid=$_SESSION['WAP']['sid'];
		$this->assign('sid',$sid);
		$m=M('Shop_goods');
		$mlabel=M('Shop_label');
		
		//追入分组ID
		$id=I('id')?I('id'):$this->error('缺少标签ID');		
		
		$label=$mlabel->where('id='.$id)->find();
		if($label){
			// 作者：郑伊凡 2016-2-5 母版本 功能：标签图片
			$labelarr=$this->getPic($label['pic']);
			$label['imgurl']=$labelarr['imgurl'];
			// 作者：郑伊凡 2016-2-5 母版本 功能：标签图片
			$this->assign('label',$label);
		}else{
			$this->error('不存在此分类！');
		}
		$shopset=M('Shop_set')->find();
		$pagesize=$shopset['pagesize']?$shopset['pagesize']:5;
		$wap['lid']=$id;
		$wap['status']='1';
		$wap['iscut']='0';
		$wap['isgroup']='0';
		$goodslist=$m->where($wap)->order('id desc')->select();/*->page(1,$pagesize)*/
		$next=$m->where($wap)->page(2,$pagesize)->order('id desc')->select();
		if($next){
			$this->assign('nextpage',2);
		}		
		foreach($goodslist as $k=>$v){
			$ta=$this->getPic($goodslist[$k]['pic']);
			$goodslist[$k]['imgurl']=$ta['imgurl'];
		}
		
		//dump($goodslist);
		$this->assign('catecache',$goodslist);
		
		$this->display();
	}
	public function labelMore(){
		if(IS_AJAX){						
			$lid=I('lid');
			$page=I('page');
			if(!$lid){
				$info['status']=0;
				$info['msg']='未获取分类参数！';
				$this->ajaxReturn($info);
			}
			if(!$page){
				$info['status']=0;
				$info['msg']='未获取分页参数！';
				$this->ajaxReturn($info);
			}
			$shopset=M('Shop_set')->find();
			$pagesize=$shopset['pagesize']?$shopset['pagesize']:5;
			$m=M('Shop_goods');
			$goodslist=$m->where('lid='.$lid)->page($page,$pagesize)->order('id desc')->select();
			if($goodslist){
				foreach($goodslist as $k=>$v){
					$ta=$this->getPic($goodslist[$k]['pic']);
					$goodslist[$k]['imgurl']=$ta['imgurl'];
				}
				$info['status']=1;
				$info['msg']='加载成功！';
				$this->assign('catecache',$goodslist);
				$info['result']=$this->fetch();
			}else{
				$info['status']=0;
				$info['msg']='没有更多产品了！';
			}
			$this->ajaxReturn($info);
		}else{
			$info['status']=0;
			$info['msg']='非法访问！';
			$this->ajaxReturn($info);
		}
		
    }
}
