<?php

namespace FlixTech\SchemaRegistryApi\Test\Model\Schema\Promised;

use FlixTech\SchemaRegistryApi\Model\Schema\Promised\RawSchema;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class RawSchemaTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_be_fulfilled_by_Promise()
    {
        $response = new Response(
            200,
            ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
            '{"schema": "{\"type\": \"string\"}"}'
        );

        $promise = new FulfilledPromise($response);

        $schema = RawSchema::withPromise($promise);
        $this->assertEquals('{"type": "string"}', $schema->value());
    }
}
