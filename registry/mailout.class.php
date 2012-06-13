<?php

class mailout {


	private $message;
	private $headers;
	private $to;
	private $from;
	private $lock;
	private $type;
	private $error;
	private $subject;
	private $fromName;
	private $method;
	
	
     public function __construct( Registry $registry ) 
    {
		$this->registry = $registry;
    	$this->startFresh();
    }
    
    public function startFresh()
	{
		// not in constructor because object is reused, so this is done on each "new email"
		$this->lock = false;
		$this->error = 'Message not sent because: ';
		$this->message = '';
	}
	
	/**
	 * Sets the recipient
	 * @param String the recipient
	 * @return bool
	 */
	public function setTo( $to )
	{
		if(eregi("\r",(urldecode($to))) || eregi("\n",(urldecode($to))))
		{
				
			// bad - header injections
				
			$this->lock();
			$this->error .= ' Receipient Email header injection attempt, probably caused by spam attempts';
			return false;
			
				
		}
		elseif( ! eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $to) )
		{
			// bad - invalid email
				
			$this->lock();
			$this->error .= ' Receipient Email address no valid';
			return false;
				
		}
		else
		{
			//good - let's do it!
			$this->to = $to;
			return true;
			
		}
		
	}
	
	/**
	 * Build email message from text (as opposed to template)
	 * @param String the message
	 * @return void
	 */
	public function buildFromText( $message )
	{
		$this->message .= $message;
	}
	
	/**
	 * Sets the sender (first header set - must be done before appending to header
	 * @param String email address (if null used, email taken from shared array
	 * @return bool
	 */
	public function setSender( $email )
	{
		if( $email == '' )
		{
			// No email passed - use something from the registry
			$this->headers = 'From: '.$this->registry->getSetting('adminEmailAddress');
			$this->from = $this->registry->getSetting('adminEmailAddress');
			return true;
		}
		else
		{
			if( strpos( ( urldecode( $email ) ), "\r" ) === true || strpos( ( urldecode( $email ) ), "\n" ) === true )
			{
				// bad - header injections
				$this->lock();
				$this->error .= ' Email header injection attempt, probably caused by spam attempts';
				return false;
				
			}
			elseif( ! preg_match( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})^", $email ) )
			{
				// bad - invalid email
				$this->lock();
				$this->error .= ' Email address not valid';
				return false;
			}
			else
			{
				//good - let's do it!
				$this->headers = 'From: '.$email;
				$this->from = $email;
				return true;
			}

		}
	}
	
	public function setSenderIgnoringRules( $email )
	{
		$this->headers = 'From: ' . $email;
	}
	
	/**
	 * Appends header fields to the email header - note setSender must be called first
	 * @param String the information to append
	 * @return void
	 */
	public function appendHeader( $toAppend )
	{
		$this->headers .= "\r\n" .	$toAppend;
	}
	
	/**
	 * Locks the email to prevent sending
	 * @return void
	 */
	public function lock()
	{
		$this->lock = true;
	}
	
	public function buildFromTemplates()
    {
	    $bits = func_get_args();
	    $content = "";
	    foreach( $bits as $bit )
	    {
		    
		    if( strpos( $bit, 'emailtemplates/' ) === false )
		    {
			    $bit = 'emailtemplates/' . $bit;
		    }
		    if( file_exists( $bit ) == true )
		    {
			    $content .= file_get_contents( $bit );
		    }
		    
	    }
	    $this->message =  $content;
    }
    
    public function replaceTags( $tags )
    {
	    // go through them all
	    if( sizeof($tags) > 0 )
	    {
	    	foreach( $tags as $tag => $data )
		    {
			    // if the tag is an array, then we need to do more than a simple find and replace!
			    if( ! is_array( $data ) )
			    {
			    	// replace the content	    	
			    	$newContent = str_replace( '{' . $tag . '}', $data, $this->message );
			    	// update the pages content
			    	$this->message = $newContent;
		    	}
		    }
	    }
	    
    }
    
    public function setMethod( $method )
	{
		$this->method = $method;
	}
	
	public function setSubject( $subject )
	{
		$this->subject = $subject;
	}
	
	/** 
	 * Sends the email
	 * @return void
	 */
	public function send()
	{
		switch( $this->method )
		{
			case 'sendmail':
				return $this->sendWithSendmail();
				break;
			case 'smtp':
				return $this->sendWithSmtp();
				break;
			default:
				return $this->sendWithSendmail();
				
		}
	}
	
	/**
	 * Sends the email using Send Mail
	 * @return void
	 */
	public function sendWithSendmail()
	{
		if($this->lock == true)
		{
			return false;
		}
		else
		{
			if( ! @mail($this->to, $this->subject, $this->message, $this->headers) )
			{
				$this->error .= ' problems sending via PHP\'s mail function';
				return false;
			}
			else
			{
				return true;
			}
		}
	}
	
	public function setFromName( $name )
	{
		$this->fromName = $name;
	}
	
	public function sendWithSMTP()
	{
  		
	}
	
	

    
    
}
?>