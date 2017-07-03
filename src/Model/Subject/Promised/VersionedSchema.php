<?php

namespace FlixTech\SchemaRegistryApi\Model\Subject\Promised;

use FlixTech\SchemaRegistryApi\CanBePromised;
use FlixTech\SchemaRegistryApi\HasPromisedProperties;
use FlixTech\SchemaRegistryApi\Model\Schema\Id;
use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;
use FlixTech\SchemaRegistryApi\Model\Schema\Schema;
use FlixTech\SchemaRegistryApi\Model\Subject\Name;
use FlixTech\SchemaRegistryApi\Model\Subject\VersionedSchema as BaseModel;
use FlixTech\SchemaRegistryApi\Model\Subject\VersionId;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class VersionedSchema extends BaseModel implements CanBePromised
{
    use HasPromisedProperties;

    public static function withPromise(PromiseInterface $promise): BaseModel
    {
        $instance = new self();

        $instance->promise = $promise->then(
            function (ResponseInterface $response) use ($instance) {
                $decodedResponse = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

                $instance->schema = Schema::createWithSchema(
                    RawSchema::create($decodedResponse['schema']),
                    Id::create($decodedResponse['id'])
                );
                $instance->versionId = VersionId::create($decodedResponse['version']);
                $instance->subjectName = Name::create($decodedResponse['subject']);
            }
        );

        return $instance;
    }

    public function versionId(): VersionId
    {
        return $this->getPromisedProperty('versionId');
    }

    public function schema(): Schema
    {
        return $this->getPromisedProperty('schema');
    }

    public function subjectName(): Name
    {
        return $this->getPromisedProperty('subjectName');
    }
}
