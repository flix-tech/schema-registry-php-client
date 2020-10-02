<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use Assert\Assert;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

final class Json
{
    private function __construct()
    {
    }

    public static function validateStringAsJson(string $schema): string
    {
        Assert::that($schema)->isJsonString('$schema must be a valid JSON string');

        return $schema;
    }

    /**
     * @param string $jsonString
     * @param int $depth
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public static function jsonDecode(string $jsonString, int $depth = 512)
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
    public static function jsonEncode($data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array<mixed, mixed>
     */
    public static function decodeResponse(ResponseInterface $response): array
    {
        $body = (string)$response->getBody();

        try {
            return Json::jsonDecode($body);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                sprintf('%s - with content "%s"', $e->getMessage(), $body),
                $e->getCode(),
                $e
            );
        }
    }

    private function __clone()
    {
    }
}
