<?php

namespace Untek\Core\DotEnv\Domain\Libs\Symfony;

class DotEnvWriter
{
    
    public function setAll(array $env): void {
        foreach ($env as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function set(string $key, string $value): void {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }
}
