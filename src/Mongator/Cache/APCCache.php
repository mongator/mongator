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
class APCCache extends AbstractCache
{
    private $data = array();

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 0)
    {
        $content = $this->pack($key, $value, $ttl);
        $string = serialize($content);
        apc_store((string)$key, $string, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        return apc_delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return apc_clear_cache('user');
    }

    /**
     * {@inheritdoc}
     */
    public function info($key) {
        if ( !$content = apc_fetch($key) ) {
            return false;
        }

        return unserialize($content);
    }
}
