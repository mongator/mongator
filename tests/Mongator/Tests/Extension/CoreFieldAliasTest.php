<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Extension;

use Mongator\Tests\TestCase;

class CoreFieldAliasTest extends TestCase
{
    public function testDocumentSettersGetters()
    {
        $this->assertTrue(method_exists('Model\Article', 'getDatabase'));
        $this->assertTrue(method_exists('Model\Article', 'setDatabase'));
        $this->assertFalse(method_exists('Model\Article', 'getBasatos'));
        $this->assertFalse(method_exists('Model\Article', 'setBasatos'));
    }

    public function testDocumentGetterQuery()
    {
        $articleRaw = array(
            'basatos' => 123
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $this->mongator->getRepository('Model\Article')->getIdentityMap()->clear();
        $article = $this->mongator->getRepository('Model\Article')->findOneById($articleRaw['_id']);

        $this->assertSame('123', $article->getDatabase());
    }

    public function testDocumentGetterQueryEmbedded()
    {
        $articleRaw = array(
            'source' => array(
                'desde' => 123,
            ),
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $this->mongator->getRepository('Model\Article')->getIdentityMap()->clear();
        $article = $this->mongator->getRepository('Model\Article')->findOneById($articleRaw['_id']);

        $this->assertSame('123', $article->getSource()->getFrom());
    }

    public function testDocumentGetterSaveFieldQueryCache()
    {
        $articleRaw = array(
            'basatos' => '123',
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $query = $this->mongator->getRepository('Model\Article')->createQuery();
        $article = $query->one();

        $cache = $query->getFullCache();
        $this->assertFalse(isset($cache['fields']));

        $article->getDatabase();
        $cache = $query->getFullCache();
        $this->assertSame(array('basatos' => 1), $cache['fields']);
    }

    public function testDocumentGetterSaveFieldQueryCacheEmbedded()
    {

        $articleRaw = array(
            'source' => array(
                'desde' => '123',
            ),
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $query = $this->mongator->getRepository('Model\Article')->createQuery();
        $article = $query->one();

        $cache = $query->getFullCache();
        $this->assertFalse(isset($cache['fields']));

        $article->getSource()->getFrom();
        $cache = $query->getFullCache();
        $this->assertSame(array('source.desde' => 1), $cache['fields']);
    }

    public function testDocumentSetDocumentData()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setDocumentData(array(
            'basatos' => 123,
        ));
        $this->assertSame('123', $article->getDatabase());
    }

    public function testDocumentQueryForSaveNew()
    {
        $article = $this->mongator->create('Model\Article')->setDatabase(123);
        $this->assertSame(array(
            'basatos' => '123',
        ), $article->queryForSave());
    }

    public function testDocumentQueryForSaveUpdate()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setDocumentData(array(
            '_id' => new \MongoId(),
            'basatos' => '234',
        ));

        $article->setDatabase(345);
        $this->assertSame(array(
            '$set' => array(
                'basatos' => '345',
            ),
        ), $article->queryForSave());

        $article->setDatabase(null);
        $this->assertSame(array(
            '$unset' => array(
                'basatos' => 1,
            ),
        ), $article->queryForSave());
    }

    public function testDocumentQueryForSaveEmbeddedNew()
    {

        $source = $this->mongator->create('Model\Source')->setFrom(123);
        $article = $this->mongator->create('Model\Article')->setSource($source);
        $this->assertSame(array(
            'source' => array(
                'desde' => '123',
            ),
        ), $article->queryForSave());
    }

    public function testDocumentQueryForSaveEmbeddedNotNew()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setDocumentData(array(
            '_id' => new \MongoId(),
            'source' => array(
                'desde' => '234',
            ),
        ));
        $source = $article->getSource();

        $source->setFrom(345);
        $this->assertSame(array(
            '$set' => array(
                'source.desde' => '345',
            ),
        ), $article->queryForSave());

        $source->setFrom(null);
        $this->assertSame(array(
            '$unset' => array(
                'source.desde' => 1,
            ),
        ), $article->queryForSave());
    }
}
