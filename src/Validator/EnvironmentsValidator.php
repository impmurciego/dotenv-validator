<?php

namespace Impmurciego\DotenvValidator\Validator;

use Impmurciego\DotenvValidator\Configuration\EnvironmentCollection;
use Impmurciego\DotenvValidator\Exception\FileNotFoundException;
use Impmurciego\DotenvValidator\Exception\InvalidConfigurationException;
use Impmurciego\DotenvValidator\Exception\InvalidDotEnvException;
use Impmurciego\DotenvValidator\Output\OutputInterface;
use Impmurciego\DotenvValidator\Reader\ConfigurationReader;
use Impmurciego\DotenvValidator\Reader\EnvironmentReader;
use Symfony\Component\Dotenv\Dotenv;

class EnvironmentsValidator
{
    private const CONFIG_FILE_PATH = './dotenv-validator.json';
    private string $configFilePath;
    private OutputInterface $output;
    private EnvironmentReader $environmentReader;
    private ConfigurationReader $configurationReader;
    private Dotenv $dotenv;

    public function __construct(OutputInterface $output, string $configFilePath = self::CONFIG_FILE_PATH)
    {
        $this->output = $output;
        $this->configFilePath = $configFilePath;
        $this->environmentReader = new EnvironmentReader();
        $this->configurationReader = new ConfigurationReader();
        $this->dotenv = new Dotenv();
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
        $error = false;
        foreach ($environmentsDiff as $diffs) {
            if (0 < count($diffs)) {
                $error = true;
                $this->output->error('Error');
                $this->output->section('You must add the following variables:');

                foreach ($environmentsDiff as $name => $missedVars) {
                    $this->printMissedVars($missedVars, $name);
                }
            }
        }

        if ($error) {
            throw new InvalidDotEnvException();
        }
    }

    private function printMissedVars(array $missedVars, string $env): void
    {
        $this->output->table(
            [$env],
            array_map(static function (string $envVar) {
                return [$envVar];
            }, $missedVars)
        );
    }

    private function analyzeEnvironments(EnvironmentCollection $environmentCollection): array
    {
        $environmentsDiff = [];

        foreach ($environmentCollection->getEnvironments() as $environment) {
            $environmentsToCompare = $environmentCollection;
            $environmentsToCompare->remove($environment);
            $environmentsDiff[$environment->getName()] = $this->analyzeEnvironment($environment->getVariables(), $environmentsToCompare);
        }

        return $environmentsDiff;
    }

    private function analyzeEnvironment(array $currentEnvironment, EnvironmentCollection $environmentsToCompare): array
    {
        $environmentsDiff = [];

        foreach ($environmentsToCompare->getEnvironments() as $environments) {
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
