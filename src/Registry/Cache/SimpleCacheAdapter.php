<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry\Cache;

use AvroSchema;
use AvroSchemaParseException;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

class SimpleCacheAdapter implements CacheAdapter
{
    /**
     * @var CacheInterface $cache
     */
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId): void
    {
        $this->cache->set((string) $schemaId, (string) $schema);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function cacheSchemaIdByHash(int $schemaId, string $schemaHash): void
    {
        $this->cache->set($schemaHash, $schemaId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version): void
    {
        $this->cache->set(
            $this->makeKeyFromSubjectAndVersion($subject, $version),
            (string) $schema
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws AvroSchemaParseException|InvalidArgumentException
     */
    public function getWithId(int $schemaId): ?AvroSchema
    {
        $rawSchema = $this->cache->get((string) $schemaId);

        if (null === $rawSchema) {
            return null;
        }

        return AvroSchema::parse($rawSchema);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdWithHash(string $hash): ?int
    {
        $rawId = $this->cache->get($hash);
        
        if (null === $rawId) {
            return null;
        }
        
        return (int) $rawId;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AvroSchemaParseException|InvalidArgumentException
     */
    public function getWithSubjectAndVersion(string $subject, int $version): ?AvroSchema
    {
        $rawSchema = $this->cache->get(
            $this->makeKeyFromSubjectAndVersion($subject, $version)
        );

        if (null === $rawSchema) {
            return null;
        }

        return AvroSchema::parse($rawSchema);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function hasSchemaForId(int $schemaId): bool
    {
        return null !== $this->cache->get((string) $schemaId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function hasSchemaIdForHash(string $schemaHash): bool
    {
        return null !== $this->cache->get($schemaHash);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function hasSchemaForSubjectAndVersion(string $subject, int $version): bool
    {
        $schema = $this->cache->get(
            $this->makeKeyFromSubjectAndVersion($subject, $version)
        );

        return null !== $schema;
    }

    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sprintf('%s_%d', $subject, $version);
    }
}
