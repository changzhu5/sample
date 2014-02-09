<div class="small-box">
	<div class="buttons">
		<a class="button red" href="" onclick="$('form').submit();return false;">Delete</a>
	</div>
	<div id="content">
		<form name="" method="post" action="<?php echo getPermalink('tinycms/category/delete');?>">
		<table class="sortable">
			<thead>
				<tr class="alt first last">
					<th rel="0"><input type="checkbox" /></th>
					<th rel="1">Name</th>
					<th rel="2">Description</th>
					<th rel="3">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if($cats){foreach($cats as $catId=>$cat){?>
				<tr>
					<td value=""><input type="checkbox" name="selected[]" value="<?php echo $catId;?>" /></td>
					<td value="<?php echo $cat->getValue('name');?>"><?php echo $cat->getValue('name');?></td>
					<td value="<?php echo $cat->getValue('description');?>"><?php echo $cat->getValue('description');?></td>
					<td value="">
						<a class="tooltip" title="Edit" href="<?php echo getPermalink('tinycms/category/edit/' . $catId);?>"><i class="icon-pencil"></i></a>&nbsp;&nbsp;&nbsp; 
						<a class="tooltip" title="Delete" href="<?php echo getPermalink('tinycms/category/delete/' . $catId);?>"><i class="icon-minus-sign"></i></a>
					</td>
				</tr>
				<?php }}?>
			</tbody>
		</table>
		</form>
	</div>
</div>