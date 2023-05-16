<?php

namespace Untek\Core\DotEnv\Domain\Exceptions;

use Throwable;
use Untek\Core\Code\Helpers\DeprecateHelper;

DeprecateHelper::hardThrow();

class DotEnvNotFoundException extends \RuntimeException
{

    public function __construct($message = "Not found DotEnv value", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}