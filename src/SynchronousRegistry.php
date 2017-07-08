<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use AvroSchema;

/**
 * Client that talk to a schema registry over http
 *
 * See http://confluent.io/docs/current/schema-registry/docs/intro.html
 * See https://github.com/confluentinc/confluent-kafka-python
 */
interface SynchronousRegistry extends Registry
{
    public function register(string $subject, AvroSchema $schema): int;

    public function schemaVersion(string $subject, AvroSchema $schema): int;

    public function schemaId(string $subject, AvroSchema $schema): int;

    public function schemaForId(int $schemaId): AvroSchema;

    public function schemaForSubjectAndVersion(string $subject, int $version): AvroSchema;
}
