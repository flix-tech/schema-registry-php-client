<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry;

use AvroSchema;
use FlixTech\SchemaRegistryApi\AsynchronousRegistry;
use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BlockingRegistryTest extends TestCase
{
    /**
     * @var \FlixTech\SchemaRegistryApi\AsynchronousRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asyncRegistry;

    /**
     * @var \FlixTech\SchemaRegistryApi\SynchronousRegistry
     */
    private $blockingRegistry;

    /**
     * @throws \ReflectionException
     */
    protected function setUp()
    {
        $this->asyncRegistry = $this->getMockForAbstractClass(AsynchronousRegistry::class);
        $this->blockingRegistry = new BlockingRegistry($this->asyncRegistry);
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \AvroSchemaParseException
     */
    public function it_should_register_schema(): void
    {
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->asyncRegistry
            ->expects($this->once())
            ->method('register')
            ->with('test', $schema)
            ->willReturn(new FulfilledPromise(2));

        $this->assertEquals(2, $this->blockingRegistry->register('test', $schema));
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \AvroSchemaParseException
     */
    public function it_can_get_the_schema_id_for_a_schema_and_subject(): void
    {
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->asyncRegistry
            ->expects($this->once())
            ->method('schemaId')
            ->with('test', $schema)
            ->willReturn(new FulfilledPromise(2));

        $this->assertEquals(2, $this->blockingRegistry->schemaId('test', $schema));
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \AvroSchemaParseException
     */
    public function it_can_get_a_schema_for_id(): void
    {
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->asyncRegistry
            ->expects($this->once())
            ->method('schemaForId')
            ->with(2)
            ->willReturn(new FulfilledPromise($schema));

        $this->assertEquals($schema, $this->blockingRegistry->schemaForId(2));
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \AvroSchemaParseException
     */
    public function it_can_get_a_schema_for_subject_and_version(): void
    {
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->asyncRegistry
            ->expects($this->once())
            ->method('schemaForSubjectAndVersion')
            ->with('test', 3)
            ->willReturn(new FulfilledPromise($schema));

        $this->assertEquals($schema, $this->blockingRegistry->schemaForSubjectAndVersion('test', 3));
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \AvroSchemaParseException
     */
    public function it_can_get_the_schema_version(): void
    {
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->asyncRegistry
            ->expects($this->once())
            ->method('schemaVersion')
            ->with('test', $schema)
            ->willReturn(new FulfilledPromise(4));

        $this->assertEquals(4, $this->blockingRegistry->schemaVersion('test', $schema));
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \AvroSchemaParseException
     */
    public function it_can_get_the_latest_version(): void
    {
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->asyncRegistry
            ->expects($this->once())
            ->method('latestVersion')
            ->with('test')
            ->willReturn(new FulfilledPromise($schema));

        $this->assertEquals($schema, $this->blockingRegistry->latestVersion('test'));
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage I was thrown in a test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \AvroSchemaParseException
     */
    public function it_throws_exceptions_from_promises(): void
    {
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->asyncRegistry
            ->expects($this->once())
            ->method('register')
            ->willReturn(new FulfilledPromise(new RuntimeException('I was thrown in a test')));

        $this->blockingRegistry->register('test', $schema);
    }
}
