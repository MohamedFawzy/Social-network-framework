<?php
/**
 * PHP Social Networking
 * @author Michael Peacock
 * Registry Class
 */
class Registry {
	
	/**
	 * Array of objects
	 */
	private $objects;
	
	/**
	 * Array of settings
	 */
	private $settings;
	
    public function __construct() {
    }
    
    /**
     * Create a new object and store it in the registry
     * @param String $object the object file prefix
     * @param String $key pair for the object
     * @return void
     */
    public function createAndStoreObject( $object, $key )
    {
    	require_once( $object . '.class.php' );
    	$this->objects[ $key ] = new $object( $this );
    }
    
    /**
     * Get an object from the registries store
     * @param String $key the objects array key
     * @return Object
     */
    public function getObject( $key )
    {
    	return $this->objects[ $key ];
    }
    
    /**
     * Store Setting
     * @param String $setting the setting data
     * @param String $key the key pair for the settings array
     * @return void
     */
    public function storeSetting( $setting, $key )
    {
    	$this->settings[ $key ] = $setting;
    }
    
    /**
     * Get a setting from the registries store
     * @param String $key the settings array key
     * @return String the setting data
     */
    public function getSetting( $key )
    {
    	return $this->settings[ $key ];
    }
    
    public function errorPage( $heading, $content )
    {
    	$this->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
    	$this->getObject('template')->getPage()->addTag( 'heading', $heading );
    	$this->getObject('template')->getPage()->addTag( 'content', $content );
    }
    
    
}

?>