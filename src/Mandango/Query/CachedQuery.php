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
    /**
     * Returns the data in cache.
     *
     * @return array|null The fields in cache, or null if there is not.
     */
    public function getDataCache()
    {
        $key = $this->generateKey(); var_dump($key);

        $cache = $this->getRepository()->getMandango()->getCache()->get($key);


        return ($cache && isset($cache['data'])) ? new \ArrayObject(unserialize($cache['data'])) : null;
    }

    public function setDataCache($data)
    {
        $key = $this->generateKey();  var_dump($key);

        $array = array();
        foreach($data as $id => $document) {
            $array[$id] = $document;
        }

        //var_dump(serialize($data));
        $cache = array(
            'key' => $key,
            'time' => time(),
            'data' => serialize($array)
        );

       

        $this->getRepository()->getMandango()->getCache()->set($key, $cache);

        return new \ArrayObject($array);
    }

    public function execute()
    {
        if ( $cache = $this->getDataCache() ) {
         echo "cache";
            return $cache;
        }

        $result = parent::execute();
        $this->setDataCache($result);
    
        return $result;
    }
}