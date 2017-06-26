<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

interface AsyncHttpClient
{
    public function send(RequestInterface $request): PromiseInterface;
}
