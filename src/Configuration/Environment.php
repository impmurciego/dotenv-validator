<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Configuration;


class Environment
{
    private string $name;
    private string $filePath;
    private array $variables = [];

    public function __construct(string $name, string $filePath)
    {
        $this->name = $name;
        $this->filePath = $filePath;
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }


    public function getVariables(): array
    {
        return $this->variables;
    }
}
