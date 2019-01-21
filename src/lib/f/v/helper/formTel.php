<?php

class f_v_helper_formTel extends f_v_helper_formElementAbstract
{

    public function helper($sName = 'tel', $mVal = null, $aAttr = array())
    {
        return "<input" . $this->_renderAttr(
                   array('type' => 'tel', 'name' => $sName, 'value' => $mVal) + $aAttr
                )
             . " />";
    }

}