<?php

class f_c_helper_uri
{

    const ASSEMBLE_RANGE_ABS   = 'ASSEMBLE_RANGE_ABS';
    const ASSEMBLE_RANGE_BASE  = 'ASSEMBLE_RANGE_BASE';
    const ASSEMBLE_RANGE_PARAM = 'ASSEMBLE_RANGE_PARAM';
    
    const RESOLVE_RANGE_ABS    = 'RESOLVE_RANGE_ABS';
    const RESOLVE_RANGE_BASE   = 'RESOLVE_RANGE_BASE';
    const RESOLVE_RANGE_PARAM  = 'RESOLVE_RANGE_PARAM';

    protected $_assembleRange = self::ASSEMBLE_RANGE_BASE;
    protected $_resolveRange  = self::RESOLVE_RANGE_BASE;
    protected $_separator     = '/';

    public static function parse($sUri)
    {
        static $keys = array('scheme'   => 0, 'user'     => 0, 'pass'     => 0, 'host'     => 0,
                             'port'     => 0, 'path'     => 0, 'query'    => 0, 'fragment' => 0);
        $matches     = array();

        if (preg_match(
                '~^((?P<scheme>[^:/?#]+):(//))?((\\3|//)?(?:(?P<user>[^:]+):(?P<pass>[^@]+)@)?(?P<host>[^/?:#]*))'
              . '(:(?P<port>\\d+))?'
              . '(?P<path>[^?#]*)(\\?(?P<query>[^#]*))?(#(?P<fragment>.*))?~u',
                $sUri,
                $matches
        )) {
            foreach ($matches as $key => $value) {
                if (!isset($keys[$key]) || empty($value)) {
                    unset($matches[$key]);
                }
            }
            return $matches;
        }
        return false;
    }

    public function helper($asUriParams)
    {
        return $this->assemble($asUriParams);
    }

    public function resolve($sUri)
    {
        switch ($this->_resolveRange) {
            case self::RESOLVE_RANGE_ABS:
                return $this->resolveAbs($sUri);

            case self::RESOLVE_RANGE_BASE:
                return $this->resolveBase($sUri);

            case self::RESOLVE_RANGE_PARAM:
                return $this->resolveParam($sUri);

            default:
                throw new f_c_exception_domain('Invalid `resolveRange`');
        }
    }

    public function resolveRange($tResolveRange = null)
    {
        if (func_num_args() == 0) {
            return $this->_resolveRange;
        }
        $this->_resolveRange = $tResolveRange;
        return $this;
    }

    public function resolveAbs($sUri)
    {
        $parsed = self::parse($sUri);
        $sUri   = $parsed['path'] ;

        if (isset($parsed['query'])) {
            $sUri .=  '?' . $parsed['query'];
        }
        
        return $this->_resolve($sUri);
    }

    public function resolveBase($sUri)
    {
        $sUri = substr($sUri, strlen(f::$c->uriBase));

        return $this->_resolve($sUri);
    }

    public function resolveParam($sUri)
    {
        return $this->_resolve($sUri);
    }

    public function assemble($asUri)
    {
        switch ($this->_assembleRange) {

            case self::ASSEMBLE_RANGE_PARAM:
                return $this->_assemble($asUri);

            case self::ASSEMBLE_RANGE_BASE:
                return f::$c->uriBase . $this->_assemble($asUri);

            case self::ASSEMBLE_RANGE_ABS:
                return f::$c->uriAbs . $this->_assemble($asUri);

            default :
                throw new f_c_exception_domain('Invalid `assembleRange`');
        }
    }

    public function assembleRange($tAssembleRange = null)
    {
        if (func_num_args() == 0) {
            return $this->_assembleRange;
        }
        $this->_assembleRange = $tAssembleRange;
        return $this;
    }

    public function assembleAbs($asUri)
    {
        return f::$c->uriAbs . $this->_assemble($asUri);
    }

    public function assembleBase($asUri)
    {
        return f::$c->uriBase . $this->_assemble($asUri);
    }

    public function assembleParam($asUri)
    {
        return $this->_assemble($asUri);
    }

    protected function _resolve($sUri)
    {
        list($sPath, $sQuery) = explode('?', $sUri, 2);
        
        $param = explode($this->_separator, $sPath);
        $aUri  = array();

        for ($i = 0; isset($param[$i]); $i += 2) {

            $j = $i + 1;

            // numeric this and next
            $aUri[$i] = $param[$i];
            $aUri[$j] = $param[$j];

            // assoc
            $aUri += array($param[$i] => $param[$j]); // add if key not exists
        }

        if (isset($sQuery)) {
            $aQuery = array();
            parse_str($sQuery, $aQuery);
            $aUri += $aQuery;
        }

        return $aUri;
    }

    protected function _assemble($asUri)
    {
        if (is_string($asUri)) {
            return $asUri;
        }

        $assoc    = array();
        $numeric  = array();
        $noscalar = array();

        foreach ($asUri as $key => $value) {
            if (!is_scalar($value)) {
                if (is_int($key)) {
                    $noscalar[] = $value;
                }
                else {
                    $noscalar[$key] = $value;
                }
            }
            else if (is_int($key)) {
                $numeric[] = $value;
            }
            else {
                $assoc[] = urlencode($key) . $this->_separator . urldecode($value);
            }
        }

        $uri = implode($this->_separator, $numeric + $assoc);

        if ($noscalar) {
            $uri .= '?' . http_build_query($noscalar);
        }

        return $uri;
    }

}