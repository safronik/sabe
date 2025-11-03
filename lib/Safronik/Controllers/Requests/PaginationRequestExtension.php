<?php

namespace Safronik\Controllers\Requests;

use Safronik\Core\Helpers\ValidationHelper;

trait PaginationRequestExtension
{
    public array $paginationRules = [
        'page'   => [ 'type' => 'integer' ],
        'offset' => [ 'type' => 'integer' ],
        'amount' => [ 'type' => 'integer' ],
    ];

    protected function validate(): void
    {
        ValidationHelper::validate(
            $this->parameters,
            $this->paginationRules,
        );

        ValidationHelper::validate(
            $this->parameters,
            $this->rules,
        );
    }

    public function getPaginationParameters(): array
    {
        return $this->getParametersBy( $this->paginationRules );
    }

    public function getNonPaginationRules(): array
    {
        $nonPaginationParameters = array_diff_assoc(
            $this->parameters,
            $this->paginationRules
        );

        return $this->getParametersBy( $nonPaginationParameters );
    }

    public function getParametersBy( array $rules ): array
    {
        return  array_filter(
            $this->parameters,
            static fn($key) => array_key_exists( $key, $rules ),
            ARRAY_FILTER_USE_KEY
        );
    }
}