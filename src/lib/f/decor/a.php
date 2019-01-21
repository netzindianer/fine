<?php

class f_decor_a extends f_decor_htmlTag
{

    const TARGET_BLANK  = '_blank';
    const TARGET_SELF   = '_self';
    const TARGET_PARENT = '_parent';
    const TARGET_TOP    = '_top';

    public function __construct()
    {
        parent::__construct("a", false);
    }

    public function setHref($url)
    {
        $this->setAttr("href", $url);
        return $this;
    }

    public function getHref()
    {
        return $this->getAttr("href");
    }

    public function setTarget($target)
    {
        $this->setAttr("target", $target);
        return $this;
    }

    public function getTarget()
    {
        return $this->getAttr("target");
    }

}
