<?php

namespace FlixTech\SchemaRegistryApi\Client;

use FlixTech\SchemaRegistryApi\AsyncHttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

class AsyncGuzzleClient implements AsyncHttpClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function send(RequestInterface $request): PromiseInterface
    {
        return $this->client->sendAsync($request);
    }
}
