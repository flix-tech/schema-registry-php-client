<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use function FlixTech\SchemaRegistryApi\Requests\jsonDecode;
use function FlixTech\SchemaRegistryApi\Requests\jsonEncode;

final class Requests
{
    private function __construct()
    {
    }

    public static function allSubjectsRequest(): RequestInterface
    {
        return new Request(
            'GET',
            '/subjects',
            Constants::ACCEPT_HEADER
        );
    }

    public static function allSubjectVersionsRequest(string $subjectName): RequestInterface
    {
        return new Request(
            'GET',
            Utils::uriFor("/subjects/$subjectName/versions"),
            Constants::ACCEPT_HEADER
        );
    }

    public static function singleSubjectVersionRequest(string $subjectName, string $versionId): RequestInterface
    {
        return new Request(
            'GET',
            Utils::uriFor("/subjects/$subjectName/versions/$versionId"),
            Constants::ACCEPT_HEADER
        );
    }

    public static function prepareJsonSchemaForTransfer(string $schema): string
    {
        $decoded = jsonDecode($schema);

        if (is_array($decoded) && array_key_exists('schema', $decoded)) {
            return jsonEncode($decoded);
        }

        return jsonEncode(['schema' => jsonEncode($decoded)]);
    }

    private function __clone()
    {
    }
}
