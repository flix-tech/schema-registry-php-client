<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use AvroSchema;
use FlixTech\SchemaRegistryApi\Registry;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * {@inheritdoc}
 */
class CachedRegistry implements Registry
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CacheAdapter
     */
    private $cacheAdapter;

    public function __construct(Registry $registry, CacheAdapter $cacheAdapter)
    {
        $this->registry = $registry;
        $this->cacheAdapter = $cacheAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $subject, AvroSchema $schema, callable $requestCallback = null)
    {
        $closure = function (int $schemaId) use ($schema) {
            $this->cacheAdapter->cacheSchemaWithId($schema, $schemaId);

            return $schemaId;
        };

        return $this->applyValueHandlers(
            $this->registry->register($subject, $schema, $requestCallback),
            function (PromiseInterface $promise) use ($closure) {
                return $promise->then($closure);
            },
            $closure
        );
    }

    /**
     * {@inheritdoc}
     */
    public function schemaVersion(string $subject, AvroSchema $schema, callable $requestCallback = null)
    {
        $closure = function (int $version) use ($schema, $subject) {
            $this->cacheAdapter->cacheSchemaWithSubjectAndVersion($schema, $subject, $version);

            return $version;
        };

        return $this->applyValueHandlers(
            $this->registry->schemaVersion($subject, $schema, $requestCallback),
            function (PromiseInterface $promise) use ($closure) {
                return $promise->then($closure);
            },
            $closure
        );
    }

    /**
     * {@inheritdoc}
     */
    public function schemaId(string $subject, AvroSchema $schema, callable $requestCallback = null)
    {
        $closure = function (int $schemaId) use ($schema) {
            $this->cacheAdapter->cacheSchemaWithId($schema, $schemaId);

            return $schemaId;
        };

        return $this->applyValueHandlers(
            $this->registry->schemaId($subject, $schema, $requestCallback),
            function (PromiseInterface $promise) use ($closure) {
                return $promise->then($closure);
            },
            $closure
        );
    }

    /**
     * {@inheritdoc}
     */
    public function schemaForId(int $schemaId, callable $requestCallback = null)
    {
        if ($this->cacheAdapter->hasSchemaForId($schemaId)) {
            return $this->cacheAdapter->getWithId($schemaId);
        }

        $closure = function (AvroSchema $schema) use ($schemaId) {
            $this->cacheAdapter->cacheSchemaWithId($schema, $schemaId);

            return $schema;
        };

        return $this->applyValueHandlers(
            $this->registry->schemaForId($schemaId, $requestCallback),
            function (PromiseInterface $promise) use ($closure) {
                return $promise->then($closure);
            },
            $closure
        );
    }

    /**
     * {@inheritdoc}
     */
    public function schemaForSubjectAndVersion(string $subject, int $version, callable $requestCallback = null)
    {
        if ($this->cacheAdapter->hasSchemaForSubjectAndVersion($subject, $version)) {
            return $this->cacheAdapter->getWithSubjectAndVersion($subject, $version);
        }

        $closure = function (AvroSchema $schema) use ($subject, $version) {
            $this->cacheAdapter->cacheSchemaWithSubjectAndVersion($schema, $subject, $version);

            return $schema;
        };

        return $this->applyValueHandlers(
            $this->registry->schemaForSubjectAndVersion($subject, $version, $requestCallback),
            function (PromiseInterface $promise) use ($closure) {
                return $promise->then($closure);
            },
            $closure
        );
    }

    /**
     * {@inheritdoc}
     */
    private function applyValueHandlers($value, callable $promiseHandler, callable $valueHandler)
    {
        if ($value instanceof PromiseInterface) {
            return $promiseHandler($value);
        }

        return $valueHandler($value);
    }
}
