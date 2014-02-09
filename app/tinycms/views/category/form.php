<div class="small-box">
	<form action="<?php echo $action;?>" method="post" class="vertical">
	<div class="col_12 column">
		<label for="text1">Name</label>
		<input id="text1" type="text" name="title" value="<?php echo (isset($category) ? $category->getValue('name') : '');?>">
		
		<label for="text3">Description</label>
		<textarea name="desc"><?php echo (isset($category) ? $category->getValue('description') : '');?></textarea>
		<button type="submit">Save</button>
		<input type="hidden" name="action" value="add" />
	</div>
	</form>
	<div class="clear"></div>
</div>