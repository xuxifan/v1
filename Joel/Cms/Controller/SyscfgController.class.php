<?php
/**
 * 系统参数设置
 */
namespace Cms\Controller;
use Cms\Controller\BaseController;
class SyscfgController extends BaseController {
	public function _initialize() {
		//你可以在此覆盖父类方法
		parent::_initialize();
		//初始化两个配置
		self::$CMS['shopset'] = M('Shop_set') -> find();
		self::$CMS['vipset'] = M('Vip_set') -> find();
	}

	/**
	 * 系统关键参数设置
	 */
	public function index() {
		$bread = array('0' => array('name' => '系统设置', 'url' => U('Cms/syscfg/index')), '1' => array('name' => '参数设置', 'url' => U('Cms/syscfg/index')));
		$this -> assign('breadhtml', $this -> getBread($bread));
		$list = M('sys_config') -> select();
		$this -> assign('list', $list);
		$this -> display();
	}

	/**
	 * 保存
	 */
	public function set() {
		if (IS_POST) {
			$data = I('post.');
			//防止重复
			if ($data['id'] != '') {
				$result = M('sys_config') -> save($data);
				if ($result !== false) {
					$info['status'] = 1;
					$info['msg'] = '修改成功';
				} else {
					$info['status'] = 0;
					$info['msg'] = '操作失败';
				}
			} else {
				$co = M('sys_config') -> where("`key`='" . $data['key'] . "'") -> count();
				$info['s'] = M('sys_config')->getLastSql();
				if ($co > 0) {
					$info['status'] = 0;
					$info['msg'] = '键已存在!';
				} else {
					$data['ctime'] = time();
					$result = M('sys_config') -> add($data);
					if ($result !== false) {
						$info['status'] = 1;
						$info['msg'] = '添加成功';
					} else {
						$info['status'] = 0;
						$info['msg'] = '操作失败';
					}
				}

			}
			$this -> ajaxReturn($info);
		} else {
			$bread = array('0' => array('name' => '系统设置', 'url' => U('Cms/syscfg/index')), '1' => array('name' => '参数设置', 'url' => U('Cms/syscfg/index')));
			$this -> assign('breadhtml', $this -> getBread($bread));
			if (I('id')) {
				$data = M('sys_config') -> where('id=' . I('id')) -> find();
				$this -> assign('data', $data);
			}
			$this -> display();
		}
	}

	public function del() {
		if ($_GET['id']) {
			$result = M('sys_config') -> where('id=' . $_GET['id']) -> delete();
			if ($result) {
				$info['status'] = 1;
				$info['msg'] = '删除成功';
			} else {
				$info['status'] = 0;
				$info['msg'] = '操作失败';
			}
			$this -> ajaxReturn($info);
		}
	}

}
