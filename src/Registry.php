<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use AvroSchema;

interface Registry
{
    public function register(string $subject, AvroSchema $schema, callable $requestCallback = null);

    public function schemaVersion(string $subject, AvroSchema $schema, callable $requestCallback = null);

    public function schemaId(string $subject, AvroSchema $schema,callable $requestCallback = null);

    public function schemaForId(int $schemaId, callable $requestCallback = null);

    public function schemaForSubjectAndVersion(string $subject, int $version, callable $requestCallback = null);
}
