<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry\Cache;

use AvroSchema;
use Doctrine\Common\Cache\Cache;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;

/**
 * {@inheritdoc}
 */
class DoctrineCacheAdapter implements CacheAdapter
{
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $doctrineCache;

    public function __construct(Cache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId)
    {
        $this->doctrineCache->save($schemaId, (string) $schema);
    }

    /**
     * {@inheritdoc}
     */
    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version)
    {
        $this->doctrineCache->save(
            $this->makeKeyFromSubjectAndVersion($subject, $version),
            (string) $schema
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getWithId(int $schemaId)
    {
        $rawSchema = $this->doctrineCache->fetch($schemaId);

        if (!$rawSchema) {
            return null;
        }

        return AvroSchema::parse($rawSchema);
    }

    /**
     * {@inheritdoc}
     */
    public function getWithSubjectAndVersion(string $subject, int $version)
    {
        $rawSchema = $this->doctrineCache->fetch(
            $this->makeKeyFromSubjectAndVersion($subject, $version)
        );

        if (!$rawSchema) {
            return null;
        }

        return AvroSchema::parse(
            $rawSchema
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaForId(int $schemaId): bool
    {
        return $this->doctrineCache->contains($schemaId);
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
