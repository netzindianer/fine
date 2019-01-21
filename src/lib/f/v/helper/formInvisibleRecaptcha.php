<?php

class f_v_helper_formInvisibleRecaptcha extends f_v_helper_formElementAbstract
{

    public function helper($sName = 'button', $mVal = null, $aAttr = array())
    {
        $key = f::$c->config->googleApi['reCAPTCHA_Site_key'];
        return "<script src=\"https://www.google.com/recaptcha/api.js?hl=de\" async defer></script>"
//                . "<div style=\"float: left\" class=\"g-recaptcha\" data-sitekey=\"" . $key . "\"></div><div style=\"clear: both;\"></div></div>";
        .'<script>
            function onSubmit(token) {
                console.log(token);
                document.getElementById("form").submit();
            }
         </script>'
        .'<div class="g-recaptcha" data-badge="inline" data-bind="recaptcha-submit" data-sitekey="'.$key.'" data-callback="onSubmit"></div>'
        .'<input type="submit" name="send" value="Submit" id="recaptcha-submit" />'
//        .'<input type="submit" name="send" value="Submit" class="g-recaptcha" data-badge="inline" data-sitekey="'.$key.'" data-callback="onSubmit">'
        
        ;
    }

}
