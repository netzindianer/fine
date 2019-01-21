<?php

class f_v_helper_formEmail extends f_v_helper_formElementAbstract
{

    public function helper($sName = 'email', $mVal = null, $aAttr = array())
    {
        return "<input" . $this->_renderAttr(
                   array('type' => 'email', 'name' => $sName, 'value' => $mVal) + $aAttr
                )
             . " />";
    }

}