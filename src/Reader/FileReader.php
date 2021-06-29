<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Reader;

use Impmurciego\DotenvValidator\Configuration\Environment;
use Impmurciego\DotenvValidator\Configuration\EnvironmentCollection;
use Impmurciego\DotenvValidator\Exception\FileNotFoundException;
use Impmurciego\DotenvValidator\Exception\InvalidConfigurationException;

class FileReader
{
    public function getContent(string $path): string
    {
        $contents = @file_get_contents($path);

        if ($contents === false) {
            throw FileNotFoundException::fromLocation($path, error_get_last()['message'] ?? '');
        }

        return $contents;
    }
}
