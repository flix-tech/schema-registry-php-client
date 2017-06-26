<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use FlixTech\SchemaRegistryApi\Model\Schema\SchemaId;

class SchemaNotFoundException extends \RuntimeException implements SchemaRegistryException
{
    const ERROR_CODE = 40403;

    public static function create(SchemaId $id): SchemaNotFoundException
    {
        return new self(
            sprintf('Error 40403 - Schema with ID "%s" could not be found', $id->value()),
            self::ERROR_CODE
        );
    }

    public function errorCode(): int
    {
        return self::ERROR_CODE;
    }
}
