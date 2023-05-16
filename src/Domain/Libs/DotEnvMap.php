<?php

namespace Untek\Core\DotEnv\Domain\Libs;

use Untek\Core\Arr\Helpers\ArrayHelper;
use Untek\Core\Pattern\Singleton\SingletonTrait;

class DotEnvMap
{

    use SingletonTrait;

    private $map = [];

    private function __construct()
    {
        $this->forgeMap();
    }

    public static function get(string $path = null, $default = null)
    {
        return self::getInstance()->getValue($path, $default);
    }

    public function getValue(string $path = null, $default = null)
    {
        return ArrayHelper::getValue($this->map, $path, $default);
    }

    private function forgeMap(): void
    {
        foreach ($_ENV as $name => $value) {
            $pureName = $this->prepareName($name);
            ArrayHelper::set($this->map, $pureName, $value);
        }
    }

    private function prepareName(string $name): string
    {
        $name = strtolower($name);
        $name = str_replace('_', '.', $name);
        return $name;
    }
}
