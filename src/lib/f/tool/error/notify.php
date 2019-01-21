<?php

class f_tool_error_notify
{
    const TMP_FOLDER = 'tmp';
    
    protected $_email;
    protected $_log;
    protected $_tmp;
    protected $_tmpPath;

    /**
     * Static constructor
     * 
     * @param array $config
     * @return c_tool_error_notify
     */
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
    
    /**
     * Pobiera / ustawia liste emaili, na ktore ma byc wyslana notyfikacja
     * 
     * @param array $emails
     * @return c_tool_error_notify
     */
    public function email(array $emails = null)
    {
        if (func_num_args() == 0) {
            return $this->_email;
        }
        
        $this->_email = $emails;
        return $this;
    }
    
    /**
     * Pobiera / ustawia sciezke do error_log
     *  
     * @param string $sLogPath
     * @return c_tool_error_notify
     */
    public function log($sLogPath = null)
    {
        if (func_num_args() == 0) {
            return $this->_log;
        }
        
        $this->_log = $sLogPath;
        return $this;
    }

    /**
     * Pobiera / ustawia sciezke do plikow tymczasowych z meta data
     * 
     * @param string $sTmpMetaDataPath
     * @return c_tool_error_notify
     */
    public function tmp($sTmpMetaDataPath = null)
    {
        if (func_num_args() == 0) {
            return $this->_tmp;
        }
        
        $this->_tmp = $sTmpMetaDataPath;
        return $this;
    }

    public function handle()
    {
        // email list
        if (!$this->_email) {
            return;
        }
        
        // path to error_log file
        if (!$this->_log) {
            $this->_log = ini_get('error_log');
        }
        if (empty($this->_log)) {
            return;
        }

        // access rights to tmp file with last checked modification time of error_log file
        if (!$this->_tmpData()) {
            return;
        }
        
        // get current modifiaction time of error_log file        
        $timemod = '';
        $file = popen("stat -c %y .." . (substr($this->_log, 0, 1) != '/' ? '/' : '') . $this->_log, "r");
        
        while (!feof($file)) {
            $timemod .= fread($file, 1024);
        }
        pclose($file);
        $timemod = strtotime(reset(explode('.',trim($timemod))));
        
        if (!empty($timemod)) {

            if (file_exists($this->_tmp)) {
                $lastmod = file_get_contents($this->_tmp);
            }
            else {
                mkdir($this->_tmpPath, 0777);
                file_put_contents('.' . $this->_tmp, $timemod);
                chmod($this->_tmp, 0777);
                $lastmod = $timemod - 1;
            }
            
            //last checked modification time is older than current modifiaction time of error_log file
            if ($lastmod && $lastmod < $timemod) {
                $text =  date('Y-m-d H:i:s', $timemod) . '
====================

Error Log
====================

';
                // read last 100 lines from error_log file
                $file = popen("tail -n 100 .." . (substr($this->_log, 0, 1) != '/' ? '/' : '') . $this->_log, "r");
                while (!feof($file)) {
                    $text .= fread($file, 1024);
                }
                pclose($file);
                $text .= '

SERVER
====================
                   
' . f_debug::varDumpPretty($_SERVER);
                    
                // send mails
                foreach ($this->_email as $email) {
                    mail(
                        $email, 
                        '[errornotify] ' . $_SERVER['SERVER_NAME'] . ' ' . date('Y-m-d H:i:s', $timemod),
                        str_replace('\n', '', $text)
                    );
                }

                // save last modification time of error_log file in cache
                file_put_contents($this->_tmp, $timemod);
            }
        }
    }
    
    /**
     * set filepath to tmp file and check access rights
     * 
     * @return bool
     */
    protected function _tmpData()
    {
        if ($this->_tmp) {
            $this->_tmp = '.' . (substr($this->_tmp, 0, 1) != '/' ? '/' : '') . $this->_tmp;
            $parts = explode('/', $this->_tmp);
            unset($parts[count($parts)-1]);
            $this->_tmpPath = implode('/', $parts);
            $bAccessRight = file_exists($this->_tmp) ? is_writable($this->_tmp) : is_writable($this->_tmpPath);
        }
        else {
            $this->_tmpPath = './' . self::TMP_FOLDER . '/error_notify/';
            $this->_tmp = $this->_tmpPath . 'lastchecked';
            $bAccessRight = file_exists($this->_tmp) ? is_writable($this->_tmp) : is_writable('./' . self::TMP_FOLDER);
        }

        return $bAccessRight;
    }
    
}