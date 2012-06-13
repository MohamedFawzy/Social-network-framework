<?php

class Relationshipscontroller{
	
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
				case 'pending':
					$this->pendingRelationships();
					break;	
				default:
					$this->myRelationships();
					break;
			}
			
		}
		else
		{
			$this->myRelationships();
		}
		
	}
	
	private function myRelationships()
	{
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			require_once( FRAMEWORK_PATH . 'models/relationships.php');
			$relationships = new Relationships( $this->registry );
			$relationships = $relationships->getByUser( $this->registry->getObject('authenticate')->getUser()->getUserID() );
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'friends/mine.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag( 'connections', array( 'SQL', $relationships ) );
		}
		else
		{
			$this->registry->errorPage('Please login', 'You need to be a logged in user to see your friends');
		}
	}
	
	private function pendingRelationships()
	{
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			require_once( FRAMEWORK_PATH . 'models/relationships.php');
			$relationships = new Relationships( $this->registry );
			$pending = $relationships->getRelationships( 0, $this->registry->getObject('authenticate')->getUser()->getUserID(), 0 );
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'friends/pending.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('pending', array( 'SQL', $pending ) );
			
		}
		else
		{
			$this->registry->errorPage( 'Please login', 'Please login to manage pending connections');
		}
	}
}
?>