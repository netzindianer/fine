<?php

class f_v_helper_formInfo extends f_v_helper_formElementAbstract
{
//    private $_style = [
//      'padding' => '6px 12px',
//      'display' => 'inline-block'
//    ];
    
    public function helper($sName = 'span', $mVal = null, $aAttr = array(), $aOption = array())
    {
        if(!isset($aAttr['value'])) {
            $aAttr['value'] = isset($aAttr['text']) ? $aAttr['text'] : '';
        }
        
        $span = "<span style='padding:6px 12px; display:inline-block'>" . (isset($aAttr['text'])?$aAttr['text']:'') . '</span>';
        $hidden = "<input type='hidden' name='$sName' value='".$aAttr['value']."' />";
        return $span . $hidden;
    }

}