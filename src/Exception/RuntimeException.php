<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use RuntimeException as PHPRuntimeException;

class RuntimeException extends PHPRuntimeException implements SchemaRegistryException
{
    public const ERROR_CODE = 99998;

    public static function errorCode(): int
    {
        return self::ERROR_CODE;
    }
}
