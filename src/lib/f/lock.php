<?php

/**
 * # Lock
 *
 * ## Example:
 *  $lock = new f_lock();
 *  $lock->id('./cron.lock');
 *  if ($lock->acquire()) {
 *      // do single instance cron job
 *      $lock->release();
 *  }
 *
 * ## Sis begins:
 *      - when `acquire` method returns true,
 *      - when method `acquire` does not throws f_lock_exception_running (if `throwException` is on).
 * ## Sis ends:
 *      - on demand using method `release`.
 *
 * ## How it works
 * Based on Symfony FlockStore
 * https://github.com/symfony/lock/blob/master/Store/FlockStore.php 
 *
 * ## Example 2 - using exceptions:
 *  try {
 *      f_lock::_()->id('./cron.lock')->throwException(true)->acquire();
 *      // do single instance cron job
 *  }
 *  catch(f_lock_exception_running $e) {
 *  }
 *
 */

class f_lock
{

    protected $_id;
    protected $_throwException = false;
    protected $_handle;

    /**
     * Static constructor
     *
     * @param array $config
     * @return f_lock
     */
    public static function _(array $config = array())
    {
        return new self($config);
    }

    /**
     * Constructor
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
     * Set/get path to file with pid
     *
     * @param string $sPidFile
     * @return f_lock|string
     */
    public function id($sPidFile = null)
    {
        if (func_num_args() == 0) {
            return $this->_id;
        }
        $this->_id = $sPidFile;
        return $this;
    }

    /**
     * Set/get for throw exception when lock is already running
     *
     * @param boolean $bThrowException
     * @return f_lock|boolean
     */
    public function throwExcetpion($bThrowException = null)
    {
        if (func_num_args() == 0) {
            return $this->_throwException;
        }
        $this->_throwException = $bThrowException;
        return $this;
    }

    /**
     * Acquire lock
     *
     * Sis by id passed in `id` method
     *
     * @throws f_lock_exception_notWritable If pid file is not writable
     * @throws f_lock_exception_running If lock already running
     * @return boolean True on success or false on failure
     */
    public function acquire()
    {
        // Silence error reporting
        set_error_handler(function ($type, $msg) use (&$error) { $error = $msg; });
        if (!$handle = fopen($this->_id, 'r+') ?: fopen($this->_id, 'r')) {
            if ($handle = fopen($this->_id, 'x')) {
                chmod($this->_id, 0777);
            } 
            elseif (!$handle = fopen($this->_id, 'r+') ?: fopen($this->_id, 'r')) {
                usleep(100); // Give some time for chmod() to complete
                $handle = fopen($this->_id, 'r+') ?: fopen($this->_id, 'r');
            }
        }
        restore_error_handler();
        if (!$handle) {
            throw new f_lock_exception_notWritable('Direcotry `' . dirname($this->_id) . '` not writable');
        }
        
        // On Windows, even if PHP doc says the contrary, LOCK_NB works, see
        // https://bugs.php.net/54129
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            
            if ($this->_throwException) {
                throw new f_lock_exception_locked("Lock `{$this->_id}` is already set");
            }
            return false;
        }
        
        $this->_handle = $handle;
        return true;
    }
    
    /**
     * Release lock
     */
    public function release()
    {
        if (file_exists($this->_id)) {
            unlink($this->_id);
        }
    }
    
    public function isLocked()
    {
        if (!file_exists($this->_id)) {     
            return false;
        }
        
        $handle = fopen($this->_id, 'r+') ?: fopen($this->_id, 'r');
        if (!$handle) {
            throw new f_lock_exception_notWritable("File `{$this->_id}` not writable");
        }
        
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return true;
        }
        
        return false;
    }
    
}
