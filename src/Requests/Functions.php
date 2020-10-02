<?php /** @noinspection AdditionOperationOnArraysInspection */

namespace FlixTech\SchemaRegistryApi\Requests;

use Assert\Assert;
use FlixTech\SchemaRegistryApi\Constants;
use FlixTech\SchemaRegistryApi\Json;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use function implode;

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
    return Json::jsonEncode(['compatibility' => $compatibilityLevel]);
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
