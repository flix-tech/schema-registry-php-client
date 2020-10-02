<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use RuntimeException as PHPRuntimeException;

abstract class AbstractSchemaRegistryException extends PHPRuntimeException implements SchemaRegistryException
{
    public const ERROR_CODE = 0;

    final public static function errorCode(): int
    {
        if (!defined('static::ERROR_CODE') || 0 === static::ERROR_CODE) {
            throw new LogicException(sprintf('Class "%s" must define constant `ERROR_CODE`', static::class));
        }

        return static::ERROR_CODE;
    }
}
