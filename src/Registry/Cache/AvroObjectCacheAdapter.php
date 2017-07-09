<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry\Cache;

use AvroSchema;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;

class AvroObjectCacheAdapter implements CacheAdapter
{
    /**
     * @var AvroSchema[]
     */
    private $idToSchema = [];

    /**
     * @var AvroSchema[]
     */
    private $subjectVersionToSchema = [];

    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId)
    {
        $this->idToSchema[$schemaId] = $schema;
    }

    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version)
    {
        $this->subjectVersionToSchema[$this->makeKeyFromSubjectAndVersion($subject, $version)] = $schema;
    }

    public function getWithId(int $schemaId)
    {
        if (!$this->hasSchemaForId($schemaId)) {
            return null;
        }

        return $this->idToSchema[$schemaId];
    }

    public function getWithSubjectAndVersion(string $subject, int $version)
    {
        $key = $this->makeKeyFromSubjectAndVersion($subject, $version);

        if (!$this->hasSchemaForSubjectAndVersion($subject, $version)) {
            return null;
        }

        return $this->subjectVersionToSchema[$key];
    }

    public function hasSchemaForId(int $schemaId): bool
    {
        return array_key_exists($schemaId, $this->idToSchema);
    }

    public function hasSchemaForSubjectAndVersion(string $subject, int $version): bool
    {
        return array_key_exists($this->makeKeyFromSubjectAndVersion($subject, $version), $this->subjectVersionToSchema);
    }

    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sprintf('%s_%d', $subject, $version);
    }
}
