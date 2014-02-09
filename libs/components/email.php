<?php
class Email extends Component
{
	private $to;
	private $subject;
	private $message;
	private $fromName;
	private $fromEmail;
	private $replyEmail;
	private $header;
	private $type = 'text/plain';
	private $characterSet = 'utf-8';
	
	public function startup(){}
	public function init($to,$subject,$message,$fromEmail=null,$type=null,$charset=null)
	{
		if(! is_null($type))
		{
			$this->type = $type;
		}
		if(! is_null($charset))
		{
			$this->characterSet = $charset;
		}
		if(! is_null($fromEmail))
		{
			$this->fromEmail = $fromEmail;
			$arr = explode('@',$fromEmail);
			$this->fromName = $arr[0];
			$this->replyEmail = $fromEmail;
		}
		$this->to = $to;
		$this->subject = $subject;
		$this->message = $message;
	}
	public function send()
	{
		$this->createHeader();
		if(mail($this->to,$this->subject,$this->message,$this->header))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	private function createHeader()
	{
		if($this->fromEmail)
		{
			$from = "From {$this->fromName}<{$this->fromEmail}>\r\n";
			$replay = "Reply-To:{$this->replyEmail}\r\n";
		}
		$params = "MIME-Version:1.0\r\n";
		$params .= "Content-type:{$this->type};charset={$this->characterSet}\r\n";
		
		$this->header = $from . $replay . $params;
		return $this->header;
	}	
}

?>