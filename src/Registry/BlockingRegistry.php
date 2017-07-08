<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use AvroSchema;
use FlixTech\SchemaRegistryApi\AsynchronousRegistry;
use FlixTech\SchemaRegistryApi\SynchronousRegistry;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Client that talk to a schema registry over http
 *
 * See http://confluent.io/docs/current/schema-registry/docs/intro.html
 * See https://github.com/confluentinc/confluent-kafka-python
 */
class BlockingRegistry implements SynchronousRegistry
{
    /**
     * @var \FlixTech\SchemaRegistryApi\AsynchronousRegistry
     */
    private $asyncRegistry;

    public function __construct(AsynchronousRegistry $registry)
    {
        $this->asyncRegistry = $registry;
    }

    public function register(string $subject, AvroSchema $schema, callable $requestCallback = null): int
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->register($subject, $schema, $requestCallback)
        )->wait();
    }

    public function schemaId(string $subject, AvroSchema $schema, callable $requestCallback = null): int
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaId($subject, $schema, $requestCallback)
        )->wait();
    }

    public function schemaForId(int $schemaId, callable $requestCallback = null): AvroSchema
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaForId($schemaId, $requestCallback)
        )->wait();
    }

    public function schemaForSubjectAndVersion(string $subject, int $version, callable $requestCallback = null): AvroSchema
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaForSubjectAndVersion($subject, $version, $requestCallback)
        )->wait();
    }

    public function schemaVersion(string $subject, AvroSchema $schema, callable $requestCallback = null): int
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaVersion($subject, $schema, $requestCallback)
        )->wait();
    }

    private function addExceptionThrowCallableToPromise(PromiseInterface $promise): PromiseInterface
    {
        return $promise->otherwise(function (\Exception $e) { throw $e; });
    }
}
