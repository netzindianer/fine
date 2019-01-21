<?php

class f_decor_decor implements f_decor_interface
{

    const PLACEMENT_PREPEND = 'PLACEMENT_PREPEND';
    const PLACEMENT_APPEND  = 'PLACEMENT_APPEND';
    const PLACEMENT_EMBRACE = 'PLACEMENT_EMBRACE';

    protected $_decor     = FALSE;
    protected $_decor2    = FALSE;
    protected $_placement = f_decor_decor::PLACEMENT_APPEND;

    /**
     * Ustawia dekorator 1 - musi to być string lub musi mieć toString()
     * @param string $decor
     * @return \f_decor_decor
     */
    public function setDecor($decor)
    {
        $this->_decor = $decor;
        return $this;
    }

    /**
     * Pobiera dekorator 1
     * @return string
     */
    public function getDecor()
    {
        return $this->_decor;
    }

    /**
     * Ustawia dekorator 2 - musi to być string lub musi mieć toString()
     * @param string $decor
     * @return \f_decor_decor
     */
    public function setDecor2($decor)
    {
        $this->_decor2 = $decor;
        return $this;
    }

    /**
     * Pobiera dekorator 2
     * @return string
     */
    public function getDecor2()
    {
        return $this->_decor;
    }

    /**
     * Ustawia położenie dekoratorów ( self::PLACEMENT_PREPEND | self::PLACEMENT_APPEND | self::PLACEMENT_EMBRACE )
     * @param string $placement
     * @return \f_decor_decor
     */
    public function setPlacement($placement)
    {
        $this->_placement = $placement;
        return $this;
    }

    /**
     * Zwraca ustawiony typ
     * @return string
     */
    public function getPlacement()
    {
        return $this->_placement;
    }

    public function decorate($subject)
    {
        switch ($this->_placement) {

            case self::PLACEMENT_PREPEND:
                return ($this->_decor !== FALSE ? $this->_decor : "") . ($this->_decor2 !== FALSE ? $this->_decor2 : "") . $subject;

            case self::PLACEMENT_APPEND:
                return $subject . ($this->_decor !== FALSE ? $this->_decor : "") . ($this->_decor2 !== FALSE ? $this->_decor2 : "");

            case self::PLACEMENT_EMBRACE:
                return ($this->_decor !== FALSE ? $this->_decor : "") . $subject . ($this->_decor2 !== FALSE ? $this->_decor2 : "");

            default:
                throw new LogicException("Unsupported placement value: " . $this->_placement);
        }
    }

}
