<?php

class f_valid_date extends f_valid_abstract
{

    const NOT_MATCH = 'NOT_MATCH';

    protected $_msg =  array(
        self::NOT_MATCH => "Czas '{val}' musi pasowac do wzorca '{formatHumanReadable}' ",
    );
    protected $_var = array(
        '{formatHumanReadable}' => '_formatHR'
    );
    
    protected $_format;
    protected $_formatHR;
    protected $_format2HR = array(
        'Y' => 'YYYY',
        'm' => 'MM',
        'd' => 'DD',
        'H' => 'HH',
        'i' => 'MM',
        's' => 'SS',
    );
    protected $_pattern = array(
        'Y' => '[0-9]{4}',
        'm' => '[01]{1}[0-9]{1}',
        'd' => '[0-3]{1}[0-9]{1}',
        'H' => '[0-2]{1}[0-9]{1}',
        'i' => '[0-5]{1}[0-9]{1}',
        's' => '[0-5]{1}[0-9]{1}',
    );
        
    public static function _(array $config = array())
    {
        return new self($config);
    }

    public function format($sFormat = null)
    {
        if (func_num_args() == 0) {
            return $this->_format;
        }
        
        $this->_format   = $sFormat;
        $this->_formatHR = str_replace(array_keys($this->_format2HR), array_values($this->_format2HR), $this->_format);
        
        return $this;
    }

    public function isValid($mValue)
    {
        $sValue = (string) $mValue;
        $this->_val($sValue);
        
        // convert format to pattern
        $aValid = array();
        $pattern   = "";
        $delimiter = "#";
        for ($i = 0, $end = strlen($this->_format); $i < $end; $i++) {
            $char     = $this->_format[$i];
            $pattern .= isset($this->_pattern[$char])
                ? "(?P<" . preg_quote($char, $delimiter). ">" . $this->_pattern[$char] . ")"
                : preg_quote($char, $delimiter);
        }
        $pattern = $delimiter. '^' . $pattern . '$' . $delimiter;

        if (!@preg_match($pattern, $sValue, $match)) {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
    
}