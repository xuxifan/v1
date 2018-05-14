<?php if (!defined('THINK_PATH')) exit();?><div class="row">
	<div class="col-md-12">
		<?php if(empty($cache)): ?>此用户暂无下线
			<?php else: ?>
			<div class="dd dd-draghandle">
				<ol class="dd-list">
					<?php if(is_array($cache)): foreach($cache as $key=>$vo): ?><li class="dd-item dd2-item">
						<div class="dd-handle dd2-handle">
							<i class="normal-icon fa fa-male"></i>

							<i class="drag-icon fa fa-arrows-alt "></i>
						</div>
						<div class="dd2-content"><?php echo ($vo["id"]); ?>：<?php echo ($vo["nickname"]); ?></div>
					</li><?php endforeach; endif; ?>
				</ol>
			</div><?php endif; ?>
	</div>
</div>