<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

class IncompatibleAvroSchemaException extends AbstractSchemaRegistryException
{
    const ERROR_CODE = 409;
    const ERROR_MESSAGE = 'Error 409 - Schema is incompatible with subject';
}
