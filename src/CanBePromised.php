<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use GuzzleHttp\Promise\PromiseInterface;

interface CanBePromised
{
    public static function withPromise(PromiseInterface $promise);

    public function wait();
}
