<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry;

use AvroSchema;
use AvroSchemaParseException;
use FlixTech\SchemaRegistryApi\Constants;
use FlixTech\SchemaRegistryApi\Exception\SchemaNotFoundException;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use FlixTech\SchemaRegistryApi\Registry\Psr18SyncRegistry;
use FlixTech\SchemaRegistryApi\Requests;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function FlixTech\SchemaRegistryApi\Requests\checkIfSubjectHasSchemaRegisteredRequest;
use function FlixTech\SchemaRegistryApi\Requests\schemaRequest;
use function FlixTech\SchemaRegistryApi\Requests\validateSchemaId;
use function FlixTech\SchemaRegistryApi\Requests\validateVersionId;

class Psr18SyncRegistryTest extends TestCase
{
    /**
     * @var Psr18SyncRegistry
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
        $expectedRequest = Requests::registerNewSchemaVersionWithSubjectRequest((string)$schema, $subject);

        $container = [];
        $this->registry = new Psr18SyncRegistry($this->clientWithMockResponses($responses, $container));

        self::assertEquals(3, $this->registry->register($subject, $schema));
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
        $this->registry = new Psr18SyncRegistry($this->clientWithMockResponses($responses, $container));

        self::assertEquals(2, $this->registry->schemaId($subject, $schema));
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
        $this->registry = new Psr18SyncRegistry($this->clientWithMockResponses($responses, $container));

        self::assertEquals($schema, $this->registry->schemaForId(1));
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
        $expectedRequest = Requests::singleSubjectVersionRequest($subject, validateVersionId($version));

        $container = [];
        $this->registry = new Psr18SyncRegistry($this->clientWithMockResponses($responses, $container));

        self::assertEquals($schema, $this->registry->schemaForSubjectAndVersion($subject, $version));
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
        $this->registry = new Psr18SyncRegistry($this->clientWithMockResponses($responses, $container));

        self::assertEquals(3, $this->registry->schemaVersion($subject, $schema));
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
        $expectedRequest = Requests::singleSubjectVersionRequest($subject, Constants::VERSION_LATEST);

        $container = [];
        $this->registry = new Psr18SyncRegistry($this->clientWithMockResponses($responses, $container));

        self::assertEquals($schema, $this->registry->latestVersion($subject));
        $this->assertRequestCallable($expectedRequest)($container[0]['request']);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     */
    public function it_will_throw_exceptions(): void
    {
        $this->expectException(SchemaNotFoundException::class);

        $responses = [
            new Response(
                404,
                [],
                sprintf('{"error_code": %d, "message": "test"}', SchemaNotFoundException::ERROR_CODE)
            )
        ];

        $this->registry = new Psr18SyncRegistry($this->clientWithMockResponses($responses));

        $this->registry->schemaForId(1);
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
            $this->assertEquals($expectedRequest->getHeader(Constants::ACCEPT), $actual->getHeader(Constants::ACCEPT));
            $this->assertEquals($expectedRequest->getHeader(Constants::CONTENT_TYPE), $actual->getHeader(Constants::CONTENT_TYPE));
            $this->assertEquals($expectedRequest->getMethod(), $actual->getMethod());
            $this->assertEquals($expectedRequest->getBody()->getContents(), $actual->getBody()->getContents());

            return $actual;
        };
    }
}
