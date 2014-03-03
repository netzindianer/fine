<?php

return array(
    'model' => array(
        'imgsize' => array(
            'big' => array(             // key with the exact parameters
                'w'        => '200',         // width (required)
                'h'        => '150',         // height (required)
                'ext'      => 'jpg png gif', // default 'jpg' (optional)
                'type'     => 'thumb',       // or 'resize', default 'thumb' (optional)
                'quality'  => '90',          // quality of JPG image, value [0-100] (optional, parameter included only if ext='jpg')
                'extend'   => true,          // or 'false', default 'true' (optional, parameter included only if type='resize')
                'position' => 'left center', // textual values as for CSS:background-position, default 'center' (optional, parameter included only if type='thumb')
                'callback' => array(/* in callable type */), // fe. array(new stdClass(), 'method_name')
              ),
            '80', // or value 
            '80x100r',
            '80re0',
            '80q90prb',
           /* shortened configuration
            * 
            * THUMB
            * '80' => array('w' => 80, 'h' => 80, 'type' => 'thumb')
            * '80t' => array('w' => 80, 'h' => 80, 'type' => 'thumb')
            * '80x100' => array('w' => 80, 'h' => 100, 'type' => 'thumb')
            * 
            * '80q90' => array('w' => 80, 'h' => 80, 'type' => 'thumb', quality => 90)
            * 
            * '80pc' => array('w' => 80, 'h' => 80, 'type' => 'thumb', 'position' => 'center')
            * '80plt' => array('w' => 80, 'h' => 80, 'type' => 'thumb', 'position' => 'left top')
            * 
            * '80q90pcc' => array('w' => 80, 'h' => 80, 'type' => 'thumb', quality => 90, 'position' => 'center center')
            * '80q90prb' => array('w' => 80, 'h' => 80, 'type' => 'thumb', quality => 90, 'position' => 'right bottom')
            * 
            * RESIZE
            * '80r' => array('w' => 80, 'h' => 80, 'type' => 'resize', 'extend' => true)
            * '80x100r' => array('w' => 80, 'h' => 100, 'type' => 'resize', 'extend' => true)
            * 
            * '80rq90' => array('w' => 80, 'h' => 80, 'type' => 'resize', 'extend' => true, quality => 90)
            * 
            * '80re0' => array('w' => 80, 'h' => 80, 'type' => 'resize', 'extend' => false)
            * '80re1' => array('w' => 80, 'h' => 80, 'type' => 'resize', 'extend' => true)
            * 
            * '80rq90e1' => array('w' => 80, 'h' => 80, 'type' => 'resize', 'extend' => true, quality => 90)
            * '80rq90e0' => array('w' => 80, 'h' => 80, 'type' => 'resize', 'extend' => false, quality => 90)
            */
        )
    )
);