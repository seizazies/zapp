<?php
/**
 * Core Base Model 
 *  
 * 
 **/

Class Core_Model extends Core_Component
{
    protected $db;
    protected $defaultProfile;

    public function __construct ()
    {
        parent::__construct ();         
        
        if ($this->defaultProfile)
            $this->db = $this->loadDatabase ($this->defaultProfile);
    }

    protected function loadDatabase ($profile)
    {
        return zApp::loadDatabase ($profile);
    }
}
 
