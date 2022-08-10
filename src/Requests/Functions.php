<?php

namespace FlixTech\SchemaRegistryApi\Requests;

use Assert\Assert;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
use function json_decode;
use function sprintf;

/**
 * @param string $jsonString
 * @param int<1, max> $depth
 *
 * @return mixed
 *
 * @throws JsonException
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Json::jsonDecode instead
 */
function jsonDecode(string $jsonString, int $depth = 512)
{
    return json_decode($jsonString, true, $depth, JSON_THROW_ON_ERROR);
}

/**
 * @param mixed $data
 *
 * @return string
 *
 * @throws JsonException
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Json::jsonEncode instead
 */
function jsonEncode($data): string
{
    return json_encode($data, JSON_THROW_ON_ERROR);
}

/**
 * @param ResponseInterface $response
 *
 * @return array<mixed, mixed>
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Json::decodeResponse instead
 */
function decodeResponse(ResponseInterface $response): array
{
    $body = (string)$response->getBody();

    try {
        return jsonDecode($body);
    } catch (JsonException $e) {
        throw new InvalidArgumentException(
            sprintf('%s - with content "%s"', $e->getMessage(), $body),
            $e->getCode(),
            $e
        );
    }
}

/**
 * @return RequestInterface
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::allSubjectsRequest instead
 */
function allSubjectsRequest(): RequestInterface
{
    return new Request(
        'GET',
        'subjects',
        ACCEPT_HEADER
    );
}

/**
 * @param string $subjectName
 *
 * @return RequestInterface
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::allSubjectVersionsRequest instead
 */
function allSubjectVersionsRequest(string $subjectName): RequestInterface
{
    return new Request(
        'GET',
        sprintf("subjects/%s/versions", $subjectName),
        ACCEPT_HEADER
    );
}

/**
 * @param string $subjectName
 * @param string $versionId
 *
 * @return RequestInterface
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::singleSubjectVersionRequest instead
 */
function singleSubjectVersionRequest(string $subjectName, string $versionId): RequestInterface
{
    return new Request(
        'GET',
        sprintf("subjects/%s/versions/%s", $subjectName, $versionId),
        ACCEPT_HEADER
    );
}

/**
 * @param string $schema
 * @param string $subjectName
 *
 * @return RequestInterface
 *
 * @throws JsonException
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::registerNewSchemaVersionWithSubjectRequest instead
 */
function registerNewSchemaVersionWithSubjectRequest(string $schema, string $subjectName): RequestInterface
{
    return new Request(
        'POST',
        sprintf("subjects/%s/versions", $subjectName),
        CONTENT_TYPE_HEADER + ACCEPT_HEADER,
        prepareJsonSchemaForTransfer(validateSchemaStringAsJson($schema))
    );
}

/**
 * @param string $schema
 * @param string $subjectName
 * @param string $versionId
 *
 * @return RequestInterface
 *
 * @throws JsonException
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::checkSchemaCompatibilityAgainstVersionRequest instead
 */
function checkSchemaCompatibilityAgainstVersionRequest(string $schema, string $subjectName, string $versionId): RequestInterface
{
    return new Request(
        'POST',
        sprintf("compatibility/subjects/%s/versions/%s", $subjectName, $versionId),
        CONTENT_TYPE_HEADER + ACCEPT_HEADER,
        prepareJsonSchemaForTransfer(validateSchemaStringAsJson($schema))
    );
}

/**
 * @param string $subjectName
 * @param string $schema
 *
 * @return RequestInterface
 *
 * @throws JsonException
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::checkIfSubjectHasSchemaRegisteredRequest instead
 */
function checkIfSubjectHasSchemaRegisteredRequest(string $subjectName, string $schema): RequestInterface
{
    return new Request(
        'POST',
        sprintf("subjects/%s", $subjectName),
        CONTENT_TYPE_HEADER + ACCEPT_HEADER,
        prepareJsonSchemaForTransfer(validateSchemaStringAsJson($schema))
    );
}

/**
 * @param string $id
 * @return RequestInterface
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::schemaRequest instead
 */
function schemaRequest(string $id): RequestInterface
{
    return new Request(
        'GET',
        sprintf("schemas/ids/%s", $id),
        ACCEPT_HEADER
    );
}

/**
 * @return RequestInterface
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::defaultCompatibilityLevelRequest instead
 */
function defaultCompatibilityLevelRequest(): RequestInterface
{
    return new Request(
        'GET',
        'config',
        ACCEPT_HEADER
    );
}

/**
 * @param string $level
 *
 * @return RequestInterface
 *
 * @throws JsonException
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::changeDefaultCompatibilityLevelRequest instead
 */
function changeDefaultCompatibilityLevelRequest(string $level): RequestInterface
{
    return new Request(
        'PUT',
        'config',
        ACCEPT_HEADER,
        prepareCompatibilityLevelForTransport(validateCompatibilityLevel($level))
    );
}

/**
 * @param string $subjectName
 *
 * @return RequestInterface
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::subjectCompatibilityLevelRequest instead
 */
function subjectCompatibilityLevelRequest(string $subjectName): RequestInterface
{
    return new Request(
        'GET',
        sprintf("config/%s", $subjectName),
        ACCEPT_HEADER
    );
}

/**
 * @param string $subjectName
 * @param string $level
 *
 * @return RequestInterface
 *
 * @throws JsonException
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::changeSubjectCompatibilityLevelRequest instead
 */
function changeSubjectCompatibilityLevelRequest(string $subjectName, string $level): RequestInterface
{
    return new Request(
        'PUT',
        sprintf("config/%s", $subjectName),
        ACCEPT_HEADER,
        prepareCompatibilityLevelForTransport(validateCompatibilityLevel($level))
    );
}

/**
 * @param int|string $versionId
 *
 * @return string
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::validateVersionId instead
 */
function validateVersionId($versionId): string
{
    if (VERSION_LATEST !== $versionId) {
        Assert::that($versionId)
            ->integerish('$versionId must be an integer of type int or string')
            ->between(1, 2 ** 31 - 1, '$versionId must be between 1 and 2^31 - 1');
    }

    return (string)$versionId;
}

/**
 * @param string $schema
 *
 * @return string
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::validateSchemaStringAsJson instead
 */
function validateSchemaStringAsJson(string $schema): string
{
    Assert::that($schema)->isJsonString('$schema must be a valid JSON string');

    return $schema;
}

/**
 * @param string $schema
 *
 * @return string
 *
 * @throws JsonException
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::prepareJsonSchemaForTransfer instead
 */
function prepareJsonSchemaForTransfer(string $schema): string
{
    $decoded = jsonDecode($schema);

    if (is_array($decoded) && array_key_exists('schema', $decoded)) {
        return jsonEncode($decoded);
    }

    return jsonEncode(['schema' => jsonEncode($decoded)]);
}

/**
 * @param string $compatibilityVersion
 *
 * @return string
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::validateCompatibilityLevel instead
 */
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

/**
 * @param string $compatibilityLevel
 *
 * @return string
 *
 * @throws JsonException
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::prepareCompatibilityLevelForTransport instead
 */
function prepareCompatibilityLevelForTransport(string $compatibilityLevel): string
{
    return jsonEncode(['compatibility' => $compatibilityLevel]);
}

/**
 * @param int|string $schemaId
 * @return string
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::validateSchemaId instead
 */
function validateSchemaId($schemaId): string
{
    Assert::that($schemaId)
        ->integerish('$schemaId must be an integer value of type int or string')
        ->greaterThan(0, '$schemaId must be greater than 0');

    return (string)$schemaId;
}

/**
 * @param string $subjectName
 *
 * @return RequestInterface
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::deleteSubjectRequest instead
 */
function deleteSubjectRequest(string $subjectName): RequestInterface
{
    return new Request(
        'DELETE',
        sprintf("subjects/%s", $subjectName),
        ACCEPT_HEADER
    );
}

/**
 * @param string $subjectName
 * @param string $versionId
 *
 * @return RequestInterface
 *
 * @deprecated Use \FlixTech\SchemaRegistryApi\Requests::deleteSubjectVersionRequest instead
 */
function deleteSubjectVersionRequest(string $subjectName, string $versionId): RequestInterface
{
    return new Request(
        'DELETE',
        sprintf("subjects/%s/versions/%s", $subjectName, $versionId),
        ACCEPT_HEADER
    );
}
