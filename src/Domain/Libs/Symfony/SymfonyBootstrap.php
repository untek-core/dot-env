<?php

namespace Untek\Core\DotEnv\Domain\Libs\Symfony;

use Dotenv\Loader\Loader;
use Dotenv\Parser\Entry;
use Dotenv\Parser\Parser;
use Dotenv\Store\StringStore;
use Symfony\Component\Dotenv\Dotenv;
use Untek\Core\Arr\Helpers\ArrayHelper;
use Untek\Core\Code\Exceptions\NotFoundDependencyException;
use Untek\Core\Code\Helpers\ComposerHelper;
use Untek\Core\DotEnv\Domain\Enums\DotEnvModeEnum;
use Untek\Core\DotEnv\Domain\Interfaces\BootstrapInterface;
use Untek\Core\FileSystem\Helpers\FilePathHelper;
use Untek\Core\Pattern\Singleton\SingletonTrait;
use Untek\Lib\Components\Store\Drivers\Php;
use Untek\Lib\Components\Store\StoreFile;

/**
 * Загрузчик переменных окружения
 */
class SymfonyBootstrap implements BootstrapInterface
{


    public function __construct(protected ?string $mode = null, protected ?string $rootDirectory = null)
    {
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    public function getRootDirectory(): string
    {
        return $this->rootDirectory;
    }

    public function setRootDirectory(string $rootDirectory): void
    {
        $this->rootDirectory = $rootDirectory;
    }

    public function parseFile(string $fileName): array {
        $loader = new DotEnvLoader();
        return $loader->loadFromFile($fileName);
    }

    public function parse(string $content): array {
        $loader = new DotEnvLoader();
        return $loader->loadFromContent($content);
    }

    public function loadFromPath(string $basePath = null, array $names = null): void
    {
        $this->checkAll();
        $this->initMode($this->mode);
        $this->initRootDirectory($this->rootDirectory);
        $this->bootSymfonyDotenv($basePath);
//        $this->bootFromLoader($this->mode, $basePath);
    }
    
    public function loadFromArray(array $env): void
    {
        $this->checkAll();
        $this->initMode($this->mode);
        $this->initRootDirectory($this->rootDirectory);
        $env = (new DotEnvResolver())->resolve($env);
        (new DotEnvWriter())->setAll($env);
//        $this->bootVlucasDotenvFromArray($this->rootDirectory, $this->mode, $env);
    }
    
    public function loadFromContent(string $content): void
    {
        $this->checkAll();
        $this->initMode($this->mode);
        $this->initRootDirectory($this->rootDirectory);

        $env = $this->getLoader()->loadFromContent($content);
        (new DotEnvWriter())->setAll($env);
    }
    
    private function saveContentsToPhpFile(array $contents): void {
        $parser = new Parser();
        $env = [];
        foreach ($contents as $contentName => $contentValue) {
            $collection = $parser->parse($contentValue);
            foreach ($collection as $entry) {
                $env[$contentName][$entry->getName()] = $entry->getValue()->get()->getChars();
            }
        }
        $store = new StoreFile(__DIR__ . '/../../../../../../../../var/env.php');
        $store->save($env);
    }

    protected function checkAll() {
        if ($this->checkInit()) {
            return;
        }
        $this->checkSymfonyDotenvPackage();
    }
    
    protected function getLoader(): DotEnvLoader
    {
        return new DotEnvLoader;
    }
    
    /**
     * Проверка повтроной инициализации
     *
     * @return bool
     */
    private function checkInit(): bool
    {
        return getenv('ROOT_DIRECTORY') != null;
        
        /*$isInited = $this->inited;
        $this->inited = true;
        return $isInited;*/
    }

    private function dump($env, $name) {
        ksort($env);
        file_put_contents(__DIR__ . '/../../../../../../../../var/' .$name.'_'.$this->mode.'.json', json_encode($env, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }





    /**
     * Загрузка переменных окружения
     *
     * Порядок загрузки файлов:
     *  - .env
     *  - .env.local (.env.test - для тестового окружения)
     *
     * @param string $basePath Путь к папке с .env* конфигами
     */
    private function bootSymfonyDotenv(string $basePath): void
    {
//        (new Dotenv('APP_ENV', 'APP_DEBUG'))->bootEnv($basePath . '/.env', 'dev', ['test'], true);

        try {
            $dotEnv = new Dotenv(false);
            $dotEnv->usePutenv(true);
            $dotEnv->bootEnv($basePath . '/.env', 'dev', ['test'], true);
        } catch (\Symfony\Component\Dotenv\Exception\PathException $e) {
        }


        // load all the .env files
//        $dotEnv->loadEnv($basePath . '/.env');


//        $this->dump($_ENV, 'bootSymfonyDotenv');
    }

    private function bootFromLoader(string $mode, string $basePath): void
    {
        $mainEnv = $this->getLoader()->loadFromFile($basePath . '/.env');
        if($mode == 'test') {
            $localEnv = $this->getLoader()->loadFromFile($basePath . '/.env.test');
        } else {
            $localEnv = $this->getLoader()->loadFromFile($basePath . '/.env.local');
        }
        $env = ArrayHelper::merge($mainEnv, $localEnv);
        $env = (new DotEnvResolver())->resolve($env);
        (new DotEnvWriter())->setAll($env);

//        $this->dump($env, 'bootFromLoader');
    }

    private function bootFromCache(string $basePath): void
    {
        $env = include $basePath . '/.env.local.php';
        (new DotEnvWriter())->setAll($env);
    }

    /**
     * Проверка установки пакета 'symfony/dotenv'
     *
     * @throws NotFoundDependencyException
     */
    private function checkSymfonyDotenvPackage(): void
    {
        ComposerHelper::requireAssert(Dotenv::class, 'symfony/dotenv', "4.*|5.*");
    }

    /**
     * Инициализация переменной окружения 'APP_MODE'
     *
     * @param string $mode Режим (main|test)
     */
    private function initMode(string $mode): void
    {
//        if (getenv('APP_MODE') == null || empty($_ENV['APP_MODE'])) {
            $_ENV['APP_MODE'] = $mode;
            putenv("APP_MODE=$mode");
//        }
    }

    /**
     * Инициализация переменной окружения 'ROOT_DIRECTORY'
     *
     * @param string $basePath Путь к корневой директории проекта
     */
    public function initRootDirectory(string $basePath): void
    {
        $value = realpath($basePath);
        $_ENV['ROOT_DIRECTORY'] = $value;
        putenv("ROOT_DIRECTORY={$value}");
    }

}
