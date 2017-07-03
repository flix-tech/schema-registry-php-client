<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test;

use FlixTech\SchemaRegistryApi\Model\Schema\Id;
use FlixTech\SchemaRegistryApi\Model\Schema\Schema;
use GuzzleHttp\Psr7\Response;

class SchemaTest extends AsyncClientTestCase
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

        $id = Id::create(1);
        $schema = Schema::createAsync($this->getClientWithMockResponses($responses), $id);

        $this->assertTrue($schema->id()->equals($id));
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
        $this->assertMethodAndUriAndBody($requestContainer, 'GET', '/schemas/ids/1');
    }
}
