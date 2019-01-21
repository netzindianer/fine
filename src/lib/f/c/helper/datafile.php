<?php

/**
 * Obsługa danych plikowych
 * ========================
 * 
 * - wspolpraca z f_m
 * - skladowanie plikow do folderu z danymi, dzielenie na foldery logiczne i ilosciowe
 * - manipulacje na obrazach - resize, thumb
 * - manipulacje na obrazach data/tmp, data/dvc
 * 
 * IMSS - Image Manipulation Short Syntax
 * --------------------------------------
 * 
 * Format: w[xh][r|t][q85][e0][p__]
 * 
 * w - wartosc, szerokosc obrazu, format [0-9]{1,4}
 * x - stala oddzielajaca szerokosc od wysokosci
 * h - wartosc, wysokosc obrazu, format [0-9]{1,4}, standardowa wartosc rowna szerokosci obrazu
 * t - stala, thumb, standardowa metoda manipulacji
 * r - stala, resize
 * q - parametr, jakosc obrazu, tylko dla jpg, w %
 * e - parametr, extend, wartosc 0 lub 1, tylko dla metody resize
 * p - parametr, position przy crop, tylko dla metody thumb, przykład: pcc = ('position' => 'center center'), obsługiwane wartości horyzontalne: left|center|right, wertykalne: top|center|bottom
 * o - parametr, optimize, wartosc 0 lub 1, optymalizuje rozmiar obrazka bez strat
 *     
 * wartosci w nawiasach kwadratowych opcjonalne
 * 
 */
class f_c_helper_datafile extends f_c
{

    /**
     * main folder with files
     */
    const MAIN_FOLDER = 'data';

    /**
     * public folder with files
     */
    const PUBLIC_FOLDER = 'cdn';

    /**
     * folder with temporary files
     */
    const TMP_FOLDER = 'tmp';

    /**
     * default logical folder
     */
    const DEFAULT_LOGICAL_FOLDER = 'default';

    /**
     * multiple of max id-number in quantity folder
     * if set or diffrent from 0, add quantity folder to path
     * 
     * @var int 
     */
    protected $_divideIntoQuantityFolders = 10000;

    /**
     * create scaled image based on uri path
     * 
     * @param string $sUriPath
     * @param boolean $bSaveFile
     * @return boolean/f_image
     */
    public function createImgSize($sUriPath, $bSaveFile = true)
    {
        list($sTable,,, $sFileName) = $this->extractDataByPath($sUriPath);

        $parts = explode(".", $sFileName);
        $sExt  = end($parts);
        list($id,, $sSize) = explode("_", substr($sFileName, 0, - (strlen($sExt) + 1)), 3);

        if (!ctype_digit($id)) {
            return false;
        }

        $sModel = "m_{$sTable}";
        $model  = new $sModel;
        $model->select($id);

        if ($model->id()) {
            return $this->createImgSizeByModel($model, $sSize, $bSaveFile);
        }

        return false;
    }

    /**
     * create scaled image based on model
     * 
     * @param f_m $model
     * @param string $sSize
     * @param boolean $bSaveFile
     * @return boolean/f_image
     */
    public function createImgSizeByModel(f_m $model, $sSize, $bSaveFile = true)
    {
        if ($model->id()) {
            list($id, $token, $extOrg, $sTable,, $aData) = $this->extractData($model);

            $config = $this->getImgConfig($sTable, $sSize);

            if ($config) {
                $oImg = new f_image();
                $oImg->load($this->getPath($model) . "{$id}_{$token}.{$extOrg}");
                if (isset($aData['imgcrop'][$sSize])) {
                    $crop = $aData['imgcrop'][$sSize];
                    $oImg->cutAndResize($crop['x1'], $crop['y1'], $crop['x2'], $crop['y2'], $config['w'], $config['h']);
                }
                else {
                    $oImg->{$config['type']}($config['w'], $config['h'], ($config['type'] == 'resize' ? $config['extend'] : $config['position']));
                }
                $ext = (isset($config['ext'])) ? $config['ext'] : $extOrg;
                $oImg->type($ext);

                // call method from class
                if (count($config['callback']) > 0) {
                    foreach ($config['callback'] as $callback) {
                        if (is_callable($callback)) {
                            call_user_func($callback, $oImg);
                        }
                    }
                }

                if ($config['quality']) {
                    $oImg->jpgQuality($config['quality']);
                }

                if ($bSaveFile) {
                    $sFilePath = $this->getPath($model, self::PUBLIC_FOLDER);

                    if ($this->setDirectory($sFilePath)) {
                        $oImg->save("{$sFilePath}{$id}_{$token}_{$sSize}.{$ext}");

                        // optymalize files
                        if ($config['optimize']) {
                            if ($oImg->type() == f_image::TYPE_PNG) {
                                exec("optipng " . "{$sFilePath}{$id}_{$token}_{$sSize}.{$ext}");
                            }
                            if ($oImg->type() == f_image::TYPE_JPG) {
                                exec("jpegoptim --strip-all " . "{$sFilePath}{$id}_{$token}_{$sSize}.{$ext}");
                            }
                        }
                    }
                }

                return $oImg;
            }
        }

        return false;
    }

    /**
     * create temporary image
     * 
     * @param string $sPath
     * @param boolean $bSaveFile
     * @return boolean/f_image
     */
    public function createTmpImgSize($sPath, $bSaveFile = true)
    {
        list(,,, $sFileName) = $this->extractDataByPath($sPath);

        list($token, $option, $name) = explode('_', $sFileName, 3);

        foreach ($this->config->data as $sTable => $v) {
            $config = $this->getImgConfig($sTable, $option);

            if ($config) {
                $oImg = new f_image();
                $oImg->load(self::PUBLIC_FOLDER . '/' . self::TMP_FOLDER . "/{$token}__{$name}")
                        ->{$config['type']}($config['w'], $config['h'], ($config['type'] == 'resize' ? !$config['extend'] : $config['position']));

                if ($bSaveFile) {
                    $oImg->save(self::PUBLIC_FOLDER . '/' . self::TMP_FOLDER . "/{$token}_{$option}_{$name}");
                }

                return $oImg;
            }
        }

        return false;
    }

    /**
     * calculate image size
     * 
     * @param f_m $model
     * @param string $size
     * @return boolean/array
     */
    public function calculateImgSize(f_m $model, $sSize)
    {
        $sTable = $model->table();

        $height = null;
        $width  = null;
        if ($model->isField($sTable . '_height') && $model->isField($sTable . '_width')) {
            $height = $model->{$sTable . '_height'};
            $width  = $model->{$sTable . '_width'};
        }
        else {
            $img = f_image::_()->load('.' . $this->uri($model));

            if ($img->resource()) {
                $height = $img->height();
                $width  = $img->width();
            }
        }

        $config = $this->getImgConfig($sTable, $sSize);

        if ($width && $height && $config) {
            $result = $config['type'] == 'thumb' ? $this->_calculateThumb($width, $height, $config['w'], $config['h']) : $this->_calculateResize($width, $height, $config['w'], $config['h'], !$config['extend']);

            return $result;
        }

        return false;
    }

    /**
     * check if it's full or short image configuration
     * and get it
     * 
     * @param string $sTable
     * @param string $sSize
     * @return boolean/ array
     */
    public function getImgConfig($sTable, $sSize)
    {
        if ($this->config->data[$sTable] && $sSize) {
            foreach ($this->config->data[$sTable] as $config) {
                foreach ($config as $size => $value) {
                    if (is_array($value) && $size == $sSize) {
                        return $value;
                    }
                    elseif ($value == $sSize) {
                        return $this->_resolveImgSize($value);
                    }
                }
            }
        }

        return false;
    }

    /**
     * Usuwa plik glowny z dysku z folderu main i w przypadku obrazu wszystkie wygenerowane rozmiary z folderu publicznego
     * 
     * @param f_m $model
     */
    public function destroy(f_m $model)
    {
        // orginal file
        $sFile = '.' . $this->uri($model);
        if (file_exists($sFile)) {
            unlink($sFile);
        }

        // scaled file
        $this->destroyImgsize($model);
    }

    /**
     * Usuwa wszystkie wygenerowane rozmiary z folderu publicznego, nie usuwa pliku glownego
     * 
     * 
     * @param f_m $model
     */
    public function destroyImgsize(f_m $model)
    {
        list($id, $token, $ext, $table) = $this->extractData($model);

        // scaled file
        if ($this->config->data[$table]) {
            $sFilePath = $this->getPath($model, self::PUBLIC_FOLDER);

            foreach ($this->config->data[$table]['imgsize'] as $k => $v) {
                $ext2 = $ext;
                if (is_array($v)) {
                    if ($v['ext']) {
                        $ext2 = $v['ext'];
                    }
                    $v = $k;
                }

                if (file_exists("{$sFilePath}{$id}_{$token}_{$v}.{$ext2}")) {
                    unlink("{$sFilePath}{$id}_{$token}_{$v}.{$ext2}");
                }
            }
        }
    }

    /**
     * store file
     * 
     * @param f_m $model
     * @param string $sSrcFilePath
     * @return boolean
     */
    public function storeFile(f_m $model, $sSrcFilePath)
    {
        // get file
        $resource = file_get_contents($sSrcFilePath);

        if (is_string($resource)) {
            $table = $model->table();

            if (!$model->id()) {
                return false;
            }

            $bFlagSaveModel = false;
            // create file token
            if (!$model->{$table . '_token'}) {
                $model->{$table . '_token'} = $this->token();
                $bFlagSaveModel             = true;
            }
            // get file extension
            if (!$model->{$table . '_ext'}) {
                $ext                      = pathinfo($sSrcFilePath, PATHINFO_EXTENSION);
                $model->{$table . '_ext'} = $ext ? $ext : '';
                $bFlagSaveModel           = true;
            }
            // get file name
            if ($model->isField($table . '_name')) {
                if (!$model->{$table . '_name'}) {
                    $name                      = pathinfo($sSrcFilePath, PATHINFO_BASENAME);
                    $model->{$table . '_name'} = $name ? $name : '';
                    $bFlagSaveModel            = true;
                }
            }
            // get default folder
            if ($model->isField($table . '_folder')) {
                if (!$model->{$table . '_folder'}) {
                    $model->{$table . '_folder'} = self::DEFAULT_LOGICAL_FOLDER;
                    $bFlagSaveModel              = true;
                }
            }

            list($id, $token, $ext) = $this->extractData($model);
            $sFilePath = $this->getPath($model);

            if ($this->setDirectory($sFilePath)) {
                $sFileName = "{$sFilePath}{$id}_{$token}" . ($ext ? '.' . $ext : '');

                // save file
                if (is_int(file_put_contents($sFileName, $resource))) {

                    // get size of upload file
                    if ($model->isField($table . '_size')) {
                        if (!$model->{$table . '_size'}) {
                            $size                      = filesize($sFileName);
                            $model->{$table . '_size'} = $size ? $size : 0;
                            $bFlagSaveModel            = true;
                        }
                    }
                    // save new model's data
                    if ($bFlagSaveModel) {
                        $model->save();
                    }

                    // remove tmp file if exists
                    if (strncmp(pathinfo($sSrcFilePath, PATHINFO_DIRNAME), self::PUBLIC_FOLDER . '/' . self::TMP_FOLDER, strlen(self::PUBLIC_FOLDER . '/' . self::TMP_FOLDER)) == 0 && is_file($sSrcFilePath)) {
                        unlink($sSrcFilePath);
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * store image
     * 
     * @param f_m $model
     * @param f_image $img
     * @return boolean
     */
    public function storeImg(f_m $model, f_image $img)
    {
        if ($img->resource()) {
            $table = $model->table();

            if (!$model->id()) {
                return false;
            }

            $bFlagSaveModel = false;
            if (!$model->{$table . '_token'}) {
                $model->{$table . '_token'} = $this->token();
                $bFlagSaveModel             = true;
            }
            if (!$model->{$table . '_ext'}) {
                $model->{$table . '_ext'} = $img->type();
                $bFlagSaveModel           = true;
            }
            if ($model->isField($table . '_height')) {
                if (!$model->{$table . '_height'}) {
                    $model->{$table . '_height'} = $img->height();
                    $bFlagSaveModel              = true;
                }
            }
            if ($model->isField($table . '_width')) {
                if (!$model->{$table . '_width'}) {
                    $model->{$table . '_width'} = $img->width();
                    $bFlagSaveModel             = true;
                }
            }
            if ($model->isField($table . '_folder')) {
                if (!$model->{$table . '_folder'}) {
                    $model->{$table . '_folder'} = self::DEFAULT_LOGICAL_FOLDER;
                    $bFlagSaveModel              = true;
                }
            }
            if ($bFlagSaveModel) {
                $model->save();
            }

            list($id, $token, $ext) = $this->extractData($model);
            $sFilePath = $this->getPath($model);

            if ($this->setDirectory($sFilePath)) {
                $sImgName = "{$sFilePath}{$id}_{$token}.{$ext}";
                $img->save($sImgName);

                if ($model->isField($table . '_size')) {
                    if (!$model->{$table . '_size'}) {
                        $size = filesize($sImgName);
                        if ($size) {
                            $model->{$table . '_size'} = $size;
                            $model->save();
                        }
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * extract file data
     * 
     * @param f_m/array $aoModel
     * @return array
     */
    public function extractData($aoModel)
    {
        if (is_object($aoModel)) {
            $aData = $aoModel->val();
            $table = $aoModel->table();
        }
        elseif (!empty($aoModel['model'])) {
            $aData = $aoModel;
            $table = $aoModel['model'];
        }

        if (count($aData) > 0 && !empty($table)) {
            return array(
                $aData[$table . '_id'], // id
                $aData[$table . '_token'], // token
                !empty($aData[$table . '_ext']) ? $aData[$table . '_ext'] : '', // extension
                $table, // type
                array_key_exists($table . '_folder', $aData) ? (!empty($aData[$table . '_folder']) ? $aData[$table . '_folder'] : self::DEFAULT_LOGICAL_FOLDER) . '/' : null, // logical folder
                strlen($aData[$table . '_data']) ? json_decode($aData[$table . '_data'], true) : null,
            );
        }

        return false;
    }

    /**
     * extract file data from path
     * 
     * @param string $sPath
     * @return array
     */
    public function extractDataByPath($sPath)
    {
        $aPath  = explode('/', preg_replace('#(^' . substr($this->uriAbs, 0, -1) . '\/)|(^\.?\/?)#', '', $sPath));
        $iCount = count($aPath) - 1;

        $sFileName = $aPath[$iCount];
        unset($aPath[$iCount]);

        list($mainFolder, $table, $folder1, $folder2) = $aPath;

        $logicalFolder  = null;
        $quantityFolder = null;
        if ($this->_divideIntoQuantityFolders && $folder2) {
            $logicalFolder  = $folder1;
            $quantityFolder = $folder2;
        }
        elseif ($this->_divideIntoQuantityFolders && $folder1) {
            $quantityFolder = $folder1;
        }
        elseif ($folder1) {
            $logicalFolder = $folder1;
        }

        return array(
            $table,
            $logicalFolder,
            $quantityFolder,
            $sFileName,
            $mainFolder,
        );
    }

    /**
     * return file path
     * 
     * @param f_m/ array $aoModel
     * @param const $folder
     * @return string ./[data|cdn]/{model}/{logicalFolder}/{quantityFolder}/
     */
    public function getPath($aoModel, $folder = self::MAIN_FOLDER)
    {
        if (is_object($aoModel)) {
            $aData = $aoModel->val();
            $table = $aoModel->table();
        }
        elseif (!empty($aoModel['model'])) {
            $aData = $aoModel;
            $table = $aoModel['model'];
        }

        if (count($aData) > 0 && !empty($table)) {
            $logicalFolder  = array_key_exists($table . '_folder', $aData) ? (!empty($aData[$table . '_folder']) ? $aData[$table . '_folder'] : self::DEFAULT_LOGICAL_FOLDER) . '/' : '';
            $quantityFolder = $this->_getQuantityFolder($aData[$table . '_id']);

            return "./{$folder}/{$table}/" . $logicalFolder . $quantityFolder;
        }

        return false;
    }

    /**
     * return uri path to file
     * 
     * @param f_m/array $aoModel
     * @return string /[data|cdn]/{model}/{logicalFolder}/{quantityFolder}/{id}_{token}_{size}.{ext}
     */
    public function uri($aoModel, $sSize = null)
    {
        list($id, $token, $ext) = $this->extractData($aoModel);
        $sFilePath = $sSize ? $this->getPath($aoModel, self::PUBLIC_FOLDER) : $this->getPath($aoModel);

        return substr($sFilePath, 1)
                . "{$id}_{$token}"
                . ($sSize ? "_{$sSize}" : "")
                . ($ext ? ".{$ext}" : "");
    }

    /**
     * check if given file has allowed extension
     * 
     * @param f_upload/ f_upload_tmp $oResource
     * @param array $aAllowedExt
     * @return boolean
     */
    public function validateFileExt($oResource, $aAllowedExt)
    {
        $ext = $oResource->extensionLower();
        return in_array($ext, $aAllowedExt);
    }

    /**
     * calculate resized image size
     * 
     * @param int $iOldW
     * @param int $iOldH
     * @param int $iNewW
     * @param int $iNewH
     * @param boolean $bExtend
     * @return array
     */
    protected function _calculateResize($iOldW, $iOldH, $iNewW, $iNewH, $bExtend = true)
    {
        if ($bExtend === false) {
            if ($iOldW <= $iNewW && $iOldH <= $iNewH) {
                return array('w' => $iOldW, 'h' => $iOldH);
            }
        }

        $w = (int) $iNewW;
        $h = (int) ($w * $iOldH / $iOldW);

        if ($h > $iNewH) {
            $h = (int) $iNewH;
            $w = (int) ($h * $iOldW / $iOldH);
        }

        return array('w' => $w, 'h' => $h);
    }

    /**
     * calculate thumb image size-name
     * 
     * @param int $iOldW
     * @param int $iOldH
     * @param int $iNewW
     * @param int $iNewH
     * @return array
     */
    protected function _calculateThumb($iOldW, $iOldH, $iNewW, $iNewH)
    {
        $w = (int) $iNewW;
        $h = (int) ($w * $iOldH / $iOldW);

        if ($h <= $iNewH) {
            $h = (int) $iNewH;
            $w = (int) ($h * $iOldW / $iOldH);
            $x = (int) ($w - $iNewW) / 2;
        }
        else {
            $y = (int) ($h - $iNewH) / 2;
        }

        $w -= $x * 2;
        $h -= $y * 2;

        return array('w' => $w, 'h' => $h);
    }

    /**
     * return quntity folder name if it's set
     * 
     * @param int $id - model id
     * @return string
     */
    protected function _getQuantityFolder($id)
    {
        $folder = '';

        if ($this->_divideIntoQuantityFolders) {
            $folder = (floor((int) $id / $this->_divideIntoQuantityFolders) + 1) . '/';
        }

        return $folder;
    }

    /**
     * extract image data from size
     * 
     * @param string $sSize
     * @return array
     */
    protected function _resolveImgSize($sSize)
    {
        $pattern = '/(?P<w>[0-9]{1,4})x?(?P<h>[0-9]{0,4})([rt]?)(q?)(?P<q>[0-9]{0,3})(e?)(?P<e>[01]{0,1})(p?)(?P<p>[lrtbc]{0,2})(?P<o>o?)/';
        preg_match($pattern, $sSize, $matches);

        $aReturn      = array();
        $aReturn['w'] = $matches['w'];
        $aReturn['h'] = $matches['h'];

        if ($aReturn['h'] === '') {
            $aReturn['h'] = $aReturn['w'];

            if (!isset($matches[3]) || $matches[3] === '') {
                $matches[3] = 't';
            }
        }

        if (isset($matches[3]) && ($matches[3] === 'r')) {
            $aReturn['type']   = 'resize';
            $aReturn['extend'] = true;
        }
        else {
            $aReturn['type'] = 'thumb';
        }

        if (isset($matches[4]) && $matches[4] == 'q' && isset($matches['q'])) {
            $aReturn['quality'] = $matches['q'];
        }

        if (isset($matches[6]) && $matches[6] == 'e' && isset($matches['e'])) {
            $aReturn['extend'] = (bool) $matches['e'];
        }

        if (isset($matches[8]) && $matches[8] == 'p' && isset($matches['p'])) {
            switch (substr($matches['p'], 0, 1)) {
                case 'l': $aReturn['position'] = 'left';
                    break;
                case 'r': $aReturn['position'] = 'right';
                    break;
                case 'c': $aReturn['position'] = 'center';
                    break;
                case 't': $aReturn['position'] = 'top';
                    break;
                case 'b': $aReturn['position'] = 'bottom';
                    break;
            }
            switch (substr($matches['p'], 1, 2)) {
                case 't': $aReturn['position'] .= ' top';
                    break;
                case 'b': $aReturn['position'] .= ' bottom';
                    break;
                case 'c': $aReturn['position'] .= ' center';
                    break;
            }
        }

        if (isset($matches[10]) && $matches[10] == 'o' && isset($matches['o'])) {
            $aReturn['optimize'] = true;
        }

        return $aReturn;
    }

}
