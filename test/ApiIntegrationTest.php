<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test;

use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;
use FlixTech\SchemaRegistryApi\Model\Schema\Schema;
use FlixTech\SchemaRegistryApi\Model\Subject\Name;
use FlixTech\SchemaRegistryApi\Model\Subject\Subject;

/**
 * Class ApiIntegrationTest
 *
 * @group integration
 */
class ApiIntegrationTest extends AsyncClientTestCase
{
    /**
     * @var \FlixTech\SchemaRegistryApi\AsyncHttpClient
     */
    private $client;

    protected function setUp()
    {
        $this->client = $this->getIntegrationTestClient();
    }

    /**
     * @test
     */
    public function you_should_be_able_to_add_Subject()
    {
        $this->assertEmpty(Subject::registeredSubjects($this->client));

        $schema = '{"namespace":"example.avro","type":"record","name":"user","fields":[{"name":"name","type":"string"},{"name":"favorite_number","type":"int"}]}';

        $rawSchema = RawSchema::create($schema);
        $schemaId = Subject::create($this->client, Name::create('test'))->registerSchema($rawSchema);

        $this->assertJsonStringEqualsJsonString(
            $rawSchema->value(),
            Schema::createAsync($this->client, $schemaId)->rawSchema()->value()
        );

        $this->assertEquals(
            [Name::create('test')],
            Subject::registeredSubjects($this->client)
        );
    }
}
