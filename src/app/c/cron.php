<?php

class c_cron extends f_c_action
{
    
    public function dayAction()
    {
        $this->_cleanDataTmpDir();
        $this->_errorNotify();
    }
    
    public function hourAction()
    {  
    }
    
    /**
     * Czysci folder /cdn/tmp/ z starych niepotrzebnych plikow
     */
    protected function _cleanDataTmpDir()
    {
        f_upload_tmp::_()->destroyAll(14 * 24 * 60 * 60);
    }
    
    /**
     * Wysyla powiadomienie ze 100 ostatnimi bledami aplikacji z error_log na podstawie 
     * sciezki podanej w configu /app/config/main.php['prod']['error_notify']['log'] z serweru produkcyjnego
     * na adresy email podane w configu /app/config/main.php['prod']['error_notify']['email']
     */    
    protected function _errorNotify()
    {
        if ($this->env != 'prod') {
            return;
        }
        
        // get error notification config
        $config = $this->config->main['error_notify'];
        
        if (!$config) {
            return;
        }

        // handle notifiaction
        f_tool_error_notify::_($config)->handle();
    }
    
}