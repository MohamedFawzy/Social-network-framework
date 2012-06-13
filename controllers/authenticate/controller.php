<?php
class Authenticatecontroller{
	
	/**
	 * Registry object reference
	 */
	private $registry;
	
	/**
	 * Quotation model object reference
	 */
	private $model;
	
	/**
	 * Controller constructor - direct call to false when being embedded via another controller
	 * @param Registry $registry our registry
	 * @param bool $directCall - are we calling it directly via the framework (true), or via another controller (false)
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		
		$urlBits = $this->registry->getObject('url')->getURLBits();
			if( isset( $urlBits[1] ) )
			{
				switch( $urlBits[1] )
				{
					case 'logout':
						$this->logout();
						break;
					case 'login':
						$this->login();
						break;
					case 'username':
						$this->forgotUsername();
						break;
					case 'password':
						$this->forgotPassword();
						break;
					case 'reset-password':
						$this->resetPassword( intval($urlBits[2]), $this->registry->getObject('db')->sanitizeData($urlBits[3]) );
						break;
					case 'register':
						$this->registrationDelegator();
						break;
				}
				
			}
		
	}
	
	private function forgotUsername()
	{
		if( isset( $_POST['email'] ) && $_POST['email'] != '' )
		{
			$e = $this->registry->getObject('db')->sanitizeData( $_POST['email'] );
			$sql = "SELECT * FROM users WHERE email='{$e}'";
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				// email the user
				$this->registry->getObject('mailout')->startFresh();
				$this->registry->getObject('mailout')->setTo( $_POST['email'] );
				$this->registry->getObject('mailout')->setSender( $this->registry->getSetting('adminEmailAddress') );
				$this->registry->getObject('mailout')->setFromName( $this->registry->getSetting('cms_name') );
				$this->registry->getObject('mailout')->setSubject( 'Username details for ' .$this->registry->getSetting('sitename') );
				$this->registry->getObject('mailout')->buildFromTemplates('authenticate/username.tpl.php');
				$tags = $this->values;
				$tags[ 'sitename' ] = $this->registry->getSetting('sitename');
				$tags['username'] = $data['username'];
				$tags['siteurl'] = $this->registry->getSetting('site_url');
				$this->registry->getObject('mailout')->replaceTags( $tags );
				$this->registry->getObject('mailout')->setMethod('sendmail');
				$this->registry->getObject('mailout')->send();
				
				// tell them that we emailed them
				$this->registry->errorPage('Username reminder sent', 'We have sent you a reminder of your username, to the email address we have on file');
				
			}
			else
			{
				// no user found
				$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/username/main.tpl.php', 'footer.tpl.php');
				$this->registry->getObject('template')->addTemplateBit('error_message', 'authenticate/username/error.tpl.php');
			}
		}
		else
		{
			// form template
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/username/main.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('error_message', '');
		}
	}
	
	private function generateKey( $len = 7 )
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		// 36 chars
		$tor = '';
		for( $i = 0; $i < $len; $i++ )
		{
			$tor .= $chars[ rand() % 35 ];
		}
		return $tor;
	}
	
	private function forgotPassword()
	{
		if( isset( $_POST['username'] ) && $_POST['username'] != '' )
		{
			$u = $this->registry->getObject('db')->sanitizeData( $_POST['username'] );
			$sql = "SELECT * FROM users WHERE username='{$u}'";
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				// have they requested a new password recently?
				if( $data['reset_expires'] > date('Y-m-d h:i:s') )
				{
					// inform them
					$this->registry->errorPage('Error sending password request', 'You have recently requested a password reset link, and as such you must wait a short while before requesting one again.  This is for security reasons.');
				}
				else
				{
					// update their row
					$changes = array();
					$rk = $this->generateKey();
					$changes['reset_key'] = $rk;
					$changes['reset_expires'] = date( 'Y-m-d h:i:s', time()+86400 );
					$this->registry->getObject('db')->updateRecords( 'users', $changes, 'ID=' . $data['ID'] );
					// email the user
					$this->registry->getObject('mailout')->startFresh();
					$this->registry->getObject('mailout')->setTo( $_POST['email'] );
					$this->registry->getObject('mailout')->setSender( $this->registry->getSetting('adminEmailAddress') );
					$this->registry->getObject('mailout')->setFromName( $this->registry->getSetting('cms_name') );
					$this->registry->getObject('mailout')->setSubject( 'Password reset request for ' .$this->registry->getSetting('sitename') );
					$this->registry->getObject('mailout')->buildFromTemplates('authenticate/password.tpl.php');
					$tags = $this->values;
					$tags[ 'sitename' ] = $this->registry->getSetting('sitename');
					$tags['username'] = $data['username'];
					$url = $this->registry->buildURL( 'authenticate', 'reset-password', $data['ID'], $rk );
					$tags['url'] = $url;
					$tags['siteurl'] = $this->registry->getSetting('site_url');
					$this->registry->getObject('mailout')->replaceTags( $tags );
					$this->registry->getObject('mailout')->setMethod('sendmail');
					$this->registry->getObject('mailout')->send();
					
					// tell them that we emailed them
					$this->registry->errorPage('Password reset link sent', 'We have sent you a link which will allow you to reset your account password');
				}
				
			}
			else
			{
				// no user found
				$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/password/main.tpl.php', 'footer.tpl.php');
				$this->registry->getObject('template')->addTemplateBit('error_message', 'authenticate/password/error.tpl.php');
			}
		}
		else
		{
			// form template
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/password/main.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('error_message', '');
		}
	}
	
	private function resetPassword( $user, $key )
	{
		$this->registry->getObject('template')->getPage()->addTag( 'user', $user );
		$this->registry->getObject('template')->getPage()->addTag('key', $key );
		$sql = "SELECT * FROM users WHERE ID={$user} AND reset_key='{$key}'";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 1 )
		{
			$data = $this->registry->getObject('db')->getRows();
			if( $data['reset_expiry'] > date('Y-m-d h:i:s') )
			{
				$this->registry->errorPage('Reset link expired', 'Password reset links are only valid for 24 hours.  This link is out of date and has expired.');
				
			}
			else
			{
				if( isset( $_POST['password'] ) )
				{
					if( strlen( $_POST['password'] ) < 6 )
					{
						$this->registry->errorPage( 'Password too short', 'Sorry, your password was too short, passwords must be greater than 6 characters');
					}
					else
					{
						if( $_POST['password'] != $_POST['password_confirm'] )
						{
							$this->registry->errorPage( 'Passwords do not match', 'Your password and password confirmation do not match, please try again.');
						}
						else
						{
							// reset the password
							$changes = array();
							$changes['password_hash'] = md5( $_POST['passowrd'] );
							$this->registry->getObject('db')->updateRecords( 'users', $changes, 'ID=' . $user );
							$this->registry->errorPage('Password reset', 'Your password has been reset to the one you entered');
							
						}
					}
				}
				else
				{
					// show the form
					$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/password/reset.tpl.php', 'footer.tpl.php');
			
				}
			}
		}
		else
		{
			$this->registry->errorPage('Invalid details', 'The password reset link was invalid');
		}
	}
	
	private function login()
	{
		// template
		if( $this->registry->getObject('authenticate')->isJustProcessed() )
		{
			
			if( isset( $_POST['login'] ) && $this->registry->getObject('authenticate')->isLoggedIn() == false )
			{
				// invalid details	
				//$this->registry->getObject('template')->addTemplateBit('error_message', 'authenticate/login/error.tpl.php');
			}
			else
			{
				// bounce them away!
				if( $_POST['referer'] == '' )
				{
					$referer = $this->registry->getSetting('siteurl');
					$this->registry->redirectUser( $referer, 'Logged in', 'Thanks, you are now logged in, you are now being redirected to the page you were previously on', false);
				}
				else
				{
					$this->registry->redirectUser( $_POST['referer'], 'Logged in', 'Thanks, you are now logged in, you are now being redirected to the page you were previously on', false);
				}
			}
		}
		else
		{
			if( $this->registry->getObject('authenticate')->isLoggedIn() == true )
			{
				$this->registry->errorPage( 'Already logged in', 'You cannot login as you are already logged in as <strong>' . $this->registry->getObject('authenticate')->getUser()->getUsername() . '</strong>');	
			}
			else
			{
				$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/login/main.tpl.php', 'footer.tpl.php' );
				$this->registry->getObject('template')->getPage()->addTag( 'referer', ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER']  : '' ) );
			}
		}
		
	}
	
	private function logout()
	{
		$this->registry->getObject('authenticate')->logout();
		$this->registry->getObject('template')->addTemplateBit('userbar', 'userbar-guest.tpl.php');
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'login.tpl.php', 'footer.tpl.php');
	}
	
	/**
	 * Delegate control to the registration controller
	 * @return void
	 */
	private function registrationDelegator()
	{
		require_once FRAMEWORK_PATH . 'controllers/authenticate/registrationcontroller.php';
		$rc = new Registrationcontroller( $this->registry );
		
	}
	
	
}
		
		
?>