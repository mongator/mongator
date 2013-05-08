<?php

/*
 * This file is part of Mongator.
 *
 * (c) Máximo Cuadros <maximo@yunait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Cache;

/**
 * AbstractCache.
 *
 * @author Máximo Cuadros <maximo@yunait.com>
 */
class RedisCache extends AbstractCache
{
    private $redis;
    private $keyPattern;

    /**
     * Constructor.
     *
     * @param Redis $redis the redis instance
     * @param string $keyPattern (optional) redis format key, printf format
     */
    public function __construct(\Redis $redis, $keyPattern = '{Mongator}{%s}')
    {
        $this->redis = $redis;
        $this->keyPattern = $keyPattern;
    }

    private function getRedisKey($key)
    {
        return sprintf($this->keyPattern, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        $key = $this->getRedisKey($key);
        return (boolean)$this->redis->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 0)
    {
        $content = $this->pack($key, $value, $ttl);
        $key = $this->getRedisKey($key);

        $string = serialize($content);
        if ( (int)$ttl == 0 ) $this->redis->set($key, $string);
        else $this->redis->setex($key, $ttl, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $key = $this->getRedisKey($key);
        return (boolean)$this->redis->delete($key);
    }

    /**
     * {@inheritdoc}
     */ 
    public function clear()
    {
        $pattern = $this->getRedisKey('*');
        foreach ($this->redis->keys($pattern) as $key) {
            $this->redis->delete($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function info($key) {
        $key = $this->getRedisKey($key);
        if ( !$content = $this->redis->get($key) ) {
            return false;
        }

        return unserialize($content);
    }
}
