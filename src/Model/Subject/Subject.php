<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Subject;

use FlixTech\SchemaRegistryApi\AsyncHttpClient;
use FlixTech\SchemaRegistryApi\Exception\InternalSchemaRegistryException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class Subject
{
    /**
     * @var AsyncHttpClient
     */
    private $client;

    /**
     * @var Name
     */
    private $name;

    public static function registeredSubjects(AsyncHttpClient $client): array
    {
        $request = new Request(
            'GET',
            '/subjects',
            ['Accept' => 'application/vnd.schemaregistry.v1+json']
        );

        return $client->send($request)
            ->then(
                function (Response $response) {
                    return array_map(
                        function (string $subjectName) {
                            return Name::create($subjectName);
                        },
                        \GuzzleHttp\json_decode($response->getBody()->getContents(), true)
                    );
                },
                function () {
                    throw InternalSchemaRegistryException::create();
                }
            )->wait();
    }

    public function __construct(AsyncHttpClient $client, Name $name)
    {
        $this->client = $client;
        $this->name = $name;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name->name();
    }
}
