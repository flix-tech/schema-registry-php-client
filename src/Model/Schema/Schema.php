<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Schema;

use FlixTech\SchemaRegistryApi\AsyncHttpClient;
use FlixTech\SchemaRegistryApi\Exception\ExceptionMap;
use function FlixTech\SchemaRegistryApi\Requests\schemaRequest;

final class Schema
{
    /**
     * @var RawSchema
     */
    private $rawSchema;

    /**
     * @var Id
     */
    private $id;

    public static function createWithSchema(RawSchema $schema, Id $id): Schema
    {
        $instance = new self();

        $instance->id = $id;
        $instance->rawSchema = $schema;

        return $instance;
    }

    public static function createAsync(AsyncHttpClient $client, Id $id): Schema
    {
        $instance = new self();

        $instance->id = $id;

        $promise = $client
            ->send(schemaRequest((string) $id))
            ->otherwise(new ExceptionMap());

        $instance->rawSchema = Promised\RawSchema::withPromise($promise);

        return $instance;
    }

    protected function __construct()
    {
    }

    public function rawSchema(): RawSchema
    {
        return $this->rawSchema;
    }

    public function id(): Id
    {
        return $this->id;
    }
}
