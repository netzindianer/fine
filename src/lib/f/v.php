<?php

class f_v
{

    public $_c;
    public $_defaultPath = 'app/';


    public function  __call($sName, $aArg)
    {
        return call_user_func_array(array($this->_c->{$sName}, 'helper'), $aArg);
    }

    public function renderSandbox($tpl, $data = null)
    {
        return $this->renderPath($this->_defaultPath . $tpl, $data);
    }

    public function renderPath($tpl, $data = null)
    {
        $_____tpl =  $tpl . '.php';
        $_____data = $data;
        unset($tpl, $data);
        
        if ($_____data) {
            extract($_____data, EXTR_SKIP);
        }

        ob_start();
        include $_____tpl;
        return ob_get_clean();
    }

}