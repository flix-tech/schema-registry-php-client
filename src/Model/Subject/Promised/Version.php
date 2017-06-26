<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Subject\Promised;

use FlixTech\SchemaRegistryApi\CanBePromised;
use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;
use FlixTech\SchemaRegistryApi\Model\Subject\Name;
use FlixTech\SchemaRegistryApi\Model\Subject\Version as BaseVersion;
use FlixTech\SchemaRegistryApi\Model\Subject\VersionId;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

final class Version extends BaseVersion implements CanBePromised
{
    /**
     * @var PromiseInterface
     */
    private $promise;

    public static function withPromise(PromiseInterface $promise): Version
    {
        $instance = new self();
        $instance->setPromise($promise);

        return $instance;
    }

    public function wait()
    {
        $this->promise->wait();
    }

    private function setPromise(PromiseInterface $promise)
    {
        $this->promise = $promise
            ->then(
                function (ResponseInterface $response) {
                    $decodedResponse = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

                    $this->schema = RawSchema::create($decodedResponse['schema']);
                    $this->id = VersionId::create($decodedResponse['version']);
                    $this->subjectName = Name::create($decodedResponse['name']);
                }
            );
    }

    public function id(): VersionId
    {
        if ($this->id) {
            return $this->id;
        }

        $this->wait();

        return $this->id;
    }

    public function subjectName(): Name
    {
        if ($this->subjectName) {
            return $this->subjectName;
        }

        $this->wait();

        return $this->subjectName;
    }

    public function schema(): RawSchema
    {
        if ($this->schema) {
            return $this->schema;
        }

        $this->wait();

        return $this->schema;
    }
}
