<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use FlixTech\SchemaRegistryApi\Model\Subject\VersionId;

class InvalidVersionException extends \RuntimeException implements SchemaRegistryException
{
    const ERROR_CODE = 42202;

    public static function create(VersionId $id): InvalidVersionException
    {
        return new self(
            sprintf('Error 42202 - Version ID "%s" is invalid', $id->value()),
            self::ERROR_CODE
        );
    }

    public function errorCode(): int
    {
        return self::ERROR_CODE;
    }
}
