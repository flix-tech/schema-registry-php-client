<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use Throwable;

abstract class AbstractSchemaRegistryException extends \RuntimeException implements SchemaRegistryException
{
    const ERROR_CODE = 0;
    const ERROR_MESSAGE = 'Internal Server Error';

    final public function __construct(Throwable $previous = null)
    {
        parent::__construct(
            static::ERROR_MESSAGE,
            static::ERROR_CODE,
            $previous
        );
    }

    final public function errorCode(): int
    {
        return static::ERROR_CODE;
    }
}
