<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Compatibility\Promised;

use FlixTech\SchemaRegistryApi\CanBePromised;
use FlixTech\SchemaRegistryApi\HasPromisedProperties;
use FlixTech\SchemaRegistryApi\Model\Compatibility\Level as BaseModel;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

final class Level extends BaseModel implements CanBePromised
{
    use HasPromisedProperties;

    public static function withPromise(PromiseInterface $promise): BaseModel
    {
        $instance = new self();
        $instance->promise = $promise->then(
            function (ResponseInterface $response) use ($instance) {
                $instance->level = \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['compatibility'];
            }
        );

        return $instance;
    }

    public function value(): string
    {
        return $this->getPromisedProperty('level');
    }
}
