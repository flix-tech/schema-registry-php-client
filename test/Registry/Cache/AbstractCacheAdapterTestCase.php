<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry\Cache;

use AvroSchema;
use AvroSchemaParseException;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;
use PHPUnit\Framework\TestCase;

abstract class AbstractCacheAdapterTestCase extends TestCase
{
    abstract protected function getAdapter(): CacheAdapter;

    /**
     * @var CacheAdapter
     */
    protected $cacheAdapter;

    protected function setUp(): void
    {
        $this->cacheAdapter = $this->getAdapter();
    }

    /**
     * @test
     * @throws AvroSchemaParseException
     */
    public function it_should_store_and_fetch_schemas_with_ids(): void
    {
        $schemaId = 3;
        $invalidSchemaId = 2;
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->cacheAdapter->cacheSchemaWithId($schema, $schemaId);

        self::assertFalse($this->cacheAdapter->hasSchemaForId($invalidSchemaId));
        self::assertTrue($this->cacheAdapter->hasSchemaForId($schemaId));

        self::assertNull($this->cacheAdapter->getWithId($invalidSchemaId));
        self::assertEquals($schema, $this->cacheAdapter->getWithId($schemaId));
    }

    /**
     * @test
     * @throws AvroSchemaParseException
     */
    public function it_should_store_and_fetch_schemas_with_subject_and_version(): void
    {
        $subject = 'test';
        $version = 2;
        $invalidSubject = 'none';
        $schema = AvroSchema::parse('{"type": "string"}');

        $this->cacheAdapter->cacheSchemaWithSubjectAndVersion($schema, $subject, $version);

        self::assertFalse($this->cacheAdapter->hasSchemaForSubjectAndVersion($invalidSubject, $version));
        self::assertTrue($this->cacheAdapter->hasSchemaForSubjectAndVersion($subject, $version));

        self::assertNull($this->cacheAdapter->getWithSubjectAndVersion($invalidSubject, $version));
        self::assertEquals($schema, $this->cacheAdapter->getWithSubjectAndVersion($subject, $version));
    }

    /**
     * @test
     */
    public function it_should_store_and_fetch_schema_ids_with_schema_hashes(): void
    {
        $schemaId = 3;
        $hash = 'hash';
        $anotherHash = 'another';

        self::assertFalse($this->cacheAdapter->hasSchemaIdForHash($hash));
        self::assertFalse($this->cacheAdapter->hasSchemaIdForHash($anotherHash));

        $this->cacheAdapter->cacheSchemaIdByHash($schemaId, $hash);

        self::assertTrue($this->cacheAdapter->hasSchemaIdForHash($hash));
        self::assertFalse($this->cacheAdapter->hasSchemaIdForHash($anotherHash));

        self::assertNull($this->cacheAdapter->getIdWithHash($anotherHash));
        self::assertSame($schemaId, $this->cacheAdapter->getIdWithHash($hash));
    }
}
