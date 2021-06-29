<?php

declare(strict_types=1);

namespace Impmurciego\DotenvValidator\Configuration;

use ArrayIterator;

class EnvironmentCollection
{
    /**
     * @var ArrayIterator<string, Environment>
     */
    private ArrayIterator $environments;

    private function __construct()
    {
        $this->environments = new ArrayIterator();
    }

    /**
     * @param Environment[] $environments
     */
    public static function createFrom(array $environments): self
    {
        $collection = new self();
        foreach ($environments as $environment) {
            $collection->add($environment);
        }

        return $collection;
    }

    public function add(Environment $environment): void
    {
        $this->environments->offsetSet($environment->getName(), $environment);
    }

    public function remove(Environment $environment): void
    {
        $this->environments->offsetUnset($environment->getName());
    }

    /**
     * @return Environment[]
     */
    public function getEnvironments(): array
    {
        return $this->environments->getArrayCopy();
    }
}
