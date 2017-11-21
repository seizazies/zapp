<?php
/**
 * Initialize the autoloader, how and where the class is instantiated
 * Set the sytem error logging
 * 
 * Must be called by core/bootstrap
 **/

function zapp_autoload ($className)
{
    static $class = array ();

    if (!isset($class[$className]))
    {
        $className = strtolower($className);
        $classPrefix = str_replace('_', '/', $className);

        $path = [];
        $classBase = null;

        if (preg_match("/^(zapp)$/i", $className)) 
            $path = [SYSTEM_PATH . 'core/'.$classPrefix];

        elseif (preg_match("/^(config_)/i", $className)) 
            $path = [APPLICATION_PATH. $classPrefix.'.'.ENVIRONMENT, APPLICATION_PATH.$classPrefix];

        elseif (preg_match("/^(ajax_|controller_|model_|base_|console_)/i", $className)) 
            $path = [APPLICATION_PATH.$classPrefix];

        elseif (preg_match("/^(core_controller|core_console|core_model|core_component)/i", $className)) 
        {
            $className = str_replace('core_','', $className);
            $classPrefix = str_replace('_', '/', $className);
            $path = [SYSTEM_PATH . 'core/base/'.$classPrefix] ;
        }

        elseif (preg_match("/^(core_)/i", $className)) 
            $path = [SYSTEM_PATH.$classPrefix] ;

        elseif (preg_match("/^(lib_)/i", $className)) 
            $path = [APPLICATION_PATH.$classPrefix, SYSTEM_PATH.$classPrefix];
        
        else
            $path = [APPLICATION_PATH. 'ext/'.$classPrefix, SYSTEM_PATH. 'ext/'.$classPrefix];

        foreach ($path as $_filename) 
        {
            if (@file_exists($_filename . '.php')) 
            {
                $classBase = $_filename;
                break;
            }
        }

        if ($classBase)
        {
            $class[$className] = $classBase;
            require_once($classBase . '.php');
        }

    }
}

function zapp_errorLogging ($errno, $errstr, $errfile, $errline) 
{
    if (SYSTEM_ERROR_LOGGING == TRUE)
    {
        $config = zApp::loadConfig()->loggingParams;
        //$file	= SYSTEM_ERROR_LOGGING_PATH . SYSTEM_ERROR_LOGGING_PREFIX . date('Ymd');
        $file	= $config['logPath']. SYSTEM_ERROR_LOGGING_PREFIX.str_replace('{date}', date('Ymd'), $config['logFileName']);
        $msg    = date('Y-m-d H:i:s') . 
                    "\t Message : " . $errstr .
                    "\t Errfile : " . $errfile . 
                    "\t Errline : " . $errline;
        
        @error_log($msg . "\n", 3, $file);
    }
}

function zapp_fatalLogging () 
{
    if(is_null($e = error_get_last()) === FALSE && SYSTEM_ERROR_LOGGING == TRUE) 
    {
        $config = zApp::loadConfig()->loggingParams;
        //$file	= SYSTEM_ERROR_LOGGING_PATH . SYSTEM_ERROR_LOGGING_PREFIX . date('Ymd');
        $file	= $config['logPath']. SYSTEM_ERROR_LOGGING_PREFIX.str_replace('{date}', date('Ymd'), $config['logFileName']);
        $msg    = date('Y-m-d H:i:s') . 
                "\t Message : " . $e['message'] .
                "\t Errfile : " . $e['file'] . 
                "\t Errline : " . $e['line'];

        @error_log($msg . "\n", 3, $file);
    } 
}


spl_autoload_register('zapp_autoload');
set_error_handler('zapp_errorLogging', E_ERROR | E_WARNING);
register_shutdown_function('zapp_fatalLogging');

