<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use Assert\Assert;
use JsonException;

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

    /**
     * @param string $jsonString
     * @param int $depth
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public static function jsonDecode(string $jsonString, int $depth = 512)
    {
        return json_decode($jsonString, true, $depth, JSON_THROW_ON_ERROR);
    }

    private function __clone()
    {
    }
}
