<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry\Cache;

use AvroSchema;
use AvroSchemaParseException;
use Doctrine\Common\Cache\Cache;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;

/**
 * {@inheritdoc}
 */
class DoctrineCacheAdapter implements CacheAdapter
{
    /**
     * @var Cache
     */
    private $doctrineCache;

    public function __construct(Cache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId): void
    {
        $this->doctrineCache->save((string) $schemaId, (string) $schema);
    }

    public function cacheSchemaIdByHash(int $schemaId, string $schemaHash): void
    {
        $this->doctrineCache->save($schemaHash, $schemaId);
    }

    /**
     * {@inheritdoc}
     */
    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version): void
    {
        $this->doctrineCache->save(
            $this->makeKeyFromSubjectAndVersion($subject, $version),
            (string) $schema
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws AvroSchemaParseException
     */
    public function getWithId(int $schemaId): ?AvroSchema
    {
        $rawSchema = $this->doctrineCache->fetch((string) $schemaId);

        if (!$rawSchema) {
            return null;
        }

        return AvroSchema::parse($rawSchema);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdWithHash(string $hash): ?int
    {
        $schemaId = $this->doctrineCache->fetch($hash);

        if (!$schemaId) {
            return null;
        }

        return $schemaId;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AvroSchemaParseException
     */
    public function getWithSubjectAndVersion(string $subject, int $version): ?AvroSchema
    {
        $rawSchema = $this->doctrineCache->fetch(
            $this->makeKeyFromSubjectAndVersion($subject, $version)
        );

        if (!$rawSchema) {
            return null;
        }

        return AvroSchema::parse($rawSchema);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaForId(int $schemaId): bool
    {
        return $this->doctrineCache->contains((string) $schemaId);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaIdForHash(string $schemaHash): bool
    {
        return $this->doctrineCache->contains($schemaHash);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaForSubjectAndVersion(string $subject, int $version): bool
    {
        return $this->doctrineCache->contains(
            $this->makeKeyFromSubjectAndVersion($subject, $version)
        );
    }

    /**
     * {@inheritdoc}
     */
    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sprintf('%s_%d', $subject, $version);
    }
}
