<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

class MasterProxyException extends AbstractSchemaRegistryException
{
    const ERROR_CODE = 50003;
    const ERROR_MESSAGE = 'Error code 50003 – Error while forwarding the request to the master';
}
