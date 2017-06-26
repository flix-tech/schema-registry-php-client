<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Model\Subject;

use FlixTech\SchemaRegistryApi\Model\Subject\Name;
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

        $api = $this->getApiWithMockResponses($responses);

        $subjects = $api->registeredSubjectNames();

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
        $this->assertMethodAndUri($requestContainer, 'GET', '/subjects');
    }

    /**
     * @test
     */
    public function it_should_return_single_Subject()
    {
        $api = $this->getApiWithMockResponses([]);

        $name = Name::create('subject');
        $subject = $api->subject($name);

        $this->assertTrue($subject->name()->equals($name));
    }

    /**
     * @test
     */
    public function it_should_find_all_VersionId()
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '[1, 2, 3]'
            )
        ];

        $api = $this->getApiWithMockResponses($responses);

        $name = Name::create('subject');
        $subject = $api->subject($name);

        $versions = $subject->versions();

        $this->assertCount(3, $versions);

        /** @var VersionId $versionId */
        foreach ($versions as $i => $versionId) {
            $this->assertInstanceOf(VersionId::class, $versionId);
            $this->assertEquals($i + 1, $versionId->value());
        }
    }

    /**
     * @test
     */
    public function it_should_get_a_specific_Version()
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '{"name": "test","version": 1,"schema": "{\"type\": \"string\"}"}'
            )
        ];

        $api = $this->getApiWithMockResponses($responses);
        $name = Name::create('test');
        $subject = $api->subject($name);

        $versionId = VersionId::create(1);
        $version = $subject->version($versionId);

        $this->assertTrue($version->id()->equals($versionId));
        $this->assertTrue($version->subjectName()->equals($name));
        $this->assertEquals('{"type": "string"}', $version->schema()->value());
    }
}
