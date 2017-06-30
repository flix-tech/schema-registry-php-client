<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

class IncompatibleAvroSchemaException extends \RuntimeException implements SchemaRegistryException
{
    const ERROR_CODE = 409;

    public static function create(): IncompatibleAvroSchemaException
    {
        return new self(
            'Error 409 - Schema is incompatible with subject',
            self::ERROR_CODE
        );
    }

    public function errorCode(): int
    {
        return self::ERROR_CODE;
    }
}
