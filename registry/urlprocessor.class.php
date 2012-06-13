<?php

class urlprocessor {
	
	private $urlBits = array();
	private $urlPath;

    public function __construct( Registry $registry )
    {
    	$this->registry = $registry;
    }
    
    /**
     * Set the URL path
     * @param String the url path
     */
    public function setURLPath($path)
	{
		$this->urlPath = $path;
	}
	
	/**
	 * Gets data from the current URL
	 * @return void
	 */
	public function getURLData()
	{
		$urldata = ( isset( $_GET['page'] ) ) ? $_GET['page'] : '' ;
		$this->urlPath = $urldata;
		if( $urldata == '' )
		{
			$this->urlBits[] = '';
			$this->urlPath = '';
		}
		else
		{
			$data = explode( '/', $urldata );
			while ( !empty( $data ) && strlen( reset( $data ) ) === 0 ) 
			{
		    	array_shift( $data );
		    }
		    while ( !empty( $data ) && strlen( end( $data ) ) === 0) 
		    {
		        array_pop($data);
		    }
			$this->urlBits = $this->array_trim( $data );
		}
	}
	
	public function getURLBits()
	{
		return $this->urlBits;
	}
	
	public function getURLBit( $whichBit )
	{
		return ( isset( $this->urlBits[ $whichBit ] ) ) ? $this->urlBits[ $whichBit ]  : 0 ;
	}
	
	public function getURLPath()
	{
		return $this->urlPath;
	}
	
	private function array_trim( $array ) 
	{
	    while ( ! empty( $array ) && strlen( reset( $array ) ) === 0) 
	    {
	        array_shift( $array );
	    }
	    
	    while ( !empty( $array ) && strlen( end( $array ) ) === 0) 
	    {
	        array_pop( $array );
	    }
	    
	    return $array;
	}
	
	public function buildURL( $bits, $qs, $admin )
	{
		$admin = ( $admin == 1 ) ? $this->registry->getSetting('admin_folder') . '/' : '';
		$the_rest = '';
		foreach( $bits as $bit )
		{
			$the_rest .= $bit . '/';
		}
		$the_rest = ( $qs != '' ) ? $the_rest . '?&' .$qs : $the_rest;
		return $this->registry->getSetting('siteurl') . $admin . $the_rest;
		
	}
	
	
	
}
?>