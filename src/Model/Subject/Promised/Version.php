<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Subject\Promised;

use FlixTech\SchemaRegistryApi\CanBePromised;
use FlixTech\SchemaRegistryApi\HasPromisedProperties;
use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;
use FlixTech\SchemaRegistryApi\Model\Subject\Name;
use FlixTech\SchemaRegistryApi\Model\Subject\Version as BaseVersion;
use FlixTech\SchemaRegistryApi\Model\Subject\VersionId;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

final class Version extends BaseVersion implements CanBePromised
{
    use HasPromisedProperties;

    public static function withPromise(PromiseInterface $promise): Version
    {
        $instance = new self();
        $instance->promise = $promise
            ->then(
                function (ResponseInterface $response) use ($instance) {
                    $decodedResponse = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

                    $instance->schema = RawSchema::create($decodedResponse['schema']);
                    $instance->id = VersionId::create($decodedResponse['version']);
                    $instance->subjectName = Name::create($decodedResponse['name']);
                }
            );

        return $instance;
    }

    public function id(): VersionId
    {
        return $this->getPromisedProperty('id');
    }

    public function subjectName(): Name
    {
        return $this->getPromisedProperty('subjectName');
    }

    public function schema(): RawSchema
    {
        return $this->getPromisedProperty('schema');
    }
}
