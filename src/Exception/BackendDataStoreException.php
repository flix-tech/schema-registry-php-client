<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

class BackendDataStoreException extends AbstractSchemaRegistryException
{
    const ERROR_CODE = 50001;
    const ERROR_MESSAGE = 'Error code 50001 – Error in the backend datastore';
}
