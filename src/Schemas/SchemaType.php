<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schemas;

/**
 * @phpstan-template T
 */
interface SchemaType
{
    /**
     * @phpstan-return T
     */
    public static function instance();

    public function value(): string;
}
