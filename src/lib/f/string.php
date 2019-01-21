<?php

class f_string extends \Stringy\Stringy
{

    public function match($pattern)
    {
        return preg_match($pattern, $this->str);
    }

    public function filename()
    {
        return static::create(pathinfo($this->str)['filename'], $this->encoding);
    }

    public function extension()
    {
        return static::create(pathinfo($this->str)['extension'], $this->encoding);
    }

    public function explode($delimiter, $limit = PHP_INT_MAX)
    {
        return explode($delimiter, $this->str, $limit);
    }

    public function escapeshellarg()
    {
        return static::create(escapeshellarg($this->str), $this->encoding);
    }

}
