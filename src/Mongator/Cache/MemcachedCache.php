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
class MemcachedCache extends AbstractCache
{
    private $memcached;
    private $keys = array();

    /**
     * Constructor.
     *
     * @param Memcache $memcache the memcache instance
     */
    public function __construct(\Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return (boolean)$this->memcached->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 0)
    {
        $this->keys[] = $key;
        $content = $this->pack($key, $value, $ttl);

        $string = serialize($content);

        if ( (int)$ttl != 0 ) $ttl = time() + $ttl;
        $this->memcached->set($key, $string, (int)$ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        return (boolean)$this->memcached->delete($key);
    }

    /**
     * {@inheritdoc}
     */ 
    public function clear()
    {
        foreach ($this->keys as $key) {
            $this->memcached->delete($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function info($key)
    {
        if ( !$content = $this->memcached->get($key) ) {
            return false;
        }

        return unserialize($content);
    }
}
