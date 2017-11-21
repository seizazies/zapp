<?php
/**
 * Base Component 
 *  
 **/

Class Core_Component 
{
    protected $mainConfig;
    protected $name;

    public function __construct ()
    {
        $this->mainConfig = zApp::loadConfig('main');
        $this->name = strtolower(get_called_class());
    }
    
    protected function logInfo ($message)
    {
        zApp::logMessage('info', $message, $this->name);
    }

    protected function logDebug ($message)
    {
        zApp::logMessage('debug', $message, $this->name);
    }

    protected function logError ($message)
    {
        zApp::logMessage('error', $message, $this->name);
    }

    protected function logWarning ($message)
    {
        zApp::logMessage('warning', $message, $this->name);
    }
}
 
