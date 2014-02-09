<?php
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
if(!defined('ROOT')){
	define('ROOT',dirname(__FILE__));
}
if(!defined('APP_PATH')){
	define('APP_PATH',ROOT.DS.'app'.DS);
}
if(!defined('CORE_PATH')){
	define('CORE_PATH',ROOT.DS.'libs'.DS);
}
if(!defined('CORE_APP_PATH')){
	define('CORE_APP_PATH',CORE_PATH . 'applications' . DS);
}
if(!defined('WEBROOT_PATH')){
	define('WEBROOT_PATH',APP_PATH.'webroot'.DS);
}
if(!defined('COMPONENT_PATH')){
	define('COMPONENT_PATH',CORE_PATH.'components'.DS);
}
if(!defined('CORE_CONTROLLER_PATH')){
	define('CORE_CONTROLLER_PATH',CORE_PATH.'controllers'.DS);
}
if(!defined('CORE_MODEL_PATH')){
	define('CORE_MODEL_PATH',CORE_PATH.'models'.DS);
}
if(!defined('CORE_DB_PATH')){
	define('CORE_DB_PATH',CORE_MODEL_PATH.'datasources'.DS);
}
if(!defined('CORE_VIEW_PATH')){
	define('CORE_VIEW_PATH',CORE_PATH.'views'.DS);
}

include CORE_PATH.'bootstrap.php';
include CORE_PATH.'dispatcher.php';
require_once CORE_PATH.'config.php';

session_start();
echo Dispatcher::dispatch();
?>
