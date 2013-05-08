<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests;

use Mongator\Cache\ArrayCache;

use Mongator\Connection;
use Mongator\Mongator;
use Mongator\Archive;
use Mongator\Id\IdGeneratorContainer;
use Mongator\Type\Container as TypeContainer;

class TestCase extends \PHPUnit_Framework_TestCase
{
    static protected $staticConnection;
    static protected $staticGlobalConnection;
    static protected $staticMongator;

    protected $metadataClass = 'Model\Mapping\Metadata';
    protected $server = 'mongodb://localhost:27017';
    protected $dbName = 'Mongator_tests';

    protected $connection;
    protected $globalConnection;
    protected $mongator;
    protected $unitOfWork;
    protected $metadataFactory;
    protected $cache;
    protected $mongo;
    protected $db;

    protected function setUp()
    {
        if (!class_exists('Mongo')) {
            $this->markTestSkipped('Mongo is not available.');
        }

        if (!static::$staticConnection) {
            static::$staticConnection = new Connection($this->server, $this->dbName);
        }
        $this->connection = static::$staticConnection;

        if (!static::$staticGlobalConnection) {
            static::$staticGlobalConnection = new Connection($this->server, $this->dbName.'_global');
        }
        $this->globalConnection = static::$staticGlobalConnection;

        if (!static::$staticMongator) {
            static::$staticMongator = new Mongator(new $this->metadataClass);
            static::$staticMongator->setConnection('default', $this->connection);
            static::$staticMongator->setConnection('global', $this->globalConnection);
            static::$staticMongator->setDefaultConnectionName('default');
            static::$staticMongator->setFieldsCache(new ArrayCache());
            static::$staticMongator->setDataCache(new ArrayCache());
        }
        $this->mongator = static::$staticMongator;
        $this->unitOfWork = $this->mongator->getUnitOfWork();
        $this->unitOfWork->clear();
        $this->unitOfWork->clear();
        $this->metadataFactory = $this->mongator->getMetadataFactory();
        $this->cache = $this->mongator->getFieldsCache();

        foreach ($this->mongator->getAllRepositories() as $repository) {
            $repository->getIdentityMap()->clear();
        }

        $this->mongo = $this->connection->getMongo();
        $this->db = $this->connection->getMongoDB();

        foreach ($this->db->listCollections() as $collection) {
            $collection->deleteIndexes();
            $collection->drop();
        }
    }

    protected function tearDown()
    {
        Archive::clear();
        IdGeneratorContainer::reset();
        TypeContainer::reset();
    }

    protected function getRepository($modelClass)
    {
        return $this->mongator->getRepository($modelClass);
    }

    protected function getCollection($modelClass)
    {
        return $this->getRepository($modelClass)->getCollection();
    }

    protected function create($modelClass)
    {
        return $this->mongator->create($modelClass);
    }

    protected function createCategory($name = 'foo')
    {
        return $this->mongator->create('Model\Category')->setName($name)->save();
    }

    protected function createArticle($title = 'foo')
    {
        return $this->mongator->create('Model\Article')->setTitle($title)->save();
    }

    protected function createArticles($nb, $idAsKey = true)
    {
        $articles = array();
        foreach ($this->createArticlesRaw($nb) as $articleRaw) {
            $article = $this->mongator->create('Model\Article')->setId($articleRaw['_id'])->setIsNew(false);
            if ($idAsKey) {
                $articles[$article->getId()->__toString()] = $article;
            } else {
                $articles[] = $article;
            }
        }

        return $articles;
    }

    protected function createArticlesRaw($nb)
    {
        $articles = array();
        for ($i=0; $i < $nb; $i++) {
            $articles[] = array(
                'title'   => 'Article'.$i,
                'content' => 'Content'.$i,
                'slug' => 'Slug'.$i,
                'slug' => 'Slug'.$i,
            );
        }
        $this->mongator->getRepository('Model\Article')->getCollection()->batchInsert($articles);

        return $articles;
    }

    protected function createMessageRaw($nb)
    {
        $messages = array();
        for ($i=0; $i < $nb; $i++) {
            $messages[] = array(
                'author'   => 'Author '.$i,
                'text' => 'Text '.$i
            );
        }
        $result = $this->mongator->getRepository('Model\Message')->getCollection()->batchInsert($messages);
        return $messages;
    }


    protected function createCachedRaw($nb)
    {
        $articles = array();
        for ($i=0; $i < $nb; $i++) {
            $articles[] = array(
                'title'   => 'Article'.$i,
                'content' => 'Content'.$i,
                'slug' => 'Slug'.$i,
                'slug' => 'Slug'.$i,
            );
        }
        $this->mongator->getRepository('Model\Cached')->getCollection()->batchInsert($articles);

        return $articles;
    }


    protected function removeFromCollection($document)
    {
        $document
            ->getRepository()
            ->getCollection()
            ->remove(array('_id' => $document->getId()));
    }

    protected function documentExists($document)
    {
        return (Boolean) $document
            ->getRepository()
            ->getCollection()
            ->findOne(array('_id' => $document->getId()));
    }

    public function fixMissingReferencesDataProvider()
    {
        return array(
            array(1),
            array(2),
            array(100),
        );
    }
}
