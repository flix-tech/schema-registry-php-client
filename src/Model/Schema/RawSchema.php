<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Schema;

use Assert\Assert;

class RawSchema
{
    /**
     * @var string
     */
    protected $schema;

    public static function create(string $schema): RawSchema
    {
        Assert::that($schema)->isJsonString();

        $instance = new self();
        $instance->schema = $schema;

        return $instance;
    }

    final protected function __construct()
    {
    }

    public function value(): string
    {
        return $this->schema;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
