<?php

namespace Safronik\Controllers\Requests;

use Safronik\Core\Helpers\ValidationHelper;

trait DynamicValidation
{
    public function validateBy( array $rules ): void
    {
        $parametersToValidate = array_diff_assoc(
            $this->parameters,
            $this->rules,
        );

        ValidationHelper::         validate( $parametersToValidate, $rules );
        ValidationHelper::validateRedundant( $parametersToValidate, $rules );
    }
}