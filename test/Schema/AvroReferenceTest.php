<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Schema;

use Generator;
use PHPUnit\Framework\TestCase;

class AvroReferenceTest extends TestCase
{
    /**
     * @dataProvider references
     */
    public function it_should_be_constructable(): void {}

    public static function references(): Generator {
        yield 'Valid with latest' => [
            'test.example.MyRecord',
            'example-value',
            'latest'
        ];
    }
}
