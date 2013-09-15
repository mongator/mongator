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

class CoreRepositoryTest extends TestCase
{
    public function testIdToMongo()
    {
        $id = '4af9f23d8ead0e1d32000000';
        $idToMongo = $this->mongator->getRepository('Model\Article')->idToMongo($id);
        $this->assertInstanceOf('MongoId', $idToMongo);
    }

    public function testSaveInsertingNotModified()
    {
        $article = $this->mongator->create('Model\Article');
        $this->mongator->getRepository('Model\Article')->save($article);
        $this->assertTrue($article->isNew());

        $articles = array(
            $this->mongator->create('Model\Article'),
            $this->mongator->create('Model\Article')->setTitle('foo'),
        );
        $this->mongator->getRepository('Model\Article')->save($articles);
        $this->assertTrue($articles[0]->isNew());
        $this->assertFalse($articles[1]->isNew());
    }

    public function testSaveUpdatingNotModified()
    {
        $article = $this->mongator->create('Model\Article')->setTitle('foo')->save();
        $this->mongator->getRepository('Model\Article')->save($article);

        $articles = array(
            $this->mongator->create('Model\Article')->setTitle('a1')->save(),
            $this->mongator->create('Model\Article')->setTitle('a2')->save()->setTitle('a2u'),
        );
        $this->mongator->getRepository('Model\Article')->save($articles);
    }

    public function testSaveInsertSingleDocument()
    {
        $article = $this->mongator->create('Model\Article')->fromArray(array(
            'title'   => 'foo',
            'content' => 12345,
        ));

        $this->mongator->getRepository('Model\Article')->save($article);
        $this->assertSame(1, $this->mongator->getRepository('Model\Article')->getCollection()->count());

        $this->assertFalse($article->isNew());
        $this->assertFalse($article->isModified());
        $articleRaw = $this->mongator->getRepository('Model\Article')->getCollection()->findOne();
        $this->assertSame(3, count($articleRaw));
        $this->assertEquals($article->getId(), $articleRaw['_id']);
        $this->assertSame('foo', $articleRaw['title']);
        $this->assertSame('12345', $articleRaw['content']);
        $this->assertTrue($this->mongator->getRepository('Model\Article')->getIdentityMap()->has($article->getId()));
    }

    public function testSaveInsertMultipleDocuments()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = $this->mongator->create('Model\Article')->fromArray(array(
                'title'   => 'foo'.$i,
                'content' => 12345 + $i,
            ));
        }

        $this->mongator->getRepository('Model\Article')->save($articles);
        $this->assertSame(5, $this->mongator->getRepository('Model\Article')->getCollection()->count());

        foreach ($articles as $i => $article) {
            $this->assertFalse($article->isNew());
            $this->assertFalse($article->isModified());
            $articleRaw = $this->mongator->getRepository('Model\Article')->getCollection()->findOne(array('_id' => $article->getId()));
            $this->assertSame(3, count($articleRaw));
            $this->assertEquals($article->getId(), $articleRaw['_id']);
            $this->assertSame('foo'.$i, $articleRaw['title']);
            $this->assertSame(strval(12345 + $i), $articleRaw['content']);
            $this->assertTrue($this->mongator->getRepository('Model\Article')->getIdentityMap()->has($article->getId()));
        }
    }

    public function testSaveUpdateSingleDocument()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = $this->mongator->create('Model\Article')->fromArray(array(
                'title'   => 'foo'.$i,
                'content' => 12345 + $i,
            ));
        }
        $this->mongator->getRepository('Model\Article')->save($articles);

        $articles[2]->setTitle('updated!');
        $this->mongator->getRepository('Model\Article')->save($articles[2]);

        $this->assertFalse($articles[2]->isModified());
        $this->assertSame(4, $this->mongator->getRepository('Model\Article')->getCollection()->find(array('title' => new \MongoRegex('/^foo/')))->count());
    }

    public function testSaveShouldConvertIdsToMongoWhenUpdating()
    {
        $article = $this->create('Model\Article')
            ->setTitle('foo')
            ->save();

        $id = $article->getId();
        $article
            ->setId($id->__toString())
            ->setTitle('bar')
            ->save();

        $collection = $this->getCollection('Model\Article');

        $result = $collection->findOne(array('_id' => $id));
        $expectedResult = array('_id' => $id, 'title' => 'bar');

        $this->assertEquals($expectedResult, $result);
    }

    public function testSaveUpdateMultipleDocument()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = $this->mongator->create('Model\Article')->setTitle('foo'.$i);
        }
        $this->mongator->getRepository('Model\Article')->save($articles);

        $articles[2]->setTitle('updated!');
        $articles[4]->setTitle('updated!');
        $this->mongator->getRepository('Model\Article')->save(array($articles[2], $articles[4]));

        $this->assertFalse($articles[4]->isModified());
        $this->assertFalse($articles[4]->isModified());
        $this->assertSame(3, $this->mongator->getRepository('Model\Article')->getCollection()->find(array('title' => new \MongoRegex('/^foo/')))->count());
    }

    public function testSaveSaveReferences()
    {
        $article = $this->mongator->create('Model\Article')->setTitle('foo');
        $author = $this->mongator->create('Model\Author')->setName('bar');
        $article->setAuthor($author);
        $article->save();

        $this->assertFalse($article->isNew());
        $this->assertFalse($author->isNew());
        $this->assertSame($author->getId(), $article->getAuthorId());
    }

    public function testSaveSaveReferencesSameClass()
    {
        $messages = array();
        $messages['barbelith'] = $this->mongator->create('Model\Message')->setAuthor('barbelith');
        $messages['pablodip'] = $this->mongator->create('Model\Message')->setAuthor('pablodip')->setReplyTo($messages['barbelith']);

        $this->mongator->getRepository('Model\Message')->save($messages);

        $this->assertFalse($messages['pablodip']->isNew());
        $this->assertFalse($messages['barbelith']->isNew());
        $this->assertSame($messages['pablodip']->getReplyToId(), $messages['barbelith']->getId());
    }


    public function testSaveEventsPreUpdateProcessQueryLater()
    {
        $document = $this->mongator->create('Model\Events');
        $document->setName('foo');
        $document->save();
        $document->setName('bar');
        $document->save();

        $doc = $document->getRepository()->getCollection()->findOne();
        $this->assertSame('preUpdating', $doc['name']);
    }

    public function testSaveResetGroups()
    {
        // insert
        $article = $this->mongator->create('Model\Article')
            ->addCategories($category = $this->mongator->create('Model\Category')
                ->setName('foo')
            )
            ->save()
        ;
        $this->assertSame(0, count($article->getCategories()->getAdd()));

        // update
        $article
             ->addCategories($category = $this->mongator->create('Model\Category')
                ->setName('foo')
            )
            ->save()
        ;
        $this->assertSame(0, count($article->getCategories()->getAdd()));
    }

    public function testDeleteSingleDocument()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = $this->mongator->create('Model\Article')->setTitle('foo');
        }
        $this->mongator->getRepository('Model\Article')->save($articles);

        $id = $articles[2]->getId();
        $this->mongator->getRepository('Model\Article')->delete($articles[2]);

        $this->assertTrue($articles[2]->isNew());
        $this->assertNull($this->mongator->getRepository('Model\Article')->getCollection()->findOne(array('_id' => $id)));
        $this->assertSame(4, $this->mongator->getRepository('Model\Article')->getCollection()->count());
        $this->assertFalse($this->mongator->getRepository('Model\Article')->getIdentityMap()->has($id));
        foreach (array(1, 3, 4, 5) as $key) {
            $this->assertFalse($articles[$key]->isNew());
            $this->assertNotNull($this->mongator->getRepository('Model\Article')->getCollection()->findOne(array('_id' => $articles[$key]->getId())));
            $this->assertTrue($this->mongator->getRepository('Model\Article')->getIdentityMap()->has($articles[$key]->getId()));
        }
    }

    public function testDeleteShouldConvertIdsToMongo()
    {
        $article = $this->create('Model\Article')
            ->setTitle('foo')
            ->save();

        $id = $article->getId();
        $article
            ->setId($id->__toString())
            ->delete();

        $collection = $this->getCollection('Model\Article');
        $result = $collection->findOne(array('_id' => $id));

        $this->assertNull($result);
    }

    public function testDeleteMultipleDocuments()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = $this->mongator->create('Model\Article')->setTitle('foo');
        }
        $this->mongator->getRepository('Model\Article')->save($articles);

        $ids = array($articles[2]->getId(), $articles[3]->getId());
        $this->mongator->getRepository('Model\Article')->delete(array($articles[2], $articles[3]));

        $this->assertTrue($articles[2]->isNew());
        $this->assertTrue($articles[3]->isNew());
        $this->assertSame(0, $this->mongator->getRepository('Model\Article')->getCollection()->find(array('_id' => array('$in' => $ids)))->count());
        $this->assertFalse($this->mongator->getRepository('Model\Article')->getIdentityMap()->has($ids[0]));
        $this->assertFalse($this->mongator->getRepository('Model\Article')->getIdentityMap()->has($ids[1]));
        foreach (array(1, 4, 5) as $key) {
            $this->assertFalse($articles[$key]->isNew());
            $this->assertNotNull($this->mongator->getRepository('Model\Article')->getCollection()->findOne(array('_id' => $articles[$key]->getId())));
            $this->assertTrue($this->mongator->getRepository('Model\Article')->getIdentityMap()->has($articles[$key]->getId()));
        }
    }

    public function testEnsureIndexesMethod()
    {

        $admin = $this->mongator->getRepository('Model\Article')
            ->getMongator()
            ->getDefaultConnection()
            ->getMongo()->admin;

        $admin->command(array(
            'setParameter' => 1,
            'textSearchEnabled' => 1
        ));

        $this->mongator->getRepository('Model\Article')->ensureIndexes();

        $indexInfo = $this->mongator->getRepository('Model\Article')->getCollection()->getIndexInfo();

        // root
        $this->assertSame(array('slug' => 1), $indexInfo[1]['key']);
        $this->assertSame(true, $indexInfo[1]['unique']);
        $this->assertSame(array('authorId' => 1, 'isActive' => 1), $indexInfo[2]['key']);

        // embeddeds one
        $this->assertSame(array('source.name' => 1), $indexInfo[3]['key']);
        $this->assertSame(true, $indexInfo[3]['unique']);
        $this->assertSame(array('source.authorId' => 1, 'source.line' => 1), $indexInfo[4]['key']);

        // embeddeds one deep
        $this->assertSame(array('source.info.note' => 1), $indexInfo[5]['key']);
        $this->assertSame(true, $indexInfo[5]['unique']);
        $this->assertSame(array('source.info.name' => 1, 'source.info.line' => 1), $indexInfo[6]['key']);

        // embeddeds many
        $this->assertSame(array('comments.line' => 1), $indexInfo[7]['key']);
        $this->assertSame(true, $indexInfo[7]['unique']);
        $this->assertSame(array('comments.authorId' => 1, 'comments.note' => 1), $indexInfo[8]['key']);

        // embeddeds many deep
        $this->assertSame(array('comments.infos.note' => 1), $indexInfo[9]['key']);
        $this->assertSame(true, $indexInfo[9]['unique']);
        $this->assertSame(array('comments.infos.name' => 1, 'comments.infos.line' => 1), $indexInfo[10]['key']);
    }

    public function testEnsureIndexesMethodTextIndexes()
    {
        $this->mongator->getRepository('Model\Message')->ensureIndexes();

        $indexInfo = $this->mongator->getRepository('Model\Message')->getCollection()->getIndexInfo();

        $this->assertSame(array('_fts' => 'text', '_ftsx' => 1), $indexInfo[1]['key']);
        $this->assertSame('ExampleTextIndex', $indexInfo[1]['name']);
        $this->assertSame(array('author' => 100, 'text' => 30), $indexInfo[1]['weights']);
    }

    /*
     * Related to Mongator\Repository
     */

    public function testDocumentClass()
    {
        $this->assertSame('Model\Article', $this->mongator->getRepository('Model\Article')->getDocumentClass());
        $this->assertSame('Model\Category', $this->mongator->getRepository('Model\Category')->getDocumentClass());
    }

    public function testIsFile()
    {
        $this->assertFalse($this->mongator->getRepository('Model\Article')->isFile());
        $this->assertTrue($this->mongator->getRepository('Model\Image')->isFile());
    }

    public function testConnectionName()
    {
        $this->assertNull($this->mongator->getRepository('Model\Article')->getConnectionName());
        $this->assertSame('global', $this->mongator->getRepository('Model\ConnectionGlobal')->getConnectionName());
    }

    public function testCollectionName()
    {
        $this->assertSame('articles', $this->mongator->getRepository('Model\Article')->getCollectionName());
        $this->assertSame('model_category', $this->mongator->getRepository('Model\Category')->getCollectionName());
    }
}
