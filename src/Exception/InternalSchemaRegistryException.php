<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

class InternalSchemaRegistryException extends \RuntimeException implements SchemaRegistryException
{
    const ERROR_CODE = 50001;

    public static function create(): InternalSchemaRegistryException
    {
        return new self('Error code 50001 – Error in the backend datastore', self::ERROR_CODE);
    }

    public function errorCode(): int
    {
        return self::ERROR_CODE;
    }
}
