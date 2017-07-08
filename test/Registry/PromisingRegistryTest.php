<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry;

use AvroSchema;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use function FlixTech\SchemaRegistryApi\Requests\registerNewSchemaVersionWithSubjectRequest;

class PromisingRegistryTest extends TestCase
{
    /**
     * @var Client
     */
    private $clientMock;

    /**
     * @var PromisingRegistry
     */
    private $registry;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @test
     */
    public function it_should_register_schemas()
    {
        $responses = [
            new Response(200, [], '{"id": 3}')
        ];
        $subject = 'test';
        $schema = AvroSchema::parse('{"type": "string"}');
        $expectedRequest = registerNewSchemaVersionWithSubjectRequest((string) $schema, $subject);

        $this->registry = new PromisingRegistry($this->clientWithMockResponses($responses));

        $promise = $this->registry->register(
            $subject,
            $schema,
            function (RequestInterface $request) use ($expectedRequest) {
                $this->assertEquals($expectedRequest->getUri(), $request->getUri());
                $this->assertEquals($expectedRequest->getHeaders(), $request->getHeaders());
                $this->assertEquals($expectedRequest->getMethod(), $request->getMethod());
                $this->assertEquals($expectedRequest->getBody()->getContents(), $request->getBody()->getContents());

                return $request;
            }
        );

        $this->assertEquals(3, $promise->wait());
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface[] $responses
     *
     * @return Client
     */
    private function clientWithMockResponses(array $responses): Client
    {
        $this->mockHandler = new MockHandler($responses);
        $stack = HandlerStack::create($this->mockHandler);

        $this->clientMock = new Client(['handler' => $stack]);

        return $this->clientMock;
    }
}
