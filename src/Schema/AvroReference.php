<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schema;

final class AvroReference
{
    /**
     * @var string
     */
    private $fullName;

    public function __construct(string $fullName)
    {
        $this->fullName = $fullName;
    }
}
