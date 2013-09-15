<?php

/*
 * This file is part of Mongator.
 *
 * (c) Máximo Cuadros <mcuadros@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator;

/**
 * Archive to save things related to objects.
 *
 * @author Máximo Cuadros <mcuadros@gmail.com>
 */
class Archive
{
    private $archive = array();

    /**
     * Returns if has a key in the archive.
     *
     * @param string $key    The key.
     *
     * @return bool If key in the archive.
     */
    public function has($key)
    {
        return isset($this->archive[$key]);
    }

    /**
     * Returns the value of a key.
     *
     * It does not check if the object key exists, if you want to check it, do by yourself.
     *
     * @param string $key    The key.
     *
     * @return mixed The value of the key.
     */
    public function get($key)
    {
        return $this->archive[$key];
    }

    /**
     * Set a key value.
     *
     * @param string $key    The key.
     * @param mixed  $value  The value.
     */
    public function set($key, $value)
    {
        if ($value === null) {
            $value = false;
        }

        $this->archive[$key] = $value;
    }

    /**
     * Remove a key.
     *
     * @param string $key    The key.
     */
    public function remove($key)
    {
        unset($this->archive[$key]);
    }

    /**
     * Returns a key by reference. It creates the key if the key does not exist.
     *
     * @param string $key     The key.
     * @param mixed  $default The default value, used to create the key if it does not exist (null by default).
     *
     * @return mixed The object key value.
     */
    public function &getByRef($key, $default = null)
    {
        if (!$this->has($key)) {
            $this->set($key, $default);
        }

        return $this->archive[$key];
    }

    /**
     * Returns an object key or returns a default value otherwise.
     *
     * @param string $key     The key.
     * @param mixed  $default The value to return if the object key does not exist.
     *
     * @return mixed The object key value or the default value.
     */
    public function getOrDefault($key, $default)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        return $default;
    }

    /**
     * Returns all objects data.
     *
     * @return array All objects data.
     */
    public function all()
    {
        return $this->archive;
    }

    /**
     * Clear all objects data.
     */
    public function clear()
    {
        $this->archive = array();
    }
}
