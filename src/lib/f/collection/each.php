<?php

class f_collection_each
{

    protected $collection;
    /**
     * Create a new collection.
     *
     * @param  mixed  $items
     * @return void
     */
    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function __call($method, $args)
    {
        $this->collection->each(function($item) use ($method, $args) {
            call_user_func_array([$item, $method], $args);
        });

        return $collection;
    }
    
}
