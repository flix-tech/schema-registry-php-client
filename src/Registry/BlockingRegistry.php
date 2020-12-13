<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use AvroSchema;
use Exception;
use FlixTech\SchemaRegistryApi\AsynchronousRegistry;
use FlixTech\SchemaRegistryApi\Schema\AvroReference;
use FlixTech\SchemaRegistryApi\SynchronousRegistry;
use GuzzleHttp\Promise\PromiseInterface;
use LogicException;

/**
 * {@inheritdoc}
 */
class BlockingRegistry implements SynchronousRegistry
{
    /**
     * @var AsynchronousRegistry
     */
    private $asyncRegistry;

    public function __construct(AsynchronousRegistry $registry)
    {
        $this->asyncRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException
     * @throws Exception
     */
    public function register(string $subject, AvroSchema $schema, AvroReference ...$references): int
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->register($subject, $schema, ...$references)
        )->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException
     * @throws Exception
     */
    public function schemaId(string $subject, AvroSchema $schema): int
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaId($subject, $schema)
        )->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException
     * @throws Exception
     */
    public function schemaForId(int $schemaId): AvroSchema
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaForId($schemaId)
        )->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException
     * @throws Exception
     */
    public function schemaForSubjectAndVersion(string $subject, int $version): AvroSchema
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaForSubjectAndVersion($subject, $version)
        )->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException
     * @throws Exception
     */
    public function schemaVersion(string $subject, AvroSchema $schema): int
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->schemaVersion($subject, $schema)
        )->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException
     * @throws Exception
     */
    public function latestVersion(string $subject): AvroSchema
    {
        return $this->addExceptionThrowCallableToPromise(
            $this->asyncRegistry->latestVersion($subject)
        )->wait();
    }

    /**
     * @param PromiseInterface $promise
     *
     * @return PromiseInterface
     */
    private function addExceptionThrowCallableToPromise(PromiseInterface $promise): PromiseInterface
    {
        $throwingValueFunction = function ($value) {
            if ($value instanceof Exception) {
                throw $value;
            }

            return $value;
        };

        return $promise->then($throwingValueFunction, $throwingValueFunction);
    }
}
