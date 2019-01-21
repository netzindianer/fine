<?php

class f_valid_callback extends f_valid_abstract
{

    const CALLBACK_ERROR = 'CALLBACK_ERROR';

    protected $_callback;
    protected $_errorMsg;
    protected $_param;
    protected $_value;
    
    public static function _(array $config = array())
    {
        return new self($config);
    }
    
    public function val()
    {
        return $this->_value;
    }
    
    public function param($form = null)
    {
        if (func_num_args() == 0) {
            return $this->_param;
        }
        $this->_param = $form;
        return $this;
    }

    public function errorMsg($error)
    {
        if (func_num_args() == 0) {
            return $this->_errorMsg;
        }
        $this->_errorMsg = $error;
        $this->msg(CALLBACK_ERROR, $this->_errorMsg);
        return $this;
    }

    public function callback($callback = null)
    {
        if (func_num_args() == 0) {
            return $this->_callback;
        }
        $this->_callback = $callback;
        return $this;
    }

    public function isValid($mValue = null)
    {
        $this->_val($mValue);
        $this->_value = $mValue;

        if (!call_user_func($this->_callback, $this)) {
            $this->_error(self::CALLBACK_ERROR);
            return false;
        }

        return true;
    }

}
