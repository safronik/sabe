<?php

namespace Safronik\Services\DB\Extensions;

trait PreparedQueryExtension
{
    private array $named_placeholders   = [];
    private array $unnamed_placeholders = [];
    
    /**
     * Safely replace placeholders in any part of query
     *
     * Doesn't create a prepared statement.
     * Look for $this->prepare if you want to make multiple similar queries.
     *
     * @param array       $values
     * @param string|null $query
     *
     * @return static
     */
	public function prepare( array $values, string $query = null ): static
    {
        $this->query                = $query ?? $this->query;
        $values                     = $this->parseValues( $values );
        $this->named_placeholders   = $this->getNamedPlaceholders( $values );
        $this->unnamed_placeholders = $this->getUnnamedPlaceholders( $values );
        
        $this->named_placeholders   && $this->preparePlaceholders( $this->named_placeholders,   'named' );
        $this->unnamed_placeholders && $this->preparePlaceholders( $this->unnamed_placeholders, 'unnamed' );
        
        return $this;
	}
    
    private function parseValues( $values )
    {
        array_walk($values, static function( &$value, $key ){ $value = ! is_array( $value ) ? [ $value ] : $value; } );
        
        return $values;
    }
    
    private function getNamedPlaceholders( array $placeholders ): array
    {
        return array_filter(
            $placeholders,
            static function( $placeholder ){
                return count( $placeholder ) === 3 || preg_match( '@^:[a-z]@', $placeholder[0] );
            }
        );
    }
    
    private function getUnnamedPlaceholders( array $placeholders ): array
    {
        return array_filter(
            $placeholders,
            static function( $placeholder ){
                return count( $placeholder ) === 1 || ! preg_match( '@^:[a-z]@', $placeholder[0] );
            }
        );
    }
    
    /**
     * Bind values to prepared query
     * Supports named(:named) and unnamed(?) placeholders
     *
     * @param array  $values
     * @param string $placeholders_type
     *
     * @return static
     */
    public function preparePlaceholders( array $values = array(), string $placeholders_type = 'unnamed' ): static
    {
        if( $placeholders_type === 'named'){
            foreach( $values as $value_data ){
                $this->preparePlaceholder(
                    $value_data[1],
                    $value_data[2] ?? 'string',
                    $value_data[0]
                );
            }
        }
        
        if( $placeholders_type === 'unnamed'){
            for($i = 0, $value_size = count($values); $i < $value_size; $i++){
                $this->preparePlaceholder(
                    $values[ $i ][0],
                    $values[ $i ][1] ?? 'string'
                );
            }
        }
        
        return $this;
    }
    
    /**
     * Bind a single placeholders with its value
     * Supports named(:named) and unnamed(?) placeholders
     *
     * @param string|int  $value
     * @param string      $type
     * @param string|null $name
     *
     * @return void
     */
    public function preparePlaceholder( string|int $value, string $type = 'string', string $name = null ): void
    {
        $sanitized_value = $this->sanitize( $value, $type );
        $this->query     = $name
            ? preg_replace( '@' . $name . '@',            $sanitized_value,       $this->query, 1 )
            : preg_replace( '/\s?\?\s?/', ' ' . $sanitized_value . ' ', $this->query, 1 );
    }
}