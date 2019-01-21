<?php

class f_di
{

    protected $_service;
    
    public function _()
    {
        return new self();
    }

    public function __get($name)
    {
        if (method_exists($this, "_{$name}")) {
            return $this->{"_{$name}"}();
        }

        if (isset($this->_service[$name])) {
            return $this->$name = new $this->_service[$name];
        }
        
        return null;
    }
    
    public function __isset($name)
    {
        return method_exists($this, "_{$name}") || isset($this->_service[$name]);
    }
    
    public function getIterator()
    {
        $services = array();
        
        foreach ($this->_service as $name => $class) {
            $services[$name] = $this->{$name};
        }
        
        return new ArrayIterator($services);
    }    

}
