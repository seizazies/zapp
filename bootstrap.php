<?php
/**
 * Bootstrap file
 *  
 **/

/**
 * -------------------------------------------------------------------
 *  Application Environment
 * -------------------------------------------------------------------
 **/
if ((!isset($environment) || empty($environment)) || 
    !preg_match("/^(production|development|testing)$/i", $environment)) 
    exit('Application environment is not set correctly.');

/**
 * -------------------------------------------------------------------
 *  Error Reporting
 * -------------------------------------------------------------------
 **/
if (isset($error_reporting))
{
	switch (strtolower($error_reporting))
	{
		case 'development':
			error_reporting(E_ALL);
		break;
	
		case 'production':
			error_reporting(0);
		break;

		default:
			exit('Error Reporting is not set correctly.');
	}
}

if (!is_dir($application_folder))
    exit("Application folder path is not set correctly.");

/*
 * -------------------------------------------------------------------
 *  System Constants
 * -------------------------------------------------------------------
 */

defined ('SYSTEM_ERROR_LOGGING') or define ('SYSTEM_ERROR_LOGGING', TRUE);
defined ('SYSTEM_ERROR_LOGGING_PATH') or define ('SYSTEM_ERROR_LOGGING_PATH', '/tmp/');
defined ('SYSTEM_ERROR_LOGGING_PREFIX') or define ('SYSTEM_ERROR_LOGGING_PREFIX', 'error-');
defined ('BEGINING_OF_TIME') or define ('BEGINING_OF_TIME', microtime(true));
defined ('AUTOSTART') or define ('AUTOSTART', TRUE);
defined ('WEB_SERVICE') or define ('WEB_SERVICE', FALSE);

define ('ENVIRONMENT',              strtolower($environment));
define ('SYSTEM_PATH',              dirname(__FILE__) .'/');
define ('APPLICATION_PATH',         realpath($application_folder).'/');
define ('APPLICATION_VIEW_PATH',    APPLICATION_PATH. 'view/');
/*
 * -------------------------------------------------------------------
 *  Start the App
 * -------------------------------------------------------------------
 */

require (SYSTEM_PATH.'core/autoload.php');
require (SYSTEM_PATH.'core/zapp.php');

if (AUTOSTART === TRUE)
    zApp::startApplication ();
