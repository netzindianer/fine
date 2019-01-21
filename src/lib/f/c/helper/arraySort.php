<?php

class f_c_helper_arraySort
{

    /*
     * klucz z tablicy asocjacyjne wg ktorego odbywa sie sortowanie
     */
    protected $_field;
    
    /*
     * typ sortowania
     * - SORT_REGULAR - sortowania elemntow normalnie (domyślnie)
     * - SORT_NUMERIC - sortowania elemntow numerycznie
     * - SORT_STRING  - sortowania elemntow jak stringi
     * - SORT_NATURAL - sortowania elemntow algorytmem 'naturalny porzadek'
     * - SORT_FLAG_CASE - sortowania elemntow bez uwzgledniania wielkosci liter (mozna laczyc przez operator `| (binarne lub)` z SORT_STRING lub SORT_NATURAL)
     */
    protected $_flags;
    
    /*
     * porzadek sortowania
     * - SORT_ASC  - sortuj w porządku rosnącym (domyślnie)
     * - SORT_DESC - sortuj w porządku malejącym
     */
    protected $_order;

    /**
     * sortowanie tablicy wg wskazanych kryteriow
     * 
     * @param array $array
     * @param string $field
     * @param CONST $order
     * @param CONST $sortFlag
     * @return array
     */
    public function helper(array $array, $field = null, $order = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        $this->_flags = $sortFlag === SORT_REGULAR ? array(SORT_REGULAR) : $this->_resolveSortFlag($sortFlag);
        $this->_field = $field;
        $this->_order = strtoupper($order);
        
        usort($array, 'f_c_helper_arraySort::_sort');
        return $this->_order == SORT_DESC ? array_reverse($array) : $array;
    }

    protected function _sort($a, $b)
    {
        if ($this->_field) {
            $a = $a[$this->_field];
            $b = $b[$this->_field];
        }

        foreach ($this->_flags as $flag) {

            if ($flag === SORT_NUMERIC) {
                if (is_numeric($a)) {
                    $a = floatval($a);
                }
                if (is_numeric($b)) {
                    $b = floatval($b);
                }
            }
            
            if ($flag === SORT_STRING) {
                $a = strval($a);
                $b = strval($b);
            }
            
            if ($flag === SORT_FLAG_CASE) {
                $a = strtolower($a);
                $b = strtolower($b);
            }

            if ($flag === SORT_NATURAL) {
                return strnatcmp($a, $b);
            }
        }
        
        return $a > $b;
    }
    
    protected function _resolveSortFlag($flag)
    {
        $flags = array();

        if ($flag & SORT_STRING) {
            $flags[] = SORT_STRING;
        }
        if ($flag & SORT_FLAG_CASE) {
            $flags[] = SORT_FLAG_CASE;
        }
        if ($flag & SORT_NUMERIC) {
            $flags[] = SORT_NUMERIC;
        } 
        if ($flag & SORT_NATURAL) {
            $flags[] = SORT_NATURAL;
        }

        return $flags;
    }
    
}