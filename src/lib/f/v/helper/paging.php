<?php

class f_v_helper_paging
{

    public function helper(f_paging $paging)
    {

        /* setup */
        $out      = '';
        $all      = $paging->all();          // liczba wszystkich elementow
        $page     = $paging->page();         // aktualna strona
        $first    = $paging->firstPage();    // pierwsza strona, standardowo 1
        $pages    = $paging->pages();        // wszystkich stron
        $last     = $first+$pages-1;         // ostatnia strona
        $uri      = $paging->uri();          // adres jako parametry dla helpera uri
        $uriParam = $paging->uriParam();     // parametry adresy okreslajacy strone
        $var      = $paging->uriVar();       // zmienna do podstawienia strony w adresie stringowym
        $width    = $paging->param('width'); // liczba linkow pomiedzy aktualna strona i spacja
        $href     = $paging->param('href');
        $onclick  = $paging->param('onclick');
        $style    = $paging->param('style');
        $link     = array();
        
        if (!($all > 0) || !($pages > 1)) {
            return '';
        }

        if (!$width || $width < 1) {
            $width = 2;
        }
        
        /*  $link - table of pages np. 1 [ ] 4 5 [6] 7 8 [ ] 934 */
        
        // bierzemy kulę o środku $page i r=$width        
        $a = $page-$width < $first ? $first : $page-$width;
        $b = $page+$width > $last ? $last : $page+$width;
        
        for ($i=$a; $i<=$b; ++$i) {
            array_push($link, $i);
        }
        
        // jeśli pomiędzy pierwszm a brzegiem kuli są więcej niż 2 elementy 
        if ($page-$width > $first+2) {
            array_unshift($link, ' ');
        } elseif ($page-$width > $first+1) {
            array_unshift($link, $first+1);
        }

        // pierwszy
        if ($page-$width > $first) {
            array_unshift($link, $first);
        }

        // jeśli pomiędzy ostatnim a brzegiem kuli są więcej niż 2 elementy 
        if ($page+$width < $last-2) {
            array_push($link, ' ');
        } elseif ($page+$width < $last-1) {
            array_push($link, $last-1);
        }

        // ostatni
        if ($page+$width < $last) {
            array_push($link, $last);
        }

        /* render */
        $itemtpl = "";

        if (isset($href) || isset($onclick)) {
            $itemtpl = '<li class="paging-li paging-page"><a class="paging-a" '
            . (isset($href) ? 'href="' . $href . '"' : '')
            . (isset($onclick) ? 'onclick="' . $onclick . '"' : '')
            . '>' . $var . '</a></li> ';
        }
        else {
            if (!$uri) {
                $uri =  f::$c->uri->assembleRequest(array($uriParam => '___page___'));
                $uri = str_replace('___page___', $var, $uri);
            }

            if (is_array($uri)) {
                $uri[$uriParam] = $var;
                $uri            = f::$c->uri($uri); // adres jako string z markerem {page}
            }

            $itemtpl .= '<li class="paging-li paging-page">'
            . '<a class="paging-a" href="' . $uri . '">' . $var . '</a>'
            . '</li> ';
        }

        
        if($style && $style == 'bootstrap'){
            $out = '<ul class="pagination">';
            foreach ($link as $i) {
                if ($i == ' ') {
                    $out .= '<li class="disabled"><a> ... </a></li> ';
                }
                else if ($i == $page) {
                    $out .= '<li class="active"><a>' . $i . '</a></li> ';
                }
                else {
                    $out .= str_replace($var, $i, $itemtpl);
                }
            }
            $out .= '</ul>';
        }
        else {        
            $out = '<div class="box-paging"><ul class="paging-ul">';
            foreach ($link as $i) {
                if ($i == ' ') {
                    $out .= '<li class="paging-li paging-space"> ... </li> ';
                }
                else if ($i == $page) {
                    $out .= '<li class="paging-li paging-current">' . $i . '</li> ';
                }
                else {
                    $out .= str_replace($var, $i, $itemtpl);
                }
            }
            $out .= '</ul><div class="paging-end"></div></div>';
        }

        return $out;
    }

}