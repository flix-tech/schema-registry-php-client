<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

abstract class AbstractSchemaRegistryException extends \RuntimeException implements SchemaRegistryException
{
    final public static function errorCode(): int
    {
        if (!defined('static::ERROR_CODE')) {
            throw new \LogicException(sprintf('Class "%s" must define constant `ERROR_CODE`', static::class));
        }

        return static::ERROR_CODE;
    }
}
