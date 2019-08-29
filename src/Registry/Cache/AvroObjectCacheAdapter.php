<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry\Cache;

use AvroSchema;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;

/**
 * {@inheritdoc}
 */
class AvroObjectCacheAdapter implements CacheAdapter
{
    /**
     * @var AvroSchema[]
     */
    private $idToSchema = [];

    /**
     * @var int[]
     */
    private $hashToSchemaId = [];

    /**
     * @var AvroSchema[]
     */
    private $subjectVersionToSchema = [];

    /**
     * {@inheritdoc}
     */
    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId): void
    {
        $this->idToSchema[$schemaId] = $schema;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheSchemaIdByHash(int $schemaId, string $schemaHash): void
    {
        $this->hashToSchemaId[$schemaHash] = $schemaId;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version): void
    {
        $this->subjectVersionToSchema[$this->makeKeyFromSubjectAndVersion($subject, $version)] = $schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getWithId(int $schemaId): ?AvroSchema
    {
        if (!$this->hasSchemaForId($schemaId)) {
            return null;
        }

        return $this->idToSchema[$schemaId];
    }

    public function getIdWithHash(string $hash): ?int
    {
        if (!$this->hasSchemaIdForHash($hash)) {
            return null;
        }

        return $this->hashToSchemaId[$hash];
    }

    /**
     * {@inheritdoc}
     */
    public function getWithSubjectAndVersion(string $subject, int $version): ?AvroSchema
    {
        $key = $this->makeKeyFromSubjectAndVersion($subject, $version);

        if (!$this->hasSchemaForSubjectAndVersion($subject, $version)) {
            return null;
        }

        return $this->subjectVersionToSchema[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaForId(int $schemaId): bool
    {
        return array_key_exists($schemaId, $this->idToSchema);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaIdForHash(string $schemaHash): bool
    {
        return array_key_exists($schemaHash, $this->hashToSchemaId);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaForSubjectAndVersion(string $subject, int $version): bool
    {
        return array_key_exists($this->makeKeyFromSubjectAndVersion($subject, $version), $this->subjectVersionToSchema);
    }

    /**
     * {@inheritdoc}
     */
    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sprintf('%s_%d', $subject, $version);
    }
}
