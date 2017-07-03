<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Schema\Promised;

use FlixTech\SchemaRegistryApi\CanBePromised;
use FlixTech\SchemaRegistryApi\Exception\ExceptionMap;
use FlixTech\SchemaRegistryApi\HasPromisedProperties;
use FlixTech\SchemaRegistryApi\Model\Schema\Id as BaseId;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

final class Id extends BaseId implements CanBePromised
{
    use HasPromisedProperties;

    public static function withPromise(PromiseInterface $promise): BaseId
    {
        $instance = new self();
        $instance->promise = $promise->then(
            function (ResponseInterface $response) use ($instance) {
                $instance->id = \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['id'];
            },
            new ExceptionMap()
        );

        return $instance;
    }

    public function value(): int
    {
        return $this->getPromisedProperty('id');
    }
}
