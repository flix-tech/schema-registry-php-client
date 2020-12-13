<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use AvroSchema;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use FlixTech\SchemaRegistryApi\Schema\AvroReference;

/**
 * Client that talk to a schema registry over http
 *
 * See http://confluent.io/docs/current/schema-registry/docs/intro.html
 * See https://github.com/confluentinc/confluent-kafka-python
 */
interface Registry
{
    /**
     * Registers a given schema with a subject
     *
     * @param string               $subject
     * @param AvroSchema           $schema
     * @param Schema\AvroReference ...$references
     *
     * @return mixed Should either return the schema id as int or a PromiseInterface
     *
     * @throws SchemaRegistryException
     */
    public function register(string $subject, AvroSchema $schema, AvroReference ...$references);

    /**
     * Look up the version of a schema for a given subject
     *
     * @param string        $subject
     * @param AvroSchema    $schema
     *
     * @return mixed Should either return the version as int or a PromiseInterface
     *
     * @throws SchemaRegistryException
     */
    public function schemaVersion(string $subject, AvroSchema $schema);

    /**
     * Fetches the latest version of a schema from a subject
     *
     * @param string $subject
     *
     * @return mixed Should either return the version as int or a PromiseInterface
     *
     * @throws SchemaRegistryException
     */
    public function latestVersion(string $subject);

    /**
     * Look up the global schema id of a schema for a given subject
     *
     * @param string        $subject
     * @param AvroSchema    $schema
     *
     * @return mixed Should either return the schema id as int or a PromiseInterface
     *
     * @throws SchemaRegistryException
     */
    public function schemaId(string $subject, AvroSchema $schema);

    /**
     * Gets an AvroSchema for a given global schema id
     *
     * @param int           $schemaId
     *
     * @return mixed Should either return the schema as AvroSchema or a PromiseInterface
     *
     * @throws SchemaRegistryException
     */
    public function schemaForId(int $schemaId);

    /**
     * Gets an AvroSchema for a given subject and version
     *
     * @param string        $subject
     * @param int           $version
     *
     * @return mixed Should either return the schema as AvroSchema or a PromiseInterface
     *
     * @throws SchemaRegistryException
     */
    public function schemaForSubjectAndVersion(string $subject, int $version);
}
