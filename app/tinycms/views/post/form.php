<div class="small-box">
	<form action="<?php echo $action;?>" method="post" class="vertical">
	<div class="col_12 column">
		<label for="text1">Title</label>
		<input id="text1" type="text" name="title" value="<?php echo (isset($post) ? $post->getValue('title') : '');?>">
		
		<label for="text2">Parent</label>
		<select name="category">
			<option value="">Select</option>
			<?php foreach($categories as $cate){?>
			<option value="<?php echo $cate->getId();?>" <?php echo (isset($category) && $category && $category->getId() == $cate->getId() ? 'selected' : '');?>><?php echo $cate->getValue('name');?></option>
			<?php }?>
		</select>
		
		<label for="text3">Content</label>
		<textarea name="content"><?php echo (isset($post) ? $post->getValue('content') : '');?></textarea>
		<button type="submit">Save</button>
		<input type="hidden" name="action" value="add" />
	</div>
	</form>
	<div class="clear"></div>
</div>