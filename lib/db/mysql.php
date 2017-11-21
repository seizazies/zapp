<?php
/**
 *  
 *  
 * 
 * @author 
 **/

class Lib_Db_Mysql extends Lib_Db_Base 
{
	protected $resource;
	private $hostname;
	private $username;
	private $password;
	private $database;
	private $port;

    private $cache_numRows = null;

    public static function load ($params = array ())
    {
        return new self ($params['hostname'], $params['username'], $params['password'], $params['database'], $params['port'], $params['options']);
    }

	public function __construct($hostname, $username, $password, $database, $port, $options=''){
		$this->hostname = $hostname;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->port     = $port;

        $this->_parseOptions($options);

		$this->_connect();

        $this->_init_cache();
	}
	
	private function _connect() {
		if($this->persistent == true) 
		    $this->resource = mysqli_pconnect ( $this->hostname, $this->username, $this->password);
        else  
            $this->resource = mysqli_connect ( $this->hostname, $this->username, $this->password, $this->force_connect );

		if ( $this->resource == false ) {
			return false;
		}
        
        if ($this->char_set)
            mysqli_set_charset($this->char_set, $this->resource);

		$selectDb = mysqli_select_db ( $this->resource, $this->database );
		if ($selectDb === false) {
			return false;
		}

		return true;
	}
	
	public function set_charset($charset = 'utf8') {
		return mysqli_set_charset ($charset, $this->resource );
	}
	
	protected function check_connection() {
		if (mysqli_ping ( $this->resource ) === FALSE) {
			$this->resource = FALSE;
			return $this->_connect();
		}
		
		return TRUE;
	}
	
	public function query($sql) {
		$this->check_connection();
        $this->cache_numRows = null;

		$query = mysqli_query($this->resource, $sql); 
		if($query === FALSE) trigger_error(mysqli_error($this->resource), E_USER_ERROR);

		return $query;
	}
	
	public function fetch($sql) 
    {
		$cacheData = $this->_getCache($sql);
		if( $cacheData !== false ) 
        {
            $this->cache_numRows = count($cacheData);
            return $cacheData;             
        }

        $result = FALSE;
		$query = $this->query ( $sql );
        if ($query)
        {
            $result = array ();
            while ( $row = mysqli_fetch_assoc ( $query ) ) $result[] = $row;
   		    $this->_setCache($sql, $result);
        }
		return $result;
	}


	public function fetchOne($sql) {
		$cacheData = $this->_getCache($sql);
		if( $cacheData !== false ) 
        {
            $this->cache_numRows = count($cacheData);
            return $cacheData;             
        }

        $result = FALSE;
		$query = $this->query ( $sql );
        if ($query)
        {
            $result = mysqli_fetch_assoc ( $query );
   		    $this->_setCache($sql, $result);
        }
		
		return $result;
	}

	public function numRows() {
        if ($this->cache_numRows != null) return $this->cache_numRows;
		return mysqli_affected_rows ($this->resource);
	}
	
	public function insertId() {
		return mysqli_insert_id ( $this->resource );
	}
	
	public function getError() {
		return mysqli_error($this->resource);
	}

    public function getResource (){
        return $this->resource;
    }
}
