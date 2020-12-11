<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schema;

use Assert\Assertion;

final class AvroReference
{
    private const REGEX = '/.*/';

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
