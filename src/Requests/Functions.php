<?php /** @noinspection AdditionOperationOnArraysInspection */

namespace FlixTech\SchemaRegistryApi\Requests;

use FlixTech\SchemaRegistryApi\Constants;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;

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
