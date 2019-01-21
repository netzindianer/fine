<?php

class f_v_helper_formRecaptcha extends f_v_helper_formElementAbstract
{

    public function helper($sName = 'button', $mVal = null, $aAttr = array())
    {
        $key = f::$c->config->googleApi['reCAPTCHA_Site_key'];
        return "<div><script src=\"https://www.google.com/recaptcha/api.js?hl=de\" async defer></script>"
                . "<div style=\"float: left\" class=\"g-recaptcha\" data-sitekey=\"" . $key . "\"></div><div style=\"clear: both;\"></div></div>";
    }

}
