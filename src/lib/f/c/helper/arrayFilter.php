<?php

class f_c_helper_arrayFilter
{

    /**
     * 
     * @param string $query
     * @param array $arr
     * @param array|string $fields
     * @return array
     */
    public function helper($query, array $arr, $fields = null, $bReturnKey = false)
    {
        $return = array();
        
        if (!$query || !$arr) {
            return $return;
        }
        
        if (!is_array($arr)) {
            return $return;
        }
        
        if ($fields && !is_array($fields)) {
            $fields = array($fields);
        }
        
        $return = $this->_filterArr($query, $arr, $fields, $bReturnKey);
        
        return $return;
    }
    
    protected function _filterArr($query, $arr, $fields, $bReturnKey)
    {
        $return = array();
        
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                if ($this->_filterArr($query, $v, $fields, $bReturnKey)) {
                    $return[] = $bReturnKey ? $k : $v;
                }
            }
            else {

                if ($fields) {
                    if (!in_array((string)$k, $fields)) {
                        continue;
                    }
                }
                
                if (strpos($v, $query) !== false) {
                    return true;
                }
            }
        }
        
        return $return;
    }
    
}