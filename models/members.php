<?php

class Members{
	
	private $registry;
	
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
	}
	
	/**
	 * Generate paginated members list
	 * @param int $offset the offset
	 * @return Object pagination object
	 */
	public function listMembers( $offset=0 )
	{
		require_once( FRAMEWORK_PATH . 'lib/pagination/pagination.class.php');
		$paginatedMembers = new Pagination( $this->registry );
		$paginatedMembers->setLimit( 25 );
		$paginatedMembers->setOffset( $offset );
		$query = "SELECT u.ID, u.username, p.name, p.dino_name, p.dino_gender, p.dino_breed FROM users u, profile p WHERE p.user_id=u.ID AND u.active=1 AND u.banned=0 AND u.deleted=0";
		$paginatedMembers->setQuery( $query );
		$paginatedMembers->setMethod( 'cache' );
		$paginatedMembers->generatePagination();
		return $paginatedMembers;
		
	}
	
	/**
	 * Generated paginated members list by surname
	 * @param String $letter 
	 * @param int $offset the offset
	 * @return Object pagination object
	 */
	public function listMembersByLetter( $letter='A', $offset=0 )
	{
		
		$alpha = strtoupper( $this->registry->getObject('db')->sanitizeData( $letter ) );
		require_once( FRAMEWORK_PATH . 'lib/pagination/pagination.class.php');
		$paginatedMembers = new Pagination( $this->registry );
		$paginatedMembers->setLimit( 25 );
		$paginatedMembers->setOffset( $offset );
		$query = "SELECT u.ID, u.username, p.name, p.dino_name, p.dino_gender, p.dino_breed FROM users u, profile p WHERE p.user_id=u.ID AND u.active=1 AND u.banned=0 AND u.deleted=0 AND SUBSTRING_INDEX(p.name,' ', -1)LIKE'".$alpha."%' ORDER BY SUBSTRING_INDEX(p.name,' ', -1) ASC";
		$paginatedMembers->setQuery( $query );
		$paginatedMembers->setMethod( 'cache' );
		$paginatedMembers->generatePagination();
		return $paginatedMembers;
		
	}
	
	/**
	 * Search for members based on their name
	 * @param String $filter name
	 * @param int $offset the offset
	 * @return Object pagination object
	 */
	public function filterMembersByName( $filter='', $offset=0 )
	{
		$filter = ( $this->registry->getObject('db')->sanitizeData( urldecode( $filter ) ) );
		require_once( FRAMEWORK_PATH . 'lib/pagination/pagination.class.php');
		$paginatedMembers = new Pagination( $this->registry );
		$paginatedMembers->setLimit( 25 );
		$paginatedMembers->setOffset( $offset );
		$query = "SELECT u.ID, u.username, p.name, p.dino_name, p.dino_gender, p.dino_breed FROM users u, profile p WHERE p.user_id=u.ID AND u.active=1 AND u.banned=0 AND u.deleted=0 AND p.name LIKE'%".$filter."%' ORDER BY p.name ASC";
		$paginatedMembers->setQuery( $query );
		$paginatedMembers->setMethod( 'cache' );
		$paginatedMembers->generatePagination();
		return $paginatedMembers;
	}
	
	
	
}



?>