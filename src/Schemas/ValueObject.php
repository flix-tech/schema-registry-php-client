<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schemas;

abstract class ValueObject
{
    final protected function __construct()
    {
    }

    /**
     * @codeCoverageIgnore
     */
    final private function __clone()
    {
    }
}
