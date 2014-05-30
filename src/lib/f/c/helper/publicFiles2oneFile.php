<?php

class f_c_helper_publicFiles2oneFile extends f_c
{
    /**
     * main folder with files
     */
    const MAIN_FOLDER = 'public';
    
    /**
     * public folder with files
     */
    const PUBLIC_FOLDER = 'cdn';
    
    /**
     * czy srodowisko deweloperskie
     * 
     * @var boolean 
     */
    protected $_isEnvDev;
 
    /**
     * podfolder zawierajacy plik
     * 
     * @var string 
     */
    protected $_fileFolder;
    
    /**
     * nazwa pliku
     * 
     * @var string 
     */
    protected $_fileName;
    
    /**
     * wzorzec nazwy pliku
     * 
     * @var string 
     */
    protected $_filePattern;

    /**
     * wersja pliku
     * 
     * @var string
     */
    protected $_fileVersion;
    
    /**
     * rozszerzenie pliku
     * 
     * @var string 
     */
    protected $_fileType;

    /**
     * Static construktor
     * 
     * @param array $config
     * @return f_c_helper_publicFiles2oneFile
     */
    public static function _(array $config = array())
    {
        return new self($config);
    }
    
    public function __construct(array $config = array())
    {
        $this->_isEnvDev = $this->env == 'dev';
        
        foreach ($config as $k => $v) {
            $this->{$k}($v);
        }
    }

    /**
     * # CSSI (CSS Implode) - Pakowanie plikow css w jeden i wersjonowanie.
     *
     * ## Wymagania
     *
     *  - zamiesczenie plikow css o rozszerzeniu `css` w folderze /public/css/{folder}
     *  - uzupelnienie danych kongiguracyjnych w `/app/config/public.php['css'][{folder}]`
     *  
     * ### Konfiguracja
     * 
     * - wymagane pola:   `/app/config/public.php['css'][{folder}]['v'] = {integer}` (numer wersji)
     * - opcjonalne pola: `/app/config/public.php['css'][{folder}]['input'] = {array}` (definiowanie sciezki folderu zawierajacego pliki lub sciezki scalanych plikow)
     * - pole `input` zawiera liste tablic z nastepujacymi kluczami:
     *      - 'type' = 'dir|file' (`dir` - scalanie plikow z folderu, `file` - scalanie plikow)
     *      - 'dir' = {string} (sciezka do scalanego folderu; tylko dla 'type'='dir')
     *      - 'ignore' = {string|array} (pojedyncza lub lista sciezek do plikow ignorowanych podczas scalania np. `common*.css`; tylko dla 'type'='dir')
     *      - 'file' = {string|array} (pojedyncza lub lista sciezek plikow do scalenia; tylko dla 'type'='file')
     * - jesli pole `input` nie jest zdefiniowane, to standardowo pakowane sa wszystkie pliki z folderu `/public/css/{folder}`
     * 
     * ## Spakowany plik
     * 
     * adres do spakowanego pliku css to: `/cdn/public/css/{folder}-v{$v}.css`, gdzie `$v` to numer wersji z `/app/config/public.php['css'][{folder}]['v']`
     *  
     * ## Aktualizacja plikow
     *
     * ### Srodowisko deweloperskie
     *
     * Modyfikujemy pliki w folderze. Nie podbijamy wersji cssa. Po uruchomieniu `cdn/public/css/{folder}-v{$v}.css` plik generuje 
     * sie dynamicznie i nie jest cachowany. W pliku sa dodatkowe komentarze informujace o tym, z ktorego pliku dana tresc jest.
     *
     * ### Srodowisko produkcyjne
     *
     * Modyfikujemy pliki w folderze. Podbijamy wersje cssa o jeden. Po uruchomieniu `cdn/public/css/{folder}-v{$v}.css` plik generuje 
     * sie dynamicznie i jest cachowany pod adresem `cdn/public/css/{folder}-v{$v}.css`. Poprzednia wersja cache jest usuwana. Kiedy wywolamy 
     * adres `cdn/public/css/{folder}-v{$v}.css`, gdzie $v jest rozne od wersji wpisanej w konfigu, to nastapi przekierowa na aktualna wersje cssa. 
     * Podbijanie wersji css najlepiej robic przed samym wgrywaniem plikow na serwer produkcyjny.
     *
     */
    public function css()
    {
        $this->fileType('css');
        $this->filePattern('/^[\w\-]*-v[0-9]*\.css$/');
        if ($this->_fileName) {
            $parts = explode('-', $this->_fileName);
            $this->fileVersion(substr(end($parts), 1, -4));
            unset($parts[count($parts)-1]);
            $this->fileFolder(implode('-', $parts));
        }
        $sFile = $this->_implodeFiles();

        // send file to client
        $this->response
            ->header('Content-Type', 'text/css')
            ->body($sFile)
            ->send();
    }

    /**
     * # JSI (JS Implode) - Pakowanie plikow js w jeden i wersjonowanie.
     * 
     * ## Wymagania
     *
     *  - zamiesczenie plikow js o rozszerzeniu `js` w folderze /public/js/{folder}
     *  - uzupelnienie danych kongiguracyjnych w `/app/config/public.php['js'][{folder}]`
     *  
     * ### Konfiguracja
     * 
     * - wymagane pola:   `/app/config/public.php['js'][{folder}]['v'] = {integer}` (numer wersji)
     * - opcjonalne pola: `/app/config/public.php['js'][{folder}]['input'] = {array}` (definiowanie sciezki folderu zawierajacego pliki lub sciezki scalanych plikow)
     * - pole `input` zawiera liste tablic z nastepujacymi kluczami:
     *      - 'type' = 'dir|file' (`dir` - scalanie plikow z folderu, `file` - scalanie plikow)
     *      - 'dir' = {string} (sciezka do scalanego folderu; tylko dla 'type'='dir')
     *      - 'ignore' = {string|array} (pojedyncza lub lista sciezek do plikow ignorowanych podczas scalania np. `common*.js`; tylko dla 'type'='dir')
     *      - 'file' = {string|array} (pojedyncza lub lista sciezek plikow do scalenia; tylko dla 'type'='file')
     * - jesli pole `input` nie jest zdefiniowane, to standardowo pakowane sa wszystkie pliki z folderu `/public/js/{folder}`
     * 
     * ## Spakowany plik
     * 
     * adres do spakowanego pliku css to: `/cdn/public/js/{folder}-v{$v}.js`, gdzie `$v` to numer wersji z `/app/config/public.php['js'][{folder}]['v']`
     * 
     * ## Aktualizacja plikow
     *
     * ### Srodowisko deweloperskie
     *
     * Modyfikujemy pliki w folderze. Nie podbijamy wersji jsa. Po uruchomieniu `cdn/public/js/{folder}-v{$v}.js` plik generuje 
     * sie dynamicznie i nie jest cachowany. W pliku sa dodatkowe komentarze informujace o tym, z ktorego pliku dana tresc jest.
     *
     * ### Srodowisko produkcyjne
     *
     * Modyfikujemy pliki w folderze. Podbijamy wersje jsa o jeden. Po uruchomieniu `cdn/public/js/{folder}-v{$v}.js` plik generuje 
     * sie dynamicznie i jest cachowany pod adresem `cdn/public/js/{folder}-v{$v}.js`. Poprzednia wersja cache jest usuwana. Kiedy wywolamy 
     * adres `cdn/public/js/{folder}-v{$v}.js`, gdzie $v jest rozne od wersji wpisanej w konfigu, to nastapi przekierowa na aktualna wersje jsa. 
     * Podbijanie wersji js najlepiej robic przed samym wgrywaniem plikow na serwer produkcyjny.
     *
     */
    public function js()
    {
        $this->fileType('js');
        $this->filePattern('/^[\w\-]*-v[0-9]*\.js$/');
        if ($this->_fileName) {
            $parts = explode('-', $this->_fileName);
            $this->fileVersion(substr(end($parts), 1, -3));
            unset($parts[count($parts)-1]);
            $this->fileFolder(implode('-', $parts));
        }
        $sFile = $this->_implodeFiles();
        
        // send file to client
        $this->response
            ->header('Content-Type', 'text/javascript; charset=utf-8')
            ->body($sFile)
            ->send();
    }

    /**
     * Ustala/ pobiera podfolder zawierajacy plik
     * 
     * @param string $sFolder
     * @return f_c_helper_publicFiles2oneFile
     */
    public function fileFolder($sFolder = null)
    {
        if ($sFolder === null) {
            return $this->_fileFolder;
        }
        $this->_fileFolder = $sFolder;
        return $this;
    }
    
    /**
     * Ustala/ pobiera nazwe pliku
     * 
     * @param string $sName
     * @return f_c_helper_publicFiles2oneFile
     */
    public function fileName($sName = null)
    {
        if ($sName === null) {
            return $this->_fileName;
        }
        $this->_fileName = $sName;
        return $this;
    }
    
    /**
     * Ustala/ pobiera wzorzec nazwy pliku
     * 
     * @param string $sPattern
     * @return f_c_helper_publicFiles2oneFile
     */
    public function filePattern($sPattern = null)
    {
        if ($sPattern === null) {
            return $this->_filePattern;
        }
        $this->_filePattern = $sPattern;
        return $this;
    }

    /**
     * Ustala/ pobiera rozszerzenie pliku
     * 
     * @param string $sType
     * @return f_c_helper_publicFiles2oneFile
     */
    public function fileType($sType = null)
    {
        if ($sType === null) {
            return $this->_fileType;
        }
        $this->_fileType = $sType;
        return $this;
    }
    
    /**
     * Ustala/ pobiera wersje pliku
     * 
     * @param string $sVersion
     * @return f_c_helper_publicFiles2oneFile
     */
    public function fileVersion($sVersion = null)
    {
        if ($sVersion === null) {
            return $this->_fileVersion;
        }
        $this->_fileVersion = $sVersion;
        return $this;
    }

    protected function _implodeFiles()
    {
        $config = $this->config->public[$this->_fileType][$this->_fileFolder];

        // file and version format ok?
        if (!preg_match($this->_filePattern, $this->_fileName)) {
            $this->notFound();
        }
        
        // is this file under JSI/CSSI system?
        if (!isset($config['v'])) {
            $this->notFound();
        }

        // is this current version? if not, go to current file version
        if ($config['v'] != $this->_fileVersion) {
            $this->redirect->uri(array(self::PUBLIC_FOLDER, self::MAIN_FOLDER, $this->_fileType, 
                $this->_fileFolder . '-v' . $config['v'] . '.' . $this->_fileType));
        }
 
        // default input
        $default = array(
            'type'   => 'dir',
            'param'  => self::MAIN_FOLDER . '/' . $this->_fileType . '/' . $this->_fileFolder,
            'ignore' => false
        );
        
        // output
        $output = '';

        foreach (($config['input'] ? $config['input'] : array($default)) as $input) {
            $type   = $default['type'];
            $param  = $default['param'];
            $ignore = $default['ignore'];

            if ($input['type']) {
                $type = $input['type'];
            }
            if ($input[$type]) {
                $param = $input[$type];
            }
            if ($input['ignore']) {
                $ignore = is_array($input['ignore']) ? $input['ignore'] : array($input['ignore']);
            }
            
            $method = '_read' . ucfirst($type);
            $output .= $this->{$method}($param, $ignore);
        }

        // replace_regexp
        if (isset($config['replace_regexp'])) {
            foreach ($config['replace_regexp'] as $k => $v) {
                $output = preg_replace($k, $v, $output);
            }
        }
        
        // save file
        if (!$this->_isEnvDev) {
            $sFilePath = self::PUBLIC_FOLDER . '/' . self::MAIN_FOLDER . '/' . $this->_fileType . '/' ;
            
            // save cache
            file_put_contents($sFilePath . $this->_fileFolder . '-v' . $this->_fileVersion . '.' . $this->_fileType, $output);
 
            // remove out-of-date cache
            $outofdate = $sFilePath . $this->_fileFolder . '-v' . ($this->_fileVersion - 1) . '.' . $this->_fileType;
            if ($this->_fileVersion > 1 && is_file($outofdate)) {
                unlink($outofdate);
            }
        }
        
        return $output;
    }
    
    protected function _getFileContent($file) 
    {
        $output = '';
        
        if (preg_match($this->_filePattern, basename($file))) {
            return $output;
        }
        
        $parts = explode('.', $file);
        if (end($parts) != $this->_fileType) {
            return $output;
        }

        // add comment
        if ($this->_isEnvDev) {
            $output .= "\n/* {$file} */\n";
        }

        $output .= file_get_contents($file) . "\n";
        
        if ($this->_fileType == 'js') {
            $output .= ';';
        }

        // end adding comment
        if ($this->_isEnvDev) {
            $output .= "\n";
        }
        
        return $output;
    }
    
    protected function _readDir($path, $ignore)
    {
        $output = '';
        
        $path = './' . $path;
        if (is_dir($path)) {
            $dirList = scandir($path);
        }

        if (count($dirList) > 0) {
            foreach ($dirList as $v) {
                
                if ($v == '.' || $v == '..') {
                    continue;
                }
                
                if ($ignore) {
                    $bIgnore = false;
                    foreach ($ignore as $pattern) {
                        if (preg_match('/' . str_replace('\*', '.*', preg_quote($pattern)) . '/', $v)) {
                            $bIgnore = true;
                        }
                    }
                    
                    if ($bIgnore) {
                        continue;
                    }
                }
                
                $filepath = $path . '/' . $v;
                    
                $output .= is_dir($filepath)
                    ? $this->_readFolder($filepath)
                    : $this->_getFileContent($filepath);
            }
        }

        return $output;
    }

    protected function _readFile($file)
    {
        $output = '';
        
        $file = is_array($file) ? $file : array($file);
        
        foreach ($file as $v) {
            $output .= $this->_getFileContent($v);
        }

        return $output;
    }
    
}