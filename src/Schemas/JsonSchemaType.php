<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schemas;

use FlixTech\SchemaRegistryApi\Constants;

/**
 * @implements SchemaType<JsonSchemaType>
 */
final class JsonSchemaType extends ValueObject implements SchemaType
{
    /**
     * @var JsonSchemaType
     */
    private static $instance;

    public static function instance(): JsonSchemaType
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new self();

        return self::$instance;
    }

    public function value(): string
    {
        return Constants::JSON_TYPE;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
