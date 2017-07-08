<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use AvroSchema;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Client that talk to a schema registry over http
 *
 * See http://confluent.io/docs/current/schema-registry/docs/intro.html
 * See https://github.com/confluentinc/confluent-kafka-python
 */
interface AsynchronousRegistry extends Registry
{
    public function register(string $subject, AvroSchema $schema, callable $requestCallback = null): PromiseInterface;

    public function schemaVersion(string $subject, AvroSchema $schema, callable $requestCallback = null): PromiseInterface;

    public function schemaId(string $subject, AvroSchema $schema, callable $requestCallback = null): PromiseInterface;

    public function schemaForId(int $schemaId, callable $requestCallback = null): PromiseInterface;

    public function schemaForSubjectAndVersion(string $subject, int $version, callable $requestCallback = null): PromiseInterface;
}
