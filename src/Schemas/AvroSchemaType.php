<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schemas;

use FlixTech\SchemaRegistryApi\Constants;

/**
 * @implements SchemaType<AvroSchemaType>
 */
final class AvroSchemaType extends ValueObject implements SchemaType
{
    /**
     * @var AvroSchemaType
     */
    private static $instance;

    public static function instance(): AvroSchemaType
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new self();

        return self::$instance;
    }

    public function value(): string
    {
        return Constants::AVRO_TYPE;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
