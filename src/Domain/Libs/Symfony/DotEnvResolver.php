<?php

namespace Untek\Core\DotEnv\Domain\Libs\Symfony;

use Symfony\Component\Dotenv\Dotenv;
use Untek\Core\Arr\Helpers\ArrayHelper;
use Untek\Core\Code\Exceptions\NotFoundDependencyException;
use Untek\Core\Code\Helpers\ComposerHelper;
use Untek\Core\DotEnv\Domain\Enums\DotEnvModeEnum;
use Untek\Core\FileSystem\Helpers\FilePathHelper;
use Untek\Core\Pattern\Singleton\SingletonTrait;

/**
 * Резолвер переменных окружения.
 *
 * Заменяет плэйсхолдеры на значения.
 */
class DotEnvResolver
{

    public const VARNAME_REGEX = '(?i:[A-Z][A-Z0-9_]*+)';

    public function resolve(array $env): array {
        
//        $env = ArrayHelper::merge(getenv(), $_ENV);

        $isUpdated = true;
        while ($isUpdated) {
            $isUpdated = false;
            foreach ($env as $key => $value) {
                $newValue = $this->resolveVariables($value, $env);
                if($newValue != $value) {
                    $env[$key] = $newValue;
//                    putenv("{$key}={$newValue}");
//                    $this->setEnv($key, $newValue);
                    $isUpdated = true;
                }
            }
        }
//        $_ENV = $env;
//        $this->setEnvs($env);
        return $env;
    }
//
//    protected function setEnvs(array $env): void {
//        foreach ($env as $key => $value) {
//            $this->setEnv($key, $value);
//        }
//    }
//
//    protected function setEnv(string $key, string $value): void {
//        putenv("{$key}={$value}");
//        $_ENV[$key] = $value;
//    }

    protected function resolveVariables(string $value, array $loadedVars) {
        if (false === strpos($value, '$')) {
            return $value;
        }

        $regex = '/
            (?<!\\\\)
            (?P<backslashes>\\\\*)             # escaped with a backslash?
            \$
            (?!\()                             # no opening parenthesis
            (?P<opening_brace>\{)?             # optional brace
            (?P<name>'.self::VARNAME_REGEX.')? # var name
            (?P<default_value>:[-=][^\}]++)?   # optional default value
            (?P<closing_brace>\})?             # optional closing brace
        /x';

        $value = preg_replace_callback($regex, function ($matches) use ($loadedVars) {
            // odd number of backslashes means the $ character is escaped
            if (1 === \strlen($matches['backslashes']) % 2) {
                return substr($matches[0], 1);
            }

            // unescaped $ not followed by variable name
            if (!isset($matches['name'])) {
                return $matches[0];
            }

            if ('{' === $matches['opening_brace'] && !isset($matches['closing_brace'])) {
                throw $this->createFormatException('Unclosed braces on variable expansion');
            }

            $name = $matches['name'];
            if (isset($loadedVars[$name]) && isset($this->values[$name])) {
                $value = $this->values[$name];
            } elseif (getenv($name)) {
                $value = getenv($name);
            } elseif (isset($_SERVER[$name]) && 0 !== strpos($name, 'HTTP_')) {
                $value = $_SERVER[$name];
            } elseif (isset($this->values[$name])) {
                $value = $this->values[$name];
            } else {
                $value = (string) getenv($name);
            }

            if ('' === $value && isset($matches['default_value']) && '' !== $matches['default_value']) {
                $unsupportedChars = strpbrk($matches['default_value'], '\'"{$');
                if (false !== $unsupportedChars) {
                    throw $this->createFormatException(sprintf('Unsupported character "%s" found in the default value of variable "$%s".', $unsupportedChars[0], $name));
                }

                $value = substr($matches['default_value'], 2);

                if ('=' === $matches['default_value'][1]) {
                    $this->values[$name] = $value;
                }
            }

            if (!$matches['opening_brace'] && isset($matches['closing_brace'])) {
                $value .= '}';
            }

            return $matches['backslashes'].$value;
        }, $value);

        return $value;
    }
}
