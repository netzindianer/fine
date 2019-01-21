<?php

class f_v_helper_formCheckbox extends f_v_helper_formElementAbstract
{

    public function helper($sName = 'checkbox', $mVal = null, $aAttr = array(),
                           $aOption = array(), $sSeparator = null)
    {
        if (substr($sName, -2) != '[]') {

            $value = $aAttr['value'];

            $input     = array(
                'type'  => 'checkbox',
                'name'  => $sName,
            );

            if ($value === null) {
                if ($mVal !== null) {
                    $input['value'] = $mVal;
                }
            }
            else {
                $input['value'] = $value;
            }

            if (($value === null && $mVal) || ($value !== null && $value == $mVal)) {
                $input['checked'] = 'checked';
            }
            else {
                unset($input['checked']);
            }
            $input += $aAttr;
            
            return "<input" . $this->_renderAttr($input) . " />";

        }
        else {

            $mVal          = (array)$mVal;
            $return        = array();
            $basePrepend   = "";
            $baseAppend    = "";
            $baseInnerPrepend = "";
            $baseInnerAppend  = "";
            $baseSeparator = "";
            $baseLabel     = array();
            if (isset($aAttr['_prepend'])) {
                $basePrepend = $aAttr['_prepend'];
                unset($aAttr['_prepend']);
            }
            if (isset($aAttr['_append'])) {
                $baseAppend = $aAttr['_append'];
                unset($aAttr['_append']);
            }
            if (isset($aAttr['_innerprepend'])) {
                $baseInnerPrepend = $aAttr['_innerprepend'];
                unset($aAttr['_innerprepend']);
            }
            if (isset($aAttr['_innerappend'])) {
                $baseInnerAppend = $aAttr['_innerappend'];
                unset($aAttr['_innerappend']);
            }
            if (isset($aAttr['_separator'])) {
                $baseSeparator = $aAttr['_separator'];
                unset($aAttr['_separator']);
            }
            if (isset($aAttr['_label'])) {
                $baseLabel = $aAttr['_label'];
                unset($aAttr['_label']);
            }

            foreach ($aOption as $key => $option) {

                $prepend   = $basePrepend;
                $append    = $baseAppend;
                $innerprepend = $baseInnerPrepend;
                $innerappend  = $baseInnerAppend;
                $separator = $baseSeparator;
                $label     = $baseLabel;
                $input     = array(
                    'type'  => 'checkbox',
                    'name'  => $sName,
                    'value' => $key,
                );
                if (in_array($key, $mVal)) {
                    $input['checked'] = 'checked';
                }
                else {
                    unset($input['checked']);
                }
                $input += $aAttr;

                if (! is_array($option)) {
                    $text = $option;
                }
                else {
                    if (isset($option['_prepend'])) {
                        $prepend = $option['_prepend'];
                        unset($option['_prepend']);
                    }
                    if (isset($option['_append'])) {
                        $append = $option['_append'];
                        unset($option['_append']);
                    }
                    if (isset($option['_innerprepend'])) {
                        $innerprepend = $option['_innerprepend'];
                        unset($option['_innerprepend']);
                    }
                    if (isset($option['_innerappend'])) {
                        $innerappend = $option['_innerappend'];
                        unset($option['_innerappend']);
                    }
                    if (isset($option['_separator'])) {
                        $separator = $option['_separator'];
                        unset($option['_separator']);
                    }
                    if (isset($option['_label'])) {
                        $label = $option['_label'] + $baseLabel;
                        unset($option['_label']);
                    }
                    $text = $option['_text'];
                    unset($option['_text']);
                    $input += $option;
                }

                $return[] = $prepend
                          . "<label" . $this->_renderAttr($label) . ">"
                          . $innerprepend
                          . "<input" . $this->_renderAttr($input) . " />"
                          . $separator
                          . $text
                          . $innerappend
                          . "</label>"
                          .$append;

            }

            return implode($sSeparator, $return);
        }

    }

}