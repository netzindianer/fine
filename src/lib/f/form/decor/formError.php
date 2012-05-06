<?php

class f_form_decor_formError extends f_form_decor_tag
{

    protected $_tag           = 'ul';
    protected $_itemPrepend   = '<li>';
    protected $_itemAppend    = '</li>';
    protected $_itemSeparator = '';
    
    /**
     * @return f_form_decor_formError
     */
    public static function _(array $config = array())
    {
        return new self($config);
    }

    public function short($bShort = null)
    {
        throw new f_form_decor_badMethodCall('Tag contains form errors can not be shrot');
    }

    public function element($aoAdditionalElement = null)
    {
        if (func_num_args() === 0) {
            return $this->_element;
        }
        if (! is_array($aoAdditionalElement)) {
            $aoAdditionalElement = array($aoAdditionalElement);
        }
        $this->_element = $aoAdditionalElement;
        return $this;
    }

    public function ignoreOwner($bIgnore = null)
    {
        if ($bIgnore === null) {
            return $this->_ignoreOwner;
        }
        $this->_ignoreOwner = (boolean)$bIgnore;
        return $this;
    }

    public function itemPrepend($sItemPrepend = null)
    {
        if (func_num_args() == 0) {
            return $this->_itemPrepend;
        }
        $this->_itemPrepend = $sItemPrepend;
        return $this;
    }

    public function itmeAppend($sItemAppend = null)
    {
        if (func_num_args() == 0) {
            return $this->_itemAppend;
        }
        $this->_itemAppend = $sItemAppend;
        return $this;
    }

    public function itemSeparator($sItemSeparator = null)
    {
        if (func_num_args() == 0) {
            return $this->_itemSeparator;
        }
        $this->_itemSeparator = $sItemSeparator;
        return $this;
    }

    public function render()
    {
        /** @todo sprawdzic czy czasem error nie zwraca tablicy z kluczami walidatorow (typami bledow) */
        // errors
        $errors = array();
        foreach ((array)$this->object->_ as $i) {
            /* @var $i f_form_element */
            if ($i->ignoreRender()) {
                continue;
            }
            $errors += $i->error();
        }

        // no errors = no decoration
        if (!$errors) {
            return $this->buffer;
        }

        // list
        $list = array();
        foreach ($errors as $error) {
            $list[] = $this->_itemPrepend . htmlspecialchars($error) . $this->_itemAppend;
        }
        $list = implode($this->_itemSeparator, $list);

        // decoration
        if ($this->_tag !== null) {
            $this->_prepare();
            $this->_decoration .= $list;
        }
        else {
            $this->_decoration  = $this->_prepend . $list;
            $this->_decoration2 = $this->_append;
        }

        return $this->_render();

    }

}