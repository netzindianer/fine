<?php

class f_decor_view implements f_decor_interface
{

    protected $_view;
    protected $_f_v;

    public function __construct($f_v, $view)
    {
        $this->_view = $view;
        $this->setViewController($f_v);
    }

    public function getView()
    {
        return $this->_view;
    }

    public function setView($view)
    {
        $this->_view = $view;
        return $this;
    }

    public function getViewController()
    {
        return $this->_f_v;
    }

    public function setViewController($f_v)
    {
        if (!($f_v instanceof f_v)) {
            throw new InvalidArgumentException("InvalidArgumentException: \$view musi implementować f_v");
        }
        $this->_f_v = $f_v;
        return $this;
    }

    public function decorate($subject)
    {
        $this->_f_v->decorSubject = $subject;
        return $this->_f_v->render($this->_view);
    }

}
