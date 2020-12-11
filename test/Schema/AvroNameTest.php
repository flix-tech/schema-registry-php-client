<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Schema;

use FlixTech\SchemaRegistryApi\Schema\AvroName;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AvroNameTest extends TestCase
{
    /**
     * @dataProvider avroReferences
     * @test
     */
    public function it_should_only_be_constructable_from_a_valid_Avro_reference(string $fullName, bool $isValid): void {
        if (!$isValid) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertSame((string) new AvroName($fullName), $fullName);
    }

    public static function avroReferences(): Generator {
        yield 'Valid root name' => ['test', true];
        yield 'Valid full name' => ['test.example', true];
        yield 'Empty full name' => ['', false];
        yield 'Invalid full name' => ['-test', false];
    }
}
