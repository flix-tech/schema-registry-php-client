<?php

namespace FlixTech\SchemaRegistryApi\Exception;

class OperationTimedOutException extends AbstractSchemaRegistryException
{
    const ERROR_CODE = 50002;
    const ERROR_MESSAGE = 'Error 50002 - Operation timed out';
}
