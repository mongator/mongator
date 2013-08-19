<?php

/*
 * This file is part of Mongator.
 *
 * (c) Máximo Cuadros <maximo@yunait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator;

use Mongator\Repository;

/**
 * Query.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class IndexManager
{
    private $repository;
    private $collection;
    private $indexes;
    private $config;

    /**
     * Constructor.
     *
     * @param \Mongator\Repository $repository The repository of the document class.
     *
     * @api
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->collection = $repository->getCollection();

        $class = $repository->getDocumentClass();

        $classConfig = $repository->getMongator()->getMetadataFactory()->getClass($class);
        if ( isset($classConfig['_indexes']) ) $this->config = $classConfig['_indexes'];
    }

    /**
     * Returns the repository.
     *
     * @return \Mongator\Repository The repository.
     *
     * @api
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Returns the indexes config for this respository.
     *
     * @return \Mongator\Repository The repository.
     *
     * @api
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the diferences between server indexes and class config, when a indexes change
     * will be marked in db as unknown and missing the new version.
     *
     * @return array Associative array with keys: missing, present and unknown
     *
     * @api
     */
    public function getDiff()
    {
        $set = $this->getIndexInfo();
        unset($set['_id_1']);

        $present = array();
        $missing = array();

        foreach ($this->config as $index) {
            if ( !isset($index['options']) ) $index['options'] = array();

            $name = $this->generateIndexKeyFromConfig($index);
            if ( isset($set[$name]) ) {
                $present[$name] = $index;
                unset($set[$name]);
            } else {
                $missing[$name] = $index;
            }
        }

        return array(
            'missing' => $missing,
            'present' => $present,
            'unknown' => $set
        );
    }

    /**
     * Commit the indexes to the database
     *
     * @param boolean $delete (optional) true by default or the unknown indexes will be dropeed from collection
     *
     * @return boolean
     *
     * @api
     */
    public function commit($delete = true)
    {
        $diff = $this->getDiff();

        if ($delete) {
            foreach ($diff['unknown'] as $index) {
                $this->deleteIndex($index['name']);
            }
        }

        foreach ($diff['missing'] as $name => $index) {
            $this->ensureIndex($index['keys'], $index['options']);
        }

        return true;
    }

    private function deleteIndex($name)
    {
        $command = array(
            'deleteIndexes' => $this->collection->getName(),
            'index' => $name
        );

        $result = $this->repository->getConnection()->getMongoDB()->command($command);

        if ( !is_array($result) && !isset($result['ok']) ) {
            throw new \RuntimeException(sprintf(
                'Unable to delete index "%s" at collection %s',
                $name, $this->collection->getName()
            ));
        }

        if ( (int) $result['ok'] != 1 ) {
            throw new \RuntimeException(sprintf(
                'Unable to delete index "%s" at collection %s, message "%s"',
                $name, $this->collection->getName(), $result['errmsg']
            ));
        }

        return true;
    }

    private function ensureIndex(array $config, array $options = array())
    {
        if ( !$this->collection->ensureIndex($config, $options) ) {
            throw new \RuntimeException(sprintf(
                'Unable to create index "%s" at collection %s',
                $name, $this->collection->getName()
            ));
        }

        return true;
    }

    private function getIndexInfo()
    {
        $indexes = array();
        foreach ( $this->collection->getIndexInfo() as $index ) {
            $name = $this->generateIndexKeyFromDB($index);
            $indexes[$name] = $index;
        }

        return $indexes;
    }

    private function generateIndexKeyFromConfig(array $index)
    {
        return $this->generateIndexKey($index['keys'], $index['options']);
    }

    private function generateIndexKeyFromDB(array $index)
    {
        return $this->generateIndexKey($index['key'], $index);
    }

    private function generateIndexKey(array $keys, array $options)
    {
        if ( isset($options['weights']) ) {
            $hash[] = 'text_1';
            $keys = $options['weights'];
        }

       foreach ($keys as $key => $value) {
            if ( is_null($value) ) $hash[] = sprintf('%s_1', $key);
            else if ( is_numeric($value) ) $hash[] = sprintf('%s_%d', $key, $value);
            else $hash[] = sprintf('%s_%s', $key, $value);
        }

        if ( isset($options['unique']) ) {
            $hash[] = sprintf('unique_%d', $options['unique']);
        }

        if ( isset($options['sparse']) ) {
            $hash[] = sprintf('sparse_%d', $options['sparse']);
        }

        if ( isset($options['language']) ) {
            $hash[] = sprintf('language_%d', $options['language']);
        } elseif ( isset($options['default_language']) ) {
            $hash[] = sprintf('language_%d', $options['default_language']);
        }

        sort($hash);

        return implode('_', $hash);
    }
}
