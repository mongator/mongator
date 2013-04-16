<?php

/*
 * This file is part of Mandango.
 *
 * (c) Máximo Cuadros <maximo@yunait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Query;

abstract class CachedQuery extends Query
{
    protected $count;


    /**
     * Returns the data in cache.
     *
     * @return array|null The fields in cache, or null if there is not.
     */
    public function getDataCache()
    {
        $key = $this->generateKey(); 
        return $this->getRepository()->getMandango()->getCache()->get($key);
    }

    public function setDataCache($data)
    {
        $key = $this->generateKey();
        return $this->getRepository()->getMandango()->getCache()->set($key, $data);
    }

    public function execute()
    {
        $this->count = false;

        if ( $cache = $this->getDataCache() ) {
            return $cache;
        }

        $result = parent::execute();
        $this->setDataCache($result);
    
        return $result;
    }

    public function count()
    {
        $this->count = true;

        if ( $count = $this->getDataCache() ) {
            return $count;
        }

        $count = parent::execute()->count();
        $this->setDataCache($count);
    
        return $count;
    }

}