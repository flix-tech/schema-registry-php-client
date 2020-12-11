<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Schema;

use FlixTech\SchemaRegistryApi\Schema\AvroName;
use FlixTech\SchemaRegistryApi\Schema\AvroReference;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AvroReferenceTest extends TestCase
{
    /**
     * @test
     * @dataProvider references
     * @param string     $avroName
     * @param string     $subject
     * @param string|int $version
     * @param bool       $isValid
     * @param string     $expectedJson
     */
    public function it_should_be_constructable(string $avroName, string $subject, $version, bool $isValid, string $expectedJson): void {
        if (!$isValid) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertJsonStringEqualsJsonString(
            \json_encode(new AvroReference(new AvroName($avroName), $subject, $version)),
            $expectedJson
        );
    }

    public static function references(): Generator {
        yield 'Valid version with latest' => [
            'test.example.MyRecord',
            'example-value',
            'latest',
            true,
            /** @lang JSON */ <<<JSON
{
  "name": "test.example.MyRecord",
  "subject": "example-value",
  "version": "latest"
}
JSON
        ];
    }
}
