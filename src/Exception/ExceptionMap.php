<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Exception;

use Exception;
use FlixTech\SchemaRegistryApi\Json;
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

    /**
     * @var array<int, callable>
     */
    private $map;

    private function __construct()
    {
        $factoryFn = static function (string $exceptionClass): callable {
            return static function (int $errorCode, string $errorMessage) use ($exceptionClass): SchemaRegistryException {
                /** @var SchemaRegistryException $e */
                $e = new $exceptionClass($errorMessage, $errorCode);

                return $e;
            };
        };

        $this->map = [
            IncompatibleAvroSchemaException::errorCode() => $factoryFn(IncompatibleAvroSchemaException::class),
            BackendDataStoreException::errorCode() => $factoryFn(BackendDataStoreException::class),
            OperationTimedOutException::errorCode() => $factoryFn(OperationTimedOutException::class),
            MasterProxyException::errorCode() => $factoryFn(MasterProxyException::class),
            InvalidVersionException::errorCode() => $factoryFn(InvalidVersionException::class),
            InvalidAvroSchemaException::errorCode() => $factoryFn(InvalidAvroSchemaException::class),
            SchemaNotFoundException::errorCode() => $factoryFn(SchemaNotFoundException::class),
            SubjectNotFoundException::errorCode() => $factoryFn(SubjectNotFoundException::class),
            VersionNotFoundException::errorCode() => $factoryFn(VersionNotFoundException::class),
            InvalidCompatibilityLevelException::errorCode() => $factoryFn(InvalidCompatibilityLevelException::class),
        ];
    }

    /**
     * Maps a ResponseInterface to the internal SchemaRegistryException types.
     *
     * @param ResponseInterface $response
     *
     * @return SchemaRegistryException
     *
     * @throws RuntimeException
     */
    public function exceptionFor(ResponseInterface $response): SchemaRegistryException
    {
        $decodedBody = $this->guardAgainstMissingErrorCode($response);
        $errorCode = $decodedBody[self::ERROR_CODE_FIELD_NAME];
        $errorMessage = $decodedBody[self::ERROR_MESSAGE_FIELD_NAME] ?? "Unknown Error";

        return $this->mapErrorCodeToException($errorCode, $errorMessage);
    }

    public function hasMappableError(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();

        return $statusCode >= 400 && $statusCode < 600;
    }

    /**
     * @param ResponseInterface $response
     * @return array<string, mixed>
     */
    private function guardAgainstMissingErrorCode(ResponseInterface $response): array
    {
        try {
            $decodedBody = Json::decode((string)$response->getBody());

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
        if (!array_key_exists($errorCode, $this->map)) {
            throw new RuntimeException(sprintf('Unknown error code "%d"', $errorCode));
        }

        return $this->map[$errorCode]($errorCode, $errorMessage);
    }
}
