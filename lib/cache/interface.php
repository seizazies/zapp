<?php
interface Lib_Cache_Interface {
	public function __construct($param);
	public function set($key, $value, $timeout);
	public function get($key);
	public function isExists($key);
	public function delete($key);
}
