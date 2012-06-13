<?php
/**
 * Authentication managed
 * 
 *
 * @version 1.0
 * @author Michael Peacock
 */
class Authenticate {

	/**
	 * The user object
	 */
	private $user;
	
	/**
	 * Boolean variable indicating if user is logged in
	 */
	private $loggedIn = false;
	
	/**
	 * Indicates if login has just been processed or not
	 */
	private $justProcessed = false;
	
	/**
	 * Authentication constructor
	 */
    public function __construct( Registry $registry ) 
    {
		$this->registry = $registry;
    }
    
    public function checkForAuthentication()
    {
    	$this->registry->getObject('template')->getPage()->addTag('error', '');
	    	
    	if( isset( $_SESSION['sn_auth_session_uid'] ) && intval( $_SESSION['sn_auth_session_uid'] ) > 0 )
    	{
    		$this->sessionAuthenticate( intval( $_SESSION['sn_auth_session_uid'] ) );
    		
    		if( $this->loggedIn == true )
	    	{
	    		$this->registry->getObject('template')->getPage()->addTag('error', '');
	    	
	    	}
	    	else
	    	{
	    		$this->registry->getObject('template')->getPage()->addTag('error', '<p><strong>Error: Your username or password was not correct, please try again</p><strong>');	
	    	}
    	}
    	elseif( isset(  $_POST['sn_auth_user'] ) &&  $_POST['sn_auth_user'] != '' && isset( $_POST['sn_auth_pass'] ) && $_POST['sn_auth_pass'] != '')
    	{
    		$this->postAuthenticate( $_POST['sn_auth_user'] , $_POST['sn_auth_pass'] );
    		if( $this->loggedIn == true )
	    	{
	    		$this->registry->getObject('template')->getPage()->addTag('error', '');
	    	
	    	}
	    	else
	    	{
	    		$this->registry->getObject('template')->getPage()->addTag('error', '<p><strong>Error: Your username or password was not correct, please try again</p><strong>');	
	    	}
    	}
    	elseif( isset( $_POST['login']) )
    	{
    		$this->registry->getObject('template')->getPage()->addTag('error', '<p><strong>Error: Your must enter a username and a password</p><strong>');	
    	}

    }
    
    private function sessionAuthenticate( $uid )
    {
    	require_once(FRAMEWORK_PATH.'registry/user.class.php');
    	$this->user = new User( $this->registry, intval( $_SESSION['sn_auth_session_uid'] ), '', '' );
    	
    	if( $this->user->isValid() )
    	{
    		if( $this->user->isActive() == false )
    		{
    			$this->loggedIn = false;
    			$this->loginFailureReason = 'inactive';
    		}
    		elseif( $this->user->isBanned() == true )
    		{
    			$this->loggedIn = false;
    			$this->loginFailureReason = 'banned';
    		}
    		else
    		{
    			$this->loggedIn = true;
    		}
    		
    	}
    	else
    	{
    		$this->loggedIn = false;
    		$this->loginFailureReason = 'nouser';
    	}
    	if( $this->loggedIn == false )
    	{
    		$this->logout();
    	}
    	
    }
    
    private function postAuthenticate( $u, $p )
    {
    	$this->justProcessed = true;
    	require_once(FRAMEWORK_PATH.'registry/user.class.php');
    	$this->user = new User( $this->registry, 0, $u, $p );
    	
    	if( $this->user->isValid() )
    	{
    		if( $this->user->isActive() == false )
    		{
    			$this->loggedIn = false;
    			$this->loginFailureReason = 'inactive';
    		}
    		elseif( $this->user->isBanned() == true )
    		{
    			$this->loggedIn = false;
    			$this->loginFailureReason = 'banned';
    		}
    		else
    		{
    			$this->loggedIn = true;
    			$_SESSION['sn_auth_session_uid'] = $this->user->getUserID();
    		}
    		
    	}
    	else
    	{
    		$this->loggedIn = false;
    		$this->loginFailureReason = 'invalidcredentials';
    	}
    }
    
    function logout() 
	{
		$_SESSION['ns_auth_session_uid'] = '';
	}
	
	public function forceLogin( $username, $password )
	{
		$this->postAuthenticate( $username, $password );
	}
    
    public function isLoggedIn()
    {
	    return $this->loggedIn;
    }
    
    public function isJustProcessed()
    {
    	return $this->justProcessed;
    }
    
    public function getUser()
    {
    	return $this->user;
    }
    
}
?>