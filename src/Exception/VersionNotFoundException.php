<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

class VersionNotFoundException extends AbstractSchemaRegistryException
{
    const ERROR_CODE = 40402;
    const ERROR_MESSAGE = 'Error 40402 - Version could not be found';
}
