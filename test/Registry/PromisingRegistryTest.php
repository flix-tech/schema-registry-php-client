<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry;

use AvroSchema;
use AvroSchemaParseException;
use Exception;
use FlixTech\SchemaRegistryApi\Exception\SchemaNotFoundException;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use const FlixTech\SchemaRegistryApi\Constants\VERSION_LATEST;
use function FlixTech\SchemaRegistryApi\Requests\checkIfSubjectHasSchemaRegisteredRequest;
use function FlixTech\SchemaRegistryApi\Requests\registerNewSchemaVersionWithSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\schemaRequest;
use function FlixTech\SchemaRegistryApi\Requests\singleSubjectVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\validateSchemaId;
use function FlixTech\SchemaRegistryApi\Requests\validateVersionId;

class PromisingRegistryTest extends TestCase
{

    /**
     * @var PromisingRegistry
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

        $this->registry = new PromisingRegistry($this->clientWithMockResponses($responses));

        $promise = $this->registry->register(
            $subject,
            $schema,
            $this->assertRequestCallable($expectedRequest)
        );

        self::assertEquals(3, $promise->wait());
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

        $this->registry = new PromisingRegistry($this->clientWithMockResponses($responses));

        $promise = $this->registry->schemaId(
            $subject,
            $schema,
            $this->assertRequestCallable($expectedRequest)
        );

        self::assertEquals(2, $promise->wait());
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

        $this->registry = new PromisingRegistry($this->clientWithMockResponses($responses));

        $promise = $this->registry->schemaForId(
            1,
            $this->assertRequestCallable($expectedRequest)
        );

        self::assertEquals($schema, $promise->wait());
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

        $this->registry = new PromisingRegistry($this->clientWithMockResponses($responses));

        $promise = $this->registry->schemaForSubjectAndVersion(
            $subject,
            $version,
            $this->assertRequestCallable($expectedRequest)
        );

        self::assertEquals($schema, $promise->wait());
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

        $this->registry = new PromisingRegistry($this->clientWithMockResponses($responses));

        $promise = $this->registry->schemaVersion(
            $subject,
            $schema,
            $this->assertRequestCallable($expectedRequest)
        );

        self::assertEquals(3, $promise->wait());
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
        $expectedRequest = singleSubjectVersionRequest($subject, VERSION_LATEST);

        $this->registry = new PromisingRegistry($this->clientWithMockResponses($responses));

        $promise = $this->registry->latestVersion(
            $subject,
            $this->assertRequestCallable($expectedRequest)
        );

        self::assertEquals($schema, $promise->wait());
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
        $this->registry = new PromisingRegistry($this->clientWithMockResponses($responses));

        /** @var Exception $exception */
        $exception = $this->registry->schemaForId(1)->wait();

        self::assertInstanceOf(SchemaNotFoundException::class, $exception);
        self::assertEquals('test', $exception->getMessage());
    }

    /**
     * @param ResponseInterface[] $responses
     *
     * @return Client
     */
    private function clientWithMockResponses(array $responses): Client
    {
        $mockHandler = new MockHandler($responses);
        $stack = HandlerStack::create($mockHandler);

        $clientMock = new Client(['handler' => $stack]);

        return $clientMock;
    }

    private function assertRequestCallable(RequestInterface $expectedRequest): callable
    {
        return function (RequestInterface $actual) use ($expectedRequest) {
            $this->assertEquals($expectedRequest->getUri(), $actual->getUri());
            $this->assertEquals($expectedRequest->getHeaders(), $actual->getHeaders());
            $this->assertEquals($expectedRequest->getMethod(), $actual->getMethod());
            $this->assertEquals($expectedRequest->getBody()->getContents(), $actual->getBody()->getContents());

            return $actual;
        };
    }
}
