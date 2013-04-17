<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Cache;

use Mandango\Tests\TestCase;

abstract class Cache extends TestCase
{
    protected $cache;

    protected function setUp()
    {
        $this->cache = $this->getCacheDriver();
    }

    protected function tearDown()
    {
    	if ( $this->cache ) $this->cache->clear();
    	parent::tearDown();
    }

    public function testCache()
    {
        $key1 = 'foo';
        $key2 = 'bar';
        $value1 = 'ups';
        $value2 = 'ngo';

        $this->assertFalse($this->cache->has($key1));
        $this->assertFalse($this->cache->has($key2));

        $this->cache->set($key1, $value1);
        $this->assertTrue($this->cache->has($key1));
        $this->assertFalse($this->cache->has($key2));
        $this->assertSame($value1, $this->cache->get($key1));
        $this->assertNull($this->cache->get($key2));

        $this->cache->set($key2, $value2);
        $this->assertTrue($this->cache->has($key1));
        $this->assertTrue($this->cache->has($key2));
        $this->assertSame($value1, $this->cache->get($key1));
        $this->assertSame($value2, $this->cache->get($key2));

        $this->cache->remove($key1);
        $this->assertFalse($this->cache->has($key1));
        $this->assertTrue($this->cache->has($key2));
        $this->assertNull($this->cache->get($key1));
        $this->assertSame($value2, $this->cache->get($key2));

        $this->cache->clear();
        $this->assertFalse($this->cache->has($key1));
        $this->assertFalse($this->cache->has($key2));
    }

    public function testCacheHas()
    {
        $key1 = 'foo';
        $key2 = 'bar';
        $value1 = 'ups';
        $value2 = 'ngo';

        $this->assertFalse($this->cache->has($key1));
        $this->assertFalse($this->cache->has($key2));
    }
    
    public function testCacheSet()
    {
        $key1 = 'foo';
        $key2 = 'bar';
        $value1 = 'ups';
        $value2 = 'ngo';

        $this->cache->set($key1, $value1);
        $this->assertTrue($this->cache->has($key1));
        $this->assertFalse($this->cache->has($key2));
        $this->assertSame($value1, $this->cache->get($key1));
        $this->assertNull($this->cache->get($key2));

        $this->cache->set($key2, $value2);
        $this->assertTrue($this->cache->has($key1));
        $this->assertTrue($this->cache->has($key2));
        $this->assertSame($value1, $this->cache->get($key1));
        $this->assertSame($value2, $this->cache->get($key2));
    }

    public function testCacheSetTtl()
    {
        $key1 = 'foo';
        $value1 = 'ups';
        $ttl = 1;

        $this->cache->set($key1, $value1, $ttl);
        $this->assertTrue($this->cache->has($key1));
        $this->assertSame($value1, $this->cache->get($key1));

        usleep(1300000);
        $this->assertFalse($this->cache->has($key1));
    }

    public function testCacheRemove()
    {
        $key1 = 'foo';
        $key2 = 'bar';
        $value1 = 'ups';
        $value2 = 'ngo';

        $this->cache->set($key1, $value1);
        $this->cache->set($key2, $value2);

        $this->cache->remove($key1);
        $this->assertFalse($this->cache->has($key1));
        $this->assertTrue($this->cache->has($key2));
        $this->assertNull($this->cache->get($key1));
        $this->assertSame($value2, $this->cache->get($key2));
    }

    public function testCacheClear()
    {
        $key1 = 'foo';
        $key2 = 'bar';
        $value1 = 'ups';
        $value2 = 'ngo';

        $this->cache->set($key1, $value1);
        $this->cache->set($key2, $value2);

        $this->cache->clear();
        $this->assertFalse($this->cache->has($key1));
        $this->assertFalse($this->cache->has($key2));
    }

    public function testCacheInfo()
    {
        $key1 = 'foo';
        $value1 = 'ups';

        $this->cache->set($key1, $value1);
        $array = $this->cache->info($key1);

        $expected = Array(
            'key' => 'foo',
            'time' => time(),
            'ttl' => 0,
            'value' => 'ups'
        );

        $this->assertSame($expected, $this->cache->info($key1));
    }

    abstract protected function getCacheDriver();
}
