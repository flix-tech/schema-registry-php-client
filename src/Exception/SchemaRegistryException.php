<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

interface SchemaRegistryException
{
    public function errorCode(): int;
}
