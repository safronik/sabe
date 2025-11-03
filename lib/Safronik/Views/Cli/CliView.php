<?php

namespace Safronik\Views\Cli;

use Safronik\Views\BaseView;
use Safronik\Views\Cli\Decoration\CliDecoration;
use Safronik\Views\Responses\ResponseInterface;

class CliView extends BaseView{

    private string $text_color = "255;255;255";
    private string $back_color = "0;0;0";
    private string $style = "0";

    public function render(): static
    {
        return $this->renderData( $this->data );
    }

    public function renderError( \Exception $exception ): static
    {
        return $this
            ->renderMessage( $exception->getMessage(), 'red' )
            ->setColor('white');
    }

    public function renderMessage( string $message, string $color = 'white', string $suffix = "\n" ): static
    {
        $this->setColor($color);
        echo CliDecoration::decorate( $message, $this->text_color, $this->back_color, $this->style ) . $suffix;
        $this->setColor('white');

        return $this;
    }

    public function renderData(object|array $data, string $prefix = ''): static
    {
        if( is_scalar( $data ) ){
            return $this->renderMessage($data);
        }

        foreach( $data as $key => $datum ){

            $this->renderMessage($prefix . $key . ':', 'green', '');

            if( is_scalar($datum ) ){
                echo $datum . "\n";
                continue;
            }

            echo "\n";
            $this->renderData( $datum, $prefix . "\t" );
        }

        return $this;
    }

    public function setColor(string $color ): self
    {
        $this->text_color = CliDecoration::encodeColor( $color );
        return $this;
    }

    public function setBackColor(string $color ): self
    {
        $this->back_color = CliDecoration::encodeColor( $color );
        return $this;
    }

    public function setStyle(string $style ): self
    {
        $this->style = CliDecoration::encodeStyle( $style );
        return $this;
    }

    public function renderResponse( ResponseInterface $response ): static
    {
        return $this
            ->setMessage( $response->message )
            ->setData( $response->data )
            ->setResponseCode( $response->code )
            ->render();

    }
}