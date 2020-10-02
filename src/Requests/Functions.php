<?php /** @noinspection AdditionOperationOnArraysInspection */

namespace FlixTech\SchemaRegistryApi\Requests;

use Assert\Assert;
use FlixTech\SchemaRegistryApi\Constants;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function implode;
use function json_decode;

/**
 * @param string $jsonString
 * @param int $depth
 *
 * @return mixed
 *
 * @throws JsonException
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
 */
function jsonEncode($data): string
{
    return json_encode($data, JSON_THROW_ON_ERROR);
}

/**
 * @param ResponseInterface $response
 *
 * @return array<mixed, mixed>
 */
function decodeResponse(ResponseInterface $response): array
{
    $body = (string) $response->getBody();

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

function allSubjectsRequest(): RequestInterface
{
    return new Request(
        'GET',
        '/subjects',
        Constants::ACCEPT_HEADER
    );
}

function allSubjectVersionsRequest(string $subjectName): RequestInterface
{
    return new Request(
        'GET',
        Utils::uriFor("/subjects/$subjectName/versions"),
        Constants::ACCEPT_HEADER
    );
}

function singleSubjectVersionRequest(string $subjectName, string $versionId): RequestInterface
{
    return new Request(
        'GET',
        Utils::uriFor("/subjects/$subjectName/versions/$versionId"),
        Constants::ACCEPT_HEADER
    );
}

function registerNewSchemaVersionWithSubjectRequest(string $schema, string $subjectName): RequestInterface
{
    return new Request(
        'POST',
        Utils::uriFor("/subjects/$subjectName/versions"),
        Constants::CONTENT_TYPE_HEADER + Constants::ACCEPT_HEADER,
        prepareJsonSchemaForTransfer(validateSchemaStringAsJson($schema))
    );
}

function checkSchemaCompatibilityAgainstVersionRequest(string $schema, string $subjectName, string $versionId): RequestInterface
{
    return new Request(
        'POST',
        Utils::uriFor("/compatibility/subjects/$subjectName/versions/$versionId"),
        Constants::CONTENT_TYPE_HEADER + Constants::ACCEPT_HEADER,
        prepareJsonSchemaForTransfer(validateSchemaStringAsJson($schema))
    );
}

function checkIfSubjectHasSchemaRegisteredRequest(string $subjectName, string $schema): RequestInterface
{
    return new Request(
        'POST',
        Utils::uriFor("/subjects/$subjectName"),
        Constants::CONTENT_TYPE_HEADER + Constants::ACCEPT_HEADER,
        prepareJsonSchemaForTransfer(validateSchemaStringAsJson($schema))
    );
}

function schemaRequest(string $id): RequestInterface
{
    return new Request(
        'GET',
        Utils::uriFor("/schemas/ids/$id"),
        Constants::ACCEPT_HEADER
    );
}

function defaultCompatibilityLevelRequest(): RequestInterface
{
    return new Request(
        'GET',
        '/config',
        Constants::ACCEPT_HEADER
    );
}

function changeDefaultCompatibilityLevelRequest(string $level): RequestInterface
{
    return new Request(
        'PUT',
        '/config',
        Constants::ACCEPT_HEADER,
        prepareCompatibilityLevelForTransport(validateCompatibilityLevel($level))
    );
}

function subjectCompatibilityLevelRequest(string $subjectName): RequestInterface
{
    return new Request(
        'GET',
        Utils::uriFor("/config/$subjectName"),
        Constants::ACCEPT_HEADER
    );
}

function changeSubjectCompatibilityLevelRequest(string $subjectName, string $level): RequestInterface
{
    return new Request(
        'PUT',
        Utils::uriFor("/config/$subjectName"),
        Constants::ACCEPT_HEADER,
        prepareCompatibilityLevelForTransport(validateCompatibilityLevel($level))
    );
}

/**
 * @param int|string $versionId
 * @return string
 */
function validateVersionId($versionId): string
{
    if (Constants::VERSION_LATEST !== $versionId) {
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
    $decoded = jsonDecode($schema);

    if (is_array($decoded) && array_key_exists('schema', $decoded)) {
        return jsonEncode($decoded);
    }

    return jsonEncode(['schema' => jsonEncode($decoded)]);
}

function validateCompatibilityLevel(string $compatibilityVersion): string
{
    $compatibilities = [
        Constants::COMPATIBILITY_NONE,
        Constants::COMPATIBILITY_BACKWARD,
        Constants::COMPATIBILITY_BACKWARD_TRANSITIVE,
        Constants::COMPATIBILITY_FORWARD,
        Constants::COMPATIBILITY_FORWARD_TRANSITIVE,
        Constants::COMPATIBILITY_FULL,
        Constants::COMPATIBILITY_FULL_TRANSITIVE,

    ];
    Assert::that($compatibilityVersion)->inArray(
        $compatibilities,
        '$level must be one of ' . implode(', ', $compatibilities)
    );

    return $compatibilityVersion;
}

function prepareCompatibilityLevelForTransport(string $compatibilityLevel): string
{
    return jsonEncode(['compatibility' => $compatibilityLevel]);
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
        Utils::uriFor("/subjects/$subjectName"),
        Constants::ACCEPT_HEADER
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
        Utils::uriFor("/subjects/$subjectName/versions/$versionId"),
        Constants::ACCEPT_HEADER
    );
}
