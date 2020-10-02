<?php /** @noinspection AdditionOperationOnArraysInspection */

namespace FlixTech\SchemaRegistryApi\Requests;

use Assert\Assert;
use FlixTech\SchemaRegistryApi\Constants;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;

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
