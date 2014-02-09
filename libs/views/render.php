<?php
/**
 * Render template file to the PHP file.
 */
class Render extends Object
{
	/**
	 * eg. <?=$var;?>
	 * @var string pattern
	 */
	private $pattern_var = '/<\?=(\$\w+;)\?>/';
	/**
	 * eg. <?each($items as $item)?> or <?each($items as $key=>$item)?>
	 * @var string pattern
	 */
	private $pattern_for_start = '/<\?each(\([^<?>]+\))\?>/';
	/**
	 * eg. <?end?>
	 * @var string pattern
	 */
	private $pattern_for_end = '/<\?(end)\?>/';
	/**
	 * Absolute file path
	 * @var string
	 */
	private $template_path;
	/**
	 * string of HTML file
	 * @var string
	 */
	private $source;
	
	public function __construct($filename,$template)
	{
		$this->template_path = APP_VIEW_PATH . 'templates' . DS . $filename;
		$this->source = file_get_contents($template);
	}
	public function render()
	{
		$this->_render($this->pattern_var,'var');
		$this->_render($this->pattern_for_start,'start');
		$this->_render($this->pattern_for_end,'end');
		file_put_contents($this->template_path,$this->source);
		return $this->template_path;
	}
	private function _render($pattern,$type)
	{
		if(preg_match_all($pattern,$this->source,$arr))
		{
			if(is_array($arr[0]))
			{
				for($i=0;$i<count($arr[0]);$i++)
				{
					$search = $arr[0][$i];
					$data = $arr[1][$i];
				}
			}
			else 
			{
				$search = $arr[0];
				$data = $arr[1];
			}
			switch($type)
			{
				case 'var':
					$replace = '<?php echo ' . $data . '?>';
					break;
				case 'start':
					$replace = '<?php foreach' . $data . '{?>';
					break;
				case 'end':
					$replace = '<?php }?>';
					break;
			}
			$this->source = str_replace($search,$replace,$this->source);
			return true;
		}
		else 
			return false;
	}
}
?>