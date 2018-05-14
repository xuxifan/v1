<?php
namespace Cms\Controller;
use Cms\Controller\BaseController;
class VipController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
		$this->assign('useroath',$_SESSION['CMS']['user']['oath']);
	}
	
	
	public function set() {
		$m = M('vip_set');
		$data = $m->find();
		if (IS_POST) {
			$post = I('post.');
			if($post['isgift']==1){
				$post['gift_detail']=$post['gift_type'].",".$post['gift_money'].",".$post['gift_days'].",".$post['gift_usemoney'];
			}
			unset($post['gift_type']);
			unset($post['gift_money']);
			unset($post['gift_days']);
			unset($post['gift_usemoney']);
			$r = $data ? $m->where('id='.$data['id'])->save($post) : $m->add($post);
			if (FALSE!==$r) {
				$info['status']=1;
				$info['msg']='设置成功！';
			} else {
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info,"json");
		} else {
			//设置面包导航，主加载器请配置
			$bread=array(
				'0'=>array(
					'name'=>'会员中心',
					'url'=>U('Cms/Vip/#')
				),
				'1'=>array(
					'name'=>'会员设置',
					'url'=>U('Cms/Vip/set')
				)
			);
			$this->assign('breadhtml',$this->getBread($bread));
			$data = $m->find();
			if($data['isgift']==1) {
				$gift=explode(",", $data['gift_detail']);
				$data['gift_type']=$gift[0];
				$data['gift_money']=$gift[1];
				$data['gift_days']=$gift[2];
				$data['gift_usemoney']=$gift[3];
			}
			$this->assign('data',$data);
			$this->display();
		}
	}
	
	public function vipList(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'会员中心',
				'url'=>U('Cms/Vip/#')
			),
			'1'=>array(
				'name'=>'会员列表',
				'url'=>U('Cms/Vip/vipList')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('vip');
		$p=$_GET['p']?$_GET['p']:1;
		$this->assign('p',$p);
		$search=I('search')?I('search'):'';
		$stype=I('stype')?I('stype'):1;
		if($stype==1){
			if($search){
				$map['nickname']=array('like',"%$search%");
				$map['mobile']=array('like',"%$search%");
				$map['_logic'] = 'OR';
				$this->assign('search',$search);
			}			
			$this->assign('stype',1);
		}
		if($stype==2){
			if($search){
				$map['id']=array('eq',"$search");
				$this->assign('search',$search);
			}
			$this->assign('stype',2);
		}
		if($stype==3){
			if($search){
				$map['sid']=array('eq',"$search");
				$this->assign('search',$search);
			}else{
				$map['sid']=array('eq',0);
				$this->assign('search',0);
			}
			$this->assign('stype',3);
		}
		if($stype==4){
			$map['isfx']=array('eq',1);
			$this->assign('stype',3);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach ($cache as $k=>$v) {
			$cache[$k]['levelname']=M('vip_level')->where('id='.$cache[$k]['levelid'])->getField('name');
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '会员列表','Joel-search');
		$this->assign('cache',$cache);
		$this->assign('count',$count);
		$this->display();
	}

    public function vipUp(){
		$map['id']=I('id');//取父ID
		$cache=M('Vip')->where($map)->find();
		$this->assign('cache',$cache);
		$mb=$this->fetch();
		$this->ajaxReturn($mb);
	}
	
	public function vipDown(){
		$map['pid']=I('id');//取自己ID
		$cache=M('Vip')->where($map)->select();
		$this->assign('cache',$cache);
		$mb=$this->fetch();
		$this->ajaxReturn($mb);
	}
	
	//CMS后台商品设置
	public function vipSet(){
		$id=I('id');
		$m=M('Vip');
		//dump($m);
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'会员中心',
				'url'=>U('Cms/Vip/#')
			),
			'1'=>array(
				'name'=>'会员列表',
				'url'=>U('Cms/Vip/vipList')
			),
			'1'=>array(
				'name'=>'会员编辑',
				'url'=>U('Cms/Vip/vipSet',array('id'=>$id))
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			//die('aa');
			$data=I('post.');
			if($id){
				//追入操作员日志
				$old=$m->where('id='.$id)->find();
				if(!$old){
					$info['status']=1;
					$info['msg']='未取得原始会员信息！';
					$this->ajaxReturn($info);
				}
				$adlog['uid']=$_SESSION['CMS']['uid'];
				$adlog['admin']=$_SESSION['CMS']['user']['username'];
				$adlog['vipid']=$old['id'];
				$adlog['vip']=$old['nickname'];
				$adlog['ip']=get_client_ip();
				$adlog['ctime']=time();
				if($data['isfx']<>$old['isfx']){
					$adlog['isfx']=$data['isfx']?"开启":"关闭";
				}else{
					$adlog['isfx']="未操作";
				}
				$adlog['score']=$old['score'].' => '.$data['score'];
				$adlog['money']=$old['money'].' => '.$data['money'];
				if($old['score']<>$data['score']){
					$adlog['event']='积分变动。';
				};
				if($old['money']<>$data['money']){
					$adlog['event']=$adlog['event'].'金钱变动。';
				};
				if(($old['score']==$data['score'])&&($old['money']==$data['money'])){
					$adlog['event']='数据未改动！';
				}
				$radlog=M('Adminlog_vip')->add($adlog);
				$re=$m->save($data);
				if(FALSE!==$re){
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				$info['status']=0;
				$info['msg']='未获取会员ID！';
			}
			$this->ajaxReturn($info);
		}
		$this->assign('p',I('p'));
		//处理编辑界面
		if($id){			
			$cache=$m->where('id='.$id)->find();
			$this->assign('cache',$cache);	
		}else{
			$info['status']=0;
			$info['msg']='未获取会员ID！';
			$this->ajaxReturn($info);
		}	
		$this->display();
	}
	
	public function message(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'会员中心',
				'url'=>U('Cms/Vip/#')
			),
			'1'=>array(
				'name'=>'消息管理',
				'url'=>U('Cms/Vip/message')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('vip_message');
		$p=$_GET['p']?$_GET['p']:1;
		$search=I('search')?I('search'):'';
		if($search){
			$map['title']=array('like',"%$search%");
			$this->assign('search',$search);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->order('id desc')->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '消息管理','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	public function messageSet(){
		$id=I('id');
		$m=M('vip_message');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'会员中心',
				'url'=>U('Cms/Vip/#')
			),
			'1'=>array(
				'name'=>'消息管理',
				'url'=>U('Cms/Vip/message')
			),
			'2'=>array(
				'name'=>'消息设置',
				'url'=>$id?U('Cms/Vip/messageSet',array('id'=>$id)):U('Cms/Vip/messageSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			$data=I('post.');
			$data['ctime']=time();
			if($id){
				$re=$m->save($data);
				if(FALSE!==$re){
					$info['status']=1;
					$info['msg']='设置成功！';
				}else{
					$info['status']=0;
					$info['msg']='设置失败！';
				}
			}else{
				$re=$m->add($data);
				if($re){
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
		if(I('pids')){
			$cache['pids']=I('pids');
			$this->assign('cache',$cache);	
		}
		$this->display();
	}

	public function mailSet(){
		$pids=I('pids');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'会员中心',
				'url'=>U('Cms/Vip/#')
			),
			'1'=>array(
				'name'=>'会员列表',
				'url'=>U('Cms/Vip/viplist')
			),
			'2'=>array(
				'name'=>'发送邮件',
				'url'=>U('Cms/Vip/messageSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			$m=M('vip');
			$data=I('post.');
			$id_arr=explode(',', $data['pids']);
			foreach ($id_arr as $k=>$v) {
				$mail_addr=$m->where('id='.$v)->getField('email');
				if ($mail_addr!='') {
					think_send_mail($mail_addr,'会员',$data['title'],$data['content']);
				}
			}
			
			$info['status']=1;
			$info['msg']=' 发送成功！';

			$this->ajaxReturn($info);
		}
		$this->assign('pids',$pids);
		$this->display();
	}
	
	public function messageDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('vip_message');
		if(!id){
			$info['status']=0;
			$info['msg']='ID不能为空!';
			$this->ajaxReturn($info);
		}
		$re=$m->delete($id);
		if($re){
			//删除消息浏览记录
			M('vip_log')->where('type=5 and opid in ('.$id.')')->delete();
			$info['status']=1;
			$info['msg']='删除成功!';
		}else{
			$info['status']=0;
			$info['msg']='删除失败!';
		}
		$this->ajaxReturn($info);
	}
	
	public function card(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'会员中心',
				'url'=>U('Cms/Vip/#')
			),
			'1'=>array(
				'name'=>'卡券列表',
				'url'=>U('Cms/Vip/card')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$this->assign('pwd','pwd');
		$this->assign('status',$status);
		$m=M('vip_card');
		$p=$_GET['p']?$_GET['p']:1;
		$search=I('search')?I('search'):'';
		if($search){
			$map['cardno']=array('like',"%$search%");
			$this->assign('search',$search);
		}
		$type=I('type');
		if($type){
			$map['type']=$type;
			$this->assign('type',$type);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->order('id desc')->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '卡券列表','Joel-search');
		$this->assign('cache',$cache);
		$this->display();
	}
	
	public function cardSet(){
		$m=M('vip_card');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'会员中心',
				'url'=>U('Cms/Vip/#')
			),
			'1'=>array(
				'name'=>'充值卡列表',
				'url'=>U('Cms/Vip/card')
			),
			'2'=>array(
				'name'=>'充值卡设置',
				'url'=>U('Cms/Vip/cardSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			$data=I('post.');
			$data['ctime']=time();
			if($data['type']==1){
				$data['status'] = 1;
			}
			if($data['usetime']!='') {
				$timeArr=explode(" - ", $data['usetime']);
				$data['stime']=strtotime($timeArr[0]);
				$data['etime']=strtotime($timeArr[1]);
			}
			$num = $data['num'];
			unset($data['usetime']);
			unset($data['num']);
			for($i=0;$i<$num;$i++){
				$cardnopwd = $this->getCardNoPwd();
				$data['cardno'] = $cardnopwd['no'];
				$data['cardpwd'] = $cardnopwd['pwd'];
				$r = $m -> add($data);
			}
			if($r){
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		} else {
			$this->display();
		}
		
	}

	public function cardDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('vip_card');
		if(!id){
			$info['status']=0;
			$info['msg']='ID不能为空!';
			$this->ajaxReturn($info);
		}
		$re=$m->delete($id);
		if($re){
			$info['status']=1;
			$info['msg']='删除成功!';
		}else{
			$info['status']=0;
			$info['msg']='删除失败!';
		}
		$this->ajaxReturn($info);
	}
	
	private function getCardNoPwd(){  
		$dict_no = "0123456789";
		$length_no = 10;
		$dict_pwd = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$length_pwd = 10;
		$card['no'] = "";
		$card['pwd'] = "";
		for($i=0;$i<$length_no;$i++){    $card['no'].=$dict_no[rand(0,(strlen($dict_no)-1))];    } 
		for($i=0;$i<$length_pwd;$i++){    $card['pwd'].=$dict_pwd[rand(0,(strlen($dict_pwd)-1))];    }   
		return $card;
	}
	
	public function sendCard(){
		$post=I('post.');
		$m=M('vip_card');
		if($post['vipid']==''){
			$info['status']=0;
			$info['msg']='请输入发送会员ID！';
			$this->ajaxReturn($info);
		}
		if(!M('vip')->where('id='.$post['vipid'])->find()){
			$info['status']=0;
			$info['msg']='该会员不存在！';
			$this->ajaxReturn($info);
		}
		$data['vipid']=$post['vipid'];
		$data['status']=1;
		$re=$m->where('id='.$post['cardid'])->save($data);
		if($re){
			$info['status']=1;
			$info['msg']='发送成功!';
		}else{
			$info['status']=0;
			$info['msg']='发送失败!';
		}
		$this->ajaxReturn($info);
	}
	
	//CMS后台会员等级列表
	public function level(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'会员中心',
				'url'=>U('Cms/Vip/#')
			),
			'1'=>array(
				'name'=>'分组列表',
				'url'=>U('Cms/Vip/level')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('Vip_level');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
		if($name){
			$map['name']=array('like',"%$name%");
			$this->assign('name',$name);
		}
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->order('exp')->page($p,$psize)->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '分组列表','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	
	//CMS后台会员等级设置
	public function levelSet(){
		$id=I('id');
		$m=M('vip_level');
		//设置面包导航，主加载器请配置		
		$bread=array(
			'0'=>array(
				'name'=>'会员中心',
				'url'=>U('Cms/Vip/#')
			),
			'1'=>array(
				'name'=>'分组列表',
				'url'=>U('Cms/Vip/level')
			),
			'2'=>array(
				'name'=>'分组设置',
				'url'=>$id?U('Cms/Vip/levelSet',array('id'=>$id)):U('Cms/Vip/levelSet')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//处理POST提交	
		if(IS_POST){
			$data=I('post.');
			$re=$id?$m->save($data):$m->add($data);
			if(FALSE!==$re){
				$info['status']=1;
				$info['msg']='设置成功！';
			}else{
				$info['status']=0;
				$info['msg']='设置失败！';
			}
			$this->ajaxReturn($info);
		} else {
			if($id){			
				$cache=$m->where('id='.$id)->find();
				$this->assign('cache',$cache);	
			}	
			$this->display();
		}
	}
	
	public function levelDel(){
		$id=$_GET['id'];//必须使用get方法
		$m=M('Vip_level');
		if(!id){
			$info['status']=0;
			$info['msg']='ID不能为空!';
			$this->ajaxReturn($info);
		}
		$re=$m->delete($id);
		if($re){
			$info['status']=1;
			$info['msg']='删除成功!';
		}else{
			$info['status']=0;
			$info['msg']='删除失败!';
		}
		$this->ajaxReturn($info);
	}
	
	public function cardExport() {
		$id=I('id');
		$type=I('type');
		if ($id) {
			$map['id']=array('in',$id);
		} else {
			$map['type']=$type;
		}
		$data=M('vip_card')->where($map)->field('id,type,cardno,cardpwd,status')->select();
		foreach($data as $k=>$v){
			switch ($v['type']) {
				case 1:$data[$k]['type']="充值卡";break;
				case 2:$data[$k]['type']="代金券";break;
			}
			switch ($v['status']) {
				case 0:$data[$k]['status']="可制作";break;
				case 1:$data[$k]['status']="已发放";break;
				case 2:$data[$k]['status']="已使用";break;
			}
		}
		$title=array('id','类型','卡号','卡密','状态');
		$this->exportexcel($data,$title,'卡券数据');
	}

	//CMS后台Vip提现订单
	public function txorder(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'会员中心',
				'url'=>U('Cms/Vip/#')
			),
			'1'=>array(
				'name'=>'提现订单',
				'url'=>U('Cms/Vip/txorder')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));		
		$status=I('status');
		if($status || $status=='0'){
			$map['status']=$status;
		}
		$this->assign('status',$status);
		//绑定搜索条件与分页
		$m=M('Vip_tx');
		$p=$_GET['p']?$_GET['p']:1;
		$name=I('name')?I('name'):'';
			$stype=I('stype')?I('stype'):'';
		if($stype==1){
				if($name){
					$map['vipid']=array('eq',"$name");
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
		$psize=self::$CMS['set']['pagesize']?self::$CMS['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->order('id desc')->select();
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '会员提现订单','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
	}
	public function txorderOk(){
		$arr=array_filter(explode(',', $_GET['id']));//必须使用get方法
		$m=M('Vip_tx');
		$mlog=M('Vip_message');
		$err=TRUE;
		foreach($arr as $k=>$v){
			if($v){
				$old=$m->where('id='.$v)->find();
				$old['status']=2;
				$old['txtime']=time();
				$rv=$m->save($old);
				if($rv!==FALSE){
					$data_msg['pids']=$old['vipid'];
					$data_msg['title']="提现已完成！".$old['txprice']."元已成功退回您的提现帐户！";
					$data_msg['content']="提现订单编号：".$old['id']."<br><br>提现申请金额：".$old['txprice']."<br><br>提现完成时间：".date('Y-m-d H:i',$old['txtime'])."<br><br>您的提现申请已完成，如有异常请联系客服！";
					$data_msg['ctime']=time();
					$rmsg=$mlog->add($data_msg);
				}else{
					$err=FALSE;
				}
			}else{
				$err=FALSE;
			}	
		}
		if($err){
			$info['status']=1;
			$info['msg']='批量设置成功!';
		}else{
			$info['status']=0;
			$info['msg']='批量设置可能存在部分失败，请刷新后重新尝试!';
		}
		$this->ajaxReturn($info);
	}
	
	public function txorderCancel(){
		$id=I('id');
		if(!$id){
			$info['status']=0;
			$info['msg']='未正常获取ID数据！';
			$this->ajaxReturn($info);
		}
		$m=M('Vip_tx');
		$mvip=M('Vip');
		$mlog=M('Shop_order_log');
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
		$vip=$mvip->where('id='.$old['vipid'])->find();
		if(!$vip){
			$info['status']=0;
			$info['msg']='未正常获取相关会员信息！';
			$this->ajaxReturn($info);
		}
		$rold=$m->where('id='.$id)->setField('status',0);
		if($rold!==FALSE){
			$rvip=$mvip->where('id='.$old['vipid'])->setInc('money',$old['txprice']);
			if($rvip){
				$data_msg['pids']=$vip['id'];
				$data_msg['title']="提现申请未通过审核！".$old['txprice']."元已成功退回您的帐户余额！";
				$data_msg['content']="提现订单编号：".$old['id']."<br><br>提现申请金额：".$old['txprice']."<br><br>提现退回时间：".date('Y-m-d H:i',time())."<br><br>您的提现申请未通过审核，如有疑问请联系客服！";
				$data_msg['ctime']=time();
				$rmsg=M('Vip_message')->add($data_msg);
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
		$data=M('Vip_tx')->where($map)->select();
		foreach($data as $k=>$v){
			switch ($v['status']) {
				case 0:$data[$k]['status']="提现失败";break;
				case 1:$data[$k]['status']="新申请";break;
				case 2:$data[$k]['status']="提现完成";break;
			}
			$data[$k]['txsqtime']=date('Y-m-d H:i:s',$v['txsqtime']);
			$data[$k]['txtime']=$v['txtime']?date('Y-m-d H:i:s',$v['txtime']):'未执行';
		}
		$title=array('ID','会员ID','提现金额','提现姓名','提现电话','提现银行','提现分行','提现银行所在地','提现银行卡卡号','提现申请时间','提现完成时间','订单状态');
		$this->exportexcel($data,$title,$tt.'订单'.date('Y-m-d H:i:s',time()));
	}
	//导出会员信息
	public function vipExport() {
		$id=I('id');
		$count=I('count');
		$sql="SELECT id,mobile,NAME,total_buy,money,score,EXP,nickname,sex,city,province,subscribe,subscribe_time,ctime,txszd,total_xxlink AS xx
			FROM joel_vip a limit ".$count.',10000';
		if($id){
			$sql=$sql." where a.id in (".$id."0)";
		}
		$data=M()->query($sql);
		foreach($data as $k=>$v){
			$data[$k]['nickname']=str_replace('"',"“",$data[$k]['nickname']);
			$data[$k]['subscribe']=($data[$k]['subscribe']==1?'是':'否');
			$data[$k]['ctime']=$v['ctime']?date('Y-m-d H:i:s',$v['ctime']):'';
			$data[$k]['subscribe_time']=$v['subscribe_time']?date('Y-m-d H:i:s',$v['subscribe_time']):'';
		}
		$title=array('会员ID',
					'真实电话',
					'真实姓名',
					'消费金额',
					'余额(元)',
					'积分',
					'经验',
					'微信昵称',
					'性别',
					'城市',
					'省份',
					'是否关注',
					'关注时间',
					'首次访问时间',
					'邮寄地址',
					'下线人数',
//					'下线关注次数'//,
					//'下线取消关注次数'//,
					//'下线购买次数'
					);
		$this->exportexcel($data,$title,'会员数据'.$count.'-'.($count+10000).' '.date('Y-m-d H:i:s',time()));
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