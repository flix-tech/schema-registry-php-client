<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry;

use AvroSchema;
use AvroSchemaParseException;
use Exception;
use FlixTech\SchemaRegistryApi\Constants;
use FlixTech\SchemaRegistryApi\Exception\SchemaNotFoundException;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use FlixTech\SchemaRegistryApi\Registry\GuzzlePromiseAsyncRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use const FlixTech\SchemaRegistryApi\Constants\ACCEPT;
use const FlixTech\SchemaRegistryApi\Constants\CONTENT_TYPE;
use function FlixTech\SchemaRegistryApi\Requests\checkIfSubjectHasSchemaRegisteredRequest;
use function FlixTech\SchemaRegistryApi\Requests\registerNewSchemaVersionWithSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\schemaRequest;
use function FlixTech\SchemaRegistryApi\Requests\singleSubjectVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\validateSchemaId;
use function FlixTech\SchemaRegistryApi\Requests\validateVersionId;

class GuzzlePromiseAsyncRegistryTest extends TestCase
{

    /**
     * @var GuzzlePromiseAsyncRegistry
     */
    private $registry;

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws AvroSchemaParseException
     */
    public function it_should_register_schemas(): void
    {
        $responses = [
            new Response(200, [], '{"id": 3}')
        ];
        $subject = 'test';
        $schema = AvroSchema::parse('{"type": "string"}');
        $expectedRequest = registerNewSchemaVersionWithSubjectRequest((string) $schema, $subject);

        $container = [];
        $client = $this->clientWithMockResponses($responses, $container);

        $this->registry = new GuzzlePromiseAsyncRegistry($client);

        $promise = $this->registry->register($subject, $schema);

        self::assertEquals(3, $promise->wait());
        $this->assertRequestCallable($expectedRequest)($container[0]['request']);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws AvroSchemaParseException
     */
    public function it_can_get_the_schema_id_for_a_schema_and_subject(): void
    {
        $responses = [
            new Response(200, [], '{"id": 2}')
        ];
        $subject = 'test';
        $schema = AvroSchema::parse('{"type": "string"}');
        $expectedRequest = checkIfSubjectHasSchemaRegisteredRequest($subject, (string) $schema);

        $container = [];
        $this->registry = new GuzzlePromiseAsyncRegistry($this->clientWithMockResponses($responses, $container));

        $promise = $this->registry->schemaId($subject, $schema);

        self::assertEquals(2, $promise->wait());
        $this->assertRequestCallable($expectedRequest)($container[0]['request']);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws AvroSchemaParseException
     */
    public function it_can_get_a_schema_for_id(): void
    {
        $responses = [
            new Response(200, [], '{"schema": "\"string\""}')
        ];
        $schema = AvroSchema::parse('"string"');
        $expectedRequest = schemaRequest(validateSchemaId(1));

        $container = [];
        $this->registry = new GuzzlePromiseAsyncRegistry($this->clientWithMockResponses($responses, $container));

        $promise = $this->registry->schemaForId(1);

        self::assertEquals($schema, $promise->wait());
        $this->assertRequestCallable($expectedRequest)($container[0]['request']);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws AvroSchemaParseException
     */
    public function it_can_get_a_schema_for_subject_and_version(): void
    {
        $responses = [
            new Response(200, [], '{"schema": "\"string\""}')
        ];
        $subject = 'test';
        $version = 2;
        $schema = AvroSchema::parse('{"type": "string"}');
        $expectedRequest = singleSubjectVersionRequest($subject, validateVersionId($version));

        $container = [];
        $this->registry = new GuzzlePromiseAsyncRegistry($this->clientWithMockResponses($responses, $container));

        $promise = $this->registry->schemaForSubjectAndVersion($subject, $version);

        self::assertEquals($schema, $promise->wait());
        $this->assertRequestCallable($expectedRequest)($container[0]['request']);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws AvroSchemaParseException
     */
    public function it_can_get_the_schema_version(): void
    {
        $responses = [
            new Response(200, [], '{"version": 3}')
        ];
        $subject = 'test';
        $schema = AvroSchema::parse('{"type": "string"}');
        $expectedRequest = checkIfSubjectHasSchemaRegisteredRequest($subject, (string) $schema);

        $container = [];
        $this->registry = new GuzzlePromiseAsyncRegistry($this->clientWithMockResponses($responses, $container));

        $promise = $this->registry->schemaVersion($subject, $schema);

        self::assertEquals(3, $promise->wait());
        $this->assertRequestCallable($expectedRequest)($container[0]['request']);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws AvroSchemaParseException
     */
    public function it_can_get_the_latest_version(): void
    {
        $responses = [
            new Response(200, [], '{"schema": "\"string\""}')
        ];

        $subject = 'test';
        $schema = AvroSchema::parse('{"type": "string"}');
        $expectedRequest = singleSubjectVersionRequest($subject, Constants::VERSION_LATEST);

        $container = [];
        $this->registry = new GuzzlePromiseAsyncRegistry($this->clientWithMockResponses($responses, $container));

        $promise = $this->registry->latestVersion($subject);

        self::assertEquals($schema, $promise->wait());
        $this->assertRequestCallable($expectedRequest)($container[0]['request']);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     */
    public function it_will_not_throw_but_pass_exceptions(): void
    {
        $responses = [
            new Response(
                404,
                [],
                sprintf('{"error_code": %d, "message": "test"}', SchemaNotFoundException::ERROR_CODE)
            )
        ];

        $this->registry = new GuzzlePromiseAsyncRegistry($this->clientWithMockResponses($responses));

        /** @var Exception $exception */
        $exception = $this->registry->schemaForId(1)->wait();

        self::assertInstanceOf(SchemaNotFoundException::class, $exception);
        self::assertEquals('test', $exception->getMessage());
    }

    /**
     * @param ResponseInterface[] $responses
     * @param array $container
     *
     * @return Client
     */
    private function clientWithMockResponses(array $responses, array &$container = []): Client
    {
        $history = Middleware::history($container);

        $mockHandler = new MockHandler($responses);
        $stack = HandlerStack::create($mockHandler);
        $stack->push($history);

        return new Client(['handler' => $stack]);
    }

    private function assertRequestCallable(RequestInterface $expectedRequest): callable
    {
        return function (RequestInterface  $actual) use ($expectedRequest) {
            $this->assertEquals($expectedRequest->getUri(), $actual->getUri());
            $this->assertEquals($expectedRequest->getHeader(ACCEPT), $actual->getHeader(ACCEPT));
            $this->assertEquals($expectedRequest->getHeader(CONTENT_TYPE), $actual->getHeader(CONTENT_TYPE));
            $this->assertEquals($expectedRequest->getMethod(), $actual->getMethod());
            $this->assertEquals($expectedRequest->getBody()->getContents(), $actual->getBody()->getContents());

            return $actual;
        };
    }
}
