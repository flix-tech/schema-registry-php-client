<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry\Cache;

use AvroSchema;
use Doctrine\Common\Cache\Cache;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;

class DoctrineCacheAdapter implements CacheAdapter
{
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $doctrineCache;

    /**
     * @param \Doctrine\Common\Cache\Cache $doctrineCache
     */
    public function __construct(Cache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;
    }

    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId)
    {
        $this->doctrineCache->save($schemaId, (string) $schema);
    }

    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version)
    {
        $this->doctrineCache->save(
            $this->makeKeyFromSubjectAndVersion($subject, $version),
            (string) $schema
        );
    }

    public function getWithId(int $schemaId)
    {
        $rawSchema = $this->doctrineCache->fetch($schemaId);

        if (!$rawSchema) {
            return null;
        }

        return AvroSchema::parse($rawSchema);
    }

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

    public function hasSchemaForId(int $schemaId): bool
    {
        return $this->doctrineCache->contains($schemaId);
    }

    public function hasSchemaForSubjectAndVersion(string $subject, int $version): bool
    {
        return $this->doctrineCache->contains(
            $this->makeKeyFromSubjectAndVersion($subject, $version)
        );
    }

    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sprintf('%s_%d', $subject, $version);
    }
}
