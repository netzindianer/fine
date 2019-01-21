<?php

class f_filter_normalizeUriScheme extends f_filter_abstract
{

    const SCHEME_HTTP = 'http://';
    const SCHEME_HTTPS = 'https://';

    public static function _()
    {
        return new self;
    }

    public function filter($sUri)
    {      
        $sUri = trim($sUri);
        if (!$sUri) {
            return $sUri;
        }
        
        if(strpos($sUri, "/") === 0) { // nie normalizuj jeśli to jest path
            return $sUri;
        }
        
        $aRepair = array(
            '#^[h]+[tp]*[:;]+[\\\/]*#i' => self::SCHEME_HTTP,
            '#^[h]*[tp]*[:;]*[\\\/]+#i' => self::SCHEME_HTTP,
            '#^[h]+[tp]*[s]+[:;]+[\\\/]*#i' => self::SCHEME_HTTPS,
            '#^[h]*[tp]*[s]+[:;]*[\\\/]+#i' => self::SCHEME_HTTPS,
        );
        
        foreach ($aRepair as $pattern => $replacement) {
            $sRet = preg_replace($pattern, $replacement, $sUri);
            
            if ($sRet != $sUri) {
                return $sRet;
            }
        }
        
        if (mb_substr($sUri, 0, 8) != self::SCHEME_HTTPS && mb_substr($sUri, 0, 7) != self::SCHEME_HTTP) {
            return self::SCHEME_HTTP . $sUri;
        }
        
        return $sUri;
    }

}