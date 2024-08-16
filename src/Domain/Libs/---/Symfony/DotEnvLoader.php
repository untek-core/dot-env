<?php

namespace Untek\Core\DotEnv\Domain\Libs\Symfony;

use Symfony\Component\Dotenv\Dotenv;

class DotEnvLoader
{

    public function loadFromFile(string $path): array
    {
        $dotEnv = new Dotenv();
        $content = file_get_contents($path);
        $parsedEnv = $dotEnv->parse($content, $path);
        return $parsedEnv;
    }

    public function loadFromContent(string $content): array
    {
        $dotEnv = new Dotenv();
        $parsedEnv = $dotEnv->parse($content);
        return $parsedEnv;
    }
}
