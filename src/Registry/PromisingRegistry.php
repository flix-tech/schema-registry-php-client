<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use AvroSchema;
use FlixTech\SchemaRegistryApi\AsynchronousRegistry;
use FlixTech\SchemaRegistryApi\Exception\ExceptionMap;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use function FlixTech\SchemaRegistryApi\Requests\checkIfSubjectHasSchemaRegisteredRequest;
use function FlixTech\SchemaRegistryApi\Requests\registerNewSchemaVersionWithSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\schemaRequest;
use function FlixTech\SchemaRegistryApi\Requests\singleSubjectVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\validateSchemaId;
use function FlixTech\SchemaRegistryApi\Requests\validateVersionId;

/**
 * Client that talk to a schema registry over http
 *
 * See http://confluent.io/docs/current/schema-registry/docs/intro.html
 * See https://github.com/confluentinc/confluent-kafka-python
 */
class PromisingRegistry implements AsynchronousRegistry
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function register(string $subject, AvroSchema $schema, callable $requestCallback = null): PromiseInterface
    {
        $request = registerNewSchemaVersionWithSubjectRequest((string) $schema, $subject);

        return $this->client
            ->sendAsync(
                null !== $requestCallback ? $requestCallback($request) : $request
            )->then(
                function (ResponseInterface $response) {
                    $schemaId = \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['id'];

                    return $schemaId;
                },
                function (RequestException $exception) {
                    return (new ExceptionMap())($exception);
                }
            );
    }

    public function schemaId(string $subject, AvroSchema $schema, callable $requestCallback = null): PromiseInterface
    {
        $request = checkIfSubjectHasSchemaRegisteredRequest($subject, (string) $schema);

        return $this->client
            ->sendAsync(
                null !== $requestCallback ? $requestCallback($request) : $request
            )->then(
                function (ResponseInterface $response) {
                    $decodedResponse = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

                    return $decodedResponse['id'];
                },
                function (RequestException $exception) {
                    return (new ExceptionMap())($exception);
                }
            );
    }

    public function schemaForId(int $schemaId, callable $requestCallback = null): PromiseInterface
    {
        $request = schemaRequest(validateSchemaId($schemaId));

        return $this->client
            ->sendAsync(
                null !== $requestCallback ? $requestCallback($request) : $request
            )->then(
                function (ResponseInterface $response) {
                    $schema = AvroSchema::parse(
                        \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['schema']
                    );

                    return $schema;
                },
                function (RequestException $exception) {
                    return (new ExceptionMap())($exception);
                }
            );
    }

    public function schemaForSubjectAndVersion(string $subject, int $version, callable $requestCallback = null): PromiseInterface
    {
        $request = singleSubjectVersionRequest($subject, validateVersionId($version));

        return $this->client
            ->sendAsync(null !== $requestCallback ? $requestCallback($request) : $request)
            ->then(
                function (ResponseInterface $response) {
                    $schema = AvroSchema::parse(
                        \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['schema']
                    );

                    return $schema;
                },
                function (RequestException $exception) {
                    return (new ExceptionMap())($exception);
                }
            );
    }

    public function schemaVersion(string $subject, AvroSchema $schema, callable $requestCallback = null): PromiseInterface
    {
        $request = checkIfSubjectHasSchemaRegisteredRequest($subject, (string) $schema);

        return $this->client
            ->sendAsync(null !== $requestCallback ? $requestCallback($request) : $request)
            ->then(
                function (ResponseInterface $response) {
                    return \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['version'];
                },
                function (RequestException $exception) {
                    return (new ExceptionMap())($exception);
                }
            );
    }
}
