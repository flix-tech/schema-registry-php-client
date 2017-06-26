<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Schema\Promised;

use FlixTech\SchemaRegistryApi\CanBePromised;
use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema as RawSchemaModel;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

final class RawSchema extends RawSchemaModel implements CanBePromised
{
    /**
     * @var PromiseInterface
     */
    private $promise;

    public static function withPromise(PromiseInterface $promise): RawSchemaModel
    {
        $instance = new self();
        $instance->setPromise($promise);

        return $instance;
    }

    private function setPromise(PromiseInterface $promise)
    {
        $this->promise = $promise->then(
            function (ResponseInterface $response) {
                $this->schema = \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['schema'];
            }
        );
    }

    public function value(): string
    {
        if ($this->schema) {
            return $this->schema;
        }

        $this->promise->wait();

        return $this->schema;
    }

    public function wait()
    {
        $this->promise->wait();
    }
}
