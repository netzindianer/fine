<?php

class f_c_helper_readfile extends f_c implements f_di_asNew_interface
{

    protected $_request;
    protected $_response;
    protected $_file;
    protected $_name;
    protected $_mimeType;
    protected $_bandwidth;
    
    /**
     * Static constructor
     * 
     * @param array $config
     * @return f_c_helper_readfile
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
     * get/ set request
     * 
     * @param f_c_request $request
     * @return f_c_helper_readfile
     */
    public function request(f_c_request $request = null)
    {
        if ($request === null) {
            return $this->_request;
        }
        $this->_request = $request;
        return $this;
    }
    
    /**
     * get/ set response
     * 
     * @param f_c_response $response
     * @return f_c_helper_readfile
     */
    public function response(f_c_response $response = null)
    {
        if ($response === null) {
            return $this->_response;
        }
        $this->_response = $response;
        return $this;
    }
    
    /**
     * get/ set path to file to download
     * 
     * @param string $path
     * @return f_c_helper_readfile
     */
    public function file($path = null)
    {
        if ($path === null) {
            return $this->_file;
        }
        $this->_file = $path;
        return $this;
    }
    
    /**
     * get/ set name of file to download
     * 
     * @param string $name
     * @return f_c_helper_readfile
     */
    public function name($name = null)
    {
        if ($name === null) {
            return $this->_name;
        }
        $this->_name = $name;
        return $this;
    }
    
    /**
     * get/ set MIME type of file to download (optional)  
     * 
     * @param string $mimeType
     * @return f_c_helper_readfile
     */
    public function mimeType($mimeType = null)
    {
        if ($mimeType === null) {
            return $this->_mimeType;
        }
        $this->_mimeType = $mimeType;
        return $this;
    }
    
    /**
     * get/ set rate of data transfer measured in bytes per second (optional)
     * 
     * @param float $bytes
     * @return f_c_helper_readfile
     */
    public function bandwidth($bytes)
    {
        if ($bytes === null) {
            return $this->_bandwidth;
        }
        $this->_bandwidth = $bytes;
        return $this;
    }
    
    /**
     * Resumable download server
     * supports partial file request (only single range)
     * 
     * @return boolean
     */
    public function handle()
    {
        if (!file_exists($this->_file)) {
            return false;
        }
        
        $handle = @fopen($this->_file, 'rb');
        if (!$handle) {
            return false;
        }
        
        $size = filesize($this->_file); // file size
        $length = $size; // content length
        $start = 0; // start byte
        $end = $size - 1; // end byte
        
        $this->_response
            ->header('Content-Disposition', 'attachment; filename=' . $this->_name)
            ->header('Content-Transfer-Encoding', 'binary')
            ->header('Accept-Ranges', '0-' . $length);
        
        if ($this->_mimeType) {
            $this->_response->header('Content-type', $this->_mimeType);
        }
        
        if ($this->_request->server('HTTP_RANGE')) {
            $tmpStart = $start;
            $tmpEnd = $end;
            
            // extract the range string
            list(, $range) = explode('=', $this->_request->server('HTTP_RANGE'), 2);
            
            // make sure client hasn't sent a multibyte range
            if (strpos($range, ',') !== false) {
                $this->_response
                    ->code(416)
                    ->header('Content-Range', "bytes {$start}-{$end}/{$size}")
                    ->send();
                    
                return false;        
            }
            
            // the range starts with an '-' so start from the beginning of file
            if ($range == '-') {
                // the n-number of the last bytes is requested
                $tmpStart = $size - substr($range, 1);
            }
            // forward the file pointer and get end bytes if specified
            else {
                $range = explode('-', $range);
                $tmpStart = $range[0];
                $tmpEnd = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }
            // end bytes can't be larger than the last byte of file
            $tmpEnd = ($tmpEnd > $end) ? $end : $tmpEnd;
            
            // validate the requested range 
            if ($tmpStart > $tmpEnd || $tmpStart > $size - 1 || $tmpEnd >= $size) {
                $this->_response
                    ->code(416)
                    ->header('Content-Range', "bytes {$start}-{$end}/{$size}")
                    ->send();
                
                return false;
            }
            
            $start = $tmpStart;
            $end = $tmpEnd;
            $length = $end - $start + 1;
            
            fseek($handle, $start);
            $this->_response->code(206);
        }
        
        $this->_response
            ->header('Content-Range', "bytes {$start}-{$end}/{$size}")
            ->header('Content-Length', $length)
            ->sendHeader();

        // start buffered download
        $buffer = 1024 * 8;
        $sleepSize = 0;
        while(!feof($handle) && ($pos = ftell($handle)) <= $end) {
                        
            if ($this->_bandwidth) {
                $sleepSize += $buffer;
                if ($sleepSize >= $this->_bandwidth) {
                    $sleepSize = 0;
                    sleep(1);
                }
            }
            
            if ($pos + $buffer > $end) {
                $buffer = $end - $pos + 1;
            }

            echo fread($handle, $buffer);
            flush();
        }

        fclose($handle);
        
        return true;
    }
    
}