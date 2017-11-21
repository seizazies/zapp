<?php
/**
 * CMS Core class functions 
 *  
 **/

Class zApp
{
    const PAGE_ERROR_404        = 'error_404';
    const PAGE_ERROR_DB         = 'error_db';
    const PAGE_ERROR_GENERAL    = 'error_general';

    private static $logger;
    private static $coreclasses = array ();
    private static $classes = array ();
    private static $extclasses = array ();
    private static $dbs = array ();

    private static $imAlive = FALSE;
    private static $uri;
    private static $controller;
    private static $method;
    private static $segments;
    private static $arguments = array ();

    /*
     * -------------------------------------------------------------------
     *  Private funtions
     * -------------------------------------------------------------------
     */

    private static function _detectSystemURI ()
    {
		if ( ! isset($_SERVER['REQUEST_URI']) OR ! isset($_SERVER['SCRIPT_NAME']))
		{
			return '';
		}

		$uri = $_SERVER['REQUEST_URI'];
		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
		{
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		}
		elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
		{
			$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		// This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING server var and $_GET array.
		if (strncmp($uri, '?/', 2) === 0)
		{
			$uri = substr($uri, 2);
		}
		$parts = preg_split('#\?#i', $uri, 2);
		$uri = $parts[0];
		if (isset($parts[1]))
		{
			$_SERVER['QUERY_STRING'] = $parts[1];
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		}
		else
		{
			$_SERVER['QUERY_STRING'] = '';
			$_GET = array();
		}

		if ($uri == '/' || empty($uri)) return '/';
		$uri = parse_url($uri, PHP_URL_PATH);

   		// Do some final cleaning of the URI and return it
		return str_replace(array('//', '../'), '/', trim($uri, '/'));
    }

    private static function _detectPage ()
    {
        $_separator = self::isCommandLineInterface() ? ':':'/';
        $_segments = explode($_separator, self::$uri);
        self::$segments = $_segments;
        
        if (self::isCommandLineInterface())
        {
            $baseController = 'console';
            $baseMethod = 'run';
        }
        else
        {
            $useAjaxController = isset(self::loadConfig('main')->useAjaxController) ? self::loadConfig('main')->useAjaxController : false; 
            $baseController = (self::isAjaxRequest() && !empty($useAjaxController)) ? 'ajax' : 'controller';
            $baseMethod = 'index';
        }
        
        $_controller = '';
        $_method = '';
        
        $_iSegment = 0;
        if ($_segments[0] == '')
        {
            if (!self::isCommandLineInterface()) 
                $_controller = $baseController.'/'.self::loadConfig('main')->defaultController; 
        }
        else
        {
            $_aController = array ();
            for ($i=0; $i<count(self::$segments); $i++)
            {
                $_cntPrev = (isset($_aController[$i-1])) ? $_aController[$i-1]['controller']."/" : '';
                $_mtdPrev = (isset(self::$segments[$i+1])) ? self::$segments[$i+1] : $baseMethod;
                $_aController[$i] = array ('controller' => $_cntPrev . self::$segments[$i], 'method' => $_mtdPrev);
            }

            $_iSegment = 1;
            if ($_aController) 
            {
                rsort($_aController);                
                foreach ($_aController as $_cnt)
                {
                    $_controller = $baseController.'/'.$_cnt['controller']; 
                    if (is_file(APPLICATION_PATH. $_controller.'.php'))
                    {
                        $_method = $_cnt['method'];
                        $_iSegment++;
                        break;
                    }
                }
            }
        }

        if ($_method == '') $_method = $baseMethod;

        // check if the controller exists
        if (!is_file(APPLICATION_PATH.$_controller.'.php'))
        {

            if (isset(self::loadConfig('main')->forceDefault) && self::loadConfig('main')->forceDefault == true)
            {
                $_controller = $baseController.'/'.self::loadConfig('main')->defaultController;
            }
            else
            {
                if (self::isCommandLineInterface())
                    self::showConsoleInfo ($_controller);
                else 
                    self::showSystemPage (array ('message' => 'Controller Not Found'), self::PAGE_ERROR_404);
            }
        }
            
        
        // normalize the controller class name
        $_controller = str_replace('/','_', $_controller);

        // check if the method exists, otherwise, set default  method
        if (!method_exists($_controller, $_method))
        {
            $_method = $baseMethod;
            if (!method_exists($_controller, $_method))
            {
                if (self::isCommandLineInterface())
                    self::showConsoleInfo ($_controller, $_method);
                else 
                    self::showSystemPage (array ('message' => 'Controller::Method Not Found'), self::PAGE_ERROR_404);
                
            }
        }
        
        if (!self::isCommandLineInterface())
            self::$arguments = array_slice ($_segments, $_iSegment);

        self::$controller = $_controller;
        self::$method = $_method;
    }

    /*
     * -------------------------------------------------------------------
     *  Public funtions
     * -------------------------------------------------------------------
     */

    public static function isWebService ()
    {
        return self::isCommandLineInterface() ? FALSE : WEB_SERVICE;
    }

    public static function isCommandLineInterface()
    {
        return (php_sapi_name() === 'cli');
    }

    public static function startConsoleApplication ()
    {
        if (self::$imAlive) return;
        
        if (!self::isCommandLineInterface()) return;

        if (isset($_SERVER['argv'][1]))
            self::$uri = $_SERVER['argv'][1];
       
        if ($_SERVER['argc'] > 2)
            self::$arguments = array_slice($_SERVER['argv'], 2); 

        // detect Page
        self::_detectPage ();
    
        // here we go ...        
        self::$imAlive = TRUE;

        $_method = self::$method;
        zApp::loadApplicationClass(self::$controller)->$_method();
        
    }

    public static function startApplication ()
    {
        if (self::$imAlive) return;
        
        if (self::isCommandLineInterface())
        {
            if (isset($_SERVER['argv'][1]))
                self::$uri = $_SERVER['argv'][1];
           
            if ($_SERVER['argc'] > 2)
                self::$arguments = array_slice($_SERVER['argv'], 2); 

            // detect Page
            self::_detectPage ();
        }
        else
        {
            // detect URI
            self::$uri = self::_detectSystemURI();

            // detect Page
            self::_detectPage ();

        }
    
        // here we go ...        
        self::$imAlive = TRUE;

        $_method = self::$method;
        zApp::loadApplicationClass(self::$controller)->$_method();
        
    }

    public static function startWebApplication ()
    {
        if (self::$imAlive) return;
        
        if (self::isCommandLineInterface()) return;

        // detect URI
        self::$uri = self::_detectSystemURI();

        // detect Page
        self::_detectPage ();
    
        // here we go ...        
        self::$imAlive = TRUE;

        $_method = self::$method;
        zApp::loadApplicationClass(self::$controller)->$_method();
        
    }

    public static function loadCoreClass ($className, $params = null, $useExisting = TRUE)
    {
        $className = 'core_'.$className;
        if ($useExisting && isset(self::$coreclasses[$className]))
            return self::$coreclasses[$className];

        return self::$coreclasses[$className] = ($params) ? new $className($params) : new $className();
    }

    public static function loadApplicationClass ($className, $params = null, $useExisting = TRUE)
    {
        if ($useExisting && isset(self::$classes[$className]))
            return self::$classes[$className];

        return self::$classes[$className] = ($params) ? new $className($params) : new $className();
    }

    public static function loadConfig ($className = null)
    {
        if (empty($className))
            $className = self::isCommandLineInterface() ? 'console': 'main';
            
        return self::loadApplicationClass('config_'. $className);
    }

    public static function loadModel ($className)
    {
        return self::loadApplicationClass('model_'. $className);
    }

    public static function loadController ($className)
    {
        return self::loadApplicationClass('controller_'. $className);
    }

    public static function loadTemplateEngine ($driver, $params = null)
    {
        return zApp::loadLibrary ('template_'.$driver, $params);
    }

    public static function loadLibrary ($className, $params = null, $useExisting = TRUE)
    {
        return self::loadApplicationClass('lib_'. $className, $params, $useExisting);
    }

    public static function loadExternal ($className, $classFilePath = null, $params = null, $useExisting = TRUE)
    {
        $_serialize = serialize (array($className, $classFilePath));
        if ($useExisting && isset(self::$extclasses[$_serialize]))
            return self::$extclasses[$_serialize];

        if ($classFilePath != null)
            require_once ($classFilePath);

        return self::$extclasses[$_serialize] = ($params) ? new $className($params) : new $className();
    }

    public static function loadDatabase ($dbProfile)
    {
        if (isset(self::$dbs[$dbProfile]))
            return self::$dbs[$dbProfile];

        $_dbParams = self::loadConfig('database')->profiles[$dbProfile];
        return self::$dbs[$dbProfile] = Lib_Db_Mysql::load ($_dbParams);
    }

    public static function getLogger ()
    {
        if (!self::$logger)
        {
            self::$logger = new Lib_Logging (self::loadConfig()->loggingParams);
        }

        return self::$logger;
    }

    public static function logMessage ($level, $message, $componentId = null)
    {
        if (self::loadConfig()->loggingEnabled)
            zApp::getLogger()->write ($message, $level, $componentId);
    }

    public static function getControllerName ()
    {
        return self::$controller;
    }

    public static function getMethodName ()
    {
        return self::$method;
    }

    public static function getSegment ()
    {
        return self::$segments;
    }

    public static function baseUrl ()
    {
        return self::loadConfig('main')->baseUrl;
    }

    public static function showSystemPage ($errorData, $errorPage = self::PAGE_ERROR_GENERAL)
    {
        if (zApp::isAjaxRequest ())
        {
            header("HTTP/1.0 404 Not Found");
            echo $errorPage;
        }
        else if (zApp::isWebService ())
        {
            echo json_encode(array (
                'status'    => false,
                'code'      => $errorPage,
                'message'   => isset($errorData['message']) ? $errorData['message'] : 'Error'
            ));
        }
        else
        {
            $errorData['app_title'] = zApp::loadConfig('app')->appTitle;
            $errorData['base_url'] = zApp::loadConfig('main')->baseUrl;
            self::loadTemplateEngine (zApp::loadConfig('main')->templateEngineDefault, $errorPage)->render( $errorData );
        }
        die();
    }

    private static function _readConsoleCommand ($directory, $root = true)
    {
        $_files = array_diff(scandir($directory), array('.', '..'));
        foreach ($_files as $_file)
        {
            if (is_dir($directory.'/'.$_file))
            {
                printf("\n%s\n", ucfirst($_file));
                self::_readConsoleCommand($directory.'/'.$_file, false);
            }
            else
            {
                $_command = basename(strtolower($_file), ".php");
                $_adir = explode("console/", $directory);
                
                if (isset($_adir[1])){
                    $_className = 'Console_'.$_adir[1].'_'.$_command;
                    $_command = $_adir[1].'/'.$_command;
                }else{
                    $_className = 'Console_'.$_command;
                }

                $_className = str_replace('/','_', $_className);
                
                if (class_exists($_className))
                {
                    $_command = str_replace("/",":", $_command);
                    $oCommand = self::loadApplicationClass ($_className);        
                    printf("%s%s%s\n", ($root ? "":"  "),str_pad($_command, 40), ($root ? "  ":"").$oCommand->description);
                }
            }
        }
    }

    public static function showConsoleInfo ($controller = '', $method = '')
    {
        if ($controller)
        {
            $command = self::$uri;
            if ($method) 
                printf("ERROR : Command '$command' could not be executed. Missing 'run' method.\n\n");
            else    
                printf("ERROR : Command '$command' not found \n\n");
        }

        print ("Available Commands :\n\n");
        self::_readConsoleCommand (APPLICATION_PATH. 'console');
        print ("\n");

        die();
    }

    public static function getArguments ()
    {
        return self::$arguments;
    }

    public static function getOptions ()
    {
        $argv = $_SERVER['argv'];
        $options = array ();
        while (($param = array_shift($argv)) !== NULL) 
        {
            if ($param{0} == '-')
            {
                $_param = str_replace('-','', $param);
                if (strlen($_param) > 1)
                {
                    $_val = substr($_param,1); 
                    $_param = $_param{0};
                }
                else
                {
                    $_val = array_shift($argv);
                    if ($_val{0} == '-') {
                        array_unshift($argv, $_val);
                        $_val = '';
                    }
                }
                $options[$_param] = $_val;
            }
        } 
        return $options;
    }

    public static function getUri ()
    {
        return (object) array (
            'uri'       => self::$uri,
            'segments'  => self::$segments,
            'arguments' => self::$arguments
        );
    }

    public static function usToPath ($string)
    {
        return str_replace('_','/', $string);
    }

    public static function isAjaxRequest ()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}
