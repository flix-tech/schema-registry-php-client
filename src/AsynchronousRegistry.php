<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use AvroSchema;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * {@inheritdoc}
 */
interface AsynchronousRegistry extends Registry
{
    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the schema id as int when fulfilled, or a SchemaRegistryException object when rejected
     */
    public function register(string $subject, AvroSchema $schema, callable $requestCallback = null): PromiseInterface;

    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the version as int when fulfilled, or a SchemaRegistryException object when rejected
     */
    public function schemaVersion(string $subject, AvroSchema $schema, callable $requestCallback = null): PromiseInterface;

    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the schema id as int when fulfilled, or a SchemaRegistryException object when rejected
     */
    public function schemaId(string $subject, AvroSchema $schema, callable $requestCallback = null): PromiseInterface;

    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the schema as AvroSchema when fulfilled, or a SchemaRegistryException object when rejected
     */
    public function schemaForId(int $schemaId, callable $requestCallback = null): PromiseInterface;

    /**
     * {@inheritdoc}
     *
     * @return PromiseInterface Either the schema as AvroSchema when fulfilled, or a SchemaRegistryException object when rejected
     */
    public function schemaForSubjectAndVersion(string $subject, int $version, callable $requestCallback = null): PromiseInterface;
}
