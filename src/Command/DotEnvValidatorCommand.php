<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;

final class DotEnvValidatorCommand extends Command
{
    /** @var array<string, array<string, string>> */
    private array $loadedEnvironments = [];

    private Dotenv $dotenv;

    public function __construct()
    {
        parent::__construct('validate');
        $this->dotenv = new Dotenv();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Check vars in all environments'
            )
            ->addArgument('environments',
                InputArgument::REQUIRED,
                "Name and path of the env file of the environments you want to validate. You must enter at least two like {'Production' => './.env.prod', 'Test' => './.env.test'}");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Check vars in all environment started');
        /** @var string $environments */
        $environments = $input->getArgument('environments');
        /** @var array<string, string> $parsedEnvironments */
        $parsedEnvironments = json_decode($environments, true, 512, JSON_THROW_ON_ERROR);

        if (2 > count($parsedEnvironments)) {
            $symfonyStyle->writeln(' You must enter at least two environments');

            return Command::FAILURE;
        }

        $this->loadEnvFiles($parsedEnvironments);
        $environmentsDiff = $this->analyzeEnvironments();

        foreach ($environmentsDiff as $diffs) {
            if (0 < count($diffs)) {
                $symfonyStyle->error('Error');
                $symfonyStyle->section('Incorrect environment variable settings. You must add the following variables:');

                foreach ($environmentsDiff as $name => $missedVars) {
                    $this->printMissedVars($symfonyStyle, $missedVars, $name);
                }

                return Command::FAILURE;
            }
        }

        $symfonyStyle->success('Process finished');

        return Command::SUCCESS;
    }

    /**
     * @return array<string, array>
     */
    private function analyzeEnvironments(): array
    {
        $environmentsDiff = [];

        foreach ($this->loadedEnvironments as $environmentName => $loadedVars) {
            $environmentsToCompare = $this->loadedEnvironments;
            unset($environmentsToCompare[$environmentName]);
            $environmentsDiff[$environmentName] = $this->analyzeEnvironment($loadedVars, $environmentsToCompare);
        }

        return $environmentsDiff;
    }

    /**
     * @param array<string, string>                $currentEnvironment
     * @param array<string, array<string, string>> $environmentsToCompare
     */
    private function analyzeEnvironment(array $currentEnvironment, array $environmentsToCompare): array
    {
        $environmentsDiff = [];

        foreach ($environmentsToCompare as $varsToCompare) {
            $environmentsDiff[] = array_keys(array_diff_key($varsToCompare, $currentEnvironment));
        }

        return array_replace_recursive([], ...$environmentsDiff);
    }

    private function printMissedVars(SymfonyStyle $symfonyStyle, array $missedVars, string $env): void
    {
        $symfonyStyle->table(
            [$env],
            array_map(static function (string $envVar) {
                return [$envVar];
            }, $missedVars)
        );
    }

    /**
     * @param array<string, string> $environments
     */
    private function loadEnvFiles(array $environments): void
    {
        foreach ($environments as $name => $path) {
            /** @var array<string, string> $loadedVars */
            $loadedVars = $this->readDotFile($path);
            $this->loadedEnvironments[$name] = $loadedVars;
        }
    }

    private function readDotFile(string $path): array
    {
        $dotFile = file_get_contents($path);

        return $this->dotenv->parse((string) $dotFile);
    }
}
