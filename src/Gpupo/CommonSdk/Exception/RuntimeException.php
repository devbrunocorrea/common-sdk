<?php

namespace Gpupo\CommonSdk\Exception;

use Gpupo\CommonSdk\Traits\ExceptionTrait;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    use ExceptionTrait;
}
