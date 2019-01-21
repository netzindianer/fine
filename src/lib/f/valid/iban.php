<?php

class f_valid_iban extends f_valid_abstract
{
    
    const LOCALECODE_OFFSET = 0;
    const LOCALECODE_LENGTH = 2;
    const CHECKSUM_OFFSET = 2;
    const CHECKSUM_LENGTH = 2;
    const ACCOUNTIDENTIFICATION_OFFSET = 4;
    const INSTITUTEIDENTIFICATION_OFFSET = 4;
    const INSTITUTEIDENTIFICATION_LENGTH = 8;
    const BANKACCOUNTNUMBER_OFFSET = 12;
    const BANKACCOUNTNUMBER_LENGTH = 10;
    const IBAN_MIN_LENGTH = 15;
    
    const STRING_EMPTY = 'STRING_EMPTY';
    const NOT_VALID = 'NOT_VALID';

    protected $_msg = array(
        self::STRING_EMPTY => 'Wymagana wartość',
        self::NOT_VALID => 'Niepoprawna wartość',
    );

    public static $letterMapping = array(
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D',
        5 => 'E',
        6 => 'F',
        7 => 'G',
        8 => 'H',
        9 => 'I',
        10 => 'J',
        11 => 'K',
        12 => 'L',
        13 => 'M',
        14 => 'N',
        15 => 'O',
        16 => 'P',
        17 => 'Q',
        18 => 'R',
        19 => 'S',
        20 => 'T',
        21 => 'U',
        22 => 'V',
        23 => 'W',
        24 => 'X',
        25 => 'Y',
        26 => 'Z'
    );
    
    public static $ibanFormatMap = array(
        'AL' => '[0-9]{8}[0-9A-Z]{16}',
        'AD' => '[0-9]{8}[0-9A-Z]{12}',
        'AT' => '[0-9]{16}',
        'BE' => '[0-9]{12}',
        'BA' => '[0-9]{16}',
        'BG' => '[A-Z]{4}[0-9]{6}[0-9A-Z]{8}',
        'HR' => '[0-9]{17}',
        'CY' => '[0-9]{8}[0-9A-Z]{16}',
        'CZ' => '[0-9]{20}',
        'DK' => '[0-9]{14}',
        'EE' => '[0-9]{16}',
        'FO' => '[0-9]{14}',
        'FI' => '[0-9]{14}',
        'FR' => '[0-9]{10}[0-9A-Z]{11}[0-9]{2}',
        'GE' => '[0-9A-Z]{2}[0-9]{16}',
        'DE' => '[0-9]{18}',
        'GI' => '[A-Z]{4}[0-9A-Z]{15}',
        'GR' => '[0-9]{7}[0-9A-Z]{16}',
        'GL' => '[0-9]{14}',
        'HU' => '[0-9]{24}',
        'IS' => '[0-9]{22}',
        'IE' => '[0-9A-Z]{4}[0-9]{14}',
        'IL' => '[0-9]{19}',
        'IT' => '[A-Z][0-9]{10}[0-9A-Z]{12}',
        'KZ' => '[0-9]{3}[0-9A-Z]{13}',
        'KW' => '[A-Z]{4}[0-9]{22}',
        'LV' => '[A-Z]{4}[0-9A-Z]{13}',
        'LB' => '[0-9]{4}[0-9A-Z]{20}',
        'LI' => '[0-9]{5}[0-9A-Z]{12}',
        'LT' => '[0-9]{16}',
        'LU' => '[0-9]{3}[0-9A-Z]{13}',
        'MK' => '[0-9]{3}[0-9A-Z]{10}[0-9]{2}',
        'MT' => '[A-Z]{4}[0-9]{5}[0-9A-Z]{18}',
        'MR' => '[0-9]{23}',
        'MU' => '[A-Z]{4}[0-9]{19}[A-Z]{3}',
        'MC' => '[0-9]{10}[0-9A-Z]{11}[0-9]{2}',
        'ME' => '[0-9]{18}',
        'NL' => '[A-Z]{4}[0-9]{10}',
        'NO' => '[0-9]{11}',
        'PL' => '[0-9]{24}',
        'PT' => '[0-9]{21}',
        'RO' => '[A-Z]{4}[0-9A-Z]{16}',
        'SM' => '[A-Z][0-9]{10}[0-9A-Z]{12}',
        'SA' => '[0-9]{2}[0-9A-Z]{18}',
        'RS' => '[0-9]{18}',
        'SK' => '[0-9]{20}',
        'SI' => '[0-9]{15}',
        'ES' => '[0-9]{20}',
        'SE' => '[0-9]{20}',
        'CH' => '[0-9]{5}[0-9A-Z]{12}',
        'TN' => '[0-9]{20}',
        'TR' => '[0-9]{5}[0-9A-Z]{17}',
        'AE' => '[0-9]{19}',
        'GB' => '[A-Z]{4}[0-9]{14}'
    );
    
    protected $iban;
    
    public static function _(array $config = array())
    {
        return new self($config);
    }

    public function isValid($iban)
    {
        $this->iban = $this->normalize($iban);

        if (!$this->isLengthValid()) {
            $this->_error(self::NOT_VALID);
            return false;
        }
        elseif (!$this->isLocalCodeValid()) {
            $this->_error(self::NOT_VALID);
            return false;
        }
        elseif (!$this->isFormatValid()) {
            $this->_error(self::NOT_VALID);
            return false;
        }
        elseif (!$this->isChecksumValid()) {
            $this->_error(self::NOT_VALID);
            return false;
        }
        else {
            return true;
        }
    }

    
    public function format()
    {
        return sprintf(
            '%s %s %s %s %s %s', $this->getLocaleCode() . $this->getChecksum(), substr($this->getInstituteIdentification(), 0, 4), substr($this->getInstituteIdentification(), 4, 4), substr($this->getBankAccountNumber(), 0, 4), substr($this->getBankAccountNumber(), 4, 4), substr($this->getBankAccountNumber(), 8, 2)
        );
    }

    public function getLocaleCode()
    {
        return substr($this->iban, f_valid_iban::LOCALECODE_OFFSET, f_valid_iban::LOCALECODE_LENGTH);
    }

    public function getChecksum()
    {
        return substr($this->iban, f_valid_iban::CHECKSUM_OFFSET, f_valid_iban::CHECKSUM_LENGTH);
    }

    public function getAccountIdentification()
    {
        return substr($this->iban, f_valid_iban::ACCOUNTIDENTIFICATION_OFFSET);
    }

    public function getInstituteIdentification()
    {
        return substr($this->iban, f_valid_iban::INSTITUTEIDENTIFICATION_OFFSET, f_valid_iban::INSTITUTEIDENTIFICATION_LENGTH);
    }

    public function getBankAccountNumber()
    {
        return substr($this->iban, f_valid_iban::BANKACCOUNTNUMBER_OFFSET, f_valid_iban::BANKACCOUNTNUMBER_LENGTH);
    }

    private function isLengthValid()
    {
        return strlen($this->iban) < f_valid_iban::IBAN_MIN_LENGTH ? false : true;
    }

    private function isLocalCodeValid()
    {
        $localeCode = $this->getLocaleCode();
        return !(isset(self::$ibanFormatMap[$localeCode]) === false);
    }

    private function isFormatValid()
    {
        $localeCode = $this->getLocaleCode();
        $accountIdentification = $this->getAccountIdentification();
        return !(preg_match('/' . self::$ibanFormatMap[$localeCode] . '/', $accountIdentification) !== 1);
    }

    private function isChecksumValid()
    {
        $localeCode = $this->getLocaleCode();
        $checksum = $this->getChecksum();
        $accountIdentification = $this->getAccountIdentification();
        $numericLocalCode = $this->getNumericLocaleCode($localeCode);
        $numericAccountIdentification = $this->getNumericAccountIdentification($accountIdentification);
        $invertedIban = $numericAccountIdentification . $numericLocalCode . $checksum;
        return $this->local_bcmod($invertedIban, 97) === '1';
    }

    private function getNumericLocaleCode($localeCode)
    {
        return $this->getNumericRepresentation($localeCode);
    }

    private function getNumericAccountIdentification($accountIdentification)
    {
        return $this->getNumericRepresentation($accountIdentification);
    }

    private function getNumericRepresentation($letterRepresentation)
    {
        $numericRepresentation = '';
        foreach (str_split($letterRepresentation) as $char) {
            if (array_search($char, self::$letterMapping)) {
                $numericRepresentation .= array_search($char, self::$letterMapping) + 9;
            }
            else {
                $numericRepresentation .= $char;
            }
        }
        return $numericRepresentation;
    }

    private function normalize($iban)
    {
        $value = trim($iban);
        $value = preg_replace('/\s+/', '', $value);

        return $value;
    }

    private function local_bcmod($x, $y)
    {
        if (!function_exists('bcmod')) {
            $take = 5;
            $mod = '';
            do {
                $a = (int) $mod . substr($x, 0, $take);
                $x = substr($x, $take);
                $mod = $a % $y;
            } while (strlen($x));
            return (int) $mod;
        }
        else {
            return bcmod($x, $y);
        }
    }
}