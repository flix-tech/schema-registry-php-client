<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use FlixTech\SchemaRegistryApi\Json;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use function array_key_exists;
use function sprintf;

final class ExceptionMap
{
    public const UNKNOWN_ERROR_MESSAGE = 'Unknown Error';
    public const ERROR_CODE_FIELD_NAME = 'error_code';
    public const ERROR_MESSAGE_FIELD_NAME = 'message';

    /**
     * @var ExceptionMap
     */
    private static $instance;

    public static function instance(): ExceptionMap
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    /**
     * Maps a RequestException to the internal SchemaRegistryException types.
     *
     * @param ResponseInterface $response
     *
     * @return SchemaRegistryException
     */
    public function __invoke(ResponseInterface $response): SchemaRegistryException
    {
        $this->guardAgainstValidHTPPCode($response);

        $decodedBody = Json::decodeResponse($response);
        $this->guardAgainstMissingErrorCode($decodedBody);
        $errorCode = $decodedBody[self::ERROR_CODE_FIELD_NAME];
        $errorMessage = $decodedBody[self::ERROR_MESSAGE_FIELD_NAME];

        return $this->mapErrorCodeToException($errorCode, $errorMessage);
    }

    public function isHttpError(ResponseInterface $response): bool
    {
        return $response->getStatusCode() >= 400 && $response->getStatusCode() < 600;
    }

    /**
     * @param array<int|string,mixed> $decodedBody
     */
    private function guardAgainstMissingErrorCode(array $decodedBody): void
    {
        if (!is_array($decodedBody) || !array_key_exists(self::ERROR_CODE_FIELD_NAME, $decodedBody)) {
            throw new RuntimeException(
                'Invalid message body received - cannot find "error_code" field in response body.'
            );
        }
    }

    private function mapErrorCodeToException(int $errorCode, string $errorMessage): SchemaRegistryException
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
                throw new RuntimeException(sprintf('Unknown error code "%d"', $errorCode));
        }
    }

    private function guardAgainstValidHTPPCode(ResponseInterface $response): void
    {
        if (!$this->isHttpError($response)) {
            throw new RuntimeException(
                sprintf('Cannot process response without invalid HTTP code %d', $response->getStatusCode()),
            );
        }
    }
}
