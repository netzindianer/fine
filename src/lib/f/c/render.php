<?php

class f_c_render extends f_c
{

    CONST EVENT_RENDER_PRE  = 'render_pre';
    CONST EVENT_RENDER_POST = 'render_post';

    /** 
     * render result kept for next view level
     * @var string
     */
    public $content;

    /**
     * View levels
     *
     * Order matters
     *
     * @var array
     */
    protected $_queue = [
        'view' => ['tpl' => null, 'data' => null],
        'layout' => ['tpl' => null, 'data' => null],
        'head' => ['tpl' => null, 'data' => null],
    ]; 
    
    protected $_loop = false;

    /**
     * Statyczny konstruktor
     *
     * @param array $config
     * @return self
     */
    public static function _(array $config = array())
    {
        return new self($config);
    }

    /**
     * Konstruktor
     *
     * @param array $config
     * @return self
     */
    public function __construct(array $config = array())
    {
        foreach ($config as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function __call($name, $args)
    {
        if (!isset($args[0])) {
            return $this->_queue[$name];
        }
        
        $this->_queue[$name] = ['tpl' => $args[0], 'data' => $args[1]];
        
        return $this;
    }

    /**
     * Renderuje widok i ustawia go jako cialo odpowiedzi
     *
     * @param string $sViewScript
     */
    public function helper($tpl = null, $data = null)
    {
        return $this->render($tpl, $data);
    }

    public function content()
    {
        return $this->content;
    }

    public function render($tpl = null, $data = null)
    {
//        /** @event render_pre */
//        if ($this->event->is(self::EVENT_RENDER_PRE)) {
//            $this->event->run($event = new f_event(array('id' => self::EVENT_RENDER_PRE, 'subject' => $this)));
//            if ($event->cancel()) {
//                return;
//            }
//        }
        
        if ($tpl) {
            $this->unshift($tpl, $data);
        }
        
        if ($this->_loop) {
            return $this->_render($this->shift());

        }
        
        $this->_loop = true;
        
        // render all levels
        while ($level = $this->shift()) {
            $content = $this->_render($level);
            if ($content !== null) {
                $this->content = $content;
            }
        }
        
        $this->_loop = false;
        
        return $this->content;

        // attaches rendered content to response body
//        $this->response->body = $this->content;
        
//        /** @event render_post */
//        if ($this->event->is(self::EVENT_RENDER_POST)) {
//            $this->event->run(new f_event(array('id' => self::EVENT_RENDER_POST, 'subject' => $this)));
//        }

    }
    
    protected function _render($level)
    {
        if (is_object($level['tpl'])) {
            return $level['tpl']->render($level['data']);
        }
        else if (is_string($level['tpl'])){
            return $this->v->renderSandbox($level['tpl'], $level['data']);
        }
        else {
            return null;
        }
    }

    public function queue($queue = null)
    {
        if (func_num_args() == 0) {
            return $this->_queue;;
        }
        
        $this->_queue = $queue;
        
        return $this;
    }
    
    public function push($tpl, $data = null)
    {
        $this->_queue[] = ['tpl' => $tpl, 'data' => $data];
        
        return $this;
    }
    
    public function unshift($tpl, $data = null)
    {
        array_unshift($this->_queue, ['tpl' => $tpl, 'data' => $data]);
        
        return $this;
    }
    
    public function shift()
    {
        return array_shift($this->_queue);
    }

}
