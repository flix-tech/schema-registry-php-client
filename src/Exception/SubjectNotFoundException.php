<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use FlixTech\SchemaRegistryApi\Model\Subject\Name;

class SubjectNotFoundException extends \RuntimeException implements SchemaRegistryException
{
    const ERROR_CODE = 40401;

    public static function create(Name $name): SubjectNotFoundException
    {
        return new self(
            sprintf('Error 40401 - Subject with name "%s" could not be found', $name->name()),
            self::ERROR_CODE
        );
    }

    public function errorCode(): int
    {
        return self::ERROR_CODE;
    }
}
