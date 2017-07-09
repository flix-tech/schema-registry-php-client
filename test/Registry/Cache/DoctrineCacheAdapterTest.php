<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry\Cache;

use AvroSchema;
use Doctrine\Common\Cache\Cache;
use FlixTech\SchemaRegistryApi\Registry\Cache\DoctrineCacheAdapter;
use PHPUnit\Framework\TestCase;

class DoctrineCacheAdapterTest extends TestCase
{
    /**
     * @var Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $doctrineCache;

    /**
     * @var DoctrineCacheAdapter
     */
    private $cacheAdapter;

    protected function setUp()
    {
        $this->doctrineCache = $this->getMockForAbstractClass(Cache::class);
        $this->cacheAdapter = new DoctrineCacheAdapter($this->doctrineCache);
    }

    /**
     * @test
     */
    public function it_should_store_schemas_with_ids()
    {
        $schemaId = 3;
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->doctrineCache
            ->expects($this->once())
            ->method('save')
            ->with(3, (string) $schema);

        $this->cacheAdapter->cacheSchemaWithId($schema, $schemaId);
    }

    /**
     * @test
     */
    public function it_should_fetch_schemas_with_id()
    {
        $schemaId = 3;
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->doctrineCache
            ->expects($this->exactly(2))
            ->method('fetch')
            ->with($schemaId)
            ->willReturnOnConsecutiveCalls((string) $schema, null);

        $this->assertEquals($schema, $this->cacheAdapter->getWithId($schemaId));
        $this->assertNull($this->cacheAdapter->getWithId($schemaId));
    }

    /**
     * @test
     */
    public function it_can_check_if_a_schema_exists_for_id()
    {
        $schemaId = 3;

        $this->doctrineCache
            ->expects($this->once())
            ->method('contains')
            ->with($schemaId)
            ->willReturn(true);

        $this->assertTrue($this->cacheAdapter->hasSchemaForId($schemaId));
    }

    /**
     * @test
     */
    public function it_can_cache_schemas_by_subject_and_version()
    {
        $version = 3;
        $subject = 'test';
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->doctrineCache
            ->expects($this->once())
            ->method('save')
            ->with($subject . '_' . $version, (string) $schema);

        $this->cacheAdapter->cacheSchemaWithSubjectAndVersion($schema, $subject, $version);
    }

    /**
     * @test
     */
    public function it_can_fetch_schemas_by_subject_and_version()
    {
        $version = 3;
        $subject = 'test';
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->doctrineCache
            ->expects($this->exactly(2))
            ->method('fetch')
            ->with($subject . '_' . $version)
            ->willReturnOnConsecutiveCalls($schema, null);

        $this->assertEquals(
            $schema,
            $this->cacheAdapter->getWithSubjectAndVersion($subject, $version)
        );
        $this->assertNull($this->cacheAdapter->getWithSubjectAndVersion($subject, $version));
    }

    /**
     * @test
     */
    public function it_can_check_if_a_schema_exists_for_subject_and_version()
    {
        $version = 3;
        $subject = 'test';

        $this->doctrineCache
            ->expects($this->once())
            ->method('contains')
            ->with($subject . '_' . $version)
            ->willReturn(true);

        $this->assertTrue($this->cacheAdapter->hasSchemaForSubjectAndVersion($subject, $version));
    }
}
