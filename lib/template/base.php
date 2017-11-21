<?php
/**
 *  
 *  
 * 
 * @author 
 **/

Class Lib_Template_Base 
{
    protected $data = array ();
    protected $templateFile;
    protected $templateDir;
    protected $templateExtFile = '.php';
    protected $config;

    public function __construct ($templateName = null)
    {
        if ($templateName) $this->setTemplate ($templateName);
        $this->config = (object) zApp::loadConfig('main')->templateEngineParams;
        $this->templateDir = APPLICATION_VIEW_PATH . zApp::loadConfig('main')->templateDir .'/';
    }

    public function setTemplate ($templateName)
    {
        $this->templateFile = str_replace('_', '/', $templateName). $this->templateExtFile;
    }

    public function setTemplateDir ($templateDir)
    {
        $this->templateDir = APPLICATION_VIEW_PATH . $templateDir .'/';
    }

    public function assign ($item, $value)
    {
        $this->data[$item] = $value;
    }

    public function render ($data = array ())
    {
        $this->data['ELAPSED_TIME'] = number_format(microtime(true) - BEGINING_OF_TIME, 4);
        $this->data['TEMPLATE_DIR'] = $this->templateDir;

        $_templateData = array_merge_recursive ($this->data, $data);
       
        if ($_templateData)
        {
            foreach ($_templateData as $key => $value) $$key = $value;
        }

        if (file_exists($this->templateDir . $this->templateFile))
            require_once ($this->templateDir . $this->templateFile);
    }
}
