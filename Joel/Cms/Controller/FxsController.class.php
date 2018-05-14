<?php
// +----------------------------------------------------------------------
// | 用户后台基础类--CMS分组商城管理类
// +----------------------------------------------------------------------
// | JoelCMS V1.0 Beta
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.JoelCMS.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Joel <2094157689@qq.com>
// +----------------------------------------------------------------------
namespace Cms\Controller;
use Cms\Controller\BaseController;
class FxsController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();	
	}
	
	//CMS后台商城分类
	public function user(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'分销商首页',
				'url'=>U('Cms/Fxs/index')
			),
			'1'=>array(
				'name'=>'分销商管理',
				'url'=>U('Cms/Fxs/user')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Fxs_user');
		$p=$_GET['p']?$_GET['p']:1;
		$search=I('search')?I('search'):'';
		$stype=I('stype')?I('stype'):1;
		if($stype==1){
			if($search){
				$map['nickname']=array('like',"%$search%");
				$this->assign('name',$search);
			}			
			$this->assign('stype',1);
		}
		if($stype==2){
			if($search){
				$map['id']=array('eq',"$search");
				$this->assign('name',$search);
			}
			$this->assign('stype',2);
		}
		if($stype==3){
			$map['pid']=array('eq',$search);
			$mappid=$search;
			$this->assign('stype',3);
		}
		if($map){
			if($stype==3){
				$field=array("id","pid","lv","no","nickname","username","mobile","total_xxyj","total_xxlink","total_xxsub","total_xxbuy","sonnum","soncate","ctime","sorts","qrticket","status","concat(path,'-',id) as bpath");
				$cache=joelTree($m, $mappid, $field);
			}else{
				$cache=$m->where($map)->select();
			}
			
		}else{
			$field=array("id","pid","lv","no","nickname","username","mobile","total_xxyj","total_xxlink","total_xxsub","total_xxbuy","sonnum","soncate","ctime","sorts","qrticket","status","concat(path,'-',id) as bpath");
				$cache=joelTree($m, 0, $field);
		}
		//JoelTree快速无限分类
		
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台商城分类设置
	public function userSet(){
		$id=I('id');
		$m=M('Fxs_user');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'分销商首页',
				'url'=>U('Cms/Fxs/index')
			),
			'1'=>array(
				'name'=>'分销商管理',
				'url'=>U('Cms/Fxs/user')
			),
			'2'=>array(
				'name'=>'分销商设置',
				'url'=>$id?U('Cms/Fxs/userSet',array('id'=>$id)):U('Cms/Fxs/userSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			$isname=$m->where(array('username'=>$data['username']))->find();
			if($isname){
				$info['status']=0;
				$info['msg']='此登陆用户名已存在！请重新设置！';
				$this->ajaxReturn($info);
			}
			if($id){
				//保存时判断
				$old=$m->where('id='.$id)->limit(1)->find();
				if($old['pid']<>$data['pid']){
					$hasson=$m->where('pid='.$id)->limit(1)->find();
					if($hasson){
						$info['status']=0;
						$info['msg']='此分销商下有子分销商，不可以移动！';
						$this->ajaxReturn($info);
					}
				}
				if($data['pid']){
					$father=$m->where('id='.$old['pid'])->find();
					//不允许超过上线最大数
					if($father['sonnum']<$data['sonnum']){
						$info['status']=0;
						$info['msg']='此分销商下线总数不可以超过'.$old['sonnum'].'个！';
						$this->ajaxReturn($info);
					}
				}
				
				if($data['pid']){					
					//更新Path，强制处理
					$path=setPath($m, $data['pid']);
					$data['path']=$path['path'];
					$data['lv']=$path['lv'];
				}else{
					$data['path']=0;
					$data['lv']=1;
				}
				//重新生成二维码
				if(!$old['qrticket']){
					$options['appid']=self::$SYS['set']['wxappid'];
					$options['appsecret']=self::$SYS['set']['wxappsecret'];
					$wx=new \Joel\wx\Wechat($options);
					$rqr=$wx->getQRCode($data['id'],1);
					if($rqr){
						$data['qrticket']=$rqr['ticket'];
						$data['qrurl']=$rqr['url'];
					}
				}
				
				//修改分销商密码
				if($data['userpass']){
					$data['userpass']=md5($data['userpass']);
				}else{
					unset($data['userpass']);
				}
			
				
				$re=$m->save($data);
				if(FALSE!==$re){
					//更新新老父级，暂不做错误处理
					if($old['pid']<>$data['pid']){
						$re=setSoncate($m, $data['pid']);
						$rold=setSoncate($m, $old['pid']);						
						$info['status']=1;
						$info['msg']=$old['pid'];
						$this->ajaxReturn($info);
					}else{
						$re=setSoncate($m, $data['pid']);
					}
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				//新增状态
				//处理下线
				if($data['pid']){
					//新增时处理最大3层逻辑
					$father=$m->where('id='.$data['pid'])->limit(1)->find();
					//不允许超过3级
					if($father['lv']>=3){
						$info['status']=0;
						$info['msg']='系统定义分销商层级不允许超过3层！';
						$this->ajaxReturn($info);
					}
					//不得超过上级下线
					if($father['sonnum']<$data['sonnum']){
						$info['status']=0;
						$info['msg']='此分销商的允许下线数量不允许超过'.$father['sonnum'].'个！';
						$this->ajaxReturn($info);
					}
					//
					$fatherson=$m->where(array('pid'=>$data['pid']))->count();
					if(($fatherson+1)>$father['sonnum']){
						$info['status']=0;
						$info['msg']='当前父分销商最多只能建立'.$father['sonnum'].'个分销商！';
						$this->ajaxReturn($info);
					}
					
				}
							
				if($data['pid']){
					//更新父级，强制处理
					$path=setPath($m, $data['pid']);
					$data['path']=$path['path'];
					$data['lv']=$path['lv'];
				}else{
					$data['path']=0;
					$data['lv']=1;
				}
				//处理下线密码
				$data['userpass']=md5($data['userpass']);
				//处理下线生成时间
				$data['ctime']=time();
				$re=$m->add($data);
				if($re){
					//生成二维码
					$options['appid']=self::$SYS['set']['wxappid'];
					$options['appsecret']=self::$SYS['set']['wxappsecret'];
					$wx=new \Joel\wx\Wechat($options);
					$rqr=$wx->getQRCode($re,1);
					if($rqr){
						$r=$m->where('id='.$re)->setField('qrticket',$rqr['ticket']);
						$rr=$m->where('id='.$re)->setField('qrurl',$rqr['url']);
					}					
					//更新父级，暂不做错误处理
					if($data['pid']){
						$re=setSoncate($m, $data['pid']);
					}
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}
			$this->ajaxReturn($info);
		}
		//处理编辑界面
		if($id){			
			$cache=$m->where('id='.$id)->find();
			$this->assign('cache',$cache);	
		}
		//JoelTree快速无限分类
		$field=array("id","pid","lv","no","nickname","username","mobile","total_xxyj","total_xxlink","total_xxsub","total_xxbuy","sonnum","soncate","status","concat(path,'-',id) as bpath");
		$cate=joelTree($m, 0, $field);
		$this->assign('cate',$cate);
		$this->display();
	}

	//CMS后台Vip提现订单
	public function txorder(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'分销商管理',
				'url'=>U('Cms/Fxs/#')
			),
			'1'=>array(
				'name'=>'分销商提现订单',
				'url'=>U('Cms/Fxs/txorder')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));		
		$status=I('status');
		if($status || $status=='0'){
			$map['status']=$status;
		}
		$this->assign('status',$status);
		//绑定搜索条件与分页
		$m=M('Fxs_tx');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
			$stype=I('stype')?I('stype'):'';
		if($stype==1){
				if($name){
					$map['sid']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',1);
			}
			if($stype==2){
				if($name){
					$map['txname']=array('eq',"$name");
					$this->assign('name',$name);
				}
				$this->assign('stype',2);
			}
		$map['status']=$status;
		$psize=self::$SYS['set']['pagesize']?self::$SYS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('id desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '分销商提现订单','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
//	public function txorderOk(){
//		$arr=array_filter(explode(',', $_GET['id']));//必须使用get方法
//		$m=M('Fxs_tx');
//		foreach($arr as $k=>$v){
//			if($v){
//				$old=$m->where('id='.$v)->find();
//				$old['status']=2;
//				$old['txtime']=time();
//				$rv=$m->save($old);
//				if($rv==FALSE){
//					$err=FALSE;
//				}
//			}else{
//				$err=FALSE;
//			}	
//		}
//		$err=TRUE;
//		if($err){
//			$info['status']=1;
//			$info['msg']='批量设置成功!';
//		}else{
//			$info['status']=0;
//			$info['msg']='批量设置可能存在部分失败，请刷新后重新尝试!';
//		}
//		$this->ajaxReturn($info);
//	}
	
		public function txorderOk(){
			$id=I('id');
			if(!$id){
				$info['status']=0;
				$info['msg']='未正常获取ID数据！';
				$this->ajaxReturn($info);
			}
			$m=M('Fxs_tx');
			$mvip=M('Fxs_user');
			$old=$m->where('id='.$id)->find();
			if(!$old){
				$info['status']=0;
				$info['msg']='未正常获取提现订单数据！';
				$this->ajaxReturn($info);
			}
			if($old['status']<>1){
				$info['status']=0;
				$info['msg']='只可以操作新申请订单！';
				$this->ajaxReturn($info);
			}
			$vip=$mvip->where('id='.$old['sid'])->find();
			if(!$vip){
				$info['status']=0;
				$info['msg']='未正常获取相关分销商信息！';
				$this->ajaxReturn($info);
			}
			$old['status']=2;
			$old['txtime']=time();
			$rold=$m->save($old);
			if($rold!==FALSE){
				$info['status']=1;
				$info['msg']='此提现请求已完成！';
				$this->ajaxReturn($info);
			}else{
				$info['status']=0;
				$info['msg']='操作失败，请重新尝试！';
				$this->ajaxReturn($info);
			}
		}
	
	public function txorderCancel(){
		$id=I('id');
		if(!$id){
			$info['status']=0;
			$info['msg']='未正常获取ID数据！';
			$this->ajaxReturn($info);
		}
		$m=M('Fxs_tx');
		$mvip=M('Fxs_user');
		$old=$m->where('id='.$id)->find();
		if(!$old){
			$info['status']=0;
			$info['msg']='未正常获取提现订单数据！';
			$this->ajaxReturn($info);
		}
		if($old['status']<>1){
			$info['status']=0;
			$info['msg']='只可以操作新申请订单！';
			$this->ajaxReturn($info);
		}
		$vip=$mvip->where('id='.$old['sid'])->find();
		if(!$vip){
			$info['status']=0;
			$info['msg']='未正常获取相关分销商信息！';
			$this->ajaxReturn($info);
		}
		$rold=$m->where('id='.$id)->setField('status',0);
		if($rold!==FALSE){
			$rvip=$mvip->where('id='.$old['sid'])->setInc('money',$old['txprice']);
			if($rvip){
				$info['status']=1;
				$info['msg']='取消提现申请成功！提现金额已自动退回用户帐户余额！';
				$this->ajaxReturn($info);
			}else{
				$info['status']=0;
				$info['msg']='取消成功，但自动退款至用户余额失败，请联系此会员！';
				$this->ajaxReturn($info);
			}
		}else{
			$info['status']=0;
			$info['msg']='操作失败，请重新尝试！';
			$this->ajaxReturn($info);
		}
	}

	public function txorderExport() {
			$id=I('id');
			$status=I('status');
			if ($id) {
				$map['id']=array('in',$id);
			} else {
				$map['status']=$status;
			}
			
			switch ($status) {
					case 0:$tt="提现失败";break;
					case 1:$tt="新申请";break;
					case 2:$tt="提现完成";break;
			}
			$data=M('Fxs_tx')->where($map)->select();
			foreach($data as $k=>$v){
				switch ($v['status']) {
					case 0:$data[$k]['status']="提现失败";break;
					case 1:$data[$k]['status']="新申请";break;
					case 2:$data[$k]['status']="提现完成";break;
				}
				$data[$k]['txsqtime']=date('Y-m-d H:i:s',$v['txsqtime']);
				$data[$k]['txtime']=$v['txtime']?date('Y-m-d H:i:s',$v['txtime']):'未执行';
			}
			$title=array('ID','分销商ID','提现金额','提现姓名','提现电话','提现银行','提现分行','提现银行所在地','提现银行卡卡号','提现申请时间','提现完成时间','订单状态');
			$this->exportexcel($data,$title,$tt.'订单'.date('Y-m-d H:i:s',time()));
		}
		
		/**
		 * 导出数据为excel表格
		 *@param $data    一个二维数组,结构如同从数据库查出来的数组
		 *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
		 *@param $filename 下载的文件名
		 *@examlpe
		 $stu = M ('User');
		 $arr = $stu -> select();
		 exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
		 */
		private function exportexcel($data = array(), $title = array(), $filename = 'report') {
			header("Content-type:application/octet-stream");
			header("Accept-Ranges:bytes");
			header("Content-type:application/vnd.ms-excel");
			header("Content-Disposition:attachment;filename=" . $filename . ".xls");
			header("Pragma: no-cache");
			header("Expires: 0");
			//导出xls 开始
			if (!empty($title)) {
				foreach ($title as $k => $v) {
					$title[$k] = iconv("UTF-8", "GB2312", $v);
				}
				$title = implode("\t", $title);
				echo "$title\n";
			}
			if (!empty($data)) {
				foreach ($data as $key => $val) {
					foreach ($val as $ck => $cv) {
						$data[$key][$ck] = iconv("UTF-8", "GB2312", $cv);
					}
					$data[$key] = implode("\t", $data[$key]);
	
				}
				echo implode("\n", $data);
			}
	
		}
	
}