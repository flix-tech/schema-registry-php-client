<?php

namespace FlixTech\SchemaRegistryApi\Requests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\UriTemplate;
use Psr\Http\Message\RequestInterface;

function subjectsRequest(): RequestInterface
{
    return new Request(
        'GET',
        '/subjects',
        ['Accept' => 'application/vnd.schemaregistry.v1+json']
    );
}

function subjectVersionsRequest(string $subjectName): RequestInterface
{
    return new Request(
        'GET',
        (new UriTemplate())->expand('/subjects/{name}/versions', ['name' => $subjectName]),
        ['Accept' => 'application/vnd.schemaregistry.v1+json']
    );
}

function subjectVersionRequest(string $subjectName, string $versionId): RequestInterface
{
    return new Request(
        'GET',
        (new UriTemplate())->expand('/subjects/{name}/versions/{id}', ['name' => $subjectName, 'id' => $versionId]),
        ['Accept' => 'application/vnd.schemaregistry.v1+json']
    );
}

function registerSchemaWithSubjectRequest(string $subjectName, string $schema): RequestInterface
{
    return new Request(
        'POST',
        (new UriTemplate())->expand('/subjects/{name}/versions', ['name' => (string) $subjectName]),
        ['Accept' => 'application/vnd.schemaregistry.v1+json'],
        $schema
    );
}

function checkSchemaCompatibilityRequest(string $subjectName, string $versionId, string $schema): RequestInterface
{
    return new Request(
        'POST',
        (new UriTemplate())->expand(
            '/compatibility/subjects/{name}/versions/{version}',
            ['name' => $subjectName, 'version' => $versionId]
        ),
        ['Accept' => 'application/vnd.schemaregistry.v1+json'],
        $schema
    );
}

function hasSchemaRequest(string $subjectName, string $schema): RequestInterface
{
    return new Request(
        'POST',
        (new UriTemplate())->expand('/subjects/{name}', ['name' => $subjectName]),
        ['Accept' => 'application/vnd.schemaregistry.v1+json'],
        $schema
    );
}

function schemaRequest(string $id): RequestInterface
{
    return new Request(
        'GET',
        (new UriTemplate())->expand('/schemas/ids/{id}', ['id' => $id]),
        ['Accept' => 'application/vnd.schemaregistry.v1+json']
    );
}

function defaultCompatibilityLevelRequest(): RequestInterface
{
    return new Request(
        'GET',
        '/config',
        ['Accept' => 'application/vnd.schemaregistry.v1+json']
    );
}

function changeDefaultCompatibilityLevelRequest(string $level): RequestInterface
{
    return new Request(
        'PUT',
        '/config',
        ['Accept' => 'application/vnd.schemaregistry.v1+json'],
        $level
    );
}

function subjectCompatibilityLevelRequest(string $subjectName): RequestInterface
{
    return new Request(
        'GET',
        (new UriTemplate())->expand('/config/{subject}', ['subject' => $subjectName]),
        ['Accept' => 'application/vnd.schemaregistry.v1+json']
    );
}

function changeSubjectCompatibilityLevelRequest(string $subjectName, string $level): RequestInterface
{
    return new Request(
        'PUT',
        (new UriTemplate())->expand('/config/{subject}', ['subject' => $subjectName]),
        ['Accept' => 'application/vnd.schemaregistry.v1+json'],
        $level
    );
}
