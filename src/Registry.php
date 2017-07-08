<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use AvroSchema;

interface Registry
{
    public function register(string $subject, AvroSchema $schema);

    public function schemaVersion(string $subject, AvroSchema $schema);

    public function schemaId(string $subject, AvroSchema $schema);

    public function schemaForId(int $schemaId);

    public function schemaForSubjectAndVersion(string $subject, int $version);
}
