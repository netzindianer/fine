<?php

class f_timer
{

    protected $_on    = false;
    protected $_start;
    protected $_time  = 0.0;
    protected $_round = 4;
    protected $_hits = 0;

    public static function microtime()
    {
        $aTime = explode(" ", microtime());
        return ((float) $aTime[0] + (float) $aTime[1]);
    }

    public static function _(array $config = array())
    {
        return new self($config);
    }

    public function __construct(array $config = array())
    {
        foreach ($config as $k => $v) {
            $this->{$k}($v);
        }
    }

    public function __toString()
    {
        return (string)$this->get();
    }

    public function round($iRound = null)
    {
        if (func_num_args() == 0) {
            return $this->_round;
        }
        $this->_round = $iRound;
        return $this;
    }

    public function clear()
    {
        $this->_time = 0.0;
        return $this;
    }

    public function start()
    {
        $this->_on = true;
        $this->_hits++;
        $this->_start = self::microtime();
        return $this;
    }

    public function stop()
    {
        $this->_on = false;
        if ($this->_start) {
            $this->_time += self::microtime() - $this->_start;
            $this->_start = null;
        }
        return $this;
    }

    public function get()
    {
        if ($this->_on) {
            return round($this->_time + (self::microtime() - $this->_start), $this->_round);
        }
        else {
            return round($this->_time, $this->_round);
        }
    }

    public function hits()
    {
        return $this->_hits;
    }

}
