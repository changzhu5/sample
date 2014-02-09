<?php
/**
 * This class mainly output a form according to the table info which is from driver.getTableInfo()
 *
 */
class Form extends Object 
{
	/**
	 * The action of form
	 * @var String
	 */
	private $action;
	/**
	 * The method of form
	 * @var String
	 */
	private $method;
	/**
	 * An data structure for producing form.
	 * @var Array
	 */
	private $elements = array();
	/**
	 * An object of current model
	 * @var Model
	 */
	private $model;
	/**
	 * Data stores of radio buttions
	 * @var Array
	 */
	private $radios = array();
	/**
	 * Init Form object includeing struct the form elements and make form.
	 *
	 * @param String $action
	 * @param String $method
	 * @param String $model_name
	 * @param String $type   'add' or 'update'
	 * @param String $data  array('model'=>array('field'=>value))
	 * @return HTML string
	 */
	public function init($action,$method,$model_name,$type="add",$data=null)
	{
		$this->action = $action;
		$this->method = $method;
		$this->model = Factory::getObject("model",$model_name);
		if($type == "add")
			$this->elements = $this->getAddFormFields();
		return $this->makeForm();
	}
	/**
	 * When init(type='add'), get data structure for blanket form.
	 *
	 * @return Array
	 */
	private function getAddFormFields()
	{
		$form_type = array();
		$model_name = $this->model->name;
		$table_type = $this->model->getFields("no-key");
		unset($table_type['id']);
		foreach($table_type as $name => $info)
		{
			$field = array();
			$field['name'] = $name;
			$field_type = $this->convertType($info['type'],$name);
			$field['type'] = $field_type;
			$field['value'] = "";
			if($field_type == "radio")
			{
				$field['value'] = $this->radios[$name];
			}
			$form_type[] = $field;
		}
		if(!empty($this->model->belongsTo))
		{
			foreach($this->model->belongsTo as $foreign_key)
			{
				$field = array();
				$field['name'] = $foreign_key . "_id";
				$field['type'] = "select";
				$model = Factory::getObject("model",$foreign_key);
				$name = $model->name;
				$referTo = $model->referTo;
				if(empty($referTo))
				{
					this.appError();
				}
				$values = $model->find(array("fields"=>array("$name.id","{$name}.{$referTo}")));
				$field['value'] = $values;
				$field['model'] = $name;
				$field['referTo'] = $referTo;
				$form_type[] = $field;
			}
		}
		if(!empty($this->model->manyToMany))
		{
			foreach($this->model->manyToMany as $modelname=>$tablename)
			{
				$field = array();
				$model = Factory::getObject("model",$modelname);
				$data = $model->find(array("fields"=>array("$modelname.{$model->referTo}","$modelname.id")));
				$field['name'] = $modelname;
				$field['type'] = "checkbox";
				$field['value'] = $data;
				$field['referTo'] = $model->referTo;
				$form_type[] = $field;
			}
		}
		return $form_type;
	}
	/**
	 * Convert database data type into form element type.
	 *
	 * @param String $type  form element type
	 * @param String $name  the name of element
	 * @return String
	 */
	private function convertType($type,$name)
	{
		if(preg_match("/enum\((\S+)\)/",$type,$arr))
		{
			$str_options = $arr[1];
			$str_options = str_replace("'","",$str_options);
			$arr_options = explode(",",$str_options);
			$this->radios[$name] = $arr_options;
			return "radio";
		}
		if(preg_match("/date/",$type))
			return "date";
		else
			return "text";
	}
	/**
	 * Make a form
	 *
	 * @param String $method
	 * @param String $post
	 * @param Array $fields format:
	 * array(0=>array('name'='fieldname','type'='text/textarea/radio/select',value=Mix),
	 * 		 1=>array('name'='fieldname','type'='text/textarea/radio/select',value=Mix)
	 * 		)
	 * @return form HTML
	 */
	private function makeForm()
	{
		$form = "<form action=$this->action method=$this->method >";
		foreach($this->elements as $field)
		{
			switch($field['type'])
			{
				case 'select':
					$form .= $this->select($field['name'],$field['value'],$field['model'],$field['referTo']);
					break;
				case 'date':
					$form .= $this->input($field['name'],"text","yyyy/mm/dd");
					break;
				case 'checkbox':
					$form .= $this->checkbox($field['name'],$field['value'],$field['referTo']);
					break;
				default:
					$form .= $this->input($field['name'],$field['type'],$field['value']);
			}
		}
		$form .= $this->input('add','submit','Add');
		$form .= '</form>';
		return $form;
	}
	/**
	 * Make select Form
	 *
	 * @param String $name
	 * @param Array $options format:
	 * the same with model
	 * @return String
	 */
	private function select($name,$values,$modelname,$referTo)
	{
		$str = "  $modelname:<select name=$name >";
		foreach($values as $value)
		{
			$str .= "<option value={$value[$modelname]['id']} > {$value[$modelname][$referTo]} </option>";
		}
		$str .= "</select><br>";
		return $str;
	}
	/**
	 * Make input field
	 *
	 * @param String $name
	 * @param String $type
	 * @param String $value
	 * @return String
	 */
	private function input($name,$type,$value="")
	{
		$str = "";
		switch($type)
		{
			case "text":
				$str = "<br>$name : <input type=$type name=$name value=$value ><br>";
				break;
			case "radio":
				foreach($value as $option)
				{
					$str .= "<input type=$type name=$name >" . $option  ;
				}
				break;
			case "submit":
				$str = "<br><input type=$type name=$name value=$value >";
				break;
		}
		return $str;
	}
	private function checkbox($name,$values,$referTo)
	{
		$str = "";
		foreach($values as $value)
		{
			$str .= "<input type='checkbox' name={$name}[] value={$value[$name]['id']} >{$value[$name][$referTo]}";
		}
		return $str;
	}
}
?>