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
    public function it_should_return_all_SubjectNames()
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
}
