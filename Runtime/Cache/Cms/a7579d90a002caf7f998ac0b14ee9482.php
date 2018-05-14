<?php if (!defined('THINK_PATH')) exit();?><div class="row">
	<div class="col-xs-12 col-md-12">
		<div class="widget">
			<div class="widget-header bg-blue">
				<i class="widget-icon fa fa-arrow-down"></i>
				<span class="widget-caption">会员列表</span>
				<div class="widget-buttons">
					<a href="#" data-toggle="maximize">
						<i class="fa fa-expand"></i>
					</a>
					<a href="#" data-toggle="collapse">
						<i class="fa fa-minus"></i>
					</a>
					<a href="#" data-toggle="dispose">
						<i class="fa fa-times"></i>
					</a>
				</div>
			</div>
			<div class="widget-body">
				<div class="table-toolbar">
					<div class="pull-left">
						<button href="#" class="btn btn-primary" id="sendMsg">
							<i class="fa fa-comment-o"></i>发送消息
						</button>
						<select class="input-sm" id="expcount">
							<?php $__FOR_START_11913__=0;$__FOR_END_11913__=$count;for($i=$__FOR_START_11913__;$i < $__FOR_END_11913__;$i+=10000){ ?><option value="<?php echo ($i); ?>"><?php echo ($i); ?>~<?php echo ($i+10000); ?></option><?php } ?>
						</select>
						<!--<button href="#" class="btn btn-sky" id="sendMail">
							<i class="fa fa-envelope-o"></i>发送邮件
						</button>-->
						<button href="javascript:void(0)" class="btn btn-primary" id="exportVip"><i class="fa fa-save"></i>导出会员数据</button>
						<a href="#" class="hide" id="sendMsgbtn" data-loader="Joel-loader" data-loadername="会员消息"></a>
						<a href="#" class="hide" id="sendMailbtn" data-loader="Joel-loader" data-loadername="发送邮件"></a>
					</div>
					<div class="pull-right">
						<form id="Joel-search">
							<label><select name="stype" class="form-control input-sm"><option value='1' <?php if(($stype) == "1"): ?>selected<?php endif; ?>>按会员昵称或手机号【模糊】</option><option value='2' <?php if(($stype) == "2"): ?>selected<?php endif; ?>>按会员ID</option><option value='3' <?php if(($stype) == "3"): ?>selected<?php endif; ?>>按渠道ID</option><option value='4' <?php if(($stype) == "4"): ?>selected<?php endif; ?>>所有分销提成VIP</option></select></label>
							<label><input name="search" type="search" class="form-control input-sm" placeholder="请输入搜索条件"></label>
							<a href="<?php echo U('Cms/Vip/viplist/');?>" class="btn btn-success" data-loader="Joel-loader" data-loadername="会员列表" data-search="Joel-search">
								<i class="fa fa-search"></i>搜索
							</a>
						</form>
					</div>
				</div>

				<table id="Joel-table" class="table table-bordered table-hover">
					<thead class="bordered-darkorange">
						<tr role="row">
							<th width="20px"><div class="checkbox" style="margin-bottom: 0px; margin-top: 0px;">
									<label style="padding-left: 4px;"> <input type="checkbox" class="Joel-checkall colored-blue">
                                     <span class="text"></span>
									</label>                                    
                                </div></th>
							<th width="80px">ID</th>
							<th width="80px">渠道ID</th>
							<th width="80px">层级</th>							
							<th width="200px">昵称</th>
							<th width="100px">下线人数</th>
							<th width="100px">手机号</th>		
							<th width="100px">姓名</th>
							<th width="100px">经验等级</th>
							<th width="100px">账户金额</th>
							<th width="100px">积分</th>
							<th width="100px">经验</th>
							<th width="100px">注册时间</th>
							<th width="100px">最后交互</th>
							<!--<th width="100px">状态</th>-->
							<th width="">操作</th>
						</tr>
						</tr>
					</thead>
					<tbody>
						<?php if(is_array($cache)): $i = 0; $__LIST__ = $cache;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id="item<?php echo ($vo["id"]); ?>">
								<td>
									<div class="checkbox" style="margin-bottom: 0px; margin-top: 0px;">
										<label style="padding-left: 4px;"> <input name="checkvalue" type="checkbox" class="colored-blue Joel-check" value="<?php echo ($vo["id"]); ?>">
	                                     <span class="text"></span>
										</label>                                    
	                                </div>
								</td>
								<td class=" sorting_1"><?php echo ($vo["id"]); ?></td>
								<td class=" "><?php echo ($vo["sid"]); ?></td>
								<td class=" "><?php echo ($vo["plv"]); ?></td>
								<td class=" "><?php echo ($vo["nickname"]); ?></td>
								<td class=" "><?php echo ($vo["total_xxlink"]); ?></td>
								<td class=" "><?php echo ($vo["mobile"]); ?></td>
								<td class=" "><?php echo ($vo["name"]); ?></td>
								<td class=" "><?php echo ($vo["levelname"]); ?></td>
								<td class=" "><?php echo ($vo["money"]); ?></td>
								<td class=" "><?php echo ($vo["score"]); ?></td>
								<td class=" "><?php echo ($vo["cur_exp"]); ?></td>
								<td class=" "><?php echo (date('Y-m-d',$vo["ctime"])); ?></td>
								<td class=" "><?php echo (date('Y-m-d',$vo["cctime"])); ?></td>
								<td class="center ">
									<a href="<?php echo U('Cms/Vip/vipSet/',array('id'=>$vo['id'],'p'=>$p));?>" class="btn btn-success btn-xs" data-loader="Joel-loader" data-loadername="会员编辑"><i class="fa fa-edit"></i> 编辑</a>
									&nbsp;&nbsp;<button class="btn btn-primary btn-xs vipup" data-id = "<?php echo ($vo["pid"]); ?>"><i class="glyphicon glyphicon-arrow-up"></i> 上级</button>
									&nbsp;&nbsp;<button class="btn btn-danger btn-xs vipdown" data-id = "<?php echo ($vo["id"]); ?>"><i class="glyphicon glyphicon-arrow-down"></i> 下级</button>
									<!--<button class="btn btn-sky btn-xs Joel-vippath" data-path="<?php echo ($vo["path"]); ?>" ><i class="fa fa-eye"></i> 层级树</button>-->
									<!--&nbsp;&nbsp;<a href="<?php echo U('Cms/vip/message/');?>" class="btn btn-danger btn-xs" data-type = "del" data-ajax="<?php echo U('Cms/vip/messageDel/',array('id'=>$vo['id']));?>" ><i class="fa fa-trash-o"></i> 删除</a>--></td>
							</tr><?php endforeach; endif; else: echo "" ;endif; ?>
												
					</tbody>
				</table>
				<div class="row DTTTFooter">
					<?php echo ($page); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<!--面包屑导航封装-->
<div id="tmpbread" style="display: none;"><?php echo ($breadhtml); ?></div>
<script type="text/javascript">
	setBread($('#tmpbread').html());
</script>
<!--/面包屑导航封装-->
<!--全选特效封装/全部删除-->
<script type="text/javascript">
	//全选
	var checkall=$('#Joel-table .Joel-checkall');
	var checks=$('#Joel-table .Joel-check');
	var trs=$('#Joel-table tbody tr');
	$(checkall).on('click',function(){
		if($(this).is(":checked")){			
			$(checks).prop("checked","checked");
		}else{
			$(checks).removeAttr("checked");
		}		
	});
	$(trs).on('click',function(){
		var c=$(this).find("input[type=checkbox]");
		if($(c).is(":checked")){
			$(c).removeAttr("checked");
		}else{
			$(c).prop("checked","checked");
		}		
	});
	$('#sendMsg').on('click',function(){
		var checks=$(".Joel-check:checked");
		var chk='';
		$(checks).each(function(){
			chk+=$(this).val()+',';
		});
		if(!chk){
			$.Joel.alert('danger','请选择要发送的对象！');
			return false;
		}
		var tourl="<?php echo U('Cms/Vip/messageSet');?>"+"/pids/"+chk;
		$('#sendMsgbtn').attr('href',tourl).trigger('click');
	});
	
	$('#sendMail').on('click',function(){
		var checks=$(".Joel-check:checked");
		var chk='';
		$(checks).each(function(){
			chk+=$(this).val()+',';
		});
		if(!chk){
			$.Joel.alert('danger','请选择要发送的对象！');
			return false;
		}
		var tourl="<?php echo U('Cms/Vip/mailSet');?>"+"/pids/"+chk;
		$('#sendMailbtn').attr('href',tourl).trigger('click');
	});
	
	//会员层级
//	var btnpath=$('.Joel-vippath');
//	$(btnpath).on('click',function(){
//		var dt=$(this).data('path');
//		var mb="<p>"+dt+"</p>";
//		bootbox.dialog({
//	                	message: mb,
//	                	title: "会员完整层级展示",
//	                	className: "modal-darkorange",
//	                	buttons: {
//			                    "取消": {
//			                        className: "btn-danger",
//			                        callback: function () { }
//		                    }
//		        	}
//		});
//		return false;
//	});

//发货快递
	var vipup=$('.vipup');
	$(vipup).on('click',function(){
			var id=$(this).data('id');
			$.ajax({
					type:"post",
					url:"<?php echo U('Cms/Vip/vipUp');?>",
					data:{'id':id},
					dataType: "json",
					//beforeSend:$.Joel.loading(),
					success:function(mb){
						//$.Joel.loading();
						bootbox.dialog({
	                	message: mb,
	                	title: "会员直属上级",
	                	className: "modal-darkorange",
	                	buttons: {
	                		   
			                    "取消": {
			                        className: "btn-danger",
			                        callback: function () { }
			                    }
		                	}
		            	});
					},
					error:function(xhr){
						$.Joel.alert('danger','通讯失败！请重试！');
					}
			});
		return false;
	});
	
	var vipdown=$('.vipdown');
	$(vipdown).on('click',function(){
			var id=$(this).data('id');
			$.ajax({
					type:"post",
					url:"<?php echo U('Cms/Vip/vipDown');?>",
					data:{'id':id},
					dataType: "json",
					//beforeSend:$.Joel.loading(),
					success:function(mb){
						//$.Joel.loading();
						bootbox.dialog({
	                	message: mb,
	                	title: "会员直属下级",
	                	className: "modal-darkorange",
	                	buttons: {
	                		   
			                    "取消": {
			                        className: "btn-danger",
			                        callback: function () { }
			                    }
		                	}
		            	});
					},
					error:function(xhr){
						$.Joel.alert('danger','通讯失败！请重试！');
					}
			});
		return false;
	});
	
	//导出会员数据
	$('#exportVip').on('click',function(){
		var checks=$(".Joel-check:checked");
		var chk='';
		$(checks).each(function(){
			chk+=$(this).val()+',';
		});
		window.open("<?php echo U('Cms/Vip/vipExport');?>/count/"+$('#expcount').val()+"/id/"+chk);
	})
</script>
<!--/全选特效封装-->