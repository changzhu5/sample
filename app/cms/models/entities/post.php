<?php
class Post extends Entity{
	protected $fields = array(
		'id'=>array('type'=>'int','length'=>10,'default'=>'auto'),
		'title'=>array('type'=>'char','length'=>50,'default'=>'null'),
		'category_id'=>array('type'=>'int','length'=>10,'default'=>0),
		'content'=>array('type'=>'text','default'=>'null'),
		'publish_date'=>array('type'=>'timestamp','default'=>'auto')
	);
	protected $cache = false;
	protected $belongsTo = array(
		array('foreignKey'=>'category_id','from'=>'category')
	);
}
?>