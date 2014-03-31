<?php
class MyAppController extends AppController{
	public $ext = '.php';
	public $layout = "kickstart";
	public $models = array('db');
	/**
	 * Check user login before any business logic
	 * @return boolean
	 */
	function beforeAction(){
		if(parent::beforeAction()){
			if($this->name == 'common' && ($this->action == 'login' || $this->action == 'register' || $this->action == 'logout' || $this->action == 'install' || $this->action == 'setup')){
				if($this->action != 'setup' && !$this->cache->getVar('installed')){
					return $this->requestAction('common','install');
				}
				if($this->action == 'login' && isset($_SESSION['user_id'])){
					$this->redirect('common/index');
				}
				if($this->action == 'logout' && !isset($_SESSION['user_id'])){
					$this->redirect('common/login');
				}
				return true;
			}
			else if(!isset($_SESSION['user_id'])){
				$this->redirect('common/login');
			}
		}
		return true;
	}
}
?>