<?php

class User{
	
	private $id;
	private $username;
	private $email;
	private $registry;
	private $banned;
	private $admin = 0;
	private $active = 0;
	private $valid = false;
	
	public function __construct( Registry $registry, $id=0, $username='', $password='' )
	{
	
		$this->registry = $registry;
		if( $id == 0 && $username != '' && $password != '' )
		{

			$user = $this->registry->getObject('db')->sanitizeData( $username );
			$hash = md5( $password );
			$sql = "SELECT * FROM users WHERE username='{$user}' AND password_hash='{$hash}' AND deleted=0";
			//echo $sql;
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->id = $data['ID'];
				$this->username = $data['username'];
				$this->active = $data['active'];
				$this->banned = $data['banned'];
				$this->admin = $data['admin'];
				$this->email = $data['email'];
				$this->pwd_reset_key = $data['reset_key'];
				$this->valid = true;
			}
		}
		elseif( $id > 0 )
		{
			$id = intval( $id );
			$sql = "SELECT * FROM users WHERE ID='{$id}' AND deleted=0";
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->id = $data['ID'];
				$this->username = $data['username'];
				$this->active = $data['active'];
				$this->banned = $data['banned'];
				$this->admin = $data['admin'];
				$this->email = $data['email'];
				$this->pwd_reset_key = $data['reset_key'];
				$this->valid = true;
			}
		}

	}
	
	public function getUserID()
	{
		return $this->id;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function resetPassword( $password )
	{
		
	}

	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function isActive()
	{
		return ( $this->active == 1 ) ? true : false;
	}
	
	public function isAdmin()
	{
		return ( $this->admin == 1 ) ? true : false;
	}
	
	public function isBanned()
	{
		return ( $this->banned == 1 ) ? true : false;
	}
	
	public function isValid()
	{
		return $this->valid;
	}
	
}

?>