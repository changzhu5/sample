<?php
require_once APP_PATH . Configure::read('app') . DS .'controller.php';
class CategoryController extends MyAppController{
	public function beforeAction(){
		if(parent::beforeAction()){
			Context::drop('currentMenu',2);
			Context::drop('showMenu',true);
			return true;
		}
	}
	public function index(){
		$cats = $this->db->getCategories();
		$this->assign('cats',$cats);
	}
	public function add(){
		if($post = $this->request->getPost()){
			if(!$post['title']){
				$this->setMsg('please enter title.','warning');
			}
			else{
				$delegator = Context::fetch('delegator');
				$delegator->create('category',array('name','description'),array($post['title'],$post['desc']));
				$this->setMsg('Success!','success');
				$this->redirect('category/index');
			}
		}
		$categories = $this->db->getCategories();
		$this->assign('categories',$categories);
		$this->assign('action',getPermalink($this->request->getAppName() . '/category/add/'));
		return $this->render('category/form');
	}
	public function edit($id){
		$delegator = $this->request->getDelegator();
		$category = $delegator->findByPK('category',$id);
		if($post = $this->request->getPost()){
			$category->setValue('name',$post['title']);
			$category->setValue('description',$post['desc']);
			$category->save();
			$this->setMsg('Success!','success');
			$this->redirect('category/index');
		}
		$this->assign('category',$category);
		$this->assign('action',getPermalink($this->request->getAppName() . '/category/edit/' . $id));
		return $this->render('category/form');
	}
	public function delete($id=null){
		$delegator = $this->request->getDelegator();
		if($post = $this->request->getPost()){
			$cates = $post['selected'];
			if(!empty($cates)){
				$delegator->remove('category',$cates);
				$this->setMsg('Success!','success');
				$this->redirect('category/index');
			}
		}
		$category = $delegator->findByPK('category',$id);
		$category->remove();
		$this->setMsg('Success!','success');
		$this->redirect('category/index');
	}
}
?>