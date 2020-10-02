<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

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

    private function __clone()
    {
    }
}
