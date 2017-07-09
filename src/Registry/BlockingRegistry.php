<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use AvroSchema;
use FlixTech\SchemaRegistryApi\AsynchronousRegistry;
use FlixTech\SchemaRegistryApi\SynchronousRegistry;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * {@inheritdoc}
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

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function register(string $subject, AvroSchema $schema, callable $requestCallback = null): int
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->register($subject, $schema, $requestCallback)
        )->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function schemaId(string $subject, AvroSchema $schema, callable $requestCallback = null): int
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaId($subject, $schema, $requestCallback)
        )->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function schemaForId(int $schemaId, callable $requestCallback = null): AvroSchema
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaForId($schemaId, $requestCallback)
        )->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function schemaForSubjectAndVersion(string $subject, int $version, callable $requestCallback = null): AvroSchema
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaForSubjectAndVersion($subject, $version, $requestCallback)
        )->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function schemaVersion(string $subject, AvroSchema $schema, callable $requestCallback = null): int
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaVersion($subject, $schema, $requestCallback)
        )->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    private function addExceptionThrowCallableToPromise(PromiseInterface $promise): PromiseInterface
    {
        return $promise->then(
            function ($value) {
                if ($value instanceof \Exception) {
                    throw $value;
                }

                return $value;
            }
        );
    }
}
