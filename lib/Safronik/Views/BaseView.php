<?php

namespace Safronik\Views;

abstract class BaseView implements ViewInterface{

    protected array|object $data = [];
    protected string       $message = '';
    protected int          $response_code = 200;

    public function __construct()
    {
        method_exists($this, 'init' ) && $this->init();
    }

    /**
     * @param mixed $data
     * @return ViewInterface
     */
    public function setData( mixed $data ): ViewInterface
    {
        $this->data = $data;

        return $this;
    }

    public function setMessage( string $message ): ViewInterface
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param mixed $response_code
     * @return ViewInterface
     */
    public function setResponseCode( int $response_code ): ViewInterface
    {
        $this->response_code = $response_code;

        return $this;
    }
}