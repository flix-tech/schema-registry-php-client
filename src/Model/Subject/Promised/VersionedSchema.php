<?php

namespace FlixTech\SchemaRegistryApi\Model\Subject\Promised;

use FlixTech\SchemaRegistryApi\CanBePromised;
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
    /**
     * @var PromiseInterface
     */
    private $promise;

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

    public function wait()
    {
        return $this->promise->wait();
    }

    public function versionId(): VersionId
    {
        if ($this->versionId) {
            return $this->versionId;
        }

        $this->promise->wait();

        return $this->versionId;
    }

    public function schema(): Schema
    {
        if ($this->schema) {
            return $this->schema;
        }

        $this->promise->wait();

        return $this->schema;
    }

    public function subjectName(): Name
    {
        if ($this->subjectName) {
            return $this->subjectName;
        }

        $this->promise->wait();

        return $this->subjectName;
    }
}
