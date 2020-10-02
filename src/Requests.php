<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use Assert\Assert;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;

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
        $decoded = Json::jsonDecode($schema);

        if (is_array($decoded) && array_key_exists('schema', $decoded)) {
            return Json::jsonEncode($decoded);
        }

        return Json::jsonEncode(['schema' => Json::jsonEncode($decoded)]);
    }

    public static function registerNewSchemaVersionWithSubjectRequest(string $schema, string $subjectName): RequestInterface
    {
        return new Request(
            'POST',
            Utils::uriFor("/subjects/$subjectName/versions"),
            Constants::CONTENT_TYPE_HEADER + Constants::ACCEPT_HEADER,
            Requests::prepareJsonSchemaForTransfer(Json::validateStringAsJson($schema))
        );
    }

    public static function checkSchemaCompatibilityAgainstVersionRequest(string $schema, string $subjectName, string $versionId): RequestInterface
    {
        return new Request(
            'POST',
            Utils::uriFor("/compatibility/subjects/$subjectName/versions/$versionId"),
            Constants::CONTENT_TYPE_HEADER + Constants::ACCEPT_HEADER,
            Requests::prepareJsonSchemaForTransfer(Json::validateStringAsJson($schema))
        );
    }

    public static function checkIfSubjectHasSchemaRegisteredRequest(string $subjectName, string $schema): RequestInterface
    {
        return new Request(
            'POST',
            Utils::uriFor("/subjects/$subjectName"),
            Constants::CONTENT_TYPE_HEADER + Constants::ACCEPT_HEADER,
            Requests::prepareJsonSchemaForTransfer(Json::validateStringAsJson($schema))
        );
    }

    public static function schemaRequest(string $id): RequestInterface
    {
        return new Request(
            'GET',
            Utils::uriFor("/schemas/ids/$id"),
            Constants::ACCEPT_HEADER
        );
    }

    public static function defaultCompatibilityLevelRequest(): RequestInterface
    {
        return new Request(
            'GET',
            '/config',
            Constants::ACCEPT_HEADER
        );
    }

    public static function validateCompatibilityLevel(string $compatibilityVersion): string
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

    public static function prepareCompatibilityLevelForTransport(string $compatibilityLevel): string
    {
        return Json::jsonEncode(['compatibility' => $compatibilityLevel]);
    }

    public static function changeDefaultCompatibilityLevelRequest(string $level): RequestInterface
    {
        return new Request(
            'PUT',
            '/config',
            Constants::ACCEPT_HEADER,
            Requests::prepareCompatibilityLevelForTransport(Requests::validateCompatibilityLevel($level))
        );
    }

    public static function subjectCompatibilityLevelRequest(string $subjectName): RequestInterface
    {
        return new Request(
            'GET',
            Utils::uriFor("/config/$subjectName"),
            Constants::ACCEPT_HEADER
        );
    }

    private function __clone()
    {
    }
}
