<?php
class Lib_Db_Base {
	protected $option;
    protected $force_connect;
	protected $persistent;
	protected $cache_on;
	protected $cache_driver;
	protected $cache_param;
	protected $char_set;
	protected $oCache;
	protected $cache_ttl;

	protected function _parseOptions($options){
		$this->option           = (isset($options['option'])) ?         $options['option'] : array();
		$this->force_connect    = (isset($options['force_connect'])) ?  $options['force_connect'] : false;
		$this->persistent       = (isset($options['persistent'])) ?     $options['persistent'] : false;
		$this->cache_on         = (isset($options['cache_on'])) ?       $options['cache_on'] : false;
		$this->cache_driver     = (isset($options['cache_driver'])) ?   $options['cache_driver'] : '';
		$this->cache_param      = (isset($options['cache_param'])) ?    $options['cache_param'] : array ();
		$this->char_set         = (isset($options['char_set'])) ?       $options['char_set'] : '';

        $this->cache_ttl        = (isset($this->cache_param['ttl'])) ? (int) $this->cache_param['ttl'] : 0;
        if ($this->cache_ttl < 0) $this->cache_ttl = 0;
	}
	
	protected function _init_cache(){
		if($this->cache_on == true){
			$cacheDriver  = 'lib_cache_' . $this->cache_driver;
            if (class_exists($cacheDriver))
			    $this->oCache = new $cacheDriver($this->cache_param);
		}
	}
	
	protected function _setCache($key, $value){
        $key = md5($key);
		return ($this->cache_on === true && $this->oCache) ? $this->oCache->set($key, $value, $this->cache_ttl) : false; 
	}
	
	protected function _getCache($key){
        $_result = ($this->cache_on === true && $this->oCache) ? $this->oCache->get($key) : false;
		return $_result;
	}
}
