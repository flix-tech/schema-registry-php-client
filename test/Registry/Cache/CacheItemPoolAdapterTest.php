<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry\Cache;

use FlixTech\SchemaRegistryApi\Registry\Cache\CacheItemPoolAdapter;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheItemPoolAdapterTest extends AbstractCacheAdapterTestCase
{
    protected function getAdapter(): CacheAdapter
    {
        return new CacheItemPoolAdapter(new ArrayAdapter());
    }
}
