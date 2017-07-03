<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

class SchemaNotFoundException extends AbstractSchemaRegistryException
{
    const ERROR_CODE = 40403;
    const ERROR_MESSAGE = 'Error 40403 - Schema could not be found';
}
