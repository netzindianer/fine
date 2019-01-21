<?php

abstract class f_v_helper_formElementAbstract
{

    protected function _renderAttr($aAttr)
    {
        $render = "";
        foreach ($aAttr as $k => $v) {
            if (is_array($v)) {
                continue;
            }
            $render .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
        }
        return $render;
    }

}