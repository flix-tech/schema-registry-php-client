<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry;

use AvroSchema;

/**
 * An adapter to easily add caching capabilities to the `CachedRegistry`
 */
interface CacheAdapter
{
    /**
     * Caches an AvroSchema with a given global schema id
     *
     * @param AvroSchema $schema
     * @param int        $schemaId
     *
     * @return void
     */
    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId);

    /**
     * Caches an AvroSchema with a given subject and version
     *
     * @param AvroSchema $schema
     * @param string     $subject
     * @param int        $version
     *
     * @return void
     */
    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version);

    /**
     * Tries to fetch a cache with the global schema id.
     * Returns either the AvroSchema when found or `null` when not.
     *
     * @param int $schemaId
     *
     * @return AvroSchema|null
     */
    public function getWithId(int $schemaId);

    /**
     * Tries to fetch a cache with a given subject and version.
     * Returns either the AvroSchema when found or `null` when not.
     *
     * @param string $subject
     * @param int    $version
     *
     * @return AvroSchema|null
     */
    public function getWithSubjectAndVersion(string $subject, int $version);

    /**
     * Checks if the cache engine has a cached schema for a given global schema id.
     *
     * @param int $schemaId
     *
     * @return bool
     */
    public function hasSchemaForId(int $schemaId): bool;

    /**
     * Checks if the cache engine has a cached schema for a given subject and version.
     *
     * @param string $subject
     * @param int    $version
     *
     * @return bool
     */
    public function hasSchemaForSubjectAndVersion(string $subject, int $version): bool;
}
