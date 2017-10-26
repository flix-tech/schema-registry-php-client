<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Registry\Cache;

use Doctrine\Common\Cache\ArrayCache;
use FlixTech\SchemaRegistryApi\Registry\Cache\DoctrineCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;

class DoctrineCacheAdapterTest extends AbstractCacheAdapterTestCase
{
    protected function getAdapter(): CacheAdapter
    {
        return new DoctrineCacheAdapter(new ArrayCache());
    }
}
