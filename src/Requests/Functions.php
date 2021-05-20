<?php

namespace FlixTech\SchemaRegistryApi\Requests;

use Assert\Assert;
use FlixTech\SchemaRegistryApi\Schema\AvroReference;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\UriTemplate\UriTemplate;
use Psr\Http\Message\RequestInterface;
use const FlixTech\SchemaRegistryApi\Constants\ACCEPT_HEADER;
use const FlixTech\SchemaRegistryApi\Constants\COMPATIBILITY_BACKWARD;
use const FlixTech\SchemaRegistryApi\Constants\COMPATIBILITY_BACKWARD_TRANSITIVE;
use const FlixTech\SchemaRegistryApi\Constants\COMPATIBILITY_FORWARD;
use const FlixTech\SchemaRegistryApi\Constants\COMPATIBILITY_FORWARD_TRANSITIVE;
use const FlixTech\SchemaRegistryApi\Constants\COMPATIBILITY_FULL;
use const FlixTech\SchemaRegistryApi\Constants\COMPATIBILITY_FULL_TRANSITIVE;
use const FlixTech\SchemaRegistryApi\Constants\COMPATIBILITY_NONE;
use const FlixTech\SchemaRegistryApi\Constants\CONTENT_TYPE_HEADER;
use const FlixTech\SchemaRegistryApi\Constants\VERSION_LATEST;
use function implode;

function allSubjectsRequest(): RequestInterface
{
    return new Request(
        'GET',
        '/subjects',
        ACCEPT_HEADER
    );
}

function allSubjectVersionsRequest(string $subjectName): RequestInterface
{
    return new Request(
        'GET',
        (new UriTemplate())->expand('/subjects/{name}/versions', ['name' => $subjectName]),
        ACCEPT_HEADER
    );
}

function singleSubjectVersionRequest(string $subjectName, string $versionId): RequestInterface
{
    return new Request(
        'GET',
        (new UriTemplate())->expand(
            '/subjects/{name}/versions/{id}',
            ['name' => $subjectName, 'id' => $versionId]
        ),
        ACCEPT_HEADER
    );
}

function registerNewSchemaVersionWithSubjectRequest(string $schema, string $subjectName, AvroReference ...$references): RequestInterface
{
    return new Request(
        'POST',
        (new UriTemplate())->expand('/subjects/{name}/versions', ['name' => $subjectName]),
        CONTENT_TYPE_HEADER + ACCEPT_HEADER,
        prepareJsonSchemaForTransfer(validateSchemaStringAsJson($schema), ...$references)
    );
}

function checkSchemaCompatibilityAgainstVersionRequest(string $schema, string $subjectName, string $versionId): RequestInterface
{
    return new Request(
        'POST',
        (new UriTemplate())->expand(
            '/compatibility/subjects/{name}/versions/{version}',
            ['name' => $subjectName, 'version' => $versionId]
        ),
        CONTENT_TYPE_HEADER + ACCEPT_HEADER,
        prepareJsonSchemaForTransfer(validateSchemaStringAsJson($schema))
    );
}

function checkIfSubjectHasSchemaRegisteredRequest(string $subjectName, string $schema): RequestInterface
{
    return new Request(
        'POST',
        (new UriTemplate())->expand('/subjects/{name}', ['name' => $subjectName]),
        CONTENT_TYPE_HEADER + ACCEPT_HEADER,
        prepareJsonSchemaForTransfer(validateSchemaStringAsJson($schema))
    );
}

function schemaRequest(string $id): RequestInterface
{
    return new Request(
        'GET',
        (new UriTemplate())->expand('/schemas/ids/{id}', ['id' => $id]),
        ACCEPT_HEADER
    );
}

function defaultCompatibilityLevelRequest(): RequestInterface
{
    return new Request(
        'GET',
        '/config',
        ACCEPT_HEADER
    );
}

function changeDefaultCompatibilityLevelRequest(string $level): RequestInterface
{
    return new Request(
        'PUT',
        '/config',
        ACCEPT_HEADER,
        prepareCompatibilityLevelForTransport(validateCompatibilityLevel($level))
    );
}

function subjectCompatibilityLevelRequest(string $subjectName): RequestInterface
{
    return new Request(
        'GET',
        (new UriTemplate())->expand('/config/{subject}', ['subject' => $subjectName]),
        ACCEPT_HEADER
    );
}

function changeSubjectCompatibilityLevelRequest(string $subjectName, string $level): RequestInterface
{
    return new Request(
        'PUT',
        (new UriTemplate())->expand('/config/{subject}', ['subject' => $subjectName]),
        ACCEPT_HEADER,
        prepareCompatibilityLevelForTransport(validateCompatibilityLevel($level))
    );
}

/**
 * @param int|string $versionId
 * @return string
 */
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

function prepareJsonSchemaForTransfer(string $schema, AvroReference ...$references): string
{
    $return = [
        'schema' => $schema
    ];

    return !$references
        ? \GuzzleHttp\json_encode($return)
        : \GuzzleHttp\json_encode(array_merge($return, ['references' => $references]));
}

function validateCompatibilityLevel(string $compatibilityVersion): string
{
    $compatibilities = [
        COMPATIBILITY_NONE,
        COMPATIBILITY_BACKWARD,
        COMPATIBILITY_BACKWARD_TRANSITIVE,
        COMPATIBILITY_FORWARD,
        COMPATIBILITY_FORWARD_TRANSITIVE,
        COMPATIBILITY_FULL,
        COMPATIBILITY_FULL_TRANSITIVE,

    ];
    Assert::that($compatibilityVersion)->inArray(
        $compatibilities,
        '$level must be one of ' . implode(', ', $compatibilities)
    );

    return $compatibilityVersion;
}

function prepareCompatibilityLevelForTransport(string $compatibilityLevel): string
{
    return \GuzzleHttp\json_encode(['compatibility' => $compatibilityLevel]);
}

/**
 * @param int|string $schemaId
 * @return string
 */
function validateSchemaId($schemaId): string
{
    Assert::that($schemaId)
        ->integerish('$schemaId must be an integer value of type int or string')
        ->greaterThan(0, '$schemaId must be greater than 0');

    return (string) $schemaId;
}

/**
 * @param string $subjectName
 * @return RequestInterface
 */
function deleteSubjectRequest(string $subjectName): RequestInterface
{
    return new Request(
        'DELETE',
        (new UriTemplate())->expand('/subjects/{name}', ['name' => $subjectName]),
        ACCEPT_HEADER
    );
}

/**
 * @param string $subjectName
 * @param string $versionId
 * @return RequestInterface
 */
function deleteSubjectVersionRequest(string $subjectName, string $versionId): RequestInterface
{
    return new Request(
        'DELETE',
        (new UriTemplate())->expand('/subjects/{name}/versions/{version}', ['name' => $subjectName, 'version' => $versionId]),
        ACCEPT_HEADER
    );
}
