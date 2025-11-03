<?php

namespace Safronik\Controllers\Api\Rest\Requests;

use Safronik\Controllers\Requests\MethodRequest;
use Safronik\Controllers\Requests\DynamicValidation;

class PatchMethodRequest extends MethodRequest
{
    use DynamicValidation;
}