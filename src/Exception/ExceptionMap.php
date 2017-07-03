<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use GuzzleHttp\Exception\RequestException;

final class ExceptionMap
{
    public function __invoke(RequestException $exception)
    {
        $decodedBody = \GuzzleHttp\json_decode($exception->getResponse()->getBody()->getContents(), true);

        if (!array_key_exists('error_code', $decodedBody)) {
            throw new \RuntimeException('Unknown Error');
        }

        $errorCode = $decodedBody['error_code'];

        switch ($errorCode) {
            case IncompatibleAvroSchemaException::ERROR_CODE:
                throw new IncompatibleAvroSchemaException($exception);

            case BackendDataStoreException::ERROR_CODE:
                throw new BackendDataStoreException($exception);

            case OperationTimedOutException::ERROR_CODE:
                throw new OperationTimedOutException($exception);

            case MasterProxyException::ERROR_CODE:
                throw new MasterProxyException($exception);

            case InvalidVersionException::ERROR_CODE:
                throw new InvalidVersionException($exception);

            case InvalidAvroSchemaException::ERROR_CODE:
                throw new InvalidAvroSchemaException($exception);

            case SchemaNotFoundException::ERROR_CODE:
                throw new SchemaNotFoundException($exception);

            case SubjectNotFoundException::ERROR_CODE:
                throw new SubjectNotFoundException($exception);

            case VersionNotFoundException::ERROR_CODE:
                throw new VersionNotFoundException($exception);

            case InvalidCompatibilityException::ERROR_CODE:
                throw new InvalidCompatibilityException($exception);

            default:
                throw new \RuntimeException('Unknown Error');
        }
    }
}
