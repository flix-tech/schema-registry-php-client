<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

class SubjectNotFoundException extends AbstractSchemaRegistryException
{
    const ERROR_CODE = 40401;
    const ERROR_MESSAGE = 'Error 40401 - Subject could not be found';
}
