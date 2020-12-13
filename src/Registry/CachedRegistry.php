<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use AvroSchema;
use Exception;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use FlixTech\SchemaRegistryApi\Registry;
use FlixTech\SchemaRegistryApi\Schema\AvroReference;
use GuzzleHttp\Promise\PromiseInterface;
use function call_user_func;

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

    /**
     * @var callable
     */
    private $hashAlgoFunc;

    public function __construct(Registry $registry, CacheAdapter $cacheAdapter, callable $hashAlgoFunc = null)
    {
        $this->registry = $registry;
        $this->cacheAdapter = $cacheAdapter;

        if (!$hashAlgoFunc) {
            $hashAlgoFunc = static function (AvroSchema $schema) {
                return md5((string) $schema);
            };
        }

        $this->hashAlgoFunc = $hashAlgoFunc;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function register(string $subject, AvroSchema $schema, AvroReference ...$references)
    {
        $closure = function ($schemaId) use ($schema) {
            if ($schemaId instanceof SchemaRegistryException) {
                return $schemaId;
            }

            $this->cacheAdapter->cacheSchemaWithId($schema, $schemaId);
            $this->cacheAdapter->cacheSchemaIdByHash($schemaId, $this->getSchemaHash($schema));

            return $schemaId;
        };

        return $this->applyValueHandlers(
            $this->registry->register($subject, $schema, ...$references),
            static function (PromiseInterface $promise) use ($closure) {
                return $promise->then($closure);
            },
            $closure
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function schemaVersion(string $subject, AvroSchema $schema)
    {
        $closure = function ($version) use ($schema, $subject) {
            if ($version instanceof SchemaRegistryException) {
                return $version;
            }

            $this->cacheAdapter->cacheSchemaWithSubjectAndVersion($schema, $subject, $version);

            return $version;
        };

        return $this->applyValueHandlers(
            $this->registry->schemaVersion($subject, $schema),
            static function (PromiseInterface $promise) use ($closure) {
                return $promise->then($closure);
            },
            $closure
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function schemaId(string $subject, AvroSchema $schema)
    {
        $schemaHash = $this->getSchemaHash($schema);

        if ($this->cacheAdapter->hasSchemaIdForHash($schemaHash)) {
            return $this->cacheAdapter->getIdWithHash($schemaHash);
        }

        $closure = function ($schemaId) use ($schema, $schemaHash) {
            if ($schemaId instanceof SchemaRegistryException) {
                return $schemaId;
            }

            $this->cacheAdapter->cacheSchemaWithId($schema, $schemaId);
            $this->cacheAdapter->cacheSchemaIdByHash($schemaId, $schemaHash);

            return $schemaId;
        };

        return $this->applyValueHandlers(
            $this->registry->schemaId($subject, $schema),
            static function (PromiseInterface $promise) use ($closure) {
                return $promise->then($closure);
            },
            $closure
        );
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function schemaForId(int $schemaId)
    {
        if ($this->cacheAdapter->hasSchemaForId($schemaId)) {
            return $this->cacheAdapter->getWithId($schemaId);
        }

        $closure = function ($schema) use ($schemaId) {
            if ($schema instanceof SchemaRegistryException) {
                return $schema;
            }

            $this->cacheAdapter->cacheSchemaWithId($schema, $schemaId);
            $this->cacheAdapter->cacheSchemaIdByHash($schemaId, $this->getSchemaHash($schema));

            return $schema;
        };

        return $this->applyValueHandlers(
            $this->registry->schemaForId($schemaId),
            static function (PromiseInterface $promise) use ($closure) {
                return $promise->then($closure);
            },
            $closure
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function schemaForSubjectAndVersion(string $subject, int $version)
    {
        if ($this->cacheAdapter->hasSchemaForSubjectAndVersion($subject, $version)) {
            return $this->cacheAdapter->getWithSubjectAndVersion($subject, $version);
        }

        $closure = function ($schema) use ($subject, $version) {
            if ($schema instanceof SchemaRegistryException) {
                return $schema;
            }

            $this->cacheAdapter->cacheSchemaWithSubjectAndVersion($schema, $subject, $version);

            return $schema;
        };

        return $this->applyValueHandlers(
            $this->registry->schemaForSubjectAndVersion($subject, $version),
            static function (PromiseInterface $promise) use ($closure) {
                return $promise->then($closure);
            },
            $closure
        );
    }

    /**
     * The latest version should not be cached, it might already be replaced by a newly registered 'latest' version.
     *
     * {@inheritdoc}
     */
    public function latestVersion(string $subject)
    {
        return $this->registry->latestVersion($subject);
    }

    /**
     * @param PromiseInterface|Exception|mixed  $value
     * @param callable                           $promiseHandler
     * @param callable                           $valueHandler
     *
     * @return mixed
     *
     * @throws Exception
     */
    private function applyValueHandlers($value, callable $promiseHandler, callable $valueHandler)
    {
        if ($value instanceof PromiseInterface) {
            return $promiseHandler($value);
        }

        if ($value instanceof Exception) {
            throw $value;
        }

        return $valueHandler($value);
    }

    private function getSchemaHash(AvroSchema $schema): string
    {
        return call_user_func($this->hashAlgoFunc, $schema);
    }
}
