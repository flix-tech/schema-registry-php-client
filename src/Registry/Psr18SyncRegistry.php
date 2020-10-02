<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use AvroSchema;
use AvroSchemaParseException;
use FlixTech\SchemaRegistryApi\Constants;
use FlixTech\SchemaRegistryApi\Exception\ExceptionMap;
use FlixTech\SchemaRegistryApi\Exception\InvalidAvroSchemaException;
use FlixTech\SchemaRegistryApi\Exception\RuntimeException;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use FlixTech\SchemaRegistryApi\Json;
use FlixTech\SchemaRegistryApi\Requests;
use FlixTech\SchemaRegistryApi\SynchronousRegistry;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
        $request = Requests::registerNewSchemaVersionWithSubjectRequest((string)$schema, $subject);

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return Json::decodeResponse($response)['id'];
    }

    public function schemaVersion(string $subject, AvroSchema $schema): int
    {
        $request = Requests::checkIfSubjectHasSchemaRegisteredRequest($subject, (string)$schema);

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return Json::decodeResponse($response)['version'];
    }

    public function latestVersion(string $subject): AvroSchema
    {
        $request = Requests::singleSubjectVersionRequest($subject, Constants::VERSION_LATEST);

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return $this->parseAvroSchema(Json::decodeResponse($response)['schema']);
    }

    public function schemaId(string $subject, AvroSchema $schema): int
    {
        $request = Requests::checkIfSubjectHasSchemaRegisteredRequest($subject, (string)$schema);

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return Json::decodeResponse($response)['id'];
    }

    public function schemaForId(int $schemaId): AvroSchema
    {
        $request = Requests::schemaRequest(Requests::validateSchemaId($schemaId));

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return $this->parseAvroSchema(Json::decodeResponse($response)['schema']);
    }

    public function schemaForSubjectAndVersion(string $subject, int $version): AvroSchema
    {
        $request = Requests::singleSubjectVersionRequest($subject, Requests::validateVersionId($version));

        $response = $this->makeRequest($request);
        $this->guardAgainstErrorResponse($response);

        return $this->parseAvroSchema(Json::decodeResponse($response)['schema']);
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
