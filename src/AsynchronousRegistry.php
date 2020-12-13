<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use AvroSchema;
use FlixTech\SchemaRegistryApi\Schema\AvroReference;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * {@inheritdoc}
 */
interface AsynchronousRegistry extends Registry
{
    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the schema id as int or a SchemaRegistryException object when fulfilled
     */
    public function register(string $subject, AvroSchema $schema, AvroReference ...$references): PromiseInterface;

    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the version as int or a SchemaRegistryException object when fulfilled
     */
    public function schemaVersion(string $subject, AvroSchema $schema): PromiseInterface;

    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the schema as AvroSchema or a SchemaRegistryException object when fulfilled
     */
    public function latestVersion(string $subject): PromiseInterface;

    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the schema id as int or a SchemaRegistryException object when fulfilled
     */
    public function schemaId(string $subject, AvroSchema $schema): PromiseInterface;

    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the schema as AvroSchema or a SchemaRegistryException object when fulfilled
     */
    public function schemaForId(int $schemaId): PromiseInterface;

    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the schema as AvroSchema or a SchemaRegistryException object when fulfilled
     */
    public function schemaForSubjectAndVersion(string $subject, int $version): PromiseInterface;
}
