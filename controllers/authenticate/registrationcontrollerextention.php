<?php

/**
 * Registration controller extention
 * Manages profile fields for our Social Network on user registration
 */
class Registrationcontrollerextention{
	
	private $registry;
	private $extraFields = array();
	private $errors = array();
	private $submittedValues = array();
	private $sanitizedValues = array();
	private $errorLabels = array();
	
	public function __construct( $registry )
	{
		$this->registry = $registry;
		$this->extraFields['dino_name'] = array( 'friendlyname' => 'Pet Dinosaurs Name', 'table' => 'profile', 'field' => 'dino_name', 'type' => 'text', 'required' => false );
		$this->extraFields['dino_breed'] = array( 'friendlyname' => 'Pet Dinosaurs Breed', 'table' => 'profile', 'field' => 'dino_breed', 'type' => 'text', 'required' => false );
		$this->extraFields['dino_gender'] = array( 'friendlyname' => 'Pet Dinosaurs Gender', 'table' => 'profile', 'field' => 'dino_gender', 'type' => 'list', 'required' => false, 'options' => array( 'male', 'female') );
		$this->extraFields['dino_dob'] = array( 'friendlyname' => 'Pet Dinosaurs Date of Birth', 'table' => 'profile', 'field' => 'dino_dob', 'type' => 'DOB', 'required' => false );
	}
	
	public function getExtraFields()
	{
		return array_keys( $this->extraFields );
	}
	
	public function checkRegistrationSubmission()
	{
		$valid = true;
		foreach( $this->extraFields as $field => $data )
		{
			if( ( ! isset( $_POST['register_' . $field] ) || $_POST['register_' . $field] == '' ) && $data['required'] = true )
			{
				$this->submittedValues[ $field ] = $_POST['register_' . $field];
				$this->errorLabels['register_' . $field .'_label'] = 'error';
				$this->errors[] = 'Field ' . $data['friendlyname'] . ' cannot be blank';
				$valid = false;
			}
			elseif( $_POST['register_' . $field] == '' )
			{
				$this->submittedValues[ 'register_' . $field ] = '';
			}
			else
			{
				if( $data['type'] == 'text' )
				{
					$this->sanitizedValues[ 'register_' . $field ] = $this->registry->getObject('db')->sanitizeData( $_POST['register_' . $field] );
					$this->submittedValues['register_' . $field] = $_POST['register_' . $field];
				}
				elseif( $data['type'] == 'int' )
				{
					$this->sanitizedValues[ 'register_' . $field ] = intval( $_POST['register_' . $field] );
					$this->submittedValues['register_' . $field] = $_POST['register_' . $field];
				}
				elseif(  $data['type'] == 'list'  )
				{
					if( ! in_array( $_POST['register_' . $field], $data['options'] ) )
					{
						$this->submittedValues[ 'register_' .$field ] = $_POST['register_' . $field];
						$this->sanitizedValues[ 'register_' . $field ] = $this->registry->getObject('db')->sanitizeData( $_POST['register_' . $field] );
					
						$this->errorLabels['register_' . $field .'_label'] = 'error';
						$this->errors[] = 'Field ' . $data['friendlyname'] . ' was not valid';
				
						$valid = false;
					}
					else
					{
						$this->sanitizedValues[ 'register_' . $field ] = intval( $_POST['register_' . $field] );
						$this->submittedValues['register_' . $field] = $_POST['register_' . $field];
					}
				}
				else
				{
					$method = 'validate' . $data['type'];
					if( $this->$method( $_POST['register_' . $field] ) == true )
					{
						$this->sanitizedValues[ 'register_' . $field ] = $this->registry->getObject('db')->sanitizeData( $_POST['register_' . $field] );
						$this->submittedValues['register_' . $field] = $_POST['register_' . $field];
					}
					else
					{
						$this->sanitizedValues[ 'register_' . $field ] = $this->registry->getObject('db')->sanitizeData( $_POST['register_' . $field] );
						$this->submittedValues['register_' . $field] = $_POST['register_' . $field];
						$this->errors[] = 'Field ' . $data['friendlyname'] . ' was not valid';
						$valid = false;
					}
				}
			}
		}
		if( $valid == true )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	private function validateDOB( $value )
	{
		// logic based on code from http://www.smartwebby.com/PHP/datevalidation.asp
		if( (strlen( $value ) != 10 ) )
		{
			return false;
		}
		else
		{
			// it needs two of these /
			if( substr_count( $value, '/' ) != 2 )
			{
				return false;
			}
			else
			{
				$date_parts = explode( '/', $value );
				// is the date valid?
				if( $date_parts[0] < 0 || $date_parts[0] > 31 )
				{
					return false;
				}
				else
				{
					// check the month is valid
					if( $date_parts[1] < 0 || $date_parts[1] > 12 )
					{
						return false;
					}
					else
					{
						// check the year is almost realistic.
						// note: needs updating in 2099 ;)
						if( $date_parts[2] < 1880 || $date_parts[2] > 2100 )
						{
							return false;
						}
						else
						{
							return true;
						}
					}
				}
			}
		}
	}
	
	/**
	 * Create our user profile
	 * @param int $uid the user ID
	 * @return bool
	 */
	public function processRegistration( $uid )
	{
		$tables = array();
		$tableData = array();
		
		// group our profile fields by table, so we only need to do one insert per table 
		foreach( $this->extraFields as $field => $data )
		{
			if( ! ( in_array( $data['table'], $tables ) ) )
			{
				$tables[] = $data['table'];
				$tableData[ $data['table'] ] = array( 'user_id' => $uid, $data['field'] => $this->sanitizedValues[ 'register_' . $field ]);
			}
			else
			{
				$tableData[ $data['table'] ][$data['field']] = $this->sanitizedValues[ 'register_' . $field ];
			}
		}
		foreach( $tableData as $table => $data )
		{
			$this->registry->getObject('db')->insertRecords( $table, $data );
		}
		return true;
	}
	
	public function getRegistrationErrors()
	{
		return $this->errors;
	}
	
	public function getRegistrationValues()
	{
		return $this->submittedValues;
	}
	
	public function getErrorLabels()
	{
		return $this->errorLabels;	
	}
	
	
}


?>