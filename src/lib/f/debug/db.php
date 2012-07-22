<?php

class f_debug_db
{
    
    /**
     * @var f_db_mysql
     */
    protected $_db;
    
    /**
     * @var container
     */
    protected $_c;


    public function __construct(array $config = array())
    {
        $this->_db = $config['_db'];
        $this->_c  = f::$c;
    }
    
    public function __call($name, $arguments)
    {
        
        if (in_array($name, array('query', 'row', 'rows', 'col', 'cols', 'val', 'rowNum', 'rowsNum', 'lastId'))) {
            
            $this->_c->debug->timer();
            $return = call_user_func_array(array($this->_db, $name), $arguments);
            $this->_c->debug->log($name == 'lastId' ? 'SELECT LAST_INSERT_ID()' : $arguments[0], 'DB Query');
            
            $result = $this->_db->result();
            
            if (is_resource($result)) { // select
                
                $iSelected  = $this->_db->countSelected();
                $this->_c->debug->log($iSelected, 'DB Selected');
                
                if ($iSelected) {
                    $num  = in_array($sMethod, array( 'col', 'cols', 'val', 'rowNum', 'rowsNum', 'lastId'));
                    $rows = array();
                    while ($i = ($num ? mysql_fetch_row($result) : mysql_fetch_assoc($result))) {
                            $rows[] = $i;
                    }
                    $this->_c->debug->table($rows, 'DB selected rows');
                }
                
            }
            else if ($result === true) { // update, insert, delete
                $this->_c->debug->log($this->_db->countAffected(), 'DB Affected');
            }
            
            return $return;
            
        }
        
        return call_user_func_array(array($this->_db, $name), $arguments);
        
    }
    
}
