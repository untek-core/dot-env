<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Dotenv\Command\DebugCommand;
use Symfony\Component\Dotenv\Command\DotenvDumpCommand;
use Untek\Core\Env\Helpers\EnvHelper;
use Untek\Core\FileSystem\Helpers\FilePathHelper;

return [
    'definitions' => [
        DotenvDumpCommand::class => function (ContainerInterface $container) {
            /** @var \Untek\Core\App\Interfaces\EnvStorageInterface $envStorage */
            $envStorage = $container->get(\Untek\Core\App\Interfaces\EnvStorageInterface::class);

            $env = $envStorage->get('APP_ENV');
            $path = FilePathHelper::rootPath();

            return new DotenvDumpCommand($path, $env);
        },
        DebugCommand::class => function (ContainerInterface $container) {
            /** @var \Untek\Core\App\Interfaces\EnvStorageInterface $envStorage */
            $envStorage = $container->get(\Untek\Core\App\Interfaces\EnvStorageInterface::class);

            $env = $envStorage->get('APP_ENV');
            $path = FilePathHelper::rootPath();

            return new DebugCommand($env, $path);
        },
    ],
];
