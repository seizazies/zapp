<?php
class Lib_Cache_Memcache implements Lib_Cache_Interface {
	
	private $oMemcache;
	
	//param = host=10.1.1.1&port=11211&ttl=60
	public function __construct($conf){

		$this->oMemcache = new Memcache;
		$this->oMemcache->connect($conf['host'], $conf['port'], $conf['ttl']);

	}
	
	public function set($key, $value, $timeout = 0) {
		return $this->oMemcache->set($key, $value, MEMCACHE_COMPRESSED, $timeout);
	}
	
	public function get($key) {
		return $this->oMemcache->get($key);
	}
	
	public function isExists($key) {
		return ( $this->get($key) === false ) ? false : true;
	}
	
	public function delete($key) {
		return $this->oMemcache->delete($key);
	}
	
	public function increment($key, $int) {
		return $this->oMemcache->increment($key, $int);
	}
}
