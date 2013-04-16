<?php

/*
 * This file is part of Mandango.
 *
 * (c) Máximo Cuadros <maximo@yunait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Cache;

/**
 * AbstractCache.
 *
 * @author Máximo Cuadros <maximo@yunait.com>
 */
class ArrayCache extends AbstractCache
{
    private $data = array();

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 0)
    {
        $content = $this->pack($key, $value, $ttl);

        $this->data[$key] = $this->pack($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function info($key)
    {
        if ( !isset($this->data[$key]) ) {
            return null;  
        }

        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->data = array();
    }
}
