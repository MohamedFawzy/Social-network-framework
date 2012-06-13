<?php

class Testcontroller{
	
	private $registry;
	
	public function __construct( $registry, $directCall=true )
	{
		$this->registry = $registry;
	}
	
	public function getCounter()
	{
		$this->registry->getCounter();
	}
	
	
}

?>