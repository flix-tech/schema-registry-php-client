<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Model\Subject;

use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;
use FlixTech\SchemaRegistryApi\Model\Subject\Name;
use FlixTech\SchemaRegistryApi\Model\Subject\Subject;
use FlixTech\SchemaRegistryApi\Model\Subject\VersionId;
use FlixTech\SchemaRegistryApi\Test\ApiTestCase;
use GuzzleHttp\Psr7\Response;

class SubjectTest extends ApiTestCase
{
    /**
     * @test
     */
    public function it_should_return_all_SubjectNames(): array
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '["subject0", "subject1"]'
            )
        ];

        $subjects = Subject::registeredSubjects($this->getClientWithMockResponses($responses));

        $this->assertCount(2, $subjects);

        /** @var \FlixTech\SchemaRegistryApi\Model\Subject\Name $subjectName */
        foreach ($subjects as $i => $subjectName) {
            $this->assertInstanceOf(Name::class, $subjectName);
            $this->assertEquals('subject' . $i, $subjectName->name());
        }

        return $this->requestContainer;
    }

    /**
     * @test
     *
     * @depends it_should_return_all_SubjectNames
     *
     * @param \GuzzleHttp\Psr7\Request[][] $requestContainer
     */
    public function it_should_call_the_correct_endpoints_for_SubjectNames_resource(array $requestContainer)
    {
        $this->assertMethodAndUriAndBody($requestContainer, 'GET', '/subjects');
    }

    /**
     * @test
     */
    public function it_should_return_single_Subject()
    {
        $name = Name::create('subject');
        $subject = new Subject($this->getClientWithMockResponses([]), $name);

        $this->assertTrue($subject->name()->equals($name));
    }

    /**
     * @test
     */
    public function it_should_find_all_VersionId(): array
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '[1, 2, 3]'
            )
        ];

        $name = Name::create('subject');
        $subject = new Subject($this->getClientWithMockResponses($responses), $name);

        $versions = $subject->versions();

        $this->assertCount(3, $versions);

        /** @var VersionId $versionId */
        foreach ($versions as $i => $versionId) {
            $this->assertInstanceOf(VersionId::class, $versionId);
            $this->assertEquals($i + 1, $versionId->value());
        }

        return $this->requestContainer;
    }

    /**
     * @test
     *
     * @depends it_should_find_all_VersionId
     *
     * @param \GuzzleHttp\Psr7\Request[][] $requestContainer
     */
    public function it_should_call_the_correct_endpoints_for_Versions_resource(array $requestContainer)
    {
        $this->assertMethodAndUriAndBody($requestContainer, 'GET', '/subjects/subject/versions');
    }

    /**
     * @test
     */
    public function it_should_get_a_specific_Version(): array
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '{"name": "test","version": 1,"schema": "{\"type\": \"string\"}"}'
            )
        ];

        $name = Name::create('test');
        $subject = new Subject($this->getClientWithMockResponses($responses), $name);

        $versionId = VersionId::create(1);
        $version = $subject->version($versionId);

        $this->assertTrue($version->id()->equals($versionId));
        $this->assertTrue($version->subjectName()->equals($name));
        $this->assertEquals('{"type": "string"}', $version->schema()->value());

        return $this->requestContainer;
    }

    /**
     * @test
     *
     * @depends it_should_get_a_specific_Version
     *
     * @param \GuzzleHttp\Psr7\Request[][] $requestContainer
     */
    public function it_should_call_the_correct_endpoints_for_Version_resource(array $requestContainer)
    {
        $this->assertMethodAndUriAndBody($requestContainer, 'GET', '/subjects/test/versions/1');
    }

    /**
     * @test
     */
    public function it_can_register_new_RawSchema()
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '{"id": 1}'
            )
        ];

        $name = Name::create('test');
        $subject = new Subject($this->getClientWithMockResponses($responses), $name);
        $rawSchema = RawSchema::create('{"type": "test"}');

        $schemaId = $subject->registerSchema($rawSchema);
        $this->assertEquals(1, $schemaId->value());
    }
}
