<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

class InvalidVersionException extends AbstractSchemaRegistryException
{
    const ERROR_CODE = 42202;
    const ERROR_MESSAGE = 'Error 42202 - Version ID is invalid';
}
