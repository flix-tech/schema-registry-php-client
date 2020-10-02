<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use AvroSchema;
use AvroSchemaParseException;
use FlixTech\SchemaRegistryApi\Exception\ExceptionMap;
use FlixTech\SchemaRegistryApi\Exception\InvalidAvroSchemaException;
use FlixTech\SchemaRegistryApi\Exception\RuntimeException;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use FlixTech\SchemaRegistryApi\SynchronousRegistry;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use const FlixTech\SchemaRegistryApi\Constants\VERSION_LATEST;
use function FlixTech\SchemaRegistryApi\Requests\checkIfSubjectHasSchemaRegisteredRequest;
use function FlixTech\SchemaRegistryApi\Requests\decodeResponse;
use function FlixTech\SchemaRegistryApi\Requests\registerNewSchemaVersionWithSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\schemaRequest;
use function FlixTech\SchemaRegistryApi\Requests\singleSubjectVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\validateSchemaId;
use function FlixTech\SchemaRegistryApi\Requests\validateVersionId;

class Psr18SyncRegistry implements SynchronousRegistry
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ExceptionMap
     */
    private $map;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->map = ExceptionMap::instance();
    }

    public function register(string $subject, AvroSchema $schema): int
    {
        $request = registerNewSchemaVersionWithSubjectRequest((string) $schema, $subject);

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return decodeResponse($response)['id'];
    }

    public function schemaVersion(string $subject, AvroSchema $schema): int
    {
        $request = checkIfSubjectHasSchemaRegisteredRequest($subject, (string) $schema);

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return decodeResponse($response)['version'];
    }

    public function latestVersion(string $subject): AvroSchema
    {
        $request = singleSubjectVersionRequest($subject, VERSION_LATEST);

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return $this->parseAvroSchema(decodeResponse($response)['schema']);
    }

    public function schemaId(string $subject, AvroSchema $schema): int
    {
        $request = checkIfSubjectHasSchemaRegisteredRequest($subject, (string) $schema);

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return decodeResponse($response)['id'];
    }

    public function schemaForId(int $schemaId): AvroSchema
    {
        $request = schemaRequest(validateSchemaId($schemaId));

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return $this->parseAvroSchema(decodeResponse($response)['schema']);
    }

    public function schemaForSubjectAndVersion(string $subject, int $version): AvroSchema
    {
        $request = singleSubjectVersionRequest($subject, validateVersionId($version));

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return $this->parseAvroSchema(decodeResponse($response)['schema']);
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws SchemaRegistryException
     */
    private function guardAgainstErrorResponse(ResponseInterface $response): void
    {
        if ($this->map->hasMappableError($response)) {
            throw $this->map->exceptionFor($response);
        }
    }

    private function makeRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new RuntimeException(
                "Unexpected error during client request",
                RuntimeException::ERROR_CODE,
                $exception
            );
        }
    }

    private function parseAvroSchema(string $schema): AvroSchema
    {
        try {
            return AvroSchema::parse($schema);
        } catch (AvroSchemaParseException $e) {
            throw new InvalidAvroSchemaException(
                "Could not parse schema: $schema",
                InvalidAvroSchemaException::ERROR_CODE,
                $e
            );
        }
    }
}