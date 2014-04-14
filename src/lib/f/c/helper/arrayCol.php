<?php

class f_c_helper_arrayCol
{

    /**
     * Return the values from a single column in the input array
     * 
     * @param array $array A multi-dimensional array (record set) from which to pull a column of values.
     * @param mixed $mColumnKey The column of values to return. 
     *                          This value may be the integer key of the column you wish to retrieve, or it may be the string key name for an associative array.
     * @param mixed $mIndexKey The column to use as the index/keys for the returned array. 
     *                         This value may be the integer key of the column, or it may be the string key name.
     * @return array Returns an array of values representing a single column from the input array.
     */
    public function helper(array $array, $mColumnKey, $mIndexKey = null)
    {
        $result = array();
     
        foreach ($array as $row) {
            if ($mIndexKey !== null) {
                $result[$row[$mIndexKey]] = $row[$mColumnKey];
            }
            else {
                $result[] = $row[$mColumnKey];
            }
        }
     
        return $result;        
    }

}