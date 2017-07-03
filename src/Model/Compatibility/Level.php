<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Compatibility;

use Assert\Assert;

class Level implements \JsonSerializable
{
    const NONE = 'NONE';
    const BACKWARD = 'BACKWARD';
    const FORWARD = 'FORWARD';
    const FULL = 'FULL';

    /**
     * @var string
     */
    protected $level;

    final public static function create(string $level): Level
    {
        Assert::that($level)->inArray([self::NONE, self::BACKWARD, self::FORWARD, self::FULL]);

        $instance = new self();
        $instance->level = $level;

        return $instance;
    }

    final protected function __construct()
    {
    }

    public function value(): string
    {
        return $this->level;
    }

    final public function __toString(): string
    {
        return $this->value();
    }

    final public function equals(Level $other): bool
    {
        return $this->value() === $other->value();
    }

    public function jsonSerialize(): array
    {
        return ['compatibility' => $this->level];
    }
}
