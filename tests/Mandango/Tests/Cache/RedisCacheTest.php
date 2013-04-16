<?php

/*
 * This file is part of Mandango.
 *
 * (c) Máximo Cuadros <maximo@yunait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Cache;

use Mandango\Cache\RedisCache;

class RedisCacheTest extends Cache
{
    protected function getCacheDriver()
    {
       if ( class_exists('Redis') == false ) {
            $this->markTestSkipped(
              'redis extension must be loaded'
            );
        }

        $redis = new \Redis();
        if ( !$connection = $redis->pconnect('127.0.0.1') ) {
            $this->markTestSkipped(
              'unable to connect to localhost redis server'
            );        
        }

        return new RedisCache($redis);
    }
}
