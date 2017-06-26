<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Schema;

use FlixTech\SchemaRegistryApi\AsyncHttpClient;
use FlixTech\SchemaRegistryApi\Exception\InternalSchemaRegistryException;
use FlixTech\SchemaRegistryApi\Exception\SchemaNotFoundException;
use FlixTech\SchemaRegistryApi\Model\Schema\Promised\RawSchema;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\UriTemplate;

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

    public function __construct(AsyncHttpClient $client, Id $id)
    {
        $this->id = $id;

        $schemaRequest = new Request(
            'GET',
            (new UriTemplate())->expand('/schemas/ids/{id}', ['id' => $id->value()]),
            ['Accept' => 'application/vnd.schemaregistry.v1+json']
        );

        $promise = $client
            ->send($schemaRequest)
            ->otherwise(
                function (RequestException $e) use ($id) {
                    if (404 === $e->getResponse()->getStatusCode()) {
                        throw SchemaNotFoundException::create($id);
                    }

                    throw InternalSchemaRegistryException::create();
                }
            );

        $this->rawSchema = RawSchema::withPromise($promise);
    }

    public function rawSchema(): RawSchema
    {
        return $this->rawSchema;
    }

    public function getId(): Id
    {
        return $this->id;
    }
}
