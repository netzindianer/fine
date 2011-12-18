<?php

class c_error extends f_c
{

    public function error()
    {

        switch ($this->error->type) {

            case f_error::ERROR_NOT_FOUND:
                $this->response->code(404)->body("404 Not Found");
                break;

            case f_error::ERROR_NO_ACCESS:
                $this->response->code(403)->body("403 Na Access");
                break;

            default:
                $this->response->code(500)->body("500 Internal Server Error");
                break;
        }
        
        $this->response->send();
    }

}