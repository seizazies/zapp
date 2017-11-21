<?php
if(!defined('LOG_SUMMARY'))	define('LOG_SUMMARY',  0);     /* Summary */
if(!defined('LOG_EMERG')) 	define('LOG_EMERG',    1);     /* System is unusable */
if(!defined('LOG_ALERT')) 	define('LOG_ALERT',    2);     /* Immediate action required */
if(!defined('LOG_CRIT'))  	define('LOG_CRIT',     3);     /* Critical conditions */
if(!defined('LOG_ERR'))   	define('LOG_ERR',      4);     /* Error conditions */
if(!defined('LOG_WARNING')) define('LOG_WARNING',  5);     /* Warning conditions */
if(!defined('LOG_NOTICE')) 	define('LOG_NOTICE',   6);     /* Normal but significant */
if(!defined('LOG_INFO')) 	define('LOG_INFO',     7);     /* Informational */
if(!defined('LOG_DEBUG')) 	define('LOG_DEBUG',    8);     /* Debug-level messages */

class Lib_Logging {

	protected $threadId;
	private static $instance;
	protected $time;

    private $currLogFileName        = null;
	
	protected $logLevel 	 		= 'debug';
	protected $logFileName 	 		= 'logging-{date}';
	protected $logPath  	 		= '/tmp/';
	protected $logTimeFormat        = 'Y-m-d H:i:s';
	protected $logLineFormat        = '{datetime} {exectime} {threadId} {level} {componentId} {file} {class} {function} {message}';
	protected $timeDigit            = 8; //0.000006
	
	public function __construct($option){
		if( isset($option['logLevel']) ) 		$this->logLevel 	= $option['logLevel'];
		if( isset($option['logFileName']) ) 	$this->logFileName  = $option['logFileName'];
		if( isset($option['logPath']) )  		$this->logPath  	= $option['logPath'];
		if( isset($option['logTimeFormat']) ) 	$this->logTimeFormat= $option['logTimeFormat'];
		if( isset($option['logLineFormat']) ) 	$this->logLineFormat= $option['logLineFormat'];
		$this->threadId	= substr(microtime(1),0,3) . rand(100,999);
		$this->time		= microtime();
	}
	
	protected function getRunTime(){
		return substr( ( microtime() - $this->time ), 0, $this->timeDigit );
	}
	
	protected function stringToPriority($level){
		switch(strtolower($level)){
			case 'summary': 	return LOG_SUMMARY; break;
			case 'emergency': 	return LOG_EMERG; break;
			case 'alert': 		return LOG_ALERT; break;
			case 'critical': 	return LOG_CRIT; break;
			case 'error': 		return LOG_ERR; break;
			case 'warning': 	return LOG_WARNING; break;
			case 'notice': 		return LOG_NOTICE; break;
			case 'info': 		return LOG_INFO; break;
			case 'debug': 		return LOG_DEBUG; break;
			default: 			return LOG_DEBUG; break;			
		}
	}
	
	protected function genFilename()
    {
        $_fileName = str_replace('{date}', date('Ymd'), $this->logFileName);

        if ($this->currLogFileName != $_fileName)
        {
            $this->currLogFileName = $_fileName;
            if (!is_writable($this->logPath . $this->currLogFileName))
            {
                @touch($this->logPath . $this->currLogFileName);
                @chmod($this->logPath . $this->currLogFileName, 0777);
            }
        }
        
		return $_fileName; 
	}
	
	public function write($message, $strLevel='debug', $componentId = null)
    {
		$level 		= $this->stringToPriority($strLevel);
		$logLevel 	= $this->stringToPriority($this->logLevel);
		$filename	= $this->genFilename();
		$dateFormat	= date($this->logTimeFormat);
		
		$debug 	    = debug_backtrace(2);
		$file		= $debug[0]['file'];
		$class		= $debug[1]['class'];
		$function	= $debug[1]['function'];
		
		if($strLevel != 'summary'){
			$message = preg_replace(array('(\s+)','(\t+)'), ' ', $message);
		}
		
		$content = str_replace(
			array('{datetime}', '{exectime}', '{threadId}', '{level}', '{componentId}', '{file}', '{class}', '{function}', '{message}'),
			array($dateFormat, $this->getRunTime(), $this->threadId, $strLevel, $componentId, $file, $class, $function, $message . "\n"),
			$this->logLineFormat			
		);
		
		if($level <= $logLevel){
			return (boolean) file_put_contents($this->logPath . $filename, $content, FILE_APPEND);
		}
	}
	
}
