<?php
require_once APP_PATH . Configure::read('app') . DS .'controller.php';

class CommonController extends MyAppController{
	
	public function index(){
		Context::drop('currentMenu',1);
		Context::drop('showMenu',true);
		$posts = $this->request->getDelegator()->find('post');
		$this->assign('posts',$posts);
	}
	
	public function head(){
		$currentMenu = Context::fetch('currentMenu');
		$showMenu = Context::fetch('showMenu');
		if(!$currentMenu){
			$currentMenu = 1;
		}
		if(!$showMenu){
			$showMenu = false;
		}
		$this->assign('currentMenu',$currentMenu);
		$this->assign('showMenu',$showMenu);
		$this->layout = null;
		return $this->render();
	}
	
	public function foot(){
		$this->layout = null;
		$this->assign('copyRight',date("Y"));
		return $this->render();
	}
	
	public function login(){
		if($post = $this->request->getPost()){
			if(!$post['username'] || !$post['pwd']){
				$this->setMsg('Please fill in all fields','warning');
			}
			else{
				$delegator = Context::fetch('delegator');
				$user = $delegator->findOne('user',array(
					array(
						'username','eq',$post['username']
					),
					array(
						'pwd','eq',$post['pwd']
					)
				));
				if(!$user){
					$this->setMsg('Wrong account infomation','warning');
				}
				else{
					$_SESSION['user_id'] = $user->getId();
					$this->setMsg('Success!','success');
					$this->redirect('common/index');
				}
			}
		}
	}
	
	public function logout(){
		unset($_SESSION['user_id']);
		$this->redirect('common/login');
	}
	
	public function register(){
		if($post = $this->request->getPost()){
			if(!$post['username'] || !$post['pass'] || !$post['confirm_pass']){
				$this->setMsg('Please fill in all fields','warning');
			}
			else if($post['pass'] != $post['confirm_pass']){
				$this->setMsg('Please match password','warning');
			}
			else{
				$delegator = Context::fetch('delegator');
				$record = $delegator->create('user',array('username','pwd'),array($post['username'],$post['pass']));
				$this->setMsg('Success!','success');
				$this->redirect('common/login');
			}
		}
	}
	
	public function install(){
		return $this->render();
	}
	
	public function setup(){
		$this->ifRender = 0;
		if($this->db->setup()){
			$this->cache->setVar('installed',true);
			$this->redirect('common/login');
		}
	}
}
?>