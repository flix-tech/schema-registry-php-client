<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Schema;

use Assert\Assert;

class Id
{
    /**
     * @var int
     */
    protected $id;

    final public static function create(int $id): Id
    {
        Assert::that($id)->greaterThan(0);

        $instance = new self();
        $instance->id = $id;

        return $instance;
    }

    final protected function __construct()
    {
    }

    public function value(): int
    {
        return $this->id;
    }

    final public function __toString(): string
    {
        return (string) $this->value();
    }

    final public function equals(Id $other): bool
    {
        return $this->value() === $other->value();
    }
}
