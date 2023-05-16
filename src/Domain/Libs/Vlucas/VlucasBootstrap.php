<?php

namespace Untek\Core\DotEnv\Domain\Libs\Vlucas;

use Dotenv\Dotenv;
use Dotenv\Loader\Loader;
use Dotenv\Parser\Entry;
use Dotenv\Parser\Parser;
use Dotenv\Store\StringStore;
use Untek\Core\Code\Exceptions\NotFoundDependencyException;
use Untek\Core\Code\Helpers\ComposerHelper;
use Untek\Core\DotEnv\Domain\Interfaces\BootstrapInterface;
use Untek\Component\FormatAdapter\StoreFile;

/**
 * Загрузчик переменных окружения
 */
class VlucasBootstrap implements BootstrapInterface
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
        $content = file_get_contents($fileName);
        return $this->parse($content);
    }

    public function parse(string $content): array {
        $parser = new Parser();
        $collection = $parser->parse($content);
        $env = [];
        foreach ($collection as $entry) {
            $env[$entry->getName()] = $entry->getValue()->get()->getChars();
        }
        return $env;
    }

    public function loadFromPath(
        string $basePath = null,
        array $names = null
    ): void {
        $this->checkAll();
        $this->bootVlucasDotenvFromFile($basePath, $names);
    }

    public function loadFromArray(array $env): void
    {
        $this->checkAll();
        $this->bootVlucasDotenvFromArray($env);
    }

    public function loadFromContent(string $content): void
    {
        $this->checkAll();

        /*$this->saveContentsToPhpFile($contents)*/

        $repository = $this->createRepository();
        $dotenv = new \Dotenv\Dotenv(new StringStore($content), new Parser(), new Loader(), $repository);
        $dotenv->load();
    }

    protected function bootVlucasDotenvFromArray(array $env): void
    {
        $repository = $this->createRepository();
        $rr = [];
        foreach ($env as $name => $value) {
            $v = \Dotenv\Parser\Value::blank();
            $v = $v->append($value, true);
            $rr[] = new Entry($name, $v);
        }
        (new Loader())->load($repository, $rr);
    }

    protected function bootVlucasDotenvFromFile(
        string $basePath,
        array $names = null
    ): void {
        $repository = $this->createRepository();
        $dotenv = \Dotenv\Dotenv::create($repository, $basePath, $names, false);
        $dotenv->load();
    }

    protected function checkAll()
    {
        if ($this->checkInit()) {
            return;
        }
        $this->checkVlucasDotenvPackage();
    }

    /**
     * Проверка повтроной инициализации
     *
     * @return bool
     */
    protected function checkInit(): bool
    {
        return getenv('ROOT_DIRECTORY') != null;

//        $isInited = $this->inited;
//        $this->inited = true;
//        return $isInited;
    }

    /**
     * Проверка установки пакета 'vlucas/phpdotenv'
     *
     * @throws NotFoundDependencyException
     */
    protected function checkVlucasDotenvPackage(): void
    {
        ComposerHelper::requireAssert(Dotenv::class, 'vlucas/phpdotenv', "5.*");
    }

    protected function createRepository(): \Dotenv\Repository\RepositoryInterface
    {
        $repository = \Dotenv\Repository\RepositoryBuilder::createWithNoAdapters()
            ->addAdapter(\Dotenv\Repository\Adapter\EnvConstAdapter::class)
//            ->addWriter(\Dotenv\Repository\Adapter\ArrayAdapter::class)
            ->addWriter(\Dotenv\Repository\Adapter\PutenvAdapter::class)
//            ->immutable()
            ->make();
        $repository->set('ROOT_DIRECTORY', $this->rootDirectory);
        $repository->set('APP_MODE', $this->mode);
        return $repository;
    }

    protected function saveContentsToPhpFile(array $contents): void
    {
        $parser = new Parser();
        $env = [];
        foreach ($contents as $contentName => $contentValue) {
            $collection = $parser->parse($contentValue);
            foreach ($collection as $entry) {
                $env[$contentName][$entry->getName()] = $entry->getValue()->get()->getChars();
            }
        }
        $store = new StoreFile(__DIR__ . '/../../../../../../../var/env.php');
        $store->save($env);
    }

    protected function dump($env, $name)
    {
        ksort($env);
        file_put_contents(
            __DIR__ . '/../../../../../../../var/' . $name . '_' . $this->mode . '.json',
            json_encode($env, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }
}
