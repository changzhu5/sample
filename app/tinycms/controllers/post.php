<?php
require_once APP_PATH . Configure::read('app') . DS .'controller.php';
class PostController extends MyAppController{
	public function beforeAction(){
		if(parent::beforeAction()){
			Context::drop('currentMenu',1);
			Context::drop('showMenu',true);
			return true;
		}
	}
	public function index(){
		$delegator = Context::fetch('delegator');
		$posts = $delegator->find('post');
		$this->assign('posts',$posts);
		return $this->render('common/index');
	}
	public function add(){
		if($post = $this->request->getPost()){
			if(!$post['title']){
				$this->setMsg('please enter title','warning');
			}
			else{
				$delegator = Context::fetch('delegator');
				$post = $delegator->create('post',array('title','category_id','content'),array($post['title'],$post['category'],$post['content']));
				$this->setMsg('Success!','success');
				$this->redirect('tinycms/post/index');
			}
		}
		$categories = $this->db->getCategories();
		$this->assign('categories',$categories);
		$this->assign('action',getPermalink('tinycms/post/add'));
		return $this->render('post/form');
	}
	public function edit($id){
		$delegator = Context::fetch('delegator');
		$post = $delegator->findByPK('post',$id);
		if($p = $this->request->getPost()){
			$post->setValue('title',$p['title']);
			$post->setValue('category_id',$p['category']);
			$post->setValue('content',$p['content']);
			$post->save();
			$this->setMsg('Success!','success');
			$this->redirect('tinycms/post/index');
		}
		
		$category = $post->getRelatedOne('category');
		$categories = $this->db->getCategories();
		$this->assign('categories',$categories);
		$this->assign('post',$post);
		$this->assign('category',$category);
		$this->assign('action',getPermalink('tinycms/post/edit/' . $id));
		return $this->render('post/form');
	}
	public function delete($id=null){
		$delegator = Context::fetch('delegator');
		if($post = $this->request->getPost()){
			$posts = $post['selected'];
			if(!empty($posts)){
				$delegator->remove('post',$posts);
				$this->setMsg('Success!','success');
				$this->redirect('tinycms/post/index');
			}
		}
		$post = $delegator->findByPK('post',$id);
		$post->remove();
		$this->setMsg('Success!','success');
		$this->redirect('tinycms/post/index');
	}
}
?>