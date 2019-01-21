<?php

class f_valid_fileImage extends f_valid_abstract
{

    const VALUE_EMPTY         = 'VALUE_EMPTY';
    const NOT_OPENABLE        = 'NOT_OPENABLE';
    const MIN_WIDTH_EXCEEDED  = 'MIN_WIDTH_EXCEEDED';
    const MIN_HEIGHT_EXCEEDED = 'MIN_HEIGHT_EXCEEDED';
    const MAX_WIDTH_EXCEEDED  = 'MAX_WIDTH_EXCEEDED';
    const MAX_HEIGHT_EXCEEDED = 'MAX_HEIGHT_EXCEEDED';

    protected $_minWidth  = 0;
    protected $_maxWidth  = 99999999;
    protected $_minHeight = 0;
    protected $_maxHeight = 99999999;
    protected $_msg = array(
        self::VALUE_EMPTY  => 'Brak pliku',
        self::NOT_OPENABLE => 'Nie można otworzyć pliku jako obraz',
        self::MAX_WIDTH_EXCEEDED  => 'Szerokość obrazka jest zbyt duża (Maksimum to {MAX_WIDTH}px)',
        self::MIN_WIDTH_EXCEEDED  => 'Szerokość obrazka jest zbyt mała (Minimum to {MIN_WIDTH}px)',
        self::MAX_HEIGHT_EXCEEDED => 'Wysokość obrazka jest zbyt duża (Maksimum to {MAX_HEIGHT}px)',
        self::MIN_HEIGHT_EXCEEDED => 'Wysokość obrazka jest zbyt mała (Minimum to {MIN_HEIGHT}px)',
    );
    protected $_var = array(
        '{MIN_WIDTH}'  => '_minWidth',
        '{MAX_WIDTH}'  => '_maxWidth',
        '{MIN_HEIGHT}' => '_minHeight',
        '{MAX_HEIGHT}' => '_maxHeight',
    );

    public static function _(array $config = array())
    {
        return new self($config);
    }

    public function minHeight($minHeight = null)
    {
        if ($minHeight === null) {
            return $this->_minHeight;
        }
        $this->_minHeight = $minHeight;
        return $this;
    }

    public function maxHeight($maxHeight = null)
    {
        if ($maxHeight === null) {
            return $this->_maxHeight;
        }
        $this->_maxHeight = $maxHeight;
        return $this;
    }

    public function minWidth($minWidth = null)
    {
        if ($minWidth === null) {
            return $this->_minWidth;
        }
        $this->_minWidth = $minWidth;
        return $this;
    }

    public function maxWidth($maxWidth = null)
    {
        if ($maxWidth === null) {
            return $this->_maxWidth;
        }
        $this->_maxWidth = $maxWidth;
        return $this;
    }

    public function isValid($mValue)
    {
        // $mValue from $_FILES ?
        if (!is_string($mValue) && is_array($mValue) && isset($mValue['tmp_name'])) {
            $mValue = $mValue['tmp_name'];
        }

        $mValue = (string) $mValue;
        $this->_val($mValue);

        if ('' === $mValue) {
            $this->_error(self::VALUE_EMPTY);
            return false;
        }

        $image = f_image::_()->load($mValue);
        if ($image->error()) {
            $this->_error(self::NOT_OPENABLE);
            return false;
        }
        if($image->width() > $this->_maxWidth) {
            $this->_error(self::MAX_WIDTH_EXCEEDED);
            return false;
        }
        if($image->height() > $this->_maxHeight) {
            $this->_error(self::MAX_HEIGHT_EXCEEDED);
            return false;
        }
        if($image->width() < $this->_minWidth) {
            $this->_error(self::MIN_WIDTH_EXCEEDED);
            return false;
        }
        if($image->height() < $this->_minHeight) {
            $this->_error(self::MIN_HEIGHT_EXCEEDED);
            return false;
        }

        return true;
    }

}
