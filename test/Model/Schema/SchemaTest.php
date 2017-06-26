<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test;

use FlixTech\SchemaRegistryApi\Model\Schema\Id;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class SchemaTest extends ApiTestCase
{
    /**
     * @test
     */
    public function it_should_create_a_Schema_from_SchemaId(): array
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '{"schema": "{\"type\": \"string\"}"}'
            )
        ];

        $api = $this->getApiWithMockResponses($responses);
        $id = Id::create(1);
        $schema = $api->schema($id);

        $this->assertTrue($schema->getId()->equals($id));
        $this->assertEquals('{"type": "string"}', $schema->rawSchema()->value());

        return $this->requestContainer;
    }

    /**
     * @test
     *
     * @depends it_should_create_a_Schema_from_SchemaId
     *
     * @param \GuzzleHttp\Psr7\Request[][] $requestContainer
     */
    public function it_should_call_the_correct_endpoints_for_the_Schema_resource(array $requestContainer)
    {
        $this->assertMethodAndUri($requestContainer, 'GET', '/schemas/ids/1');
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
        $id = Id::create(1);
        $schema = $api->schema($id);
        $schema->rawSchema()->wait();
    }
}
