<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Subject;

use FlixTech\SchemaRegistryApi\AsyncHttpClient;
use FlixTech\SchemaRegistryApi\Exception\InternalSchemaRegistryException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class Subject
{
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
                            return SubjectName::create($subjectName);
                        },
                        \GuzzleHttp\json_decode($response->getBody()->getContents(), true)
                    );
                },
                function () {
                    throw InternalSchemaRegistryException::create();
                }
            )->wait();
    }
}
