<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schemas;

abstract class SchemaTypes
{
    final public static function avro(): AvroSchemaType
    {
        return AvroSchemaType::instance();
    }

    final public static function json(): JsonSchemaType
    {
        return JsonSchemaType::instance();
    }

    final public static function protobuf(): ProtobufSchemaType
    {
        return ProtobufSchemaType::instance();
    }

    final protected function __construct()
    {
    }

    /**
     * @codeCoverageIgnore
     */
    final private function __clone()
    {
    }
}
