<?php

class f_c_helper_noAccess
{

    public function helper()
    {
        throw new f_c_exception_noAccess();
    }

    public function ifNot($bExpression)
    {
        if ($bExpression) {
            return;
        }
        throw new f_c_exception_noAccess();
    }

}
