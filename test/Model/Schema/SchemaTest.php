<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test;

use FlixTech\SchemaRegistryApi\Model\Schema\SchemaId;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class SchemaTest extends ApiTestCase
{
    /**
     * @test
     */
    public function it_should_create_a_Schema_from_SchemaId()
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '{"schema": "{\"type\": \"string\"}"}'
            )
        ];

        $api = $this->getApiWithMockResponses($responses);
        $id = SchemaId::create(1);
        $schema = $api->schema($id);

        $this->assertTrue($schema->getId()->equals($id));
        $this->assertEquals('{"type": "string"}', $schema->rawSchema()->value());
        $this->assertEquals('GET', $this->requestContainer[0]['request']->getMethod());
        $this->assertEquals('/schemas/ids/1', $this->requestContainer[0]['request']->getUri());
        $this->assertEquals(
            ['application/vnd.schemaregistry.v1+json'],
            $this->requestContainer[0]['request']->getHeader('Accept')
        );
    }

    /**
     * @test
     *
     * @expectedException \FlixTech\SchemaRegistryApi\Exception\SchemaNotFoundException
     */
    public function it_should_throw_SchemaNotFoundException_for_404()
    {
        $responses = [
            new RequestException(
                'Not Found',
                new Request('GET', '/schemas/ids/1'),
                new Response(
                    404,
                    ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                    '{"error_code": 40403,"message": "Error code 40403 – Schema not found"}'
                )
            )
        ];

        $api = $this->getApiWithMockResponses($responses);
        $id = SchemaId::create(1);
        $schema = $api->schema($id);
        $schema->rawSchema()->wait();
    }
}
