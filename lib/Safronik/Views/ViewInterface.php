<?php

namespace Safronik\Views;

use Safronik\Views\Responses\ResponseInterface;

interface ViewInterface{

    /**
     * Outputs all the data been prepared earlier
     */
    public function render(): static;

    /**
     * Outputs an error
     */
    public function renderError( \Exception $exception ): static;

    /**
     * Outputs passed message
     */
    public function renderMessage( string $message ): static;

    /**
     * Outputs passed data
     */
    public function renderData( array $data ): static;

    /**
     * Outputs passed data
     */
    public function renderResponse( ResponseInterface $response ): static;
}