<?php
class Libs_Cache_Apc implements Libs_Cache_Interface {
	
	public function __construct($param){
		
	}
	
	public function set($key, $value, $timeout = 0) {
		return apc_add($key, $value);
	}
	
	public function get($key) {
		return apc_fetch($key);
	}
	
	public function isExists($key) {
		return apc_exists($key);
	}
	
	public function delete($key) {
		return apc_delete($key);
	}
}
