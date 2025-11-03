<?php

namespace Controllers\Exceptions;

// @todo implement in exceptions and views

/**
 * Interface for Machine 2 Machine communications
 * It's a layer that helps to convert original exceptions to such messages
 */
interface M2MExceptionInterface
{
    public function getM2MMessage(): ?string;
    public function makeM2mMessage(): static;
}