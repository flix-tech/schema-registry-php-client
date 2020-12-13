<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use AvroSchema;
use FlixTech\SchemaRegistryApi\Schema\AvroReference;

/**
 * {@inheritdoc}
 */
interface SynchronousRegistry extends Registry
{
    /**
     * {@inheritdoc}
     *
     * @return int The schema id of the registered AvroSchema
     */
    public function register(string $subject, AvroSchema $schema, AvroReference ...$references): int;

    /**
     * {@inheritdoc}
     *
     * @return int The schema version of this AvroSchema for the given subject
     */
    public function schemaVersion(string $subject, AvroSchema $schema): int;

    /**
     * {@inheritdoc}
     *
     * @return AvroSchema The latest schema for the given subject
     */
    public function latestVersion(string $subject): AvroSchema;

    /**
     * {@inheritdoc}
     *
     * @return int The schema id of the registered AvroSchema
     */
    public function schemaId(string $subject, AvroSchema $schema): int;

    /**
     * {@inheritdoc}
     *
     * @return AvroSchema The schema for the given schema id
     */
    public function schemaForId(int $schemaId): AvroSchema;

    /**
     * {@inheritdoc}
     *
     * @return AvroSchema The schema for the given subject and version
     */
    public function schemaForSubjectAndVersion(string $subject, int $version): AvroSchema;
}
