<?php

class f_valid_dateMin extends f_valid_abstract
{
    const NOT_MIN = 'NOT_MIN';
    
    protected $_msg =  array(
        self::NOT_MIN => "Czas '{val}' musi być większy od '{min}'",
    );
    
    protected $_var = array(
        '{min}' => '_min'
    );
    
    protected $_min;

    public static function _(array $config = array())
    {
        return new self($config);
    }
    
    public function min($iMin = null)
    {
        if (func_num_args() == 0) {
            return $this->_min;
        }
        
        $this->_min = $iMin;
        return $this;
    }

    public function isValid($mValue)
    {
        $sValue = (string) $mValue;
        $this->_val($sValue);
        
        if (strtotime($sValue) <= strtotime($this->_min)) {
            $this->_error(self::NOT_MIN);
            return false;
        }

        return true;
    }

}
