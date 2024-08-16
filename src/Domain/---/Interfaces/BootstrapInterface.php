<?php

namespace Untek\Core\DotEnv\Domain\Interfaces;

interface BootstrapInterface
{

    public function getMode(): string;

    public function getRootDirectory(): string;

    public function parseFile(string $fileName): array;

    public function parse(string $content): array;

    public function loadFromPath(string $basePath = null, array $names = null): void;

    public function loadFromArray(array $env): void;

    public function loadFromContent(string $content): void;
}
