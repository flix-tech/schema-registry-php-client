<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schema;

use Assert\Assertion;

final class AvroName
{
    /**
     * This regex is created according to the Avro specification for names.
     * @link https://avro.apache.org/docs/current/spec.html#names
     */
    private const REGEX = '/^[a-zA-Z_]+(\.[a-zA-Z0-9_]+)*?$/';

    /**
     * @var string
     */
    private $fullName;

    public function __construct(string $fullName)
    {
        Assertion::notBlank($fullName);
        Assertion::regex($fullName, self::REGEX);
        $this->fullName = $fullName;
    }

    public function __toString(): string
    {
        return $this->fullName;
    }
}
