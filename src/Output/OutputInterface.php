<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Output;

interface OutputInterface
{
    public function error(string $error): void;

    public function section(string $section): void;

    public function table(array $headers, array $rows): void;
}
