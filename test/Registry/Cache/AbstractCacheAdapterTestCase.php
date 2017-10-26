<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry\Cache;

use AvroSchema;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;
use PHPUnit\Framework\TestCase;

abstract class AbstractCacheAdapterTestCase extends TestCase
{
    abstract protected function getAdapter(): CacheAdapter;

    /**
     * @var CacheAdapter
     */
    protected $cacheAdapter;

    protected function setUp()
    {
        $this->cacheAdapter = $this->getAdapter();
    }

    /**
     * @test
     */
    public function it_should_store_and_fetch_schemas_with_ids()
    {
        $schemaId = 3;
        $invalidSchemaId = 2;
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->cacheAdapter->cacheSchemaWithId($schema, $schemaId);

        $this->assertFalse($this->cacheAdapter->hasSchemaForId($invalidSchemaId));
        $this->assertTrue($this->cacheAdapter->hasSchemaForId($schemaId));

        $this->assertNull($this->cacheAdapter->getWithId($invalidSchemaId));
        $this->assertEquals($schema, $this->cacheAdapter->getWithId($schemaId));
    }

    /**
     * @test
     */
    public function it_should_store_and_fetch_schemas_with_subject_and_version()
    {
        $subject = 'test';
        $version = 2;
        $invalidSubject = 'none';
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->cacheAdapter->cacheSchemaWithSubjectAndVersion($schema, $subject, $version);

        $this->assertFalse($this->cacheAdapter->hasSchemaForSubjectAndVersion($invalidSubject, $version));
        $this->assertTrue($this->cacheAdapter->hasSchemaForSubjectAndVersion($subject, $version));

        $this->assertNull($this->cacheAdapter->getWithSubjectAndVersion($invalidSubject, $version));
        $this->assertEquals($schema, $this->cacheAdapter->getWithSubjectAndVersion($subject, $version));
    }

    /**
     * @test
     */
    public function it_should_store_and_fetch_schema_ids_with_schema_hashes()
    {
        $schemaId = 3;
        $hash = 'hash';
        $anotherHash = 'another';

        $this->assertFalse($this->cacheAdapter->hasSchemaIdForHash($hash));
        $this->assertFalse($this->cacheAdapter->hasSchemaIdForHash($anotherHash));

        $this->cacheAdapter->cacheSchemaIdByHash($schemaId, $hash);

        $this->assertTrue($this->cacheAdapter->hasSchemaIdForHash($hash));
        $this->assertFalse($this->cacheAdapter->hasSchemaIdForHash($anotherHash));

        $this->assertNull($this->cacheAdapter->getIdWithHash($anotherHash));
        $this->assertSame($schemaId, $this->cacheAdapter->getIdWithHash($hash));
    }
}
