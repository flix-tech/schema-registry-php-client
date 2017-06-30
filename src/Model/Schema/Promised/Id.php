<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Schema\Promised;

use FlixTech\SchemaRegistryApi\CanBePromised;
use FlixTech\SchemaRegistryApi\Exception\IncompatibleAvroSchemaException;
use FlixTech\SchemaRegistryApi\Model\Schema\Id as BaseId;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

final class Id extends BaseId implements CanBePromised
{
    /**
     * @var PromiseInterface
     */
    private $promise;

    public static function withPromise(PromiseInterface $promise): BaseId
    {
        $instance = new self();
        $instance->promise = $promise->then(
            function (ResponseInterface $response) use ($instance) {
                $instance->id = \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['id'];
            },
            function (RequestException $e) {
                if (409 === $e->getResponse()->getStatusCode()) {
                    throw IncompatibleAvroSchemaException::create();
                }
            }
        );

        return $instance;
    }

    public function value(): int
    {
        if ($this->id) {
            return $this->id;
        }

        $this->wait();

        return $this->id;
    }

    public function wait()
    {
        $this->promise->wait();
    }
}
