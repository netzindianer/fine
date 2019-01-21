<?php

class f_c_action extends f_c implements f_c_action_interface
{

    public function dispatch()
    {          
        if (isset($this->request->get(1)[0])) {
            $action = $this->request->get(1);
        }
        else {
            $action = 'index';
        }
        
        $method = $action.'Action';        
        if (! method_exists($this, $method)) {
            $index = 'indexAction';
            
            if (! method_exists($this, $index)) {
                throw new f_c_exception_notFound();
            }
            
            $method = $index;
        }
        
        return $this->{$method}();
    }

}
