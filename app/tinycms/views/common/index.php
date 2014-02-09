<div class="small-box">
	<div class="buttons">
		<a class="button red" href="" onclick="$('form').submit();return false;">Delete</a>
	</div>
	<div id="content">
		<form name="" method="post" action="<?php echo getPermalink('tinycms/post/delete');?>">
		<table class="sortable">
			<thead>
				<tr class="alt first last">
					<th rel="0" value="Checkbox"><input type="checkbox" /></th>
					<th rel="1" value="Name">Title</th>
					<th rel="2" value="Author">Category</th>
					<th rel="3" value="Publish Date">Publish Date</th>
					<th rel="4" value="Actions">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if($posts){foreach($posts as $post){$category = $post->getRelatedOne('category');?>
				<tr>
					<td value=""><input type="checkbox" name="selected[]" value="<?php echo $post->getId();?>" /></td>
					<td value="<?php echo $post->getValue('title');?>"><?php echo $post->getValue('title');?></td>
					<td value="<?php echo ($category ? $category->getValue('name'):'');?>"><?php echo ($category ? $category->getValue('name') : 'None');?></td>
					<td value="<?php echo $post->getValue('publish_date');?>"><?php echo $post->getValue('publish_date');?></td>
					<td value="">
						<a class="tooltip" title="Edit" href="<?php echo getPermalink('tinycms/post/edit/' . $post->getId());?>"><i class="icon-pencil"></i></a>&nbsp;&nbsp;&nbsp; 
						<a class="tooltip" title="Delete" href="<?php echo getPermalink('tinycms/post/delete/' . $post->getId());?>"><i class="icon-minus-sign"></i></a>
					</td>
				</tr>
				<?php }}?>
			</tbody>
		</table>
		</form>
	</div>
</div>
