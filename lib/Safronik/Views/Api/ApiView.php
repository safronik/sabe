<?php

namespace Safronik\Views\Api;

use Safronik\CodePatterns\Exceptions\ContainerException;
use Safronik\CodePatterns\Structural\DI;
use Safronik\Controllers\Exceptions\ControllerException;
use Safronik\Globals\Header;
use Safronik\Views\BaseView;
use Safronik\Views\JsonView;
use Safronik\Views\ViewInterface;
use Safronik\Views\XmlView;

class ApiView extends BaseView{
    
    private ViewInterface $view;

    /**
     * @throws ContainerException
     */
    public function init(): void
    {
        $this->view = DI::get($this->detectViewType());
    }

    private function detectViewType(): string
    {
        return match( true ){
            str_contains( Header::get('accept'), 'application/json') => JsonView::class,
            str_contains( Header::get('accept'), 'application/xml')  => XmlView::class,
            default => JsonView::class,
        };
    }

    public function render(): ViewInterface
    {
        $this->view->render();
        http_response_code( $this->response_code );

        return $this;
    }

    public function renderError(\Exception $exception ): ViewInterface
    {
        $response = new ApiResponse();
        $response->setError( true);
        $response->setMessage( $exception->getMessage() );
        $exception instanceof ControllerException
            && $response->setM2mMessage( $exception->getM2mMessage() );
        
        return $this->view
            ->setData($response)
            ->setResponseCode( $exception->getCode() )
            ->render();
    }
    
    public function renderMessage( string $message ): ViewInterface
    {
        $response = new ApiResponse();
        $response->setMessage( $message );

        $this
            ->setData($response)
            ->setResponseCode($this->response_code)
            ->render();

        return $this;
    }

    public function renderData(object|array $data): ViewInterface
    {
        $response = new ApiResponse();
        $response->setData( $data );

        return $this->view
            ->setData( $response )
            ->setResponseCode($this->response_code)
            ->render();
    }

    /**
     * @param mixed $data
     * @return ViewInterface
     */
    public function setData( mixed $data ): ViewInterface
    {
        $this->view->data = $data;

        return $this;
    }

    public function setMessage( string $message ): ViewInterface
    {
        $this->view->message = $message;

        return $this;
    }

    /**
     * @param mixed $response_code
     * @return ViewInterface
     */
    public function setResponseCode( int $response_code ): ViewInterface
    {
        $this->view->response_code = $response_code;

        return $this;
    }
}