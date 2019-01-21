<?php

class f_decor_htmlTag extends f_decor_decor
{

    protected $_placement = f_decor_decor::PLACEMENT_EMBRACE;
    protected $_tag       = 'div';
    protected $_attr      = array();
    protected $_short     = false;

    public function __construct($tag = "div", $short = false)
    {
        $this->_tag   = $tag;
        $this->_short = $short;
    }

    /**
     * Ustawia tag HTML (np. div)
     * @param string $tag
     * @return \f_decor_htmlTag
     */
    public function setTag($tag)
    {
        $this->_tag = $tag;
        return $this;
    }

    /**
     * Pobiera ustawiony tag HTML
     * @return string
     */
    public function getTag()
    {
        return $this->_tag;
    }

    /**
     * Sprawdza czy ustawiony jest atrybut
     * Uwaga na wielkość liter
     * @param type $name
     * @return type
     */
    public function hasAttr($name)
    {
        return array_key_exists($name, $this->_attr);
    }

    /**
     * Ustawia atrybut
     * @param string $name Nazwa atrybutu
     * @param string $val Wartość atrybutu
     * @return \f_decor_htmlTag
     */
    public function setAttr($name, $val)
    {
        $this->_attr[$name] = $val;
        return $this;
    }

    /**
     * Pobiera wartość atrybutu
     * @param type $name Nazwa atrybutu
     * @return string
     */
    public function getAttr($name)
    {
        return $this->_attr[$name];
    }

    /**
     * Usuwa atrybut
     * @param type $name Nazwa atrybutu
     */
    public function removeAttr($name)
    {
        unset($this->_attr[$name]);
    }

    /**
     * Sprawdza czy ma ustawiony atrybut
     * @param array $names Nazwa atrybutu
     * @return boolean
     */
    public function hasAttrs(array $names)
    {
        foreach ($names as $name) {
            if ($this->isAttrs($name)) {
                continue;
            }
            return false;
        }
        return true;
    }

    /**
     * Ustawia atrybuty
     * @param array $attrs
     */
    public function setAttrs(array $attrs)
    {
        foreach ($attrs as $name => $val) {
            $this->setAttr($name, $val);
        }
    }

    /**
     * Pobiera atrybuty
     * @return array
     */
    public function getAttrs()
    {
        return $this->_attr;
    }

    /**
     * Usuwa wszystkie atrybuty
     * @return \f_decor_htmlTag
     */
    public function removeAttrs()
    {
        $this->_attr = array();
        return $this;
    }

    /**
     * Ustawia czy to ma być shorttag (np. <input />)
     * @param boolean $short
     * @return \f_decor_htmlTag
     */
    public function setShort($short)
    {
        $this->_short = $short;
        return $this;
    }

    /**
     * Sprawdza czy to jest shorttag
     * @return boolean
     */
    public function isShort()
    {
        return $this->_short;
    }

    /**
     * Pobiera id
     * @return type
     */
    public function getId()
    {
        return $this->getAttr("id");
    }

    /**
     * Ustawia id
     * @param string $id
     */
    public function setId($id)
    {
        $this->setAttr("id", $id);
    }

    /**
     * Dodaje klasę
     * @param string $class
     * @return \f_decor_htmlTag
     */
    public function addClass($class)
    {
        $this->addClasses(array($class));
        return $this;
    }

    /**
     * Usuwa klasę
     * @param string $class
     * @return \f_decor_htmlTag
     */
    public function removeClass($class)
    {
        $this->removeClasses(array($class));
        return $this;
    }

    /**
     * Sprawdza czy ma klasę
     * @param string $class
     * @return boolean
     */
    public function hasClass($class)
    {
        return in_array($class, $this->getClasses());
    }

    /**
     * Dodaje klasy
     * @param array $classes
     * @return \f_decor_htmlTag
     */
    public function addClasses($classes)
    {
        $this->setClasses(array_merge($classes, $this->getClasses()));
        return $this;
    }

    /**
     * Ustawia klasy
     * @param array $classes
     * @return \f_decor_htmlTag
     */
    public function setClasses($classes)
    {
        if ($classes) {
            $this->setAttr('class', implode(' ', $classes));
        }
        else {
            $this->removeAttr('class');
        }
        return $this;
    }

    /**
     * Czyści wszystkie klasy
     * @return \f_decor_htmlTag
     */
    public function clearClasses()
    {
        $this->setClasses(array());
        return $this;
    }

    /**
     * Usuwa klasy
     * @param array $classes
     * @return \f_decor_htmlTag
     */
    public function removeClasses($classes)
    {
        $this->setClasses(array_diff($this->getClasses(), $classes));
        return $this;
    }

    /**
     * Sprawdza czy ma wszystkie klasy z tablicy
     * @param array $classes
     * @return boolean
     */
    public function hasClasses($classes)
    {
        foreach ($classes as $c) {
            if (!$this->hasClass($c)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Pobiera klasy
     * @return type
     */
    public function getClasses()
    {
        $class = $this->getAttr("class");
        if ($class) {
            return explode(" ", $class);
        }
        return array();
    }

    /**
     * Dodaje styl
     * @param string $name Nazwa parametru (np. color)
     * @param string $value Wartość parametru (np. red)
     * @return \f_decor_htmlTag
     */
    public function addStyle($name, $value)
    {
        $this->addStyles(array($name => $value));
        return $this;
    }

    /**
     * Usuwa styl
     * @param string $name Nazwa parametru (np. color)
     * @return \f_decor_htmlTag
     */
    public function removeStyle($name)
    {
        $this->removeStyles(array($name));
        return $this;
    }

    /**
     * Czy ma ustawiony styl
     * @param string $name Nazwa parametru (np. color)
     * @return boolean
     */
    public function hasStyle($name)
    {
        return in_array($name, array_keys($this->getStyles()));
    }

    /**
     * Sprawdza czy ma ustawione wszystkie style
     * @param array $styles
     * @return \f_decor_htmlTag
     */
    public function setStyles($styles)
    {
        $css = array();
        foreach ($styles as $name => $val) {
            $css[] = $name . ": " . $val . ";";
        }
        if ($css) {
            $this->setAttr('style', implode(' ', $css));
        }
        else {
            $this->removeAttr('style');
        }
        return $this;
    }

    /**
     * Czyści wszystkie style
     * @return \f_decor_htmlTag
     */
    public function clearStyles()
    {
        $this->setStyles(array());
        return $this;
    }

    /**
     * Dodaje style
     * @param array $styles
     * @return \f_decor_htmlTag
     */
    public function addStyles($styles)
    {
        $stls = $this->getStyles();
        foreach ($styles as $name => $val) {
            $stls[$name] = $val;
        }
        $this->setStyles($stls);
        return $this;
    }

    /**
     * Usuwa wszystkie podane style
     * @param array $styles
     * @return \f_decor_htmlTag
     */
    public function removeStyles($styles)
    {
        $stls = $this->getStyles();
        foreach ($styles as $s) {
            unset($stls[$s]);
        }
        $this->setStyles($stls);
        return $this;
    }

    /**
     * Sprawdza czy ma wszystkie style
     * @param array $styles
     * @return boolean
     */
    public function hasStyles($styles)
    {
        foreach ($styles as $name) {
            if (!$this->hasStyle($name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Pobiera wartość stylu
     * @param string $name
     * @return string
     */
    public function getStyle($name)
    {
        $styles = $this->getStyles();
        return $styles[$name];
    }

    /**
     * Pobiera style
     * @return array
     */
    public function getStyles()
    {
        $style = $this->getAttr("style");
        if ($style) {
            $style  = str_replace(' ', '', $style);
            $spl    = explode(';', $style);
            $styles = array();
            foreach ($spl as $s) {
                $expl2 = explode(':', $s);
                if ($expl2[0]) {
                    $styles[$expl2[0]] = $expl2[1];
                }
            }
            return $styles;
        }
        return array();
    }

    //
    public function setDecor($decor)
    {
        throw new LogicException("Function: setDecor cannot be used in htmlTag decor");
    }

    public function setDecor2($decor)
    {
        throw new LogicException("Function: setDecor2 cannot be used in htmlTag decor");
    }

    public function decorate($subject)
    {
        $attr = "";
        if ($this->_attr) {
            foreach ((array) $this->_attr as $k => $v) {
                $attr .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
            }
        }

        parent::setDecor("<" . $this->_tag . $attr . ($this->_short ? " />" : ">"));
        if (!$this->_short) {
            parent::setDecor2("</" . $this->_tag . ">");
        }

        return parent::decorate($subject);
    }

}
