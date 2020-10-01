<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Exception;

use FlixTech\SchemaRegistryApi\Exception\AbstractSchemaRegistryException;
use FlixTech\SchemaRegistryApi\Exception\BackendDataStoreException;
use FlixTech\SchemaRegistryApi\Exception\ExceptionMap;
use FlixTech\SchemaRegistryApi\Exception\IncompatibleAvroSchemaException;
use FlixTech\SchemaRegistryApi\Exception\InvalidAvroSchemaException;
use FlixTech\SchemaRegistryApi\Exception\InvalidCompatibilityLevelException;
use FlixTech\SchemaRegistryApi\Exception\InvalidVersionException;
use FlixTech\SchemaRegistryApi\Exception\MasterProxyException;
use FlixTech\SchemaRegistryApi\Exception\OperationTimedOutException;
use FlixTech\SchemaRegistryApi\Exception\SchemaNotFoundException;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use FlixTech\SchemaRegistryApi\Exception\SubjectNotFoundException;
use FlixTech\SchemaRegistryApi\Exception\VersionNotFoundException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ExceptionMapTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_handle_InvalidAvroSchema_code(): void
    {
        $this->assertSchemaRegistryException(
            InvalidAvroSchemaException::class,
            'Invalid Avro schema',
            42201,
            (ExceptionMap::instance())(
                new RequestException(
                '422 Unprocessable Entity',
                    new Request('GET', '/'),
                    new Response(
                        422,
                        ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                        '{"error_code":42201,"message": "Invalid Avro schema"}'
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_handle_IncompatibleAvroSchema_code(): void
    {
        $this->assertSchemaRegistryException(
            IncompatibleAvroSchemaException::class,
            'Incompatible Avro schema',
            409,
            (ExceptionMap::instance())(
                new RequestException(
                    '409 Conflict',
                    new Request('GET', '/'),
                    new Response(
                        409,
                        ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                        '{"error_code":409,"message": "Incompatible Avro schema"}'
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_handle_BackendDataStore_code(): void
    {
        $this->assertSchemaRegistryException(
            BackendDataStoreException::class,
            'Error in the backend datastore',
            50001,
            (ExceptionMap::instance())(
                new RequestException(
                    '500 Internal Server Error',
                    new Request('GET', '/'),
                    new Response(
                        500,
                        ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                        '{"error_code":50001,"message": "Error in the backend datastore"}'
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_handle_InvalidCompatibilityLevel_code(): void
    {
        $this->assertSchemaRegistryException(
            InvalidCompatibilityLevelException::class,
            'Invalid compatibility level',
            42203,
            (ExceptionMap::instance())(
                new RequestException(
                    '422 Unprocessable Entity',
                    new Request('GET', '/'),
                    new Response(
                        422,
                        ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                        '{"error_code":42203,"message": "Invalid compatibility level"}'
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_handle_InvalidVersion_code(): void
    {
        $this->assertSchemaRegistryException(
            InvalidVersionException::class,
            'Invalid version',
            42202,
            (ExceptionMap::instance())(
                new RequestException(
                    '422 Unprocessable Entity',
                    new Request('GET', '/'),
                    new Response(
                        422,
                        ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                        '{"error_code":42202,"message": "Invalid version"}'
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_handle_MasterProxy_code(): void
    {
        $this->assertSchemaRegistryException(
            MasterProxyException::class,
            'Error while forwarding the request to the master',
            50003,
            (ExceptionMap::instance())(
                new RequestException(
                    '500 Internal server Error',
                    new Request('GET', '/'),
                    new Response(
                        500,
                        ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                        '{"error_code":50003,"message": "Error while forwarding the request to the master"}'
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_handle_OperationTimedOut_code(): void
    {
        $this->assertSchemaRegistryException(
            OperationTimedOutException::class,
            'Operation timed out',
            50002,
            (ExceptionMap::instance())(
                new RequestException(
                    '500 Internal server Error',
                    new Request('GET', '/'),
                    new Response(
                        500,
                        ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                        '{"error_code":50002,"message": "Operation timed out"}'
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_handle_SchemaNotFound_code(): void
    {
        $this->assertSchemaRegistryException(
            SchemaNotFoundException::class,
            'Schema not found',
            40403,
            (ExceptionMap::instance())(
                new RequestException(
                    '404 Not Found',
                    new Request('GET', '/'),
                    new Response(
                        404,
                        ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                        '{"error_code":40403,"message": "Schema not found"}'
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_handle_SubjectNotFound_code(): void
    {
        $this->assertSchemaRegistryException(
            SubjectNotFoundException::class,
            'Subject not found',
            40401,
            (ExceptionMap::instance())(
                new RequestException(
                    '404 Not Found',
                    new Request('GET', '/'),
                    new Response(
                        404,
                        ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                        '{"error_code":40401,"message": "Subject not found"}'
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_handle_VersionNotFound_code(): void
    {
        $this->assertSchemaRegistryException(
            VersionNotFoundException::class,
            'Version not found',
            40402,
            (ExceptionMap::instance())(
                new RequestException(
                    '404 Not Found',
                    new Request('GET', '/'),
                    new Response(
                        404,
                        ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                        '{"error_code":40402,"message": "Version not found"}'
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_not_process_exceptions_with_missing_response(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("RequestException has no response to inspect");

        (ExceptionMap::instance())(
            new RequestException(
                '404 Not Found',
                new Request('GET', '/')
            )
        );
    }

    /**
     * @test
     */
    public function it_will_check_for_invalid_schema_registry_exceptions_not_defining_a_code(): void
    {
        $this->expectException(LogicException::class);
        InvalidNewSchemaRegistryException::errorCode();
    }

    /**
     * @test
     */
    public function it_should_not_process_exceptions_with_missing_error_codes(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid message body received - cannot find "error_code" field in response body');

        (ExceptionMap::instance())(
            new RequestException(
                '404 Not Found',
                new Request('GET', '/'),
                new Response(
                    404,
                    ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                    '{"message": "This JSON has no \'error_code\' field."}'
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_should_not_process_unknown_error_codes(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown error code "99999"');

        (ExceptionMap::instance())(
            new RequestException(
                '404 Not Found',
                new Request('GET', '/'),
                new Response(
                    404,
                    ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                    '{"error_code":99999,"message": "Subject not found"}'
                )
            )
        );
    }

    private function assertSchemaRegistryException(
        string $exceptionClass,
        string $expectedMessage,
        int $errorCode,
        SchemaRegistryException $exception
    ): void
    {
        self::assertInstanceOf($exceptionClass, $exception);
        self::assertEquals($errorCode, $exception->getCode());
        self::assertEquals($expectedMessage, $exception->getMessage());
    }
}

class InvalidNewSchemaRegistryException extends AbstractSchemaRegistryException
{
}
