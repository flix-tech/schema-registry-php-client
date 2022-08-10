<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
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
     * @param GuzzleException $exception
     *
     * @return SchemaRegistryException
     *
     * @throws RuntimeException
     */
    public function __invoke(GuzzleException $exception): SchemaRegistryException
    {
        if (!$exception instanceof RequestException) {
            throw $exception;
        }

        $response = $this->guardAgainstMissingResponse($exception);
        $decodedBody = $this->guardAgainstMissingErrorCode($response);
        $errorCode = $decodedBody[self::ERROR_CODE_FIELD_NAME];
        $errorMessage = $decodedBody[self::ERROR_MESSAGE_FIELD_NAME];

        return $this->mapErrorCodeToException($errorCode, $errorMessage);
    }

    private function guardAgainstMissingResponse(RequestException $exception): ResponseInterface
    {
        $response = $exception->getResponse();

        if (!$response) {
            throw new RuntimeException('RequestException has no response to inspect', 0, $exception);
        }

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @return array<mixed, mixed>
     */
    private function guardAgainstMissingErrorCode(ResponseInterface $response): array
    {
        try {
            $decodedBody = \GuzzleHttp\json_decode((string) $response->getBody(), true);

            if (!is_array($decodedBody) || !array_key_exists(self::ERROR_CODE_FIELD_NAME, $decodedBody)) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid message body received - cannot find "error_code" field in response body "%s"',
                        (string) $response->getBody()
                    )
                );
            }

            return $decodedBody;
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf(
                    'Invalid message body received - cannot find "error_code" field in response body "%s"',
                    (string) $response->getBody()
                ),
                $e->getCode(),
                $e
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
}
