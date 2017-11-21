<?php
/**
 *  
 *  
 * 
 * @author 
 **/

Class Lib_Template_Smarty extends Lib_Template_Base
{
    protected $templateExtFile = '.html';
    protected $smarty;

    public function __construct ($templateName = null)
    {
        parent::__construct ($templateName);
        $this->smarty = zApp::loadExternal ('Smarty', SYSTEM_PATH. 'ext/smarty/Smarty.class.php');
        
        $this->smarty->caching = ($this->config->caching == false) ? 0 : 1;
        $this->smarty->force_compile = ($this->config->caching == false) ? true : false;
        $this->smarty->cache_lifetime = $this->config->cacheLifetime;

        $this->smarty->setCompileDir($this->config->compileDir);
        $this->smarty->setCacheDir($this->config->cacheDir);
    }

    public function assign ($item, $value)
    {
        $this->smarty->assign ($item, $value);
    }

    public function render ($data = array ())
    {
        $this->smarty->setTemplateDir($this->templateDir);
        $this->smarty->setConfigDir($this->templateDir);
        
        $data['ELAPSED_TIME'] = number_format(microtime(true) - BEGINING_OF_TIME, 4);
        $data['TEMPLATE_DIR'] = $this->templateDir;

        if (is_array($data) && !empty($data))
        {
            foreach ($data as $item => $value)
                $this->smarty->assign ($item, $value);
        }
        $this->smarty->display ($this->templateFile);
    }
}
