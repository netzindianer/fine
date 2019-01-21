<?php

class f_v_helper_formMatrixRadio extends f_v_helper_formElementAbstract
{
    
    public function helper($sName = 'radio', $mVal = null, $aAttr = array(), $aOption = array())
    {
        $return = array();
        foreach ($aOption['row'] as $row => $rv) { 
            $sRow  = '';
            foreach($aOption['col'] as $key => $cv){
                $input = array(
                    'type'  => 'radio',
                    'name'  => $sName.'['.$row.']',
                    'value' => $key,
                );

                if (isset($mVal[$row]) && $mVal[$row] == $key) {
                    $input['checked'] = 'checked';
                }
                else {
                    unset($input['checked']);
                }
                $input += $aAttr;                
                $sRow .= "<td><input" . $this->_renderAttr($input) . " /></td>";
            }
            $return[] = "<th>".$rv."</th>".$sRow;
        }

        $head   = '<th></th>';
        foreach($aOption['col'] as $k => $v){
            $head .= '<th>'.$v.'</th>';
        }

        $class = $aOption['tabClass']['class'] ? ' class="'.$aOption['tabClass']['class'].'"' : '';
        
        return '<table'.$class.'><thead><tr>'.$head.'</tr></thead><tbody><tr>'.implode('</tr><tr>', $return).'</tr></tbody></table>';
    }

}