<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Document;

use Mongator\Tests\TestCase;
use Mongator\Document\Document as BaseDocument;

class Document extends BaseDocument
{
}

class DocumentTest extends TestCase
{
    public function testSetGetId()
    {
        $document = new Document($this->mongator);
        $this->assertNull($document->getId());

        $id = new \MongoId('4af9f23d8ead0e1d32000000');
        $this->assertSame($document, $document->setId($id));
        $this->assertSame($id, $document->getId());
    }

    public function testQueryHashes()
    {
        $hashes = array(md5(1), md5(2), md5(3));

        $document = new Document($this->mongator);
        $this->assertSame(array(), $document->getQueryHashes());
        $document->addQueryHash($hashes[0]);
        $this->assertSame(array($hashes[0]), $document->getQueryHashes());
        $document->addQueryHash($hashes[1]);
        $document->addQueryHash($hashes[2]);
        $this->assertSame($hashes, $document->getQueryHashes());
        $document->removeQueryHash($hashes[1]);
        $this->assertSame(array($hashes[0], $hashes[2]), $document->getQueryHashes());
        $document->clearQueryHashes();
        $this->assertSame(array(), $document->getQueryHashes());
    }

    public function testAddFieldCache()
    {
        $query1 = $this->mongator->getRepository('Model\Article')->createQuery();
        $query2 = $this->mongator->getRepository('Model\Article')->createQuery();

        $article = $this->mongator->create('Model\Article');
        $article->addQueryHash($query1->getHash());
        $article->addFieldCache('title');
        $cache1= $query1->getFullCache();
        $this->assertSame(array('title' => 1), $cache1['fields']);

        $article->addFieldCache('source.name');
        $cache1= $query1->getFullCache();
        $this->assertSame(array('title' => 1, 'source.name' => 1), $cache1['fields']);

        $article->addQueryHash($query2->getHash());
        $article->addFieldCache('note');
        $cache1= $query1->getFullCache();
        $this->assertSame(array('title' => 1, 'source.name' => 1, 'note' => 1), $cache1['fields']);

        $cache2= $query2->getFullCache();
        $this->assertSame(array('note' => 1), $cache2['fields']);

        $article->addFieldCache('comments.infos');
        $cache1= $query1->getFullCache();
        $cache2= $query2->getFullCache();

        $this->assertSame(array('title' => 1, 'source.name' => 1, 'note' => 1, 'comments.infos' => 1), $cache1['fields']);
        $this->assertSame(array('note' => 1, 'comments.infos' => 1), $cache2['fields']);

    }

    public function testAddReferenceCache()
    {
        $query1 = $this->mongator->getRepository('Model\Article')->createQuery();
        $query2 = $this->mongator->getRepository('Model\Article')->createQuery();

        $article = $this->mongator->create('Model\Article');
        $article->addQueryHash($query1->getHash());
        $article->addReferenceCache('author');
        $cache1= $query1->getFullCache();
        $this->assertSame(array('author'), $cache1['references']);

        $article->addReferenceCache('categories');
        $cache1= $query1->getFullCache();
        $this->assertSame(array('author', 'categories'), $cache1['references']);

        $article->addQueryHash($query2->getHash());
        $article->addReferenceCache('note');
        $cache1= $query1->getFullCache();
        $this->assertSame(array('author', 'categories', 'note'), $cache1['references']);

        $cache2= $query2->getFullCache();
        $this->assertSame(array('note'), $cache2['references']);

        $article->addReferenceCache('comments');
        $cache1= $query1->getFullCache();
        $cache2= $query2->getFullCache();

        $this->assertSame(array('author', 'categories', 'note', 'comments'), $cache1['references']);
        $this->assertSame(array('note', 'comments'), $cache2['references']);
    }

    public function testAddReferenceCacheDouble()
    {
        $query1 = $this->mongator->getRepository('Model\Article')->createQuery();
        $query2 = $this->mongator->getRepository('Model\Article')->createQuery();

        $article = $this->mongator->create('Model\Article');
        $article->addQueryHash($query1->getHash());
        $article->addReferenceCache('author');

        $cache1= $query1->getFullCache();
        $this->assertSame(array('author'), $cache1['references']);

        $article->addReferenceCache('author');
        $cache1= $query1->getFullCache();
        $this->assertSame(array('author'), $cache1['references']);

        $article->addQueryHash($query2->getHash());
        $article->addReferenceCache('author');
        $cache1= $query1->getFullCache();
        $cache2= $query2->getFullCache();
        $this->assertSame(array('author'), $cache1['references']);
        $this->assertSame(array('author'), $cache2['references']);

        $article->addReferenceCache('author');
        $cache1= $query1->getFullCache();
        $cache2= $query2->getFullCache();
        $this->assertSame(array('author'), $cache1['references']);
        $this->assertSame(array('author'), $cache2['references']);
    }

    public function testIsnew()
    {
        $document = new Document($this->mongator);
        $this->assertTrue($document->isNew());

        $document->setIsNew(false);
        $this->assertFalse($document->isNew());
    }

    public function testLoadFullBug()
    {
        $article = $this->mongator->create('Model\Article');
        $article->setTitle('foo');

        $article->getAuthor();
        $article->getContent();
        $this->assertSame('foo', $article->getTitle());
    }

    public function testLoadFullOnDemand()
    {
        $articleRaw = array(
            'content' => 'bar',
            'source' => array(
                'note' => 'fooups',
                'info' => array(
                    ''
                ),
            ),
        );
        $this->mongator->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $article = $this->mongator->getRepository('Model\Article')
            ->createQuery()
            ->fields(array('title' => 1, 'source.note' => 1))
            ->one();

        $articleRaw['source']['name'] = 'foobar';
        $articleRaw['content'] = 'baz';
        $this->mongator->getRepository('Model\Article')->getCollection()->save($articleRaw);

        $this->assertEquals('baz', $article->getContent()); // This will cause a query

        $this->mongator->getRepository('Model\Article')->getCollection()->remove($article->getId());

        // No more queries from now on
        $this->assertEquals('fooups', $article->getSource()->getNote());
        $this->assertEquals('foobar', $article->getSource()->getName());
    }
}
