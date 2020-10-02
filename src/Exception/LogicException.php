<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use LogicException as PHPLogicException;

class LogicException extends PHPLogicException implements SchemaRegistryException
{
    public const ERROR_CODE = 99997;

    public static function errorCode(): int
    {
        return self::ERROR_CODE;
    }
}
