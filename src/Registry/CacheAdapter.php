<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use AvroSchema;

interface CacheAdapter
{
    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId);
    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version);

    /**
     * @param int $schemaId
     *
     * @return AvroSchema|null
     */
    public function getWithId(int $schemaId);

    /**
     * @param string $subject
     * @param int    $version
     *
     * @return AvroSchema|null
     */
    public function getWithSubjectAndVersion(string $subject, int $version);

    public function hasSchemaForId(int $schemaId): bool;
    public function hasSchemaForSubjectAndVersion(string $subject, int $version): bool;
}
