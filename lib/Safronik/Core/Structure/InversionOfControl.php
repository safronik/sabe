<?php

namespace Safronik\Core\Structure;

use Exception;
use ReflectionException;
use ReflectionMethod;
use Safronik\CodePatterns\Exceptions\ContainerException;

trait InversionOfControl
{
    public function call( string $functionName, array $arguments = [] ): void
    {
        $arguments = $this->compileArguments( $functionName, $arguments );

        $this->$functionName( ...( $arguments ) );
    }

    /**
     * Executes method of class with parameters
     * Generates arguments for method with DI
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function __call( string $functionName, array $arguments ): void
    {
        // Override magic __call by static class
        if( method_exists( $this, '_call' ) ){
            $this->_call( $functionName, $arguments);
            return;
        }

        // Method doesn't exists
        method_exists( $this, $functionName )
            || throw new \BadMethodCallException("Method $functionName does not exist");

        $this->call( $functionName, $arguments );
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     * @throws Exception
     */
    protected function compileArguments( string $functionName, array $arguments = [] ): array
    {
        $methodReflection = new ReflectionMethod( static::class, $functionName );

        foreach($methodReflection->getParameters() as $parameter){

            $parameterType  = $parameter->getType()?->getName();

            $parameterName  = $parameter->getName();
            $parameterValue = $parameters[ $parameterName ] ?? null;
            $parameterValue = !$parameterValue && class_exists( $parameterType )
                ? $this->app()->di->get( $parameterType )
                : $parameterValue;

            $arguments[ $parameterName ] = $parameterValue;
        }

        return $arguments;
    }
}