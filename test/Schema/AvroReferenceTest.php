<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Schema;

use Generator;
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
     */
    public function it_should_be_constructable(string $avroName, string $subject, $version, bool $isValid): void {}

    public static function references(): Generator {
        yield 'Valid with latest' => [
            'test.example.MyRecord',
            'example-value',
            'latest',
            true
        ];
    }
}
