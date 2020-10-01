<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry;

use AvroSchema;
use AvroSchemaParseException;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use FlixTech\SchemaRegistryApi\Exception\SubjectNotFoundException;
use FlixTech\SchemaRegistryApi\Registry;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CachedRegistryTest extends TestCase
{
    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var CacheAdapter|MockObject
     */
    private $cacheAdapter;

    /**
     * @var CachedRegistry
     */
    private $cachedRegistry;

    /**
     * @var string
     */
    private $subject = 'test';

    /**
     * @var AvroSchema
     */
    private $schema;

    /**
     * @var callable
     */
    private $hashFunction;

    /**
     * @throws AvroSchemaParseException
     */
    protected function setUp(): void
    {
        $this->schema = AvroSchema::parse('{"type": "string"}');
        $this->registryMock = $this->getMockForAbstractClass(Registry::class);
        $this->cacheAdapter = $this->getMockForAbstractClass(CacheAdapter::class);

        $this->hashFunction = static function (AvroSchema $schema) {
            return md5((string) $schema);
        };

        $this->cachedRegistry = new CachedRegistry($this->registryMock, $this->cacheAdapter);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws SchemaRegistryException
     */
    public function it_should_cache_from_register_responses(): void
    {
        $promise = new FulfilledPromise(4);

        $this->registryMock
            ->expects(self::exactly(2))
            ->method('register')
            ->with($this->subject, $this->schema)
            ->willReturnOnConsecutiveCalls($promise, 4);

        $this->cacheAdapter
            ->expects(self::exactly(2))
            ->method('cacheSchemaWithId')
            ->with($this->schema, 4);

        $this->cacheAdapter
            ->expects(self::exactly(2))
            ->method('cacheSchemaIdByHash')
            ->with(4, call_user_func($this->hashFunction, $this->schema));

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->register($this->subject, $this->schema);

        self::assertInstanceOf(PromiseInterface::class, $promise);
        self::assertEquals(4, $promise->wait());

        $schemaId = $this->cachedRegistry->register($this->subject, $this->schema);
        self::assertEquals(4, $schemaId);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws SchemaRegistryException
     */
    public function it_should_cache_from_schema_version_responses(): void
    {
        $promise = new FulfilledPromise(3);

        $this->registryMock
            ->expects(self::exactly(2))
            ->method('schemaVersion')
            ->with($this->subject, $this->schema)
            ->willReturnOnConsecutiveCalls($promise, 3);

        $this->cacheAdapter
            ->expects(self::exactly(2))
            ->method('cacheSchemaWithSubjectAndVersion')
            ->with($this->schema, $this->subject, 3);

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->schemaVersion($this->subject, $this->schema);

        self::assertInstanceOf(PromiseInterface::class, $promise);
        self::assertEquals(3, $promise->wait());

        $version = $this->cachedRegistry->schemaVersion($this->subject, $this->schema);
        self::assertEquals(3, $version);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws SchemaRegistryException
     */
    public function it_should_cache_from_schema_id_responses(): void
    {
        $promise = new FulfilledPromise(1);

        $this->registryMock
            ->expects(self::exactly(2))
            ->method('schemaId')
            ->with($this->subject, $this->schema)
            ->willReturnOnConsecutiveCalls($promise, 1);

        $this->cacheAdapter
            ->expects(self::exactly(2))
            ->method('cacheSchemaWithId')
            ->with($this->schema, 1);

        $this->cacheAdapter
            ->expects(self::exactly(2))
            ->method('cacheSchemaIdByHash')
            ->with(1, call_user_func($this->hashFunction, $this->schema));

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->schemaId($this->subject, $this->schema);

        self::assertInstanceOf(PromiseInterface::class, $promise);
        self::assertEquals(1, $promise->wait());

        $version = $this->cachedRegistry->schemaId($this->subject, $this->schema);
        self::assertEquals(1, $version);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     */
    public function it_should_return_schema_id_from_the_cache_for_schema_hash(): void
    {
        $this->registryMock
            ->expects(self::never())
            ->method('schemaId');

        $this->cacheAdapter
            ->expects(self::once())
            ->method('hasSchemaIdForHash')
            ->with(call_user_func($this->hashFunction, $this->schema))
            ->willReturn(true);

        $this->cacheAdapter
            ->expects(self::once())
            ->method('getIdWithHash')
            ->with(call_user_func($this->hashFunction, $this->schema))
            ->willReturn(3);

        self::assertEquals(3, $this->cachedRegistry->schemaId($this->subject, $this->schema));
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws SchemaRegistryException
     */
    public function it_should_cache_schema_id_for_hash_if_cache_is_stale(): void
    {
        $promise = new FulfilledPromise(3);

        $this->registryMock
            ->expects(self::exactly(2))
            ->method('schemaId')
            ->with($this->subject, $this->schema)
            ->willReturnOnConsecutiveCalls($promise, 3);

        $this->cacheAdapter
            ->expects(self::exactly(2))
            ->method('hasSchemaIdForHash')
            ->with(call_user_func($this->hashFunction, $this->schema))
            ->willReturn(false);

        $this->cacheAdapter
            ->expects(self::never())
            ->method('getIdWithHash');

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->schemaId($this->subject, $this->schema);

        self::assertInstanceOf(PromiseInterface::class, $promise);
        self::assertEquals(3, $promise->wait());

        $id = $this->cachedRegistry->schemaId($this->subject, $this->schema);
        self::assertEquals(3, $id);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     */
    public function it_should_accept_different_hash_algo_functions(): void
    {
        $sha1HashFunction = static function (AvroSchema $schema) {
            return sha1((string) $schema);
        };

        $this->cachedRegistry = new CachedRegistry($this->registryMock, $this->cacheAdapter, $sha1HashFunction);

        $this->registryMock
            ->expects(self::never())
            ->method('schemaId');

        $this->cacheAdapter
            ->expects(self::once())
            ->method('hasSchemaIdForHash')
            ->with($sha1HashFunction($this->schema))
            ->willReturn(true);

        $this->cacheAdapter
            ->expects(self::once())
            ->method('getIdWithHash')
            ->with($sha1HashFunction($this->schema))
            ->willReturn(3);

        $this->cachedRegistry->schemaId($this->subject, $this->schema);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     */
    public function it_should_return_schema_from_the_cache_for_schema_by_id(): void
    {
        $this->registryMock
            ->expects(self::never())
            ->method('schemaForId');

        $this->cacheAdapter
            ->expects(self::once())
            ->method('hasSchemaForId')
            ->with(1)
            ->willReturn(true);

        $this->cacheAdapter
            ->expects(self::once())
            ->method('getWithId')
            ->with(1)
            ->willReturn($this->schema);

        self::assertEquals($this->schema, $this->cachedRegistry->schemaForId(1));
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws SchemaRegistryException
     */
    public function it_should_cache_schema_for_id_responses_if_cache_is_stale(): void
    {
        $promise = new FulfilledPromise($this->schema);

        $this->registryMock
            ->expects(self::exactly(2))
            ->method('schemaForId')
            ->with(1)
            ->willReturnOnConsecutiveCalls($promise, $this->schema);

        $this->cacheAdapter
            ->expects(self::exactly(2))
            ->method('hasSchemaForId')
            ->with(1)
            ->willReturn(false);

        $this->cacheAdapter
            ->expects(self::never())
            ->method('getWithId');

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->schemaForId(1);

        self::assertInstanceOf(PromiseInterface::class, $promise);
        self::assertEquals($this->schema, $promise->wait());

        $schema = $this->cachedRegistry->schemaForId(1);
        self::assertEquals($this->schema, $schema);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     */
    public function it_should_return_schema_from_the_cache_for_schema_by_subject_and_version(): void
    {
        $this->registryMock
            ->expects(self::never())
            ->method('schemaForSubjectAndVersion');

        $this->cacheAdapter
            ->expects(self::once())
            ->method('hasSchemaForSubjectAndVersion')
            ->with($this->subject, 5)
            ->willReturn(true);

        $this->cacheAdapter
            ->expects(self::once())
            ->method('getWithSubjectAndVersion')
            ->with($this->subject, 5)
            ->willReturn($this->schema);

        self::assertEquals($this->schema, $this->cachedRegistry->schemaForSubjectAndVersion($this->subject, 5));
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws SchemaRegistryException
     */
    public function it_should_cache_schema_for_subject_and_version_responses_if_cache_is_stale(): void
    {
        $promise = new FulfilledPromise($this->schema);

        $this->registryMock
            ->expects(self::exactly(2))
            ->method('schemaForSubjectAndVersion')
            ->with($this->subject, 4)
            ->willReturnOnConsecutiveCalls($promise, $this->schema);

        $this->cacheAdapter
            ->expects(self::exactly(2))
            ->method('hasSchemaForSubjectAndVersion')
            ->with($this->subject, 4)
            ->willReturn(false);

        $this->cacheAdapter
            ->expects(self::never())
            ->method('getWithSubjectAndVersion');

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->schemaForSubjectAndVersion($this->subject, 4);

        self::assertInstanceOf(PromiseInterface::class, $promise);
        self::assertEquals($this->schema, $promise->wait());

        $schema = $this->cachedRegistry->schemaForSubjectAndVersion($this->subject, 4);
        self::assertEquals($this->schema, $schema);
    }

    /**
     * @test
     * @throws SchemaRegistryException
     * @throws SchemaRegistryException
     */
    public function it_should_not_cache_latest_version_calls(): void
    {
        $promise = new FulfilledPromise($this->schema);

        $this->registryMock
            ->expects(self::exactly(2))
            ->method('latestVersion')
            ->with($this->subject)
            ->willReturnOnConsecutiveCalls($promise, $this->schema);

        $this->cacheAdapter
            ->expects(self::never())
            ->method('hasSchemaForSubjectAndVersion');

        $this->cacheAdapter
            ->expects(self::never())
            ->method('getWithSubjectAndVersion');

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->latestVersion($this->subject);

        self::assertInstanceOf(PromiseInterface::class, $promise);
        self::assertEquals($this->schema, $promise->wait());

        self::assertEquals($this->schema, $this->cachedRegistry->latestVersion($this->subject));
    }

    /**
     * @test
     * @throws SchemaRegistryException
     */
    public function it_should_handle_exceptions_wrapped_in_promises_correctly(): void
    {
        $subjectNotFoundException = new SubjectNotFoundException();

        $promise = new FulfilledPromise($subjectNotFoundException);

        $this->registryMock
            ->expects(self::once())
            ->method('register')
            ->with($this->subject, $this->schema)
            ->willReturn($promise);

        self::assertEquals(
            $this->cachedRegistry->register($this->subject, $this->schema)->wait(),
            $subjectNotFoundException
        );
    }
}
