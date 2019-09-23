<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry;

use AvroSchema;
use FlixTech\SchemaRegistryApi\Exception\SubjectNotFoundException;
use FlixTech\SchemaRegistryApi\Registry;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit\Framework\TestCase;
use const FlixTech\SchemaRegistryApi\Constants\VERSION_LATEST;

class CachedRegistryTest extends TestCase
{
    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var CacheAdapter|\PHPUnit_Framework_MockObject_MockObject
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
     * @throws \AvroSchemaParseException
     * @throws \ReflectionException
     */
    protected function setUp()
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
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_cache_from_register_responses(): void
    {
        $promise = new FulfilledPromise(4);

        $this->registryMock
            ->expects($this->exactly(2))
            ->method('register')
            ->with($this->subject, $this->schema)
            ->willReturnOnConsecutiveCalls($promise, 4);

        $this->cacheAdapter
            ->expects($this->exactly(2))
            ->method('cacheSchemaWithId')
            ->with($this->schema, 4);

        $this->cacheAdapter
            ->expects($this->exactly(2))
            ->method('cacheSchemaIdByHash')
            ->with(4, call_user_func($this->hashFunction, $this->schema));

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->register($this->subject, $this->schema);

        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertEquals(4, $promise->wait());

        $schemaId = $this->cachedRegistry->register($this->subject, $this->schema);
        $this->assertEquals(4, $schemaId);
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_cache_from_schema_version_responses(): void
    {
        $promise = new FulfilledPromise(3);

        $this->registryMock
            ->expects($this->exactly(2))
            ->method('schemaVersion')
            ->with($this->subject, $this->schema)
            ->willReturnOnConsecutiveCalls($promise, 3);

        $this->cacheAdapter
            ->expects($this->exactly(2))
            ->method('cacheSchemaWithSubjectAndVersion')
            ->with($this->schema, $this->subject, 3);

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->schemaVersion($this->subject, $this->schema);

        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertEquals(3, $promise->wait());

        $version = $this->cachedRegistry->schemaVersion($this->subject, $this->schema);
        $this->assertEquals(3, $version);
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_cache_from_schema_id_responses(): void
    {
        $promise = new FulfilledPromise(1);

        $this->registryMock
            ->expects($this->exactly(2))
            ->method('schemaId')
            ->with($this->subject, $this->schema)
            ->willReturnOnConsecutiveCalls($promise, 1);

        $this->cacheAdapter
            ->expects($this->exactly(2))
            ->method('cacheSchemaWithId')
            ->with($this->schema, 1);

        $this->cacheAdapter
            ->expects($this->exactly(2))
            ->method('cacheSchemaIdByHash')
            ->with(1, call_user_func($this->hashFunction, $this->schema));

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->schemaId($this->subject, $this->schema);

        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertEquals(1, $promise->wait());

        $version = $this->cachedRegistry->schemaId($this->subject, $this->schema);
        $this->assertEquals(1, $version);
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_return_schema_id_from_the_cache_for_schema_hash(): void
    {
        $this->registryMock
            ->expects($this->never())
            ->method('schemaId');

        $this->cacheAdapter
            ->expects($this->once())
            ->method('hasSchemaIdForHash')
            ->with(call_user_func($this->hashFunction, $this->schema))
            ->willReturn(true);

        $this->cacheAdapter
            ->expects($this->once())
            ->method('getIdWithHash')
            ->with(call_user_func($this->hashFunction, $this->schema))
            ->willReturn(3);

        $this->assertEquals(3, $this->cachedRegistry->schemaId($this->subject, $this->schema));
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_cache_schema_id_for_hash_if_cache_is_stale(): void
    {
        $promise = new FulfilledPromise(3);

        $this->registryMock
            ->expects($this->exactly(2))
            ->method('schemaId')
            ->with($this->subject, $this->schema)
            ->willReturnOnConsecutiveCalls($promise, 3);

        $this->cacheAdapter
            ->expects($this->exactly(2))
            ->method('hasSchemaIdForHash')
            ->with(call_user_func($this->hashFunction, $this->schema))
            ->willReturn(false);

        $this->cacheAdapter
            ->expects($this->never())
            ->method('getIdWithHash');

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->schemaId($this->subject, $this->schema);

        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertEquals(3, $promise->wait());

        $id = $this->cachedRegistry->schemaId($this->subject, $this->schema);
        $this->assertEquals(3, $id);
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_accept_different_hash_algo_functions(): void
    {
        $sha1HashFunction = static function (AvroSchema $schema) {
            return sha1((string) $schema);
        };

        $this->cachedRegistry = new CachedRegistry($this->registryMock, $this->cacheAdapter, $sha1HashFunction);

        $this->registryMock
            ->expects($this->never())
            ->method('schemaId');

        $this->cacheAdapter
            ->expects($this->once())
            ->method('hasSchemaIdForHash')
            ->with($sha1HashFunction($this->schema))
            ->willReturn(true);

        $this->cacheAdapter
            ->expects($this->once())
            ->method('getIdWithHash')
            ->with($sha1HashFunction($this->schema))
            ->willReturn(3);

        $this->cachedRegistry->schemaId($this->subject, $this->schema);
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_return_schema_from_the_cache_for_schema_by_id(): void
    {
        $this->registryMock
            ->expects($this->never())
            ->method('schemaForId');

        $this->cacheAdapter
            ->expects($this->once())
            ->method('hasSchemaForId')
            ->with(1)
            ->willReturn(true);

        $this->cacheAdapter
            ->expects($this->once())
            ->method('getWithId')
            ->with(1)
            ->willReturn($this->schema);

        $this->assertEquals($this->schema, $this->cachedRegistry->schemaForId(1));
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_cache_schema_for_id_responses_if_cache_is_stale(): void
    {
        $promise = new FulfilledPromise($this->schema);

        $this->registryMock
            ->expects($this->exactly(2))
            ->method('schemaForId')
            ->with(1)
            ->willReturnOnConsecutiveCalls($promise, $this->schema);

        $this->cacheAdapter
            ->expects($this->exactly(2))
            ->method('hasSchemaForId')
            ->with(1)
            ->willReturn(false);

        $this->cacheAdapter
            ->expects($this->never())
            ->method('getWithId');

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->schemaForId(1);

        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertEquals($this->schema, $promise->wait());

        $schema = $this->cachedRegistry->schemaForId(1);
        $this->assertEquals($this->schema, $schema);
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_return_schema_from_the_cache_for_schema_by_subject_and_version(): void
    {
        $this->registryMock
            ->expects($this->never())
            ->method('schemaForSubjectAndVersion');

        $this->cacheAdapter
            ->expects($this->once())
            ->method('hasSchemaForSubjectAndVersion')
            ->with($this->subject, 5)
            ->willReturn(true);

        $this->cacheAdapter
            ->expects($this->once())
            ->method('getWithSubjectAndVersion')
            ->with($this->subject, 5)
            ->willReturn($this->schema);

        $this->assertEquals($this->schema, $this->cachedRegistry->schemaForSubjectAndVersion($this->subject, 5));
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_cache_schema_for_subject_and_version_responses_if_cache_is_stale(): void
    {
        $promise = new FulfilledPromise($this->schema);

        $this->registryMock
            ->expects($this->exactly(2))
            ->method('schemaForSubjectAndVersion')
            ->with($this->subject, 4)
            ->willReturnOnConsecutiveCalls($promise, $this->schema);

        $this->cacheAdapter
            ->expects($this->exactly(2))
            ->method('hasSchemaForSubjectAndVersion')
            ->with($this->subject, 4)
            ->willReturn(false);

        $this->cacheAdapter
            ->expects($this->never())
            ->method('getWithSubjectAndVersion');

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->schemaForSubjectAndVersion($this->subject, 4);

        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertEquals($this->schema, $promise->wait());

        $schema = $this->cachedRegistry->schemaForSubjectAndVersion($this->subject, 4);
        $this->assertEquals($this->schema, $schema);
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_not_cache_latest_version_calls(): void
    {
        $promise = new FulfilledPromise($this->schema);

        $this->registryMock
            ->expects($this->exactly(2))
            ->method('latestVersion')
            ->with($this->subject)
            ->willReturnOnConsecutiveCalls($promise, $this->schema);

        $this->cacheAdapter
            ->expects($this->never())
            ->method('hasSchemaForSubjectAndVersion');

        $this->cacheAdapter
            ->expects($this->never())
            ->method('getWithSubjectAndVersion');

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->latestVersion($this->subject);

        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertEquals($this->schema, $promise->wait());

        $this->assertEquals($this->schema, $this->cachedRegistry->latestVersion($this->subject));
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_return_bool_for_schema_compatibility_calls(): void
    {
        $promise = new FulfilledPromise(true);

        $this->registryMock
            ->expects($this->once())
            ->method('checkSchemaCompatibilityAgainstVersion')
            ->with($this->schema, $this->subject, VERSION_LATEST)
            ->willReturn($promise);

        /** @var PromiseInterface $promise */
        $promise = $this->cachedRegistry->checkSchemaCompatibilityAgainstVersion($this->schema, $this->subject, VERSION_LATEST);

        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertTrue($promise->wait());
    }

    /**
     * @test
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function it_should_handle_exceptions_wrapped_in_promises_correctly(): void
    {
        $subjectNotFoundException = new SubjectNotFoundException();

        $promise = new FulfilledPromise($subjectNotFoundException);

        $this->registryMock
            ->expects($this->once())
            ->method('register')
            ->with($this->subject, $this->schema)
            ->willReturn($promise);

        $this->assertEquals(
            $this->cachedRegistry->register($this->subject, $this->schema)->wait(),
            $subjectNotFoundException
        );
    }
}
