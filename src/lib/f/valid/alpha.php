<?php

class f_valid_alpha extends f_valid_abstract
{

    const STRING_EMPTY = 'STRING_EMPTY';
    const NOT_ALPHA    = 'NOT_ALPHA';
    
    protected $_msg = array(
        self::STRING_EMPTY => "Wymagana wartość",
        self::NOT_ALPHA    => "Wymagane znaki alfabetyczne (a-z, A-Z, np. qweRTY)",
    );

    public static function _(array $config = array())
    {
        return new self($config);
    }

    public function isValid($mValue)
    {
        $sValue = (string) $mValue;
        $this->_val($sValue);

        if ('' === $sValue) {
            $this->_error(self::STRING_EMPTY);
            return false;
        }

        if (!ctype_alpha($sValue)) {
            $this->_error(self::NOT_ALPHA);
            return false;
        }

        return true;
    }

}