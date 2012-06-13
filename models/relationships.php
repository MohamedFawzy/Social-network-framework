<?php

class Relationships{
	
	
	public function __construct( Registry $registry )
	{
		$this->registry = $registry; 
	}
	
	/**
	 * Get the types of relationships
	 * @param $cache bool - should we cache the types?
	 * @return mixed [int|array]
	 */
	public function getTypes( $cache=false )
	{
		$sql = "SELECT ID as type_id, name as type_name, plural_name as type_plural_name, mutual as type_mutual FROM relationship_types WHERE active=1";
		if( $cache == true )
		{
			$cache = $this->registry->getObject('db')->cacheQuery( $sql );
			return $cache;
		}
		else
		{
			$types = array();
			while( $row = $this->registry->getObject('db')->getRows() )
			{
				$types[] = $row;
			}
			return $types;
		}
	}
	
	public function getRelationships( $usera, $userb, $approved=0 )
	{
		$sql = "SELECT t.name as type_name, t.plural_name as type_plural_name, uap.name as usera_name, ubp.name as userb_name, r.ID FROM relationships r, relationship_types t, profile uap, profile ubp WHERE t.ID=r.type AND uap.user_id=r.usera AND ubp.user_id=r.userb AND r.accepted={$approved}";
		if( $usera != 0 )
		{
			$sql .= " AND r.usera={$usera} ";
		}
		if( $userb != 0 )
		{
			$sql .= " AND r.userb={$userb} ";
		}
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	public function getByUser( $user )
	{
		$sql = "SELECT t.plural_name, p.name as users_name, u.ID FROM users u, profile p, relationships r, relationship_types t WHERE t.ID=r.type AND r.accepted=1 AND (r.usera={$user} OR r.userb={$user}) AND IF( r.usera={$user},u.ID=r.userb,u.ID=r.usera) AND p.user_id=u.ID";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
}

?>