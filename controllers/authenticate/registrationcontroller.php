<?php

/**
 * Registration controller
 * Manages user registration etc. 
 */
class Registrationcontroller{
	
	/**
	 * Our registry object
	 */
	private $registry;
	
	/**
	 * Standard registration fields
	 */
	private $fields = array( 'user' => 'username', 'password' => 'password', 'password_confirm' => 'password confirmation', 'email' => 'email address');
	
	/**
	 * Any errors in the registration
	 */
	private $registrationErrors = array();
	
	/**
	 * Array of error label classes - allows us to make a field a different color, to indicate there were errors
	 */
	private $registrationErrorLabels = array();
	
	/**
	 * The values the user has submitted when registering
	 */
	private $submittedValues = array();
	
	/**
	 * The santized versions of the values the user has submitted - these are database ready
	 */
	private $sanitizedValues = array();
	
	/**
	 * Should our users automatically be "active" or should they require email verification? 
	 */
	private $activeValue = 1;
	
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
		require_once FRAMEWORK_PATH . 'controllers/authenticate/registrationcontrollerextention.php';
		$this->registrationExtention = new Registrationcontrollerextention( $this->registry );
		if( isset( $_POST['process_registration'] ) )
		{
			if( $this->checkRegistration() == true )
			{
				$userId = $this->processRegistration();
				if( $this->activeValue == 1 )
				{
					$this->registry->getObject('authenticate')->forceLogin( $this->submittedValues['register_user'], md5( $this->submittedValues['register_password'] ) );
				}
				$this->uiRegistrationProcessed();
			}
			else
			{
				$this->uiRegister( true );
			}
			
		}
		else
		{
			$this->uiRegister( false );
		}
	}
	
	/**
	 * Process the users registration, and create the user and users profiles
	 * @return int
	 */
	private function processRegistration()
	{
		// insert
		$this->registry->getObject('db')->insertRecords( 'users', $this->sanitizedValues );
		// get ID
		$uid = $this->registry->getObject('db')->lastInsertID();
		// call extention to insert the profile
		$this->registrationExtention->processRegistration( $uid );
		// return the ID for the frameworks reference - autologin?
		return $uid;
	}
	
	private function checkRegistration()
	{
		$allClear = true;
		// blank fields
		foreach( $this->fields as $field => $name )
		{
			if( ! isset( $_POST[ 'register_' . $field ] ) || $_POST[ 'register_' . $field ] == '' )
			{
				$allClear = false;
				$this->registrationErrors[] = 'You must enter a ' . $name;
				$this->registrationErrorLabels['register_' . $field . '_label'] = 'error';
			}
		}
		
		// passwords match
		if( $_POST[ 'register_password' ]!= $_POST[ 'register_password_confirm' ] )
		{
			$allClear = false;
			$this->registrationErrors[] = 'You must confirm your password';
			$this->registrationErrorLabels['register_password_label'] = 'error';
			$this->registrationErrorLabels['register_password_confirm_label'] = 'error';
		}

		// password length
		if( strlen( $_POST['register_password'] ) < 6 )
		{
			$allClear = false;
			$this->registrationErrors[] = 'Your password is too short, it must be at least 6 characters';
			$this->registrationErrorLabels['register_password_label'] = 'error';
			$this->registrationErrorLabels['register_password_confirm_label'] = 'error';
		}
		
		
		// email headers
		if( strpos( ( urldecode( $_POST[ 'register_email' ] ) ), "\r" ) === true || strpos( ( urldecode( $_POST[ 'register_email' ] ) ), "\n" ) === true )
		{
			$allClear = false;
			$this->registrationErrors[] = 'Your email address is not valid (security)';
			$this->registrationErrorLabels['register_email_label'] = 'error';
		}
		
		// email valid
		if( ! preg_match( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})^", $_POST[ 'register_email' ] ) )
		{
			$allClear = false;
			$this->registrationErrors[] = 'You must enter a valid email address';
			$this->registrationErrorLabels['register_email_label'] = 'error';

		}
		
		// terms accepted
		if( ! isset( $_POST['register_terms'] ) || $_POST['register_terms'] != 1 )
		{
			$allClear = false;
			$this->registrationErrors[] = 'You must accept our terms and conditions.';
			$this->registrationErrorLabels['register_terms_label'] = 'error';
		}
		
		// duplicate user+email check
		$u = $this->registry->getObject('db')->sanitizeData( $_POST['register_user'] );
		$e = $this->registry->getObject('db')->sanitizeData( $_POST['register_email'] );
		$sql = "SELECT * FROM users WHERE username='{$u}' OR email='{$e}'";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 2 )
		{
			$allClear = false;
			// both	
			$this->registrationErrors[] = 'Both your username and password are already in use on this site.';
			$this->registrationErrorLabels['register_user_label'] = 'error';
			$this->registrationErrorLabels['register_email_label'] = 'error';
		}
		elseif( $this->registry->getObject('db')->numRows() == 1 )
		{
			// possibly both, or just one
			$u = $this->registry->getObject('db')->sanitizeData( $_POST['register_user'] );
			$e = $this->registry->getObject('db')->sanitizeData( $_POST['register_email'] );
			$data = $this->registry->getObject('db')->getRows();
			if( $data['username'] == $u && $data['email'] == $e )
			{
				$allClear = false;
				$this->registrationErrors[] = 'Both your username and password are already in use on this site.';
				$this->registrationErrorLabels['register_user_label'] = 'error';
				$this->registrationErrorLabels['register_email_label'] = 'error';
				// both	
			}
			elseif( $data['username'] == $u )
			{
				$allClear = false;
				// username	
				$this->registrationErrors[] = 'Your username is already in use on this site.';
				$this->registrationErrorLabels['register_user_label'] = 'error';
				
			}
			else
			{
				$allClear = false;
				// email address	
				$this->registrationErrors[] = 'Your email address is already in use on this site.';
				$this->registrationErrorLabels['register_email_label'] = 'error';
			}
		}
		// captcha
		if( $this->registry->getSetting('captcha.enabled') == 1 )
		{
			// captcha check
		}
		
		// hook
		if( $this->registrationExtention->checkRegistrationSubmission() == false )
		{
			$allClear = false;
		}
		
		if( $allClear == true )
		{
			$this->sanitizedValues['username'] = $u;
			$this->sanitizedValues['email'] = $e;
			$this->sanitizedValues['password_hash'] = md5( $_POST['register_password'] );
			$this->sanitizedValues['active'] = $this->activeValue;
			$this->sanitizedValues['admin'] = 0;
			$this->sanitizedValues['banned'] = 0;
			
			$this->submittedValues['register_user'] = $_POST['register_user'];
			$this->submittedValues['register_password'] = $_POST['register_password'];
			return true;
		}
		else
		{
			$this->submittedValues['register_user'] = $_POST['register_user'];
			$this->submittedValues['register_email'] = $_POST['register_email'];
			$this->submittedValues['register_password'] = $_POST['register_password'] ;
			$this->submittedValues['register_password_confirm'] = $_POST['register_password_confirm'] ;
			$this->submittedValues['register_captcha'] = ( isset( $_POST['register_captcha'] ) ? $_POST['register_captcha']  : '' );
			return false;
		}
		
		
		
	}
	
	private function uiRegistrationProcessed()
	{
		$this->registry->getObject('template')->getPage()->setTitle( 'Registration for ' . $this->registry->getSetting('sitename') . ' complete');
		$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/register/complete.tpl.php', 'footer.tpl.php' );
		
	}
	
	private function uiRegister( $error )
	{
		$this->registry->getObject('template')->getPage()->setTitle( 'Register for ' . $this->registry->getSetting('sitename') );
		$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/register/main.tpl.php', 'footer.tpl.php' );
		// blank out the field tags
		$fields = array_keys( $this->fields );
		$fields = array_merge( $fields, $this->registrationExtention->getExtraFields() );
		foreach( $fields as $field )
		{
			$this->registry->getObject('template')->getPage()->addTag( 'register_' . $field . '_label', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'register_' . $field, '' );
		}
		if( $error == false )
		{
			$this->registry->getObject('template')->getPage()->addTag( 'error', '' );
		}
		else
		{
			$this->registry->getObject('template')->addTemplateBit( 'error', 'authenticate/register/error.tpl.php');
			$errorsData = array();
			$errors = array_merge( $this->registrationErrors, $this->registrationExtention->getRegistrationErrors() );
			foreach( $errors as $error )
			{
				$errorsData[] = array( 'error_text' => $error );
			}
			$errorsCache = $this->registry->getObject('db')->cacheData( $errorsData );
			$this->registry->getObject('template')->getPage()->addTag( 'errors', array( 'DATA', $errorsCache ) );
			$toFill = array_merge( $this->submittedValues, $this->registrationExtention->getRegistrationValues(), $this->registrationErrorLabels, $this->registrationExtention->getErrorLabels() );
			foreach( $toFill as $tag => $value )
			{
				$this->registry->getObject('template')->getPage()->addTag( $tag, $value );
			}
		}
	}
	
	
}



?>