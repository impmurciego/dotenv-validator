<?php

namespace Impmurciego\DotenvValidator\Validator;

use Impmurciego\DotenvValidator\Configuration\Environment;
use Impmurciego\DotenvValidator\Configuration\EnvironmentCollection;
use Impmurciego\DotenvValidator\Exception\FileNotFoundException;
use Impmurciego\DotenvValidator\Exception\InvalidConfigurationException;
use Impmurciego\DotenvValidator\Exception\InvalidDotEnvException;
use Impmurciego\DotenvValidator\Output\OutputInterface;
use Impmurciego\DotenvValidator\Reader\ConfigurationReader;
use Impmurciego\DotenvValidator\Reader\EnvironmentReader;

class EnvironmentsValidator
{
    private const CONFIG_FILE_PATH = './dotenv-validator.json';
    private string $configFilePath;
    private OutputInterface $output;
    private EnvironmentReader $environmentReader;
    private ConfigurationReader $configurationReader;

    public function __construct(OutputInterface $output, string $configFilePath = self::CONFIG_FILE_PATH)
    {
        $this->output = $output;
        $this->configFilePath = $configFilePath;
        $this->environmentReader = new EnvironmentReader();
        $this->configurationReader = new ConfigurationReader();
    }

    /**
     * @throws FileNotFoundException
     * @throws InvalidConfigurationException
     * @throws InvalidDotEnvException
     */
    public function validate(): void
    {
        $environmentCollection = $this->readConfiguration();

        foreach ($environmentCollection->getEnvironments() as $environment) {
            $parsedVars = $this->configurationReader->read($environment->getFilePath());
            $environment->setVariables($parsedVars);
        }

        $environmentsDiff = $this->analyzeEnvironments($environmentCollection);
        $hasErrors = $this->hasErrors($environmentsDiff);

        if ($hasErrors) {
            $this->printMissedVars($environmentsDiff);

            throw new InvalidDotEnvException();
        }
    }

    private function hasErrors(array $environmentsDiff): bool
    {
        foreach ($environmentsDiff as $diffs) {
            if (0 < count($diffs)) {
                return true;
            }
        }

        return false;
    }

    private function printMissedVars(array $environmentsDiff): void
    {
        $this->output->error('Error');
        $this->output->section('You must add the following variables:');
        foreach ($environmentsDiff as $name => $missedVars) {
            $this->output->table(
                [$name],
                array_map(static function (string $envVar) {
                    return [$envVar];
                }, $missedVars)
            );
        }
    }

    private function analyzeEnvironments(EnvironmentCollection $environmentCollection): array
    {
        $environmentsDiff = [];

        foreach ($environmentCollection->getEnvironments() as $environment) {
            $environmentsToCompare = $environmentCollection->getEnvironments();
            unset($environmentsToCompare[$environment->getName()]);

            $environmentsDiff[$environment->getName()] = $this->analyzeEnvironment($environment->getVariables(), $environmentsToCompare);
        }

        return $environmentsDiff;
    }

    /**
     * @param array<string, Environment> $environmentsToCompare
     */
    private function analyzeEnvironment(array $currentEnvironment, array $environmentsToCompare): array
    {
        $environmentsDiff = [];

        foreach ($environmentsToCompare as $environments) {
            $environmentsDiff[] = array_keys(array_diff_key($environments->getVariables(), $currentEnvironment));
        }

        return array_replace_recursive([], ...$environmentsDiff);
    }

    /**
     * @return EnvironmentCollection
     * @throws FileNotFoundException
     * @throws InvalidConfigurationException
     */
    private function readConfiguration(): EnvironmentCollection
    {
        $environments = $this->environmentReader->read($this->configFilePath);

        if (2 > count($environments->getEnvironments())) {
            throw InvalidConfigurationException::fromEnvironments();
        }

        return $environments;
    }
}
