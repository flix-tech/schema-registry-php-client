<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry\Cache;

use AvroSchema;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;
use Psr\SimpleCache\CacheInterface;

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
     */
    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId): void
    {
        $this->cache->set((string) $schemaId, (string) $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function cacheSchemaIdByHash(int $schemaId, string $schemaHash): void
    {
        $this->cache->set($schemaHash, $schemaId);
    }

    /**
     * {@inheritdoc}
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
     * @throws \AvroSchemaParseException
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

        return $this->cache->get($hash);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \AvroSchemaParseException
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
     */
    public function hasSchemaForId(int $schemaId): bool
    {
        return null !== $this->cache->get((string) $schemaId);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaIdForHash(string $schemaHash): bool
    {
        return null !== $this->cache->get($schemaHash);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaForSubjectAndVersion(string $subject, int $version): bool
    {
        $schema = $this->cache->get(
            $this->makeKeyFromSubjectAndVersion($subject, $version)
        );

        return null !== $schema;
    }

    /**
     * {@inheritdoc}
     */
    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sprintf('%s_%d', $subject, $version);
    }
}
