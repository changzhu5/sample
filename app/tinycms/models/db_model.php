<?php
class DbModel extends AppModel{
	public function test(){
		$post = $this->getEntity('post');
		$post->install($this->request->getAppName());
	}
	public function getCategories(){
		$delegator = Context::fetch('delegator');
		$cats = $delegator->find('category');
		if(!$cats){
			return null;
		}
		$data = array();
		foreach($cats as $cat){
			$data[$cat->getId()] = $cat;
 		}
		return $data;
	}
}
?>