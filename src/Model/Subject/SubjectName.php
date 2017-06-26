<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Subject;

use Assert\Assert;

final class SubjectName
{
    /**
     * @var string
     */
    private $name;

    public static function create(string $name): SubjectName
    {
        Assert::that($name)->notBlank();

        $subjectName = new self();
        $subjectName->name = $name;

        return $subjectName;
    }

    private function __construct()
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name();
    }
}
