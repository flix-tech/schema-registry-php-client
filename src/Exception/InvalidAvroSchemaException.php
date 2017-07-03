<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;


class InvalidAvroSchemaException extends AbstractSchemaRegistryException
{
    const ERROR_CODE = 42201;
    const ERROR_MESSAGE = 'Error 42201 - Invalid Avro schema';
}
