<?php

class Relationshipcontroller {
	
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
				case 'create':
					$this->createRelationship( intval( $urlBits[2] ) );
					break;	
				case 'approve':
					$this->approveRelationship( intval( $urlBits[2] ) );
					break;
				case 'reject':
					$this->rejectRelationship( intval( $urlBits[2] ) );
					break;
				default:
					break;
			}
			
		}
		else
		{
		}
		
	}
	
	private function createRelationship( $userb )
	{
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			$usera = $this->registry->getObject('authenticate')->getUser()->getUserID();
			$type = intval( $_POST['relationship_type'] );
			//echo '<pre>' . print_r( $_POST, true ) . '</pre>';
			require_once( FRAMEWORK_PATH . 'models/relationship.php');
			$relationship = new Relationship( $this->registry, 0, $usera, $userb, 0, $type );
			if( $relationship->isApproved() )
			{
				// email the user, tell them they have a new connection
				/**
				 * Can you remember how the email sending object works?
				 */
				 $this->registry->errorPage('Relationship created', 'Thank you for connecting!');
			}
			else
			{
				// email the user, tell them they have a new pending connection
				/**
				 * Can you remember how the email sending object works?
				 */
				 $this->registry->errorPage('Request sent', 'Thanks for requesting to connect!');
			}
			
		}
		else
		{
			$this->registry->errorPage('Please login', 'Only logged in members can connect on this site');
			// display an error
		}
	}
	
	private function approveRelationship( $r )
	{
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			require_once( FRAMEWORK_PATH . 'models/relationship.php');
			$relationship = new Relationship( $this->registry, $r, 0, 0, 0, 0 );
			if( $relationship->getUserB() == $this->registry->getObject('authenticate')->getUser()->getUserID() )
			{
				// we can approve this!
				$relationship->approveRelationship();
				$relationship->save();
				$this->registry->errorPage( 'Relationship approved', 'Thank you for approving the relationship');
			}
			else
			{
				$this->registry->errorPage('Invalid request', 'You are not authorized to approve that request');
			}
		}
		else
		{
			$this->registry->errorPage('Please login', 'Please login to approve this connection');
		}
		
	}
	
	private function rejectRelationship( $r )
	{
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			require_once( FRAMEWORK_PATH . 'models/relationship.php');
			$relationship = new Relationship( $this->registry, $r, 0, 0, 0, 0 );
			if( $relationship->getUserB() == $this->registry->getObject('authenticate')->getUser()->getUserID() )
			{
				// we can reject this!
				$relationship->delete();
				$this->registry->errorPage( 'Relationship rejected', 'Thank you for rejecting the relationship');
			}
			else
			{
				$this->registry->errorPage('Invalid request', 'You are not authorized to reject that request');
			}
		}
		else
		{
			$this->registry->errorPage('Please login', 'Please login to reject this connection');
		}
	}
	
	
	
}


?>