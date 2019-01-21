<?php

class f_filter_normalizePhone extends f_filter_abstract
{

    public static function _()
    {
        return new self;
    }

    public function filter($sPhone)
    {      
        $sPhone = trim($sPhone);
        if (!$sPhone) {
            return $sPhone;
        }
        
        if ($sPhone[0] == '+') {
            $sPhone = '00'.substr($sPhone, 0);
        }
                        
        return preg_replace('#\D#', '', $sPhone);
    }

}