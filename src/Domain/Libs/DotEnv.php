<?php

namespace Untek\Core\DotEnv\Domain\Libs;

use Untek\Core\Code\Helpers\DeprecateHelper;
use Untek\Core\Contract\Common\Exceptions\NotSupportedException;
use Untek\Core\DotEnv\Domain\Enums\DotEnvModeEnum;
use Untek\Core\DotEnv\Domain\Interfaces\BootstrapInterface;
use Untek\Core\DotEnv\Domain\Libs\Vlucas\VlucasBootstrap;

/**
 * Class DotEnv
 * @package Untek\Core\DotEnv\Domain\Libs
 * @deprecated
 */
class DotEnv
{

    /**
     * @param string $mode
     * @throws \Untek\Core\Code\Exceptions\DeprecatedException
     * @deprecated
     */
    public static function init(string $mode = DotEnvModeEnum::MAIN): void
    {
        DeprecateHelper::hardThrow('DotEnv::init deprecated!');
//        dd(getenv());
        $rootDirectory = realpath(__DIR__ . '/../../../../../../../gate.vea');
        $basePath = $rootDirectory;

        $bootstrap = new VlucasBootstrap($mode, $rootDirectory);
//        $bootstrap = new SymfonyBootstrap($mode, $rootDirectory);

//        self::loadFromContent($bootstrap, $basePath);
        self::loadFromFiles($bootstrap, $basePath);
//        self::loadFromArray($bootstrap, $basePath);
//        dd($_ENV);
    }
    
    protected static  function loadFromFiles(BootstrapInterface $bootstrap, string $basePath): void {
        $names = [
            '.env',
            $bootstrap->getMode() == 'test' ? '.env.test' : '.env.local',
        ];
        $bootstrap->loadFromPath($basePath, $names);
    }

    protected static  function loadFromArray(BootstrapInterface $bootstrap, string $basePath): void {
        $envs = include __DIR__ . '/../../../../../../../../var/env.php';
        $mode = $bootstrap->getMode();
        $bootstrap->loadFromArray($envs[$mode]);
    }

    protected static  function loadFromContent(BootstrapInterface $bootstrap, string $basePath): void {
        $content = self::getEnvContentByMode($bootstrap);
        $bootstrap->loadFromContent($content);
    }
    
    protected static function getEnvContentByMode(BootstrapInterface $bootstrap): string {
        $mode = $bootstrap->getMode();
        $rootDirectory = $bootstrap->getRootDirectory();
        $files = [
            '.env',
        ];
        if($mode == 'main') {
            $files[]  = '.env.local';
        } elseif($mode == 'test') {
            $files[]  = '.env.test';
        } else {
            throw new NotSupportedException("The mode \"{$mode}\" not supported!");
        }

        $content = '';
        foreach ($files as $file) {
            $content .= file_get_contents($rootDirectory . '/' . $file) . PHP_EOL;
        }
        return $content;
        
        /*$content = file_get_contents($rootDirectory . '/.env') . PHP_EOL;
        if($mode == 'main') {
            $contentMain = file_get_contents($rootDirectory . '/.env.local') . PHP_EOL;
            return $content . PHP_EOL . $contentMain;
        } elseif($mode == 'test') {
            $contentTest = file_get_contents($rootDirectory . '/.env.test') . PHP_EOL;
            $contents['test'] = $content . PHP_EOL  . $contentTest;
        } else {
            throw new NotSupportedException("The mode \"{$mode}\" not supported!");
        }*/
    }
}
