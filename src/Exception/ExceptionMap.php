<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use GuzzleHttp\Exception\RequestException;

final class ExceptionMap
{
    const UNKNOWN_ERROR_MESSAGE = 'Unknown Error';
    const ERROR_CODE_FIELD_NAME = 'error_code';
    const ERROR_MESSAGE_FIELD_NAME = 'message';

    /**
     * @var \FlixTech\SchemaRegistryApi\Exception\ExceptionMap
     */
    private static $instance;

    public static function instance(): ExceptionMap
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    /**
     * @param \GuzzleHttp\Exception\RequestException $exception
     *
     * @return \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     *
     * @throws \RuntimeException
     */
    public function __invoke(RequestException $exception): SchemaRegistryException
    {
        $response = $exception->getResponse();

        $this->guardAgainstMissingResponse($response);

        $decodedBody = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        $this->guardAgainstMissingErrorCode($decodedBody);

        $errorCode = $decodedBody[self::ERROR_CODE_FIELD_NAME];
        $errorMessage = $decodedBody[self::ERROR_MESSAGE_FIELD_NAME];

        return $this->mapErrorCodeToException($errorCode, $errorMessage);
    }

    private function guardAgainstMissingResponse($response)
    {
        if (!$response) {
            throw new \RuntimeException(self::UNKNOWN_ERROR_MESSAGE);
        }
    }

    private function guardAgainstMissingErrorCode(array $decodedBody)
    {
        if (!array_key_exists(self::ERROR_CODE_FIELD_NAME, $decodedBody)) {
            throw new \RuntimeException(self::UNKNOWN_ERROR_MESSAGE);
        }
    }

    private function mapErrorCodeToException($errorCode, $errorMessage)
    {
        switch ($errorCode) {
            case IncompatibleAvroSchemaException::errorCode():
                return new IncompatibleAvroSchemaException($errorMessage, $errorCode);

            case BackendDataStoreException::errorCode():
                return new BackendDataStoreException($errorMessage, $errorCode);

            case OperationTimedOutException::errorCode():
                return new OperationTimedOutException($errorMessage, $errorCode);

            case MasterProxyException::errorCode():
                return new MasterProxyException($errorMessage, $errorCode);

            case InvalidVersionException::errorCode():
                return new InvalidVersionException($errorMessage, $errorCode);

            case InvalidAvroSchemaException::errorCode():
                return new InvalidAvroSchemaException($errorMessage, $errorCode);

            case SchemaNotFoundException::errorCode():
                return new SchemaNotFoundException($errorMessage, $errorCode);

            case SubjectNotFoundException::errorCode():
                return new SubjectNotFoundException($errorMessage, $errorCode);

            case VersionNotFoundException::errorCode():
                return new VersionNotFoundException($errorMessage, $errorCode);

            case InvalidCompatibilityLevelException::errorCode():
                return new InvalidCompatibilityLevelException($errorMessage, $errorCode);

            default:
                return new \RuntimeException(self::UNKNOWN_ERROR_MESSAGE);
        }
    }
}
