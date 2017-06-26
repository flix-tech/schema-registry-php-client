<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Model\Subject;

use FlixTech\SchemaRegistryApi\Model\Subject\SubjectName;
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

        /** @var \FlixTech\SchemaRegistryApi\Model\Subject\SubjectName $subjectName */
        foreach ($subjects as $i => $subjectName) {
            $this->assertInstanceOf(SubjectName::class, $subjectName);
            $this->assertEquals('subject' . $i, $subjectName->name());
        }
    }
}
