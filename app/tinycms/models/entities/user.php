<?php
class User extends Entity{
	protected $fields = array(
		'id'=>array('type'=>'int','length'=>10,'default'=>'auto'),
		'username'=>array('type'=>'char','length'=>50,'default'=>'null'),
		'pwd'=>array('type'=>'char','length'=>50,'default'=>'null')
	);
	protected $cache = false;
}
?>