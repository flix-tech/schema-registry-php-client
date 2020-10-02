<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use Assert\Assert;

final class Json
{
    private function __construct()
    {
    }

    public static function validateStringAsJson(string $schema): string
    {
        Assert::that($schema)->isJsonString('$schema must be a valid JSON string');

        return $schema;
    }

    private function __clone()
    {
    }
}
