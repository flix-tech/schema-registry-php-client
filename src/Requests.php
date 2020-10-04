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
            self::prepareJsonSchemaForTransfer(Json::validateStringAsJson($schema))
        );
    }

    public static function checkSchemaCompatibilityAgainstVersionRequest(string $schema, string $subjectName, string $versionId): RequestInterface
    {
        return new Request(
            'POST',
            Utils::uriFor("/compatibility/subjects/$subjectName/versions/$versionId"),
            Constants::CONTENT_TYPE_HEADER + Constants::ACCEPT_HEADER,
            self::prepareJsonSchemaForTransfer(Json::validateStringAsJson($schema))
        );
    }

    public static function checkIfSubjectHasSchemaRegisteredRequest(string $subjectName, string $schema): RequestInterface
    {
        return new Request(
            'POST',
            Utils::uriFor("/subjects/$subjectName"),
            Constants::CONTENT_TYPE_HEADER + Constants::ACCEPT_HEADER,
            self::prepareJsonSchemaForTransfer(Json::validateStringAsJson($schema))
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
            self::prepareCompatibilityLevelForTransport(self::validateCompatibilityLevel($level))
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

    public static function changeSubjectCompatibilityLevelRequest(string $subjectName, string $level): RequestInterface
    {
        return new Request(
            'PUT',
            Utils::uriFor("/config/$subjectName"),
            Constants::ACCEPT_HEADER,
            self::prepareCompatibilityLevelForTransport(self::validateCompatibilityLevel($level))
        );
    }

    /**
     * @param int|string $versionId
     * @return string
     */
    public static function validateVersionId($versionId): string
    {
        if (Constants::VERSION_LATEST !== $versionId) {
            Assert::that($versionId)
                ->integerish('$versionId must be an integer of type int or string')
                ->between(1, 2 ** 31 - 1, '$versionId must be between 1 and 2^31 - 1');
        }

        return (string)$versionId;
    }

    /**
     * @param int|string $schemaId
     * @return string
     */
    public static function validateSchemaId($schemaId): string
    {
        Assert::that($schemaId)
            ->integerish('$schemaId must be an integer value of type int or string')
            ->greaterThan(0, '$schemaId must be greater than 0');

        return (string)$schemaId;
    }

    /**
     * @param string $subjectName
     * @param bool $permanent
     * @return RequestInterface
     */
    public static function deleteSubjectRequest(string $subjectName, bool $permanent): RequestInterface
    {
        $query = $permanent ? "true" : "false";

        return new Request(
            'DELETE',
            Utils::uriFor("/subjects/$subjectName?permanent=$query"),
            Constants::ACCEPT_HEADER
        );
    }

    /**
     * @param string $subjectName
     * @param string $versionId
     * @return RequestInterface
     */
    public static function deleteSubjectVersionRequest(string $subjectName, string $versionId): RequestInterface
    {
        return new Request(
            'DELETE',
            Utils::uriFor("/subjects/$subjectName/versions/$versionId"),
            Constants::ACCEPT_HEADER
        );
    }

    private function __clone()
    {
    }
}
