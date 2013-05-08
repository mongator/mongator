<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Type;

/**
 * Container of types.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class Container
{
    static private $map = array(
        'bin_data'       => 'Mongator\Type\BinDataType',
        'boolean'        => 'Mongator\Type\BooleanType',
        'date'           => 'Mongator\Type\DateType',
        'float'          => 'Mongator\Type\FloatType',
        'integer'        => 'Mongator\Type\IntegerType',
        'raw'            => 'Mongator\Type\RawType',
        'referenceOne'  => 'Mongator\Type\ReferenceOneType',
        'referenceMany' => 'Mongator\Type\ReferenceManyType',
        'serialized'     => 'Mongator\Type\SerializedType',
        'string'         => 'Mongator\Type\StringType',
    );

    static private $types = array();

    /**
     * Returns if exists a type by name.
     *
     * @param string $name The type name.
     *
     * @return bool Returns if the type exists.
     *
     * @api
     */
    static public function has($name)
    {
        return isset(static::$map[$name]);
    }

    /**
     * Add a type.
     *
     * @param string $name  The type name.
     * @param string $class The type class.
     *
     * @throws \InvalidArgumentException If the type already exists.
     * @throws \InvalidArgumentException If the class is not a subclass of Mongator\Type\Type.
     *
     * @api
     */
    static public function add($name, $class)
    {
        if (static::has($name)) {
            throw new \InvalidArgumentException(sprintf('The type "%s" already exists.', $name));
        }

        $r = new \ReflectionClass($class);
        if (!$r->isSubclassOf('Mongator\Type\Type')) {
            throw new \InvalidArgumentException(sprintf('The class "%s" is not a subclass of Mongator\Type\Type.', $class));
        }

        static::$map[$name] = $class;
    }

    /**
     * Returns a type.
     *
     * @param string $name The type name.
     *
     * @return \Mongator\Type\Type The type.
     *
     * @throws \InvalidArgumentException If the type does not exists.
     *
     * @api
     */
    static public function get($name)
    {
        if (!isset(static::$types[$name])) {
            if (!static::has($name)) {
                throw new \InvalidArgumentException(sprintf('The type "%s" does not exists.', $name));
            }

            static::$types[$name] = new static::$map[$name];
        }

        return static::$types[$name];
    }

    /**
     * Remove a type.
     *
     * @param string $name The type name.
     *
     * @throws \InvalidArgumentException If the type does not exists.
     *
     * @api
     */
    static public function remove($name)
    {
        if (!static::has($name)) {
            throw new \InvalidArgumentException(sprintf('The type "%s" does not exists.', $name));
        }

        unset(static::$map[$name], static::$types[$name]);
    }

    /**
     * Reset the types.
     *
     * @api
     */
    static public function reset()
    {
        static::$map = array(
            'bin_data'       => 'Mongator\Type\BinDataType',
            'boolean'        => 'Mongator\Type\BooleanType',
            'date'           => 'Mongator\Type\DateType',
            'float'          => 'Mongator\Type\FloatType',
            'integer'        => 'Mongator\Type\IntegerType',
            'raw'            => 'Mongator\Type\RawType',
            'referenceOne'  => 'Mongator\Type\ReferenceOneType',
            'referenceMany' => 'Mongator\Type\ReferenceManyType',
            'serialized'     => 'Mongator\Type\SerializedType',
            'string'         => 'Mongator\Type\StringType',
        );

        static::$types = array();
    }
}
