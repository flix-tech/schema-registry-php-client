<?php

namespace FlixTech\SchemaRegistryApi\Requests;

use Assert\Assert;
use const FlixTech\SchemaRegistryApi\Constants\VERSION_LATEST;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\UriTemplate;
use Psr\Http\Message\RequestInterface;

function allSubjectsRequest(): RequestInterface
{
    return new Request(
        'GET',
        '/subjects',
        ['Accept' => 'application/vnd.schemaregistry.v1+json']
    );
}

function allSubjectVersionsRequest(string $subjectName): RequestInterface
{
    return new Request(
        'GET',
        (new UriTemplate())->expand('/subjects/{name}/versions', ['name' => $subjectName]),
        ['Accept' => 'application/vnd.schemaregistry.v1+json']
    );
}

function singleSubjectVersionRequest(string $subjectName, $versionId): RequestInterface
{
    return new Request(
        'GET',
        (new UriTemplate())->expand(
            '/subjects/{name}/versions/{id}',
            ['name' => $subjectName, 'id' => validateVersionId($versionId)]
        ),
        ['Accept' => 'application/vnd.schemaregistry.v1+json']
    );
}

function registerNewSchemaVersionWithSubjectRequest(string $schema, string $subjectName): RequestInterface
{
    return new Request(
        'POST',
        (new UriTemplate())->expand('/subjects/{name}/versions', ['name' => (string) $subjectName]),
        ['Accept' => 'application/vnd.schemaregistry.v1+json'],
        prepareJsonSchemaForTransfer(validateSchemaStringAsJson($schema))
    );
}

function checkSchemaCompatibilityRequest(string $schema, string $subjectName, string $versionId): RequestInterface
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

function validateVersionId($versionId): string
{
    if (VERSION_LATEST !== $versionId) {
        Assert::that($versionId)
            ->integerish('$versionId must be an integer of type int or string')
            ->between(1, 2 ** 31 - 1, '$versionId must be between 1 and 2^31 - 1');
    }

    return (string) $versionId;
}

function validateSchemaStringAsJson(string $schema): string
{
    Assert::that($schema)->isJsonString('$schema must be a valid JSON string');

    return $schema;
}

function prepareJsonSchemaForTransfer(string $schema): string
{
    $decoded = \GuzzleHttp\json_decode($schema, true);

    if (array_key_exists('schema', $decoded)) {
        return \GuzzleHttp\json_encode($decoded);
    }

    return \GuzzleHttp\json_encode(['schema' => \GuzzleHttp\json_encode($decoded)]);
}
