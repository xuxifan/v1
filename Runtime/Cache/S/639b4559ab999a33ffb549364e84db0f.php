<?php if (!defined('THINK_PATH')) exit();?><div class="row">
	<div class="col-xs-12 col-md-12">
		<h6 class="row-title before-blue">您总共拥有下线分销商&nbsp;<b class="darkorange"><?php echo ($all); ?></b>&nbsp;个，当前可以开设&nbsp;<b class="darkorange"><?php echo ($_SESSION['S']['user']['sonnum']); ?></b>&nbsp;个直属下级分销商，已开设&nbsp;<b class="darkorange"><?php echo ($now); ?></b>&nbsp;个，剩余&nbsp;<b class="darkorange"><?php echo ($left); ?></b>&nbsp;个名额</h6>
		<div class="widget">
			<div class="widget-header bg-blue">
				<i class="widget-icon fa fa-arrow-down"></i>
				<span class="widget-caption">分销商管理</span>
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
					<a href="<?php echo U('S/Fxs/userSet/');?>" class="btn btn-primary" data-loader="Joel-loader" data-loadername="设置分组">
						<i class="fa fa-plus"></i>新增直属分销商
					</a>
					<div class="pull-right">
						<form id="Joel-search">
							<label style="margin-bottom: 0px;">
								<input name="name" type="search" class="form-control input-sm">
							</label>
							<a href="<?php echo U('S/Fxs/user/');?>" class="btn btn-success" data-loader="Joel-loader" data-loadername="分销商管理" data-search="Joel-search">
								<i class="fa fa-search"></i>搜索
							</a>
						</form>
					</div>
				</div>

				<table id="Joel-table" class="table table-bordered table-hover">
					<thead class="bordered-darkorange">
						<tr role="row">
							<th>ID</th>
							<!--<th>PID</th>
							<th>Path</th>-->
							<th>层级</th>
							<th>自定义编码</th>
							<th>分销商名称</th>	
							<th>分销商电话</th>	
							<th>后台登陆名</th>
							<th>可开设下级总数</th>	
							<th>累计佣金</th>
							<th>会员总数</th>
							<th>关注会员</th>
							<th>累计购买</th>
							<th>加入时间</th>
							<th>渠道二微码</th>
							<th>分销商状态</th>
							<th>操作</th>
						</tr>
					</thead>
					<tbody>
						<?php if(is_array($cache)): $i = 0; $__LIST__ = $cache;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id="item<?php echo ($vo["id"]); ?>">
								<td class=" sorting_1"><?php echo ($vo["id"]); ?></td>
								<!--<td class=" "><?php echo ($vo["pid"]); ?></td>
								<td class=" "><?php echo ($vo["bpath"]); ?></td>-->
								<td class=" "><?php echo ($vo["lv"]); ?></td>
								<td class=" "><?php echo ($vo["no"]); ?></td>
								<td class=" "><?php echo ($vo["nickname"]); ?></td>
								<td class=" "><?php echo ($vo["mobile"]); ?></td>
								<td class=" "><?php echo ($vo["username"]); ?></td>	
								<td class=" "><?php echo ($vo["sonnum"]); ?></td>	
								<td class=" "><?php echo ($vo["total_xxyj"]); ?>元</td>
								<td class=" "><?php echo ($vo["total_xxlink"]); ?>人</td>
								<td class=" "><?php echo ($vo["total_xxsub"]); ?>人</td>
								<td class=" "><?php echo ($vo["total_xxbuy"]); ?>次</td>
								<td class=" "><?php echo (date('Y-m-d',$vo["ctime"])); ?></td>
								<td class=" "><button class="btn btn-xs btn-blue showqr" data-title="<?php echo ($vo["nickname"]); ?>" data-qr="<?php echo ($vo["qrticket"]); ?>">点击查看</button></td>
								<td class=" "><?php if(($vo["status"]) == "1"): ?><button class="btn btn-xs btn-success">正常</button><?php else: ?><button class="btn btn-xs btn-danger">停用</button><?php endif; ?></td>
								<td><a href="<?php echo U('S/Fxs/myuserSet/',array('id'=>$vo['id']));?>" class="btn btn-success btn-xs" data-loader="Joel-loader" data-loadername="设置分销商"><i class="fa fa-edit"></i> 编辑</a></td>
							</tr>
							<?php if(is_array($vo['_child'])): $i = 0; $__LIST__ = $vo['_child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo2): $mod = ($i % 2 );++$i;?><tr id="item<?php echo ($vo2["id"]); ?>">
									<td class=" sorting_1"><?php echo ($vo2["id"]); ?></td>
									<!--<td class=" "><?php echo ($vo2["pid"]); ?></td>
									<td class=" "><?php echo ($vo2["bpath"]); ?></td>-->
									<td class=" "><?php echo ($vo2["lv"]); ?></td>
									<td class=" "><?php echo ($vo2["no"]); ?></td>
									<td class=" ">&nbsp;&nbsp;└<?php echo ($vo2["nickname"]); ?></td>
									<td class=" "><?php echo ($vo2["mobile"]); ?></td>
									<td class=" "><?php echo ($vo2["username"]); ?></td>	
									<td class=" "><?php echo ($vo2["sonnum"]); ?></td>	
									<td class=" "><?php echo ($vo2["total_xxyj"]); ?>元</td>
									<td class=" "><?php echo ($vo2["total_xxlink"]); ?>人</td>
									<td class=" "><?php echo ($vo2["total_xxsub"]); ?>人</td>
									<td class=" "><?php echo ($vo2["total_xxbuy"]); ?>次</td>
									<td class=" "><?php echo (date('Y-m-d',$vo2["ctime"])); ?></td>
									<td class=" "><button class="btn btn-xs btn-blue showqr" data-title="<?php echo ($vo2["nickname"]); ?>" data-qr="<?php echo ($vo2["qrticket"]); ?>">点击查看</button></td>
									<td class=" "><?php if(($vo2["status"]) == "1"): ?><button class="btn btn-xs btn-success">正常</button><?php else: ?><button class="btn btn-xs btn-danger">停用</button><?php endif; ?></td>
									<td><a href="<?php echo U('S/Fxs/myuserSet/',array('id'=>$vo2['id']));?>" class="btn btn-success btn-xs" data-loader="Joel-loader" data-loadername="设置分销商"><i class="fa fa-edit"></i> 编辑</a></td>
								</tr>
								<?php if(is_array($vo2['_child'])): foreach($vo2['_child'] as $key=>$vo3): ?><tr id="item<?php echo ($vo3["id"]); ?>">
									<td class=" sorting_1"><?php echo ($vo3["id"]); ?></td>
									<!--<td class=" "><?php echo ($vo3["pid"]); ?></td>
									<td class=" "><?php echo ($vo3["bpath"]); ?></td>-->
									<td class=" "><?php echo ($vo3["lv"]); ?></td>
									<td class=" "><?php echo ($vo3["no"]); ?></td>
									<td class=" ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└<?php echo ($vo3["nickname"]); ?></td>
									<td class=" "><?php echo ($vo3["mobile"]); ?></td>
									<td class=" "><?php echo ($vo3["username"]); ?></td>		
									<td class=" "><?php echo ($vo3["sonnum"]); ?></td>	
									<td class=" "><?php echo ($vo3["total_xxyj"]); ?>元</td>
									<td class=" "><?php echo ($vo3["total_xxlink"]); ?>人</td>
									<td class=" "><?php echo ($vo3["total_xxsub"]); ?>人</td>
									<td class=" "><?php echo ($vo3["total_xxbuy"]); ?>次</td>
									<td class=" "><?php echo (date('Y-m-d',$vo3["ctime"])); ?></td>
									<td class=" "><button class="btn btn-xs btn-blue showqr" data-title="<?php echo ($vo3["nickname"]); ?>" data-qr="<?php echo ($vo3["qrticket"]); ?>">点击查看</button></td>
									<td class=" "><?php if(($vo3["status"]) == "1"): ?><button class="btn btn-xs btn-success">正常</button><?php else: ?><button class="btn btn-xs btn-danger">停用</button><?php endif; ?></td>
									<td><a href="<?php echo U('S/Fxs/myuserSet/',array('id'=>$vo3['id']));?>" class="btn btn-success btn-xs" data-loader="Joel-loader" data-loadername="设置分销商"><i class="fa fa-edit"></i> 编辑</a></td>
									</tr><?php endforeach; endif; endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; ?>
												
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
<!--QRCODE封装-->
<script type="text/javascript">
var qrs=$('.showqr');
$(qrs).on('click',function(){
	var title=$(this).data('title')+'-渠道二微码';
	var ticket=encodeURI($(this).data('qr'));
	var u='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='+ticket;
	var mb="<div style='width:100%;text-align:center'><img src='"+u+"' /><p>若未显示二微码，请重新打开或编辑一次分销商。</p></div>";
	bootbox.dialog({
	  message: mb,
	  title: title,
	  className: "modal-darkorange",
	  buttons: {
		"取消": {
		className: "btn-danger",
		callback: function () { }
	  	}
	  }
	});
});
</script>
<!--/QRCODE封装-->