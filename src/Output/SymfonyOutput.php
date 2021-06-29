<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Output;

use Symfony\Component\Console\Style\SymfonyStyle;

class SymfonyOutput implements OutputInterface
{
    private SymfonyStyle $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function error(string $error): void
    {
        $this->symfonyStyle->error($error);
    }

    public function section(string $section): void
    {
        $this->symfonyStyle->section($section);
    }

    public function table(array $headers, array $rows): void
    {
        $this->symfonyStyle->table($headers, $rows);
    }
}
