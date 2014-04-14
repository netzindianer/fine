<?php

class f_c_helper_responseJsonApi extends f_c
{
    
    protected $_registred = false;

    public function helper()
    {
        $this->register();
        
        $this->_c->response->head = (object)array('status' => 'ok');
      
        return $this;
    }
    
    public function error($sErrorKey, $sErrorMsg = null)
    {
        $this->_c->response->head->status = 'error';
        $this->_c->response->head->error  = $sErrorKey;
        if ($sErrorMsg !== null) {
            $this->_c->response->head->error_msg  = $sErrorMsg;
        }
        return $this;
    }
    
    public function body($mData)
    {
        $this->_c->response->body = $mData;
        return $this;
    }

    public function head($sKey, $mData)
    {
        $this->_c->response->head->{$sKey} = $mData;
        return $this;
    }
    
    public function register()
    {
        if ($this->_registred !== false) {
            return;
        }
        
        $this->_registred = true;
        
        f::$c->render->off();
        
        f::$c->event->on(f_c_response::EVENT_RESPONSE_PRE, array($this, 'responsePre'));
    }

    public function responsePre(f_event $event)
    {
        /* @var $response f_c_response */
        
        $response = $event->subject();

        $response->header('Content-Type', 'application/json');
        $response->body = json_encode((object)array('head' => $response->head, 'body' => $response->body));
    }

}