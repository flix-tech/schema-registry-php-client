<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Subject;

use FlixTech\SchemaRegistryApi\AsyncHttpClient;
use FlixTech\SchemaRegistryApi\Exception\InternalSchemaRegistryException;
use FlixTech\SchemaRegistryApi\Exception\SubjectNotFoundException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\UriTemplate;

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

    /**
     * @var array
     */
    private $versions = [];

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

    public function versions(): array
    {
        if ($this->versions) {
            return $this->versions;
        }

        $request = new Request(
            'GET',
            (new UriTemplate())->expand('/subjects/{name}/versions', ['name' => (string) $this]),
            ['Accept' => 'application/vnd.schemaregistry.v1+json']
        );

        $this->versions = $this->client
            ->send($request)
            ->then(
                function (Response $response) {
                    return array_map(
                        function (int $version) {
                            return VersionId::create($version);
                        },
                        \GuzzleHttp\json_decode($response->getBody()->getContents(), true)
                    );
                },
                function (RequestException $e) {
                    if (404 === $e->getResponse()->getStatusCode()) {
                        throw SubjectNotFoundException::create($this->name);
                    }

                    throw InternalSchemaRegistryException::create();
                }
            )->wait();

        return $this->versions;
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
