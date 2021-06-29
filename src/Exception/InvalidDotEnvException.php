<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Exception;

use Exception;
use Throwable;

final class InvalidDotEnvException extends Exception
{
    private const MESSAGE = 'Incorrect environment variable settings.';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
