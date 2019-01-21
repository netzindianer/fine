<?php

/**
 * Przetwarzanie obrazkow 
 *
 * Obslugiwane formaty: gif, jpg, png.

 * Obecnie zmiana rozmiaru (metoda `resize`) i zmiana rozmiaru z wycinaniem (metoda `thumb`)
 *
 * Ladowanie pliku odbywa sie wedlug typu zwracanego przez `getimagesize()[2]`.
 *
 */
class f_image
{

    const TYPE_GIF = 'gif';
    const TYPE_JPG = 'jpg';
    const TYPE_PNG = 'png';
    const MIMETYPE_GIF = 'image/gif';
    const MIMETYPE_JPG = 'image/jpeg';
    const MIMETYPE_PNG = 'image/png';
    const ERROR_LOAD_NO_FILE = 'ERROR_LOAD_NO_FILE';
    const ERROR_LOAD_UNSUPPORTED_TYPE = 'ERROR_LOAD_UNSUPPORTED_TYPE';
    const ERROR_LOAD_NOT_LOADED = 'ERROR_LOAD_NOT_LOADED';
    const ERROR_SAVE_NOT_LOADED = 'ERROR_SAVE_NOT_LOADED';
    const ERROR_SAVE_NOT_SAVED = 'ERROR_SAVE_NOT_SAVED';
    const ERROR_SAVE_UNSUPPORTED_TYPE = 'ERROR_SAVE_UNSUPPORTED_TYPE';
    const ERROR_RENDER_NOT_LOADED = 'ERROR_RENDER_NOT_LOADED';
    const ERROR_RESIZE_NOT_LOADED = 'ERROR_RESIZE_NOT_LOADED';
    const ERROR_RESIZE = 'ERROR_RESIZE';
    const ERROR_THUMB_NOT_LOADED = 'ERROR_THUMB_NOT_LOADED';
    const ERROR_THUMB = 'ERROR_THUMB';

    protected $_resource;
    protected $_file;
    protected $_typeLoaded;
    protected $_type;
    protected $_transparency;
    protected $_error = array();
    protected $_jpgQuality = 90;
    protected $_renderSendsHeader = true;

    /* Object managment */

    /**
     * Statyczny konstruktor
     *
     * @param array $config
     * @return f_image
     */
    public static function _(array $config = array())
    {
        return new self($config);
    }

    /**
     * Konstruktor
     * 
     * @param array $config 
     */
    public function __construct(array $config = array())
    {
        foreach ($config as $k => $v) {
            $this->{$k}($v);
        }
    }

    /**
     * Destruktor
     *
     * Niszczy zasob obrazu, zwalnia pamiec
     */
    public function __destruct()
    {
        $this->destroy();
    }

    /**
     * Tworzy kopie 
     *
     * @return f_image Nowy obiekty z kopia zasobu
     */
    public function copy()
    {
        $copy = $this->_createResoruce($this->width(), $this->height());
        imagecopy($copy, $this->resource(), 0, 0, 0, 0, $this->width(), $this->height());

        $image = new f_image();
        $image->resource($copy);
        $image->file($this->file());

        return $image;
    }

    /* Image properties */

    /**
     * Ustala/pobiera sciezke pliku obrazka
     *
     * @param type $sFile Sciezka do pliku
     * @return f_image
     */
    public function file($sFile = null)
    {
        // getter
        if (func_num_args() == 0) {
            return $this->_file;
        }

        // setter
        $this->_file = $sFile;

        return $this;
    }

    /**
     * Ustala/pobiera typ pliku
     *
     * @param const $tType Jedna ze stalych self::TYPE_*
     * @return string Jedna ze stalych self::TYPE_*
     * 
     */
    public function type($tType = null)
    {
        if (func_num_args() == 0) {
            return strlen($this->_type) > 0 ? $this->_type : $this->_typeLoaded;
        }

        $this->_type = $tType;

        return $this;
    }

    /**
     * Ustala/pobiera zachowanie przezroczystosci
     * 
     * @param boolean $bTransparency
     * @return boolean|f_image
     */
    public function transparency($bTransparency = null)
    {
        if (func_num_args() == 0) {
            return $this->_transparency;
        }

        $this->_transparency = $bTransparency;

        return $this;
    }

    /**
     * Zwraca szerokosc obrazu
     *
     * @return int
     */
    public function width()
    {
        return imagesx($this->_resource);
    }

    /**
     * Zwraca wysokosc obrazu
     *
     * @return type
     */
    public function height()
    {
        return imagesy($this->_resource);
    }

    /**
     * Ustala/pobiera jakosc obrazu dla formatu jpg
     *
     * @param int $iJpgQuality Jakosc jpg <0,100>
     * @return int|this
     */
    public function jpgQuality($iJpgQuality = null)
    {
        if (func_num_args() == 0) {
            return $this->_jpgQuality;
        }

        $this->_jpgQuality = $iJpgQuality;

        return $this;
    }

    /* Image I/O - load, save, render */

    /**
     * Konwertuje rozszerzenie pliku do typu obrazu
     *
     * @param string $sFileExtension
     * @param const $tDefaultType jest wykorzystywany jezeli nie uda sie ustalic typu (self::TYPE_* )
     * @return const self::TYPE_*
     */
    public function extension2Type($sFileExtension, $tDefaultType = null)
    {
        switch ($sFileExtension) {

            case 'gif':
                return self::TYPE_GIF;

            case 'jpg':
            case 'jpeg':
            case 'jpe':
            case 'jif':
            case 'jfif':
            case 'jfi':
                return self::TYPE_JPG;

            case 'png':
                return self::TYPE_PNG;

            default:
                return $tDefaultType;
        }
    }

    /**
     * Laduje obraz z pliku
     *
     * @param string $sFile Plik
     * @return $this
     */
    function load($sFile = null)
    {
        $this->_error = array();

        if (func_num_args() == 1) {
            $this->file($sFile);
        }

        if (!is_file($this->_file)) {
            $this->_error(self::ERROR_LOAD_NO_FILE);
            return $this;
        }

        list(,, $type) = getimagesize($this->_file);

        switch ($type) {

            case IMAGETYPE_GIF:
                $this->_resource = imagecreatefromgif($this->_file);
                $this->_typeLoaded = self::TYPE_GIF;
                break;

            case IMAGETYPE_JPEG:
                $this->_resource = imagecreatefromjpeg($this->_file);
                $this->_typeLoaded = self::TYPE_JPG;
                break;

            case IMAGETYPE_PNG:
                $this->_resource = imagecreatefrompng($this->_file);
                $this->_typeLoaded = self::TYPE_PNG;
                break;

            default:
                $this->_error(self::ERROR_LOAD_UNSUPPORTED_TYPE);
                return $this;
        }

        if (!$this->_resource) {
            $this->_typeLoaded = null;
            $this->_error(self::ERROR_LOAD_NOT_LOADED);
        }

        return $this;
    }

    /**
     * Zapisuje obraz jako plik
     *
     * @param string|null $sFile Plik
     * @param integer $iJpgQuality
     * @return $this
     */
    function save($sFile = null)
    {

        // is resource loaded?
        if (!$this->_resource) {
            $this->_error(self::ERROR_SAVE_NOT_LOADED);
            return $this;
        }

        // file
        if ($sFile !== null) {
            $this->file($sFile);
        }

        $status = null;

        // transparency
        $this->_setTransparency();

        switch ($this->_resolveType()) {

            case self::TYPE_GIF:
                $status = imagegif($this->_resource, $this->_file);
                break;

            case self::TYPE_JPG:
                $status = imagejpeg($this->_resource, $this->_file, $this->_jpgQuality);
                break;

            case self::TYPE_PNG:
                $status = imagepng($this->_resource, $this->_file);
                break;

            default:
                $this->_error(self::ERROR_SAVE_UNSUPPORTED_TYPE);
                return $this;
        }

        if ($status === false) {
            $this->_error(self::ERROR_SAVE_NOT_SAVED);
        }

        return $this;
    }

    /**
     * Ustala/pobiera czy render* ma wysylac header `Content-Type`
     *
     * @param boolean $bRenderSendsHeader
     * @return this
     */
    public function renderSendsHeader($bRenderSendsHeader = null)
    {
        if (func_num_args() == 0) {
            return $this->_renderSendsHeader;
        }

        $this->_renderSendsHeader = $bRenderSendsHeader;
        return $this;
    }

    public function render($tType = null)
    {
        // is resource loaded?
        if (!$this->_resource) {
            $this->_error(self::ERROR_RENDER_NOT_LOADED);
            return $this;
        }

        // type
        $type = $this->_resolveType($tType);

        // header Content-Type
        if ($this->_renderSendsHeader) {
            switch ($type) {
                case self::TYPE_GIF:
                    $mime = self::MIMETYPE_GIF;
                    break;
                case self::TYPE_JPG:
                    $mime = self::MIMETYPE_JPG;
                    break;
                case self::TYPE_PNG:
                    $mime = self::MIMETYPE_PNG;
                    break;
            }
            header('Content-Type: ' . $mime);
        }

        // transparency
        $this->_setTransparency();

        // render
        switch ($type) {

            case self::TYPE_GIF:
                imagegif($this->_resource);
                break;

            case self::TYPE_JPG:
                imagejpeg($this->_resource, null, $this->_jpgQuality);
                break;

            case self::TYPE_PNG:
                imagepng($this->_resource);
                break;
        }

        return $this;
    }

    public function renderGif()
    {
        return $this->render(self::TYPE_GIF);
    }

    public function renderJpg()
    {
        return $this->render(self::TYPE_JPG);
    }

    public function renderPng()
    {
        return $this->render(self::TYPE_PNG);
    }

    /**
     * Ustala/pobiera zasob obrazu
     *
     * @param resource $rImageResourceIdentifier
     * @return $this|resource
     */
    public function resource($rImageResourceIdentifier = null)
    {
        if (func_num_args() == 0) {
            return $this->_resource;
        }

        $this->_error = array();
        $this->_resource = $rImageResourceIdentifier;

        return $this;
    }

    /**
     * Niszczy zasób obrazu, zwalnia pamięć
     *
     * @return $this
     */
    public function destroy()
    {
        if (!$this->_resource) {
            return $this;
        }

        imagedestroy($this->_resource);
        $this->_resource = null;
        $this->_error = array();

        return $this;
    }

    /* Errors */

    /**
     * Pobiera tablice napotkanych błędów
     *
     * @return array
     */
    public function error()
    {
        return $this->_error;
    }

    /* Image processing */

    function resize($iNewWidth, $iNewHeight, $bExtend = true)
    {
        if (!$this->_resource) {
            $this->_error(self::ERROR_RESIZE_NOT_LOADED);
            return $this;
        }

        if ($iNewWidth <= 0) {
            throw new f_image_exception_invalidArgument("Szerokosc nowego obrazu musi byc wieksza od zera.");
        }

        if ($iNewHeight <= 0) {
            throw new f_image_exception_invalidArgument("Wysokosc nowego obrazu musi byc wieksza od zera.");
        }

        if ($bExtend === false) {
            if ($this->width() <= $iNewWidth && $this->height() <= $iNewHeight) {
                return $this;
            }
        }

        $iWidth = $iNewWidth;
        $iHeight = (int) ($iWidth * $this->height() / $this->width());
        if ($iHeight > $iNewHeight) {
            $iHeight = $iNewHeight;
            $iWidth = (int) ($iHeight * $this->width() / $this->height());
        }

        if (!$rImage = $this->_createResoruce($iWidth, $iHeight)) {
            $this->_error(self::ERROR_RESIZE);
            return $this;
        }

        if (!imagecopyresampled($rImage, $this->_resource, 0, 0, 0, 0, $iWidth, $iHeight, $this->width(), $this->height())) {
            $this->_error(self::ERROR_RESIZE);
            return $this;
        }
        $this->_resource = $rImage;

        return $this;
    }

    /**
     * $sPosition - set textual values as for CSS:background-position
     * 
     * @param int $iNewWidth
     * @param int $iNewHeight
     * @param string $sPosition
     * @return f_image
     * @throws f_image_exception_invalidArgument
     */
    function thumb($iNewWidth, $iNewHeight = null, $sPosition = 'center')
    {
        if (!$this->_resource) {
            $this->_error(self::ERROR_THUMB_NOT_LOADED);
            return $this;
        }

        if ($iNewHeight === null) {
            $iNewHeight = $iNewWidth;
        }

        if ($iNewWidth <= 0) {
            throw new f_image_exception_invalidArgument("Szerokosc nowego obrazu musi byc wieksza od zera.");
        }

        if ($iNewHeight <= 0) {
            throw new f_image_exception_invalidArgument("Wysokosc nowego obrazu musi byc wieksza od zera.");
        }

        // set new width and height
        $iWidth = $iNewWidth;
        $iHeight = (int) ($iWidth * $this->height() / $this->width());
        if ($iHeight <= $iNewHeight) {
            $iHeight = $iNewHeight;
            $iWidth = (int) ($iHeight * $this->width() / $this->height());
        }

        list($sX, $sY) = explode(' ', $sPosition);

        // set x- and y-coordinates
        $iX = -1;
        $iY = -1;

        // percentage support -------------------------------------------
        $percentX = 0;
        $percentY = 0;
        $percent = substr_count($sPosition, '%');
        if ($percent > 1) { // if both percents
            $percentX = (int) trim(str_replace('%', '', $sX));
            $sX = 'percent';
            $percentY = (int) trim(str_replace('%', '', $sY));
            $sY = 'percent';
        } else if ($percent == 1) { // if one percent
            if (!$sY) { // if only one percent, for both axis
                $percentX = $percentY = (int) trim(str_replace('%', '', $sX));
                $sX = $sY = 'percent';
            } else if (strstr($sX, '%')) { // if first has percent
                $percentX = (int) trim(str_replace('%', '', $sX));
                $sX = 'percent';
            } else if (strstr($sY, '%')) { // if second has percent
                $percentY = (int) trim(str_replace('%', '', $sY));
                $sY = 'percent';
            }
        }
        $moveX = 0;
        $moveY = 0;
        if ($sX == 'percent') {
            $moveX = (int) ($percentX * $iNewWidth) / 100;
            if ($iNewWidth + $moveX > $iWidth) { // if too much move x
                $sX = 'right';
            }
        }
        if ($sY == 'percent') {
            $moveY = (int) ($percentY * $iNewHeight) / 100;
            if ($iNewHeight + $moveY > $iHeight) { // if too much move y
                $sY = 'bottom';
            }
        }
        // --------------------------------------------------------------

        switch ($sX) {
            case 'left';
                $iX = 0;
                break;
            case 'right':
                $iX = (int) ($iWidth - $iNewWidth);
                break;
            case 'center':
                $iX = (int) ($iWidth - $iNewWidth) / 2;
                break;
            case 'top':
                $iY = 0;
                break;
            case 'bottom':
                $iY = (int) ($iHeight - $iNewHeight);
                break;
            case 'percent':
                $iX = $moveX;
                break;
        }
        switch ($sY) {
            case 'top':
                $iY = 0;
                break;
            case 'bottom':
                $iY = (int) ($iHeight - $iNewHeight);
                break;
            case 'percent':
                $iY = $moveY;
                break;
            default: // if specify one keyword, other is 'center'
                if ($iX == -1) {
                    $iX = (int) ($iWidth - $iNewWidth) / 2;
                }
                if ($iY == -1) {
                    $iY = (int) ($iHeight - $iNewHeight) / 2;
                }
                break;
        }

        if (!$rImage = $this->_createResoruce($iWidth, $iHeight)) {
            $this->_error(self::ERROR_THUMB);
            return $this;
        }

        if (!imagecopyresampled($rImage, $this->_resource, 0, 0, 0, 0, $iWidth, $iHeight, $this->width(), $this->height())) {
            $this->_error(self::ERROR_THUMB);
            return $this;
        }

        if (!$rImage2 = $this->_createResoruce($iNewWidth, $iNewHeight)) {
            $this->_error(self::ERROR_THUMB);
            return $this;
        }

        if (!imagecopy($rImage2, $rImage, 0, 0, $iX, $iY, $iNewWidth, $iNewHeight)) {
            $this->_error(self::ERROR_THUMB);
            return $this;
        }
        $this->_resource = $rImage2;

        return $this;
    }

    function cutAndResize($x1, $y1, $x2, $y2, $iNewWidth, $iNewHeight)
    {
        if ($iNewWidth <= 0 || $iNewHeight <= 0) {
            $this->_error(self::ERROR_THUMB);
            return $this;
        }
        if (!$rImage = $this->_createResoruce($iNewWidth, $iNewHeight)) {
            $this->_error(self::ERROR_THUMB);
            return $this;
        }
        if (!imagecopyresampled($rImage, $this->_resource, 0, 0, $x1, $y1, $iNewWidth, $iNewHeight, $x2 - $x1, $y2 - $y1)) {
            $this->_error(self::ERROR_THUMB);
            return $this;
        }

        $this->_width = $iNewWidth;
        $this->_height = $iNewHeight;
        $this->_resource = $rImage;

        return $this;
    }

    /* Private api */

    protected function _error($tError)
    {
        $this->_error[] = $tError;
    }

    protected function _resolveType($tDefaultType = null)
    {

        // 1. Podany przez metode `type`
        if ($this->_type !== null) {
            return $this->_type;
        }

        // 2. Wedlug rozszerzenia
        $parts = explode('.', $this->_file);
        $type = $this->extension2Type(strtolower(end($parts))); // wedlug rozszerzenia
        if ($type !== null) {
            return $type;
        }

        // 3. Wedlug typu zaladowanego pliku
        if ($this->_typeLoaded !== null) {
            return $this->_typeLoaded;
        }

        // 4. Standardowy z argumentu
        if ($tDefaultType !== null) {
            return $tDefaultType;
        }

        // 5. Super standardowy np. jezeli zaladujemy obraz przez `resource` nie podajac `type`
        return self::TYPE_JPG;
    }

    protected function _resolveTransparency($tDefaultTransparency = null)
    {
        // 1. Podany przez metode `transparency`
        if ($this->_transparency !== null) {
            return $this->_transparency;
        }

        // 2. Standardowy z argumentu
        if ($tDefaultTransparency !== null) {
            return $tDefaultTransparency;
        }

        // 3. Wedlug typu
        $type = $this->_resolveType();
        if ($type && ($type == self::TYPE_GIF || $type == self::TYPE_PNG)) {
            return true;
        }

        // 4. Super standardowy np. jezeli zaladujemy obraz przez `resource` nie podajac `transparency`
        return false;
    }

    protected function _setTransparency()
    {
        $transparency = $this->_resolveTransparency();

        imagealphablending($this->_resource, !$transparency);
        imagesavealpha($this->_resource, $transparency);
    }

    protected function _createResoruce($iWidth, $iHeight)
    {
        if (!$rImage = imagecreatetruecolor($iWidth, $iHeight)) {
            return false;
        }

        if ($this->_resolveTransparency()) {
            $transparencyIndex = imagecolortransparent($this->_resource);
            $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);

            if ($transparencyIndex >= 0 && $transparencyIndex < imagecolorstotal($this->_resource)) {
                $transparencyColor = imagecolorsforindex($this->_resource, $transparencyIndex);
            }

            $transparency = imagecolorallocate($rImage, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
            imagealphablending($rImage, false);
            imagefilledrectangle($rImage, 0, 0, $iWidth, $iHeight, $transparency);
            imagecolortransparent($rImage, $transparency);
            imagesavealpha($rImage, true);
        }

        return $rImage;
    }

}
