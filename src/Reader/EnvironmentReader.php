<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Reader;

use Impmurciego\DotenvValidator\Configuration\Environment;
use Impmurciego\DotenvValidator\Configuration\EnvironmentCollection;
use Impmurciego\DotenvValidator\Exception\FileNotFoundException;
use Impmurciego\DotenvValidator\Exception\InvalidConfigurationException;

class EnvironmentReader
{
    private FileReader $fileReader;

    public function __construct()
    {
        $this->fileReader = new FileReader();
    }

    /**
     * @param string $path
     * @return EnvironmentCollection
     * @throws FileNotFoundException
     * @throws InvalidConfigurationException
     */
    public function read(string $path): EnvironmentCollection
    {
        $configFile = $this->fileReader->getContent($path);

        try {
            /** @var array<string, string> $parsedEnvironments */
            $parsedEnvironments = json_decode($configFile, true, 512, JSON_THROW_ON_ERROR);

            foreach ($parsedEnvironments as $envName => $envFilePath) {
                $environments[] = new Environment($envName, $envFilePath);
            }

        } catch (\JsonException $exception) {
            throw InvalidConfigurationException::fromFormat($configFile);
        }

        return EnvironmentCollection::createFrom($environments ?? []);
    }
}
