<?php if (!defined('THINK_PATH')) exit();?> <div class="row">
        <div class="col-md-12">
                <?php if(empty($cache)): ?>此用户已是顶级
                	<?php else: ?>
                	<div class="dd dd-draghandle">
				<ol class="dd-list">
					<li class="dd-item dd2-item">
						<div class="dd-handle dd2-handle">
							<i class="normal-icon fa fa-male"></i>

							<i class="drag-icon fa fa-arrows-alt "></i>
						</div>
						<div class="dd2-content"><?php echo ($cache["id"]); ?>：<?php echo ($cache["nickname"]); ?></div>
					</li>
				</ol>
			</div><?php endif; ?>
        </div>
</div>