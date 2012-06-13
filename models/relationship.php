<?php

class Relationship{
	
	private $registry;
	private $usera;
	private $userb;
	private $accepted;
	private $id = 0;
	private $type;
	
	/**
	 * Relationship constructor
	 * @param Registry $registry the registry
	 * @param int $id the relationship ID
	 * @param int $usera the id of user a
	 * @param int $userb the id of user b
	 * @param bool $approved if the relationship is approved
	 * @param int $type the ID of the relationship type
	 * @return void
	 */
	public function __construct( Registry $registry, $id=0, $usera, $userb, $approved=0, $type=0 )
	{
		$this->registry = $registry;
		// if no ID is passed, then we want to create a new relationship
		if( $id == 0 )
		{
			$this->createRelationship( $usera, $userb, $approved, $type );
		}
		else
		{
			// if an ID is passed, populate based off that
			$sql = "SELECT * FROM relationships WHERE ID=" . $id;
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->populate( $data['ID'], $data['usera'], $data['userb'], $data['type'], $data['accepted'] );
			}
			
		}
	}
	
	/**
	 * Create a new relationship where one currently doesn't exist, if one does exist, populate from that
	 */
	public function createRelationship( $usera, $userb, $approved=0, $type=0 )
	{
		// check for pre-existing relationship
		$sql = "SELECT * FROM relationships WHERE (usera={$usera} AND userb={$userb}) OR (usera={$userb} AND userb={$usera})";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 1 )
		{
			// one exists: populate
			$data = $this->registry->getObject('db')->getRows();
			$this->populate( $data['ID'], $data['usera'], $data['userb'], $data['type'], $data['accepted'] );
		}
		else
		{
			// one doesnt exist
			if( $type != 0 )
			{
				// check type for mutual
				$sql = "SELECT * FROM relationship_types WHERE ID=" . $type;
				$this->registry->getObject('db')->executeQuery( $sql );
				if( $this->registry->getObject('db')->numRows() == 1 )
				{
					$data = $this->registry->getObject('db')->getRows();
					// auto approve non-mutual relationships
					if( $data['mutual'] == 0 )
					{
						$approved = 1;
					}
				}
				$this->accepted = $approved;
				// create the relationsip
				$insert = array();
				$insert['usera'] = $usera;
				$insert['userb'] = $userb;
				$insert['type'] = $type;
				$insert['accepted'] = $approved;
				$this->registry->getObject('db')->insertRecords( 'relationships', $insert );
				$this->id = $this->registry->getObject('db')->lastInsertID();
			}
		}
		
	}
	
	/**
	 * Approve relationship
	 * @return void
	 */
	public function approveRelationship()
	{
		$this->accepted = true;
	}
	
	
	/** 
	 * Delete relationship
	 * @return void
	 */
	public function delete()
	{
		$this->registry->getObject('db')->deleteRecords( 'relationships', 'ID=' . $this->id, 1 );
		$this->id = 0;
	}
	
	/**
	 * Save relationship
	 * @return void
	 */
	public function save()
	{
		$changes = array();
		$changes['usera'] = $this->usera;
		$changes['userb'] = $this->userb;
		$changes['type'] = $this->type;
		$changes['accepted'] = $this->accepted;
		$this->registry->getObject('db')->updateRecords( 'relationships', $changes, "ID=" . $this->id );
	}
	
	/** 
	 * Populate relationship object
	 * @param int $id the user id
	 * @param int $usera user a
	 * @param int $userb user b
	 * @param int $type the type
	 * @param bool $approved 
	 * @return void
	 */
	private function populate( $id, $usera, $userb, $type, $approved )
	{
		$this->id = $id;
		$this->type = $type;
		$this->usera = $usera;
		$this->userb = $userb;
		$this->accepted = $approved;
	}
	
	public function isApproved()
	{
		return $this->accepted;
	}
	
	public function getUserB()
	{
		return $this->userb;
	}
}

?>