<?php

namespace bb;

class Template{
	
	
	public function __construct(){
		
	}
	
	/*
	 * $name 要发送的模板名称
	 * $openid 发给谁
	 * $data array 要传入的数据
	 */
	public function enddata($name,$openid,$array){
		$template=M('template')->where(array('name'=>$name))->find();
		$template_data=M('template_data')->where(array('data_id'=>$template['id']))->select();

		$josn['touser']=$openid;
		$josn['template_id'] = $template['template_id'];
		$url=$template['url'];
		foreach($array as $tk=>$tv){
			$url=str_replace("{".$tk."}", $tv,$url);  //替换传入的数组的面的数据
		}
		$josn['url'] = $url;
	    $josn['topcolor'] = $template['topcolor'];
		foreach($template_data as $k=>$v){
			$value=$v['data_value'];
			foreach($array as $tk=>$tv){
				$value=str_replace("{".$tk."}", $tv,$value);  //替换传入的数组的面的数据
			}
			$data['value']=$value;
			$data['color']=$v['data_color'];
			$josn['data'][$v['data_key']]=$data;
		}
		return $josn;
	}
}