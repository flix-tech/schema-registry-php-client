<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test;

use FlixTech\SchemaRegistryApi\AsyncHttpClient;
use FlixTech\SchemaRegistryApi\Client\AsyncGuzzleClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

abstract class AsyncClientTestCase extends TestCase
{
    /**
     * @var \GuzzleHttp\Psr7\Request[][]
     */
    protected $requestContainer = [];

    /**
     * @param \GuzzleHttp\Exception\RequestException[]|Response[] $responses
     *
     * @return AsyncHttpClient
     */
    protected function getClientWithMockResponses(array $responses): AsyncHttpClient
    {
        $mockHandler = new MockHandler($responses);
        $stack = HandlerStack::create($mockHandler);
        $stack->push(Middleware::history($this->requestContainer));

        return new AsyncGuzzleClient(
            new Client(['handler' => $stack])
        );
    }

    /**
     * @param \GuzzleHttp\Psr7\Request[][] $requestContainer
     * @param string                       $method
     * @param string                       $uri
     * @param string|null                  $body
     */
    protected function assertMethodAndUriAndBody(array $requestContainer, string $method, string $uri, string $body = null)
    {
        $this->assertEquals($method, $requestContainer[0]['request']->getMethod());
        $this->assertEquals($uri, $requestContainer[0]['request']->getUri());
        $this->assertEquals(
            ['application/vnd.schemaregistry.v1+json'],
            $requestContainer[0]['request']->getHeader('Accept')
        );

        if ($body) {
            $this->assertEquals($body, $requestContainer[0]['request']->getBody()->getContents());
        }
    }
}
