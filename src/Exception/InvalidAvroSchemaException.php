<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;


class InvalidAvroSchemaException extends \RuntimeException implements SchemaRegistryException
{
    const ERROR_CODE = 42201;

    public static function create(): InvalidAvroSchemaException
    {
        return new self(
            sprintf('Error %s - Invalid Avro schema', self::ERROR_CODE),
            self::ERROR_CODE
        );
    }

    public function errorCode(): int
    {
        return self::ERROR_CODE;
    }
}
