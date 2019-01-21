<?php

class f_v_helper_formElement
{

    public function helper(f_form_element $element)
    {
        return f::$c->v->{$element->helper()}($element->name(), $element->val(), $element->attr(), $element->option(), $element->separator());
    }
    
}