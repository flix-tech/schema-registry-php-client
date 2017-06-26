<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test;

use FlixTech\SchemaRegistryApi\Api;
use FlixTech\SchemaRegistryApi\Client\AsyncGuzzleClient;
use FlixTech\SchemaRegistryApi\SchemaRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

abstract class ApiTestCase extends TestCase
{
    /**
     * @var \GuzzleHttp\Psr7\Request[][]
     */
    protected $requestContainer = [];

    /**
     * @param \GuzzleHttp\Exception\RequestException[]|Response[] $responses
     *
     * @return SchemaRegistry
     */
    protected function getApiWithMockResponses(array $responses): SchemaRegistry
    {
        $mockHandler = new MockHandler($responses);
        $stack = HandlerStack::create($mockHandler);
        $stack->push(Middleware::history($this->requestContainer));

        return new Api(
            new AsyncGuzzleClient(
                new Client(['handler' => $stack])
            )
        );
    }
}
