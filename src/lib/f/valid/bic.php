<?php

class f_valid_bic extends f_valid_abstract
{

    const STRING_EMPTY = 'STRING_EMPTY';
    const NOT_VALID    = 'NOT_VALID';
    
    protected $_msg = array(
        self::STRING_EMPTY => "Wymagana wartość",
        self::NOT_VALID    => 'Niepoprawna wartość',
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

        if (!preg_match("/^[a-z]{6}[0-9a-z]{2}([0-9a-z]{3})?\z/i", $sValue)) {
            $this->_error(self::NOT_VALID);
            return false;
        }

        return true;
    }

}