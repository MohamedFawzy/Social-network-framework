<?php

/**
 * Pagination class
 * Making pagination of records easy(ier)
 */
class Pagination {
	
	/**
	 * The query we will be paginating
	 */
	private $query = "";
	
	/**
	 * The processed query which will be executed
	 */
	private $executedQuery = "";
	
	/**
	 * The maximum number of results to display per page
	 */
	private $limit = 25;
	
	/**
	 * The results offset - i.e. page we are on (-1)
	 */
	private $offset = 0;
	
	/**
	 * The method of pagination
	 */
	private $method = 'query';
	
	/**
	 * The cache ID if we paginate by caching results
	 */
	private $cache;
	
	/**
	 * The results set if we paginate by executing directly
	 */
	private $results;
	
	/**
	 * The number of rows there were in the query passed
	 */
	private $numRows;
	
	/**
	 * The number of rows on the current page (main use if on last page, may not have as many as limit on the page)
	 */
	private $numRowsPage;
	
	/**
	 * Number of pages of results tehre are
	 */
	private $numPages;
	
	/**
	 * Is this the first page of results?
	 */
	private $isFirst;
	
	/**
	 * Is this the last page of results?
	 */
	private $isLast;
	
	/**
	 * The current page we are on
	 */
	private $currentPage;
	
	/**
	 * Our constructor
	 * @param Object registry
	 * @return void
	 */
    function __construct( Registry $registry) 
    {
    	$this->registry = $registry;
    }
    
    /**
     * Set the query to be paginated
     * @param String $sql the query
     * @return void
     */
    public function setQuery( $sql )
    {
    	$this->query = $sql;
    }
    
    /**
     * Set the limit of how many results should be displayed per page
     * @param int $limit the limit
     * @return void
     */
    public function setLimit( $limit )
    {
    	$this->limit = $limit;	
    }
    
    /**
     * Set the offset - i.e. if offset is 1, then we show the next page of results
     * @param int $offset the offset
     * @return void
     */
    public function setOffset( $offset )
    {
    	$this->offset = $offset;
    }
    
    /**
     * Set the method we want to use to paginate
     * @param String $method [cache|do]
     * @return void
     */
    public function setMethod( $method )
    {
    	$this->method = $method;
    }
    
    /**
     * Process the query, and set the paginated properties
     * @return bool
     */
    public function generatePagination()
    {
    	$temp_query = $this->query;
    	
    	// how many results?
    	$this->registry->getObject('db')->executeQuery( $temp_query );
    	$nums = $this->registry->getObject('db')->numRows();
    	$this->numRows = $nums;
    	
    	// limit!
    	$limit = " LIMIT ";
    	$limit .= ( $this->offset * $this->limit ) . ", " . $this->limit;
    	$temp_query = $temp_query . $limit;
    	$this->executedQuery = $temp_query;
    	if( $this->method == 'cache' )
    	{
    		$this->cache = $this->registry->getObject('db')->cacheQuery( $temp_query );
    	}
    	elseif( $this->method == 'do' )
    	{
    		$this->registry->getObject('db')->executeQuery( $temp_query );
    		$this->results = $this->registry->getObject('db')->getRows();
    	}
    	
    	// be nice...do some calculations - so controllers don't have to!
		
		// num pages
		$this->numPages = ceil($this->numRows / $this->limit);
		
		// is first
		$this->isFirst = ( $this->offset == 0 ) ? true : false;
		
		// is last
		
		$this->isLast = ( ( $this->offset + 1 ) == $this->numPages ) ? true : false;
		
		// current page
		$this->currentPage = ( $this->numPages == 0 ) ? 0 : $this->offset +1;
		$this->numRowsPage = $this->registry->getObject('db')->numRows();
		if( $this->numRowsPage == 0 )
		{
			return false;
		}
		else
		{
			return true;
		}
    	
    }
    
    /**
     * Get the cached results
     * @return int
     */
    public function getCache()
    {
    	return $this->cache;
    }
    
    /**
     * Get the result set
     * @return array
     */
    public function getResults()
    {
    	return $this->results;
    }
    
    /**
     * Get the number of pages of results there are
     * @return int
     */
    public function getNumPages()
    {
    	return $this->numPages;
    }
    
    /**
     * Is this page the first page of results?
     * @return bool
     */
    public function isFirst()
    {
    	return $this->isFirst;
    }
    
    /**
     * Is this page the last page of results?
     * @return bool
     */
    public function isLast()
    {
    	return $this->isLast;
    }
    
    /**
     * Get the current page within the paginated results we are viewing
     * @return int
     */
    public function getCurrentPage()
    {
    	return $this->currentPage;
    }
    
    public function getNumRowsPage()
    {
    	return $this->numRowsPage;    	
    }
}
?>