<?php

class f_decor_chain implements f_decor_interface
{

    protected $_decor;

    public function __construct($decors = array())
    {
        $this->setDecors($decors);
    }

    /**
     * Dodaje dekorator do łańcucha z uwzględnieniem kolejności
     * @param f_decor_interface $decor Dekorator
     * @param int $pos Kolejność na liście 0 - dekorator będzie dodany na początku, -1 - dekorator będzie dodany na końcu
     * @return \f_decor_chain
     * @throws InvalidArgumentException
     */
    public function addDecor($decor, $pos = -1)
    {
        if (!($decor instanceof f_decor_interface)) {
            throw new InvalidArgumentException("InvalidArgumentException: \$decor must implement f_decor_interface");
        }

        $new = array();
        for ($i = 0; $i < count($this->_decor); $i ++) {
            if ($i == $pos) {
                $new[] = $decor;
            }
            $new[] = $this->_decor[$i];
        }

        if ($pos == -1) {
            $new[] = $decor;
        }

        $this->_decor = $new;
        
        return $this;
    }

    /**
     * Dodaje wszystkie dekoratory z tablicy do łańcucha
     * @param array $decors
     * @param int $pos
     * @return \f_decor_chain
     * @throws InvalidArgumentException
     */
    public function addDecors($decors, $pos = -1)
    {
        foreach ($decors as $decor) {
            $this->addDecor($decor, $pos);
        }
        return $this;
    }

    /**
     * Zwraca dekoratory z łańcucha
     * @return arrary
     */
    public function getDecors()
    {
        return $this->_decor;
    }

    /**
     * Ustawia tablicę dekoratorów
     * @param array $decors
     * @return \f_decor_chain
     */
    public function setDecors($decors)
    {
        $this->clearDecors();
        $this->addDecors($decors);
        return $this;
    }

    /**
     * Czyści łańcuch dekoratorów
     * @return \f_decor_chain
     */
    public function clearDecors()
    {
        $this->_decor = array();
        return $this;
    }

    public function decorate($subject)
    {
        foreach ($this->_decor as $decor) {
            $subject = $decor->decorate($subject);
        }
        return $subject;
    }

}
