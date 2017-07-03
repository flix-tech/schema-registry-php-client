<?php

namespace FlixTech\SchemaRegistryApi\Test\Model\Compatibility;

use FlixTech\SchemaRegistryApi\Model\Compatibility\Compatibility;
use FlixTech\SchemaRegistryApi\Model\Compatibility\Level;
use FlixTech\SchemaRegistryApi\Model\Subject\Name;
use FlixTech\SchemaRegistryApi\Test\AsyncClientTestCase;
use GuzzleHttp\Psr7\Response;

class CompatibilityTest extends AsyncClientTestCase
{
    /**
     * @test
     */
    public function it_should_get_default_Compatibility_Level(): array
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '{"compatibility": "FULL"}'
            )
        ];

        $compatibility = Compatibility::create($this->getClientWithMockResponses($responses));

        $this->assertEquals(Level::FULL, $compatibility->defaultLevel()->value());

        return $this->requestContainer;
    }

    /**
     * @test
     *
     * @depends it_should_get_default_Compatibility_Level
     *
     * @param \GuzzleHttp\Psr7\Request[][] $requestContainer
     */
    public function it_calls_correct_endpoints_for_Compatibility_Level(array $requestContainer)
    {
        $this->assertMethodAndUriAndBody(
            $requestContainer,
            'GET',
            '/config'
        );
    }

    /**
     * @test
     */
    public function it_should_change_default_Compatibility_Level(): array
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '{"compatibility": "BACKWARD"}'
            )
        ];

        $compatibility = Compatibility::create($this->getClientWithMockResponses($responses));

        $this->assertEquals(
            Level::BACKWARD,
            $compatibility->changeDefaultLevel(Level::create(Level::BACKWARD))->value()
        );

        return $this->requestContainer;
    }

    /**
     * @test
     *
     * @depends it_should_change_default_Compatibility_Level
     *
     * @param \GuzzleHttp\Psr7\Request[][] $requestContainer
     */
    public function it_calls_correct_endpoints_for_changing_Compatibility_Level(array $requestContainer)
    {
        $this->assertMethodAndUriAndBody(
            $requestContainer,
            'PUT',
            '/config',
            '{"compatibility":"BACKWARD"}'
        );
    }

    /**
     * @test
     */
    public function it_should_get_Subject_Compatibility_Level(): array
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '{"compatibility": "FORWARD"}'
            )
        ];

        $compatibility = Compatibility::create($this->getClientWithMockResponses($responses));

        $this->assertEquals(
            Level::FORWARD,
            $compatibility->subjectLevel(Name::create('test'))->value()
        );

        return $this->requestContainer;
    }

    /**
     * @test
     *
     * @depends it_should_get_Subject_Compatibility_Level
     *
     * @param \GuzzleHttp\Psr7\Request[][] $requestContainer
     */
    public function it_calls_correct_endpoints_for_Subject_Compatibility_Level(array $requestContainer)
    {
        $this->assertMethodAndUriAndBody(
            $requestContainer,
            'GET',
            '/config/test'
        );
    }

    /**
     * @test
     */
    public function it_should_change_Subject_Compatibility_Level(): array
    {
        $responses = [
            new Response(
                200,
                ['Content-Type' => 'application/vnd.schemaregistry.v1+json'],
                '{"compatibility": "NONE"}'
            )
        ];

        $compatibility = Compatibility::create($this->getClientWithMockResponses($responses));

        $this->assertEquals(
            Level::NONE,
            $compatibility->changeSubjectLevel(Name::create('test'), Level::create(Level::NONE))->value()
        );

        return $this->requestContainer;
    }

    /**
     * @test
     *
     * @depends it_should_change_Subject_Compatibility_Level
     *
     * @param \GuzzleHttp\Psr7\Request[][] $requestContainer
     */
    public function it_calls_correct_endpoints_for_changing_Subject_Compatibility_Level(array $requestContainer)
    {
        $this->assertMethodAndUriAndBody(
            $requestContainer,
            'PUT',
            '/config/test',
            '{"compatibility":"NONE"}'
        );
    }
}
