<h1>Tiny CMS Demo</h1>
<!-- Msg Box start -->
<?php if($error = $this->getMsg('warning')){?>
<div class="notice warning small-box"><?php echo $error;?></div>
<?php }?>

<?php if($success = $this->getMsg('success')){?>
<div class="notice success small-box"><?php echo $success;?></div>
<?php }?>
<!-- Msg Box end -->
<?php if($showMenu){?>
<!-- Menu start -->
<div class="small-box">
	<ul class="menu">
		<li class="<?php echo($currentMenu==1?'current':'');?>"><a href="<?php echo getPermalink('tinycms/post/index');?>">Post</a>
			<ul>
				<li><a href="<?php echo getPermalink('tinycms/post/add');?>"><i class="icon-file"></i> New</a></li>
			</ul>
		</li>
		<li class="<?php echo($currentMenu==2?'current':'');?>"><a href="<?php echo getPermalink('tinycms/category/index');?>">Category</a>
			<ul>
				<li><a href="<?php echo getPermalink('tinycms/category/add');?>"><i class="icon-folder-open-alt"></i> New</a></li>
			</ul>
		</li>
		<li><a href="<?php echo getPermalink('tinycms/common/logout');?>">Logout</a></li>
	</ul>
</div>
<!-- Menu end -->
<?php }?>
