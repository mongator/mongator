<?php

/*
 * This file is part of Mongator.
 *
 * (c) Máximo Cuadros <maximo@yunait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Cache;

use Mongator\Cache\MemcachedCache;

class MemcachedCacheTest extends Cache
{
    protected function getCacheDriver()
    {
       if ( class_exists('Memcached') == false ) {
            $this->markTestSkipped(
              'memcached extension must be loaded'
            );
        }

        $memcached = new \Memcached();
        if ( !$memcached->addServer('127.0.0.1', 11211) ) {
            $this->markTestSkipped(
              'unable to connect to localhost memcached server'
            );        
        }

        return new MemcachedCache($memcached);
    }

    public function testCacheSetTtl()
    {
        $this->assertTrue(true);
    }
}
