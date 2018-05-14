<?php
namespace S\Controller;
use S\Controller\BaseController;
class VipController extends BaseController {
	
	public function _initialize() {
		//你可以在此覆盖父类方法	
		parent::_initialize();
	}
	
	
	public function vipList(){
		//设置面包导航，主加载器请配置
		$bread=array(
			'0'=>array(
				'name'=>'我的会员',
				'url'=>U('S/Vip/#')
			),
			'1'=>array(
				'name'=>'会员列表',
				'url'=>U('S/Vip/vipList')
			)
		);
		$this->assign('breadhtml',$this->getBread($bread));
		//绑定搜索条件与分页
		$m=M('vip');
		$p=$_GET['p']?$_GET['p']:1;
		$search=I('search')?I('search'):'';
		$stype=I('stype')?I('stype'):1;
		if($stype==1){
			if($search){
				$map['nickname']=array('like',"%$search%");
				$this->assign('search',$search);
			}			
			$this->assign('stype',1);
		}
		if($stype==3){
			if($search){
				$map['mobile']=array('like',"%$search%");
				$this->assign('search',$search);
			}			
			$this->assign('stype',3);
		}
		if($stype==2){
			if($search){
				$map['id']=array('eq',"$search");
				$this->assign('search',$search);
			}
			$this->assign('stype',2);
		}
		if($stype==4){
			$map['isfx']=array('eq',1);
			$this->assign('stype',3);
		}
		//追入分销商代码
		$map['sid']=self::$S['uid'];
		$psize=self::$S['set']['pagesize']?self::$S['set']['pagesize']:20;
		$cache=$m->where($map)->page($p,$psize)->select();
		foreach ($cache as $k=>$v) {
			$cache[$k]['levelname']=M('vip_level')->where('id='.$cache[$k]['levelid'])->getField('name');
		}
		$count=$m->where($map)->count();
		$this->getPage($count, $psize, 'Joel-loader', '会员列表','Joel-search');
		$this->assign('cache',$cache);		
		$this->display();
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