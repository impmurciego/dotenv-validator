<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Command;

use Impmurciego\DotenvValidator\Exception\FileNotFoundException;
use Impmurciego\DotenvValidator\Exception\InvalidConfigurationException;
use Impmurciego\DotenvValidator\Exception\InvalidDotEnvException;
use Impmurciego\DotenvValidator\Output\SymfonyOutput;
use Impmurciego\DotenvValidator\Validator\EnvironmentsValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DotEnvValidatorCommand extends Command
{
    public function __construct()
    {
        parent::__construct('validate');
    }

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Check vars in all environments'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Check vars in all environment started');
        $symfonyOutput = new SymfonyOutput($symfonyStyle);
        $validator = new EnvironmentsValidator($symfonyOutput);

        try {
            $validator->validate();

        } catch (FileNotFoundException | InvalidConfigurationException $exception) {
            $symfonyStyle->error('Error');
            $symfonyStyle->writeln($exception->getMessage());

            return Command::FAILURE;
        } catch (InvalidDotEnvException $DotEnvException) {
            $symfonyStyle->writeln($DotEnvException->getMessage());
            return Command::FAILURE;
        }

        $symfonyStyle->success('Process finished');

        return Command::SUCCESS;

    }
}
