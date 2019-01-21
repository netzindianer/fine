<?php

class f_decor_t extends f_decor_decor
{

    protected $_t;

    public function t($t)
    {
        $this->_t = $t;
        return $this;
    }

    public function decorate($subject)
    {
        parent::setDecor(f::$c->t($this->_t));
        return parent::decorate($subject);
    }

}
