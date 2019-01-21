<?php

class f_form_recaptcha extends f_form_element
{

    protected $_type = 'recaptcha';
    protected $_helper = 'formRecaptcha';
    protected $_attr = array('class' => 'form-captcha');
    protected $_requiredClass = 'f_valid_recaptcha';

    public function __construct(array $config = array())
    {
        parent::__construct($config);
        $this->required(true);
    }

}
