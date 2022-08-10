<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Schemas;

use Error;
use FlixTech\SchemaRegistryApi\Constants;
use FlixTech\SchemaRegistryApi\Schemas\AvroSchemaType;
use FlixTech\SchemaRegistryApi\Schemas\JsonSchemaType;
use FlixTech\SchemaRegistryApi\Schemas\ProtobufSchemaType;
use Generator;
use PHPUnit\Framework\TestCase;

class SchemaTypeTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideSchemaTypes
     *
     * @phpstan-template template T of SchemaType
     * @param string $className
     * @phpstan-param class-string<T> $className
     * @param string $expected
     */
    public function type_should_match_the_value(string $className, string $expected): void
    {
        self::assertEquals($expected, $className::instance()->value());
        self::assertEquals($expected, (string)$className::instance());
    }

    /**
     * @test
     * @dataProvider provideSchemaTypes
     *
     * @phpstan-template template T of SchemaType
     * @param string $className
     * @phpstan-param class-string<T> $className
     */
    public function types_cannot_be_cloned(string $className): void
    {
        $this->expectException(Error::class);
        $result = clone $className::instance();
    }

    public function provideSchemaTypes(): Generator
    {
        yield 'AvroSchemaType' => [
            AvroSchemaType::class,
            Constants::AVRO_TYPE,
        ];

        yield 'JsonSchemaType' => [
            JsonSchemaType::class,
            Constants::JSON_TYPE,
        ];

        yield 'ProtobufSchemaType' => [
            ProtobufSchemaType::class,
            Constants::PROTOBUF_TYPE,
        ];
    }
}
