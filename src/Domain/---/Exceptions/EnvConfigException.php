<?php

namespace Untek\Core\DotEnv\Domain\Exceptions;

use Untek\Core\Code\Helpers\DeprecateHelper;
use Untek\Core\Contract\Common\Exceptions\InvalidConfigException;

DeprecateHelper::hardThrow();

class EnvConfigException extends InvalidConfigException
{

}
