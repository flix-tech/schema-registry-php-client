<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Client;

use FlixTech\SchemaRegistryApi\Client\AsyncGuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class AsyncGuzzleClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_call_Guzzle_async()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface $guzzle */
        $guzzle = $this->getMockForAbstractClass(ClientInterface::class);
        $request = new Request('GET', '/test');

        $guzzle
            ->expects($this->once())
            ->method('sendAsync')
            ->with($request)
            ->willReturn($this->getMockForAbstractClass(PromiseInterface::class));

        $client = new AsyncGuzzleClient($guzzle);

        $this->assertInstanceOf(PromiseInterface::class, $client->send($request));
    }
}
