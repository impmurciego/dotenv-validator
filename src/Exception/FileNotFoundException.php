<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Exception;

use Exception;
use Throwable;

final class FileNotFoundException extends Exception
{
    public static function fromLocation(string $location, string $reason = '', Throwable $previous = null): self
    {
        return  new self(rtrim("Unable to read file from location: {$location}. {$reason}"), 0, $previous);
    }
}
