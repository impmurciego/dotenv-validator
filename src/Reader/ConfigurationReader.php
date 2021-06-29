<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Reader;

use Impmurciego\DotenvValidator\Exception\FileNotFoundException;
use Symfony\Component\Dotenv\Dotenv;

class ConfigurationReader
{
    private FileReader $fileReader;
    private Dotenv $dotenv;

    public function __construct()
    {
        $this->fileReader = new FileReader();
        $this->dotenv = new Dotenv();
    }

    /**
     * @param string $path
     * @return array
     * @throws FileNotFoundException
     */
    public function read(string $path): array
    {
        $dotFile = $this->fileReader->getContent($path);

        return $this->dotenv->parse($dotFile);
    }
}
