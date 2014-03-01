<?php
/**
 * base url
 */
Configure::write('base',Dispatcher::getInstance()->getWebRoot());
/**
 * Define host address
 */
Configure::write('hostname','hostname');
/**
 * Define database name
 */
Configure::write('dbname','dbname');
/**
 * Define username
 */
Configure::write('user','dbuser');
/**
 * Define password
 */
Configure::write('pwd','dbpassword');
/*
 * Define charset that the result from database and header to client
 */
Configure::write('charset','UTF-8');
/**
 * Define whether launch debug 1:open 0:shut down
 */
Configure::write('debug',1);
/**
 * Define administrator's url.
 * Eg.If you type http://website/{$admin}/index/tablename  ,you will enter the table management page. 
 */
Configure::write('admin','changzhu5');
/**
 * Define  
 */
Configure::write('admin_dispatcher','main');
/**
 * Define dfault app
 */
Configure::write('app','tinycms');
/**
 * Define the default controller
 */
Configure::write('controller','home');
/**
 * Define the default action
 */
Configure::write('action','index');
/**
 * If open the render engine
 */
Configure::write('render',0);
/**
 * If log erros
 */
Configure::write('logError',0);
/**
 * Single app mode
 */
Configure::write('single-mode',false);
/**
 * Cache method ['xcache' | 'fcache']
 */
Configure::write('cache-method','fcache');

?>
