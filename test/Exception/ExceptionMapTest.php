<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Exception;

use FlixTech\SchemaRegistryApi\Exception\ExceptionMap;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ExceptionMapTest extends TestCase
{
    /**
     * @test
     *
     * @expectedException \FlixTech\SchemaRegistryApi\Exception\InvalidAvroSchemaException
     */
    public function it_should_handle_InvalidAvroSchema_code()
    {
        (new ExceptionMap())(
            new RequestException(
            '422 Unprocessable Entity',
                new Request('GET', '/'),
                new Response(
                    422,
                    ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                    '{"error_code":42201,"message": "Invalid Avro schema"}'
                )
            )
        );
    }

    /**
     * @test
     *
     * @expectedException \FlixTech\SchemaRegistryApi\Exception\IncompatibleAvroSchemaException
     */
    public function it_should_handle_IncompatibleAvroSchema_code()
    {
        (new ExceptionMap())(
            new RequestException(
                '409 Conflict',
                new Request('GET', '/'),
                new Response(
                    409,
                    ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                    '{"error_code":409,"message": "Incompatible Avro schema"}'
                )
            )
        );
    }

    /**
     * @test
     *
     * @expectedException \FlixTech\SchemaRegistryApi\Exception\BackendDataStoreException
     */
    public function it_should_handle_BackendDataStore_code()
    {
        (new ExceptionMap())(
            new RequestException(
                '500 Internal Server Error',
                new Request('GET', '/'),
                new Response(
                    500,
                    ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                    '{"error_code":50001,"message": "Error in the backend datastore"}'
                )
            )
        );
    }
}
