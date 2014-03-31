<?php
class Category extends Entity{
	protected $fields = array(
		'id'=>array('type'=>'int','length'=>10,'default'=>'auto'),
		'name'=>array('type'=>'char','length'=>50,'default'=>'null'),
		'parent'=>array('type'=>'int','length'=>10,'default'=>0),
		'description'=>array('type'=>'char','length'=>200,'default'=>'null')
	);
	protected $cache = false;
	protected $hasMany = array(
		array('to'=>'post','foreignKey'=>'category_id')
	);
}
?>