<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

class InvalidCompatibilityException extends AbstractSchemaRegistryException
{
    const ERROR_CODE = 42203;
    const ERROR_MESSAGE = 'Error 42203 - Invalid compatibility level';
}
