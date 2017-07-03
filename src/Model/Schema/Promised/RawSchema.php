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
        $instance->promise = $promise->then(
            function (ResponseInterface $response) use ($instance) {
                $instance->schema = \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['schema'];
            }
        );

        return $instance;
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
        return $this->promise->wait();
    }
}
