<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schemas;

use FlixTech\SchemaRegistryApi\Constants;

/**
 * @implements SchemaType<ProtobufSchemaType>
 */
final class ProtobufSchemaType extends SchemaTypes implements SchemaType
{
    /**
     * @var ProtobufSchemaType
     */
    private static $instance;

    public static function instance(): ProtobufSchemaType
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new self();

        return self::$instance;
    }

    public function value(): string
    {
        return Constants::PROTOBUF_TYPE;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
