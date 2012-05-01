<?php

class f_form_submit extends f_form_element
{

    protected $_type       = 'submit';
    protected $_viewHelper = 'formSubmit';
    protected $_attr       = array('class' => 'form-submit');
    protected $_ignoreVal  = true;
    
    protected $_decorForm  = array(
        'viewHelper' => 'f_form_decor_viewHelper',
        'tag'        => 'f_form_decor_tag',
    );

}