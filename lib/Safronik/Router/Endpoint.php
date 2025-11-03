<?php

namespace Safronik\Router;

use ReflectionMethod;

/**
 * Class Method
 *
 * Describe method of controller. Contains the following properties:
 * - name
 * - parameters
 * - path
 * - type
 * - controllerType
 *
 * @package Safronik\Routers
 */
class Endpoint
{
    public string $name;
    public array  $parameters;
    public string $path;
    public string $type;
    public string $description;
    public string $controllerType;

    /**
     * Method constructor.
     *
     * @param ReflectionMethod $endpoint_reflection
     * @param string           $path
     */
    public function __construct( ReflectionMethod $endpoint_reflection, string $path )
    {
        $namespace = $endpoint_reflection->getDeclaringClass()->getNamespaceName();
        $docBlock  = $endpoint_reflection->getDocComment();

        $this->name        = lcfirst( str_replace( [ 'action', 'method' ], '', $endpoint_reflection->getName() ) );

        $this->parameters  = $this->getParametersFromDocBlock($docBlock)
            ?: $endpoint_reflection->getParameters();

        $pathArray = explode('/', $path);
        array_pop($pathArray);
        $pathArray[] = $this->name;
        $this->path = implode('/', $pathArray);

        $this->description = $this->getDescriptionFromDocBlock($docBlock);
        $this->type        = $this->detectMethodType($endpoint_reflection->getName());
        $this->controllerType = match (true){
            str_contains( $namespace, 'Cli' ) =>'cli',
            str_contains( $namespace, 'Web' ) =>'web',
            str_contains( $namespace, 'Rest' ) =>'rest',
            str_contains( $namespace, 'Api' ) =>'api',
            default => 'common',
        };
    }

    private function detectMethodType( string $name ): string
    {
        return match (true){
            str_starts_with( $name, 'action' ) => 'action',
            str_starts_with( $name, 'method' ) => 'method',
            in_array( $name, ['post','get','put','delete','list'] ) => 'method',
        };
    }

    /**
     * Gets parameters from doc block
     * Looks at the "@param-api" tag
     *
     * @param false|string $docBlock
     * @return array
     */
    private function getParametersFromDocBlock( false|string $docBlock ): array
    {
        if( ! $docBlock ){
            return [];
        }

        $lines = explode( PHP_EOL, $docBlock );
        $parameters_lines = array_filter( $lines, fn($line) => str_contains( $line, '@param-api' ) );
        foreach( $parameters_lines as $parameter_line ){
            preg_match('/\*\s@param-api\s([a-z]+)\s\$([a-z]+)\s(.+?)[\r\n\t]?$/', $parameter_line, $matches);
            $parameters[] = [
                'type'        => $matches[1] ?? '',
                'name'        => $matches[2] ?? '',
                'description' => $matches[3] ?? '',
            ];
        }

        return $parameters ?? [];
    }

    /**
     * Gets method description from doc block
     *
     * @param false|string $docBlock
     * @return string
     */
    private function getDescriptionFromDocBlock( false|string $docBlock ): string
    {
        if( ! $docBlock ){
            return '';
        }

        $lines = explode( PHP_EOL, $docBlock );
        $lines = array_map( fn($line) => trim($line, "\ \n\r\t\v\0/*"), $lines );
        $lines = array_filter( $lines, static fn( $line) => $line !== '' && ! str_contains( $line, '@' ) );

        return $lines
            ? implode(' ', $lines)
            : '';
    }

    /**
     * Compile help array to use in other places
     *
     * @return array
     */
    public function compileHelp(): array
    {
        return [
            'command: '     . $this->getName(),
            'parameters: ' ,  $this->getParameters(),
            'description: ' . $this->getDescription(),
        ];
    }

    /**
     * Get method name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get method parameters
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get method path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get method type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get method description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function getControllerType(): string
    {
        return $this->controllerType;
    }
}