<?php

class f_collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{

    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $_items = [];
    
    /**
     * Create a new collection.
     *
     * @param  mixed  $items
     * @return void
     */
    public function __construct($items = [])
    {
        $this->_items = $items;
    }

    public function __get($property)
    {
        if ($property === 'each') {
            return new f_collection_each($this);
        }

        return null;
    }
    
    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->_items;
    }
    
    public function col($key)
    {
        if (!$this->_items) {
            return null;
        }
        
        $return = array();
        foreach ($this->_items as $k => $item) {
            $return[$k] = $item[$key];
        }
        return $return;
    }
    
    public function cols($key1, $key2)
    {
        if (!$this->_items) {
            return null;
        }
        
        $return = array();
        foreach ($this->_items as $item) {
            $return[$item[$key1]] = $item[$key2];
        }
        return $return;
    }
    
    public function count()
    {
        return count($this->_items);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_items);
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->_items);
    }

    public function offsetGet($key)
    {
        return $this->_items[$key];
    }

    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->_items[] = $value;
        } else {
            $this->_items[$key] = $value;
        }
    }

    public function offsetUnset($key)
    {
        unset($this->_items[$key]);
    }
    
    /**
     * Get and remove the first item from the collection.
     *
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->_items);
    }
    
    /**
     * Push an item onto the end of the collection.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function push($value)
    {
        $this->offsetSet(null, $value);
        return $this;
    }
    
    /**
     * Get and remove an item from the collection.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function pull($key)
    {
        return $this->_items[$key];
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->_items);
    }
    
    /**
     * Push an item onto the beginning of the collection.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function prepend($value)
    {
        array_unshift($this->_items, $value);
        return $this;
    }

    /*
     * JsonSerializable
     */
    
    public function jsonSerialize()
    {
        return $this->_items;
    }

    /**
     * Merge the collection with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function merge($items)
    {
        return new self(array_merge(
            $this->_items, 
            is_array($items) ? $items : $items->all()
        ));
    }

    public function contains($value)
    {
        return in_array($value, $this->_items);
    }

    public function has($key)
    {
        return array_key_exists($key, $this->_items);
    }

    /**
     * Run a map over each of the items.
     *
     * @param  callable  $callback
     * @return static
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->_items);
        $items = array_map($callback, $this->_items, $keys);
        return new static(array_combine($keys, $items));
    }
    
    /**
     * Transform each item in the collection using a callback.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function transform(callable $callback)
    {
        $this->_items = $this->map($callback)->all();
        return $this;
    }
    
    public function unique()
    {
        return new self(array_unique($this->_items));
    }

    public function toJson()
    {
        return json_encode($this);
    }

    public function reject($callback)
    {
        $stay = [];
        foreach ($this->_items as $k => $v) {
            if (!$callback($v, $k)) {
                $stay[$k] = $v;
            }
        }
        return new self($stay);
    }

    public function filter($callback = null)
    {
        if (!$callback) {
            return new self(array_filter($this->_items));
        }

        $stay = [];
        foreach ($this->_items as $k => $v) {
            if ($callback($v, $k)) {
                $stay[$k] = $v;
            }
        }
        return new self($stay);
    }

    public function filterInstanceOf($type)
    {
        $types = !is_array($type) ? [$type] : $type;

        $stay = [];
        foreach ($this->_items as $k => $v) {
            foreach ($types as $type) {
                if ($v instanceof $type) {
                    $stay[$k] = $v;
                    continue;
                }
            }
        }
        return new self($stay);
    }

    public function rejectInstanceOf($type)
    {
        $types = !is_array($type) ? [$type] : $type;

        $stay = [];
        foreach ($this->_items as $k => $v) {
            $reject = false;
            foreach ($types as $type) {
                if ($v instanceof $type) {
                    $reject = true;
                    break;
                }
            }
            if (!$reject) {
                $stay[$k] = $v;
            }
        }
        return new self($stay);
    }

    public function values()
    {
        return new self(array_values($this->_items));
    }

    public function keys()
    {
        return new self(array_keys($this->_items));
    }

    public function each($callback)
    {
        foreach ($this->_items as $k => $v) {
            $callback($v, $k);
        }
        return $this;
    }

    public function implode($glue)
    {
        return implode($glue, $this->_items);
    }

    public function slice($offset, $length = null)
    {
        return new self(array_slice($this->_items, $offset, $length));
    }

    public function toArray()
    {
        return $this->all();
    }

}
