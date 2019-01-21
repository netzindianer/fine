<?php

class f_c_helper_cut
{

    public function helper($sText, $iLength, $sEnd = '…')
    {
        if (isset($sText[$iLength-1])) {
            return mb_substr($sText, 0 , $iLength, 'UTF-8') . $sEnd;
        }
        
        return $sText;
    }

    public function hash($sString, $length = 255, $spliter = '...')
    {
        // jezeli sie miesci to nic nie skracamy
        if (strlen($sString) <= $length) {
            return $sString;
        }
        
        return substr($sString, 0, $length - 40 - strlen($spliter)) . $spliter . sha1($sString);
        
    }

}
