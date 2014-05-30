<?php

return array(
    'css' => array(
        'style'  => array(
            'v'  => 1, // (required)
            'input' => array( // (optional), more info in `lib/f/c/helper/publicFiles2OneFile.php`
                /* fe.
                 * array('type' => 'dir', 'dir' => 'public/css/style'),
                 * array('type' => 'file', 'file' => array('public/css/style/file1.css', 'public/css/style/file2.css'))
                 */
            ),
            'replace_regexp'  => array(), // (optional)
        ),
        
    ),
    'js' => array(
        'style'  => array(
            'v'  => 1, // (required)
            'input' => array(), // (optional), more info in `lib/f/c/helper/publicFiles2OneFile.php`
            'replace_regexp'  => array(), // (optional)
        ),
        
    ),
);