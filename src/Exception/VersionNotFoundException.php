<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use FlixTech\SchemaRegistryApi\Model\Subject\VersionId;

class VersionNotFoundException extends \RuntimeException implements SchemaRegistryException
{
    const ERROR_CODE = 40402;

    public static function create(VersionId $id): VersionNotFoundException
    {
        return new self(
            sprintf('Error 40402 - Version with ID "%s" could not be found', $id->value()),
            self::ERROR_CODE
        );
    }

    public function errorCode(): int
    {
        return self::ERROR_CODE;
    }
}
