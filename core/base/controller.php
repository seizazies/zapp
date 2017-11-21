<?php
/**
 * Core Base Controller 
 *  
 * 
 **/

Class Core_Controller extends Core_Component 
{
    protected $templateEngine;
    protected $templateDir;
    protected $baseUrl;
    protected $uri;
    protected $input;

    public function __construct ()
    {
        parent::__construct ();         
        
        if (zApp::isWebService() == FALSE)
        {
            if (empty($this->templateEngine))
                $this->templateEngine = $this->mainConfig->templateEngineDefault;
            
            if (empty($this->templateDir))
                $this->templateDir = $this->mainConfig->templateDir;
        }

        $this->baseUrl = $this->mainConfig->baseUrl;
        $this->input = zApp::loadCoreClass('input');
        $this->uri = zApp::getUri();
    }

    protected function uri()
    {
        return $this->uri->uri;
    }

    protected function uriSegment ($index)
    {
        if (isset($this->uri->segments[$index]))
            return $this->uri->segments[$index];
    }

    protected function uriArgument ($index)
    {
        if (isset($this->uri->arguments[$index]))
            return $this->uri->arguments[$index];

        return null;            
    }

    protected function loadConfig ($configName)
    {
        return zApp::loadConfig ($configName);
    }

    protected function loadLibrary ($libraryName, $params = null)
    {
        return zApp::loadLibrary ($libraryName, $params);
    }

    protected function loadModel ($modelName)
    {
        return zApp::loadModel ($modelName);
    }

    protected function loadView ($templateName, $templateEngine = null, $templateDir = null)
    {
        if ($templateEngine == null) $templateEngine = $this->templateEngine;
        if ($templateDir == null) $templateDir = $this->templateDir;
        $oView =  zApp::loadTemplateEngine ($templateEngine);
        $oView->setTemplateDir ($templateDir);
        $oView->setTemplate ($templateName);

        return $oView;
    }

	protected function ajaxResponse ($response) 
    {
		echo json_encode ($response);
		exit;
	}
    
}

