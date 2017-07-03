<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Subject;

use Assert\Assert;

final class VersionId
{
    const LATEST = 'latest';

    /**
     * @var int|string
     */
    private $id;

    public static function latest(): VersionId
    {
        $instance = new self();
        $instance->id = self::LATEST;

        return $instance;
    }

    public static function create(int $id): VersionId
    {
        Assert::that($id)->between(1, (2 ** 31) - 1);

        $instance = new self();
        $instance->id = (int) $id;

        return $instance;
    }

    private function __construct()
    {
    }

    public function value()
    {
        return $this->id;
    }

    public function equals(VersionId $other): bool
    {
        return $this->id === $other->id;
    }

    public function __toString(): string
    {
        return (string) $this->value();
    }
}
