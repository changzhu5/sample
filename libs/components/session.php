<?php
/**
 * Session component, fundamental of session read and write
 *
 */
class Session extends Component 
{
	public function startup()
	{
		session_start();
	}
	public function read($key)
	{
		if(isset($_SESSION[$key]))
		{
			return $_SESSION[$key];
		}
		return null;
	}
	public function write($key,$val)
	{
		$_SESSION[$key] = $val;
	}
}
?>