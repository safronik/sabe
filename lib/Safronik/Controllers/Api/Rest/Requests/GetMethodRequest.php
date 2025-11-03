<?php

namespace Safronik\Controllers\Api\Rest\Requests;

use Safronik\Controllers\Requests\MethodRequest;
use Safronik\Controllers\Requests\DynamicValidation;
use Safronik\Controllers\Requests\PaginationRequestExtension;

class GetMethodRequest extends MethodRequest
{
    use PaginationRequestExtension;
    use DynamicValidation;
}