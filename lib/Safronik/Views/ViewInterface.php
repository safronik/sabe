<?php

namespace Safronik\Views;

interface ViewInterface{
    public function render(): self;
    public function renderError( \Exception $exception ): self;
    public function renderMessage( string $message ): self;
    public function renderData( array|object $data ): self;
}